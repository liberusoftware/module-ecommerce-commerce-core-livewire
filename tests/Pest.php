<?php

use Liberu\Ecommerce\CommerceCore\Models\Channel;
use Liberu\Ecommerce\CommerceCore\Models\Store;
use Liberu\PackageTestbench\PackageTestCase;
use Liberu\PackageTestbench\TestUser;
use Liberu\PackageTestbench\UsesTestUser;

uses(PackageTestCase::class, UsesTestUser::class)->in(__DIR__);

/**
 * The team every fixture belongs to unless a test says otherwise.
 */
const TEAM = 7;

/**
 * An authenticated actor working in a team.
 *
 * `current_team_id` is set in memory rather than persisted: the column belongs
 * to the host's users table, which a package under test does not own, and the
 * policies read the property rather than the column.
 */
function actor(int $teamId = TEAM): TestUser
{
    $user = TestUser::factory()->create();
    $user->current_team_id = $teamId;

    test()->actingAs($user);

    return $user;
}

/**
 * An actor whose account is not attached to any team.
 *
 * Not `actor(0)`: zero is a team identifier, and the domain's policies read the
 * property's absence rather than its falsiness.
 */
function teamlessActor(): TestUser
{
    $user = TestUser::factory()->create();

    test()->actingAs($user);

    return $user;
}

function storeOwnedBy(int $teamId = TEAM, string $state = 'active'): Store
{
    return Store::factory()->{$state}()->ownedBy($teamId)->create();
}

function channelOf(Store $store, string $state = 'active'): Channel
{
    return Channel::factory()->{$state}()->create(['store_id' => $store->getKey()]);
}

/**
 * Every field a component renders has a real label pointing at it.
 *
 * A placeholder is not a label: it disappears on the first keystroke and screen
 * readers are not obliged to read it. This walks the rendered markup rather
 * than trusting a per-view assertion, so a field added later without a label
 * fails here.
 */
function expectEveryFieldToBeLabelled(string $html): void
{
    preg_match_all('/<input\b[^>]*>/i', $html, $inputs);

    expect($inputs[0])->not->toBeEmpty();

    foreach ($inputs[0] as $input) {
        expect($input)->toMatch('/\sid="[^"]+"/');

        preg_match('/\sid="([^"]+)"/', $input, $id);

        expect($html)->toContain('for="'.$id[1].'"');
    }
}
