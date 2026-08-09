<?php

use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreList;
use Liberu\Ecommerce\CommerceCore\Models\Store;
use Livewire\Livewire;

it('lists only the stores the actor\'s team owns', function () {
    actor();

    $mine = storeOwnedBy();
    $theirs = storeOwnedBy(9);

    Livewire::test(StoreList::class)
        ->assertSee($mine->name)
        ->assertDontSee($theirs->name);
});

it('says so when the team owns nothing yet', function () {
    actor();

    Livewire::test(StoreList::class)
        ->assertSee(__('module-ecommerce-commerce-core::commerce.store.empty'));
});

it('shows a loading state while it works', function () {
    actor();

    Livewire::test(StoreList::class)
        ->assertSeeHtml('wire:loading')
        ->assertSee(__('module-ecommerce-commerce-core::commerce.loading'));
});

it('creates a store in the actor\'s own team', function () {
    actor();

    Livewire::test(StoreList::class)
        ->set('name', 'Harbour Goods')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.store-created')
        ->assertSet('name', '');

    expect(Store::query()->where('name', 'Harbour Goods')->value('team_id'))->toBe(TEAM);
});

it('refuses a name the domain would not accept', function () {
    actor();

    Livewire::test(StoreList::class)
        ->set('name', 'x')
        ->call('create')
        ->assertHasErrors(['name' => 'min']);

    expect(Store::query()->count())->toBe(0);
});

it('denies an actor with no team at all', function () {
    teamlessActor();

    Livewire::test(StoreList::class)->assertForbidden();
});

it('denies an actor who is not signed in', function () {
    Livewire::test(StoreList::class)->assertForbidden();
});

it('bounds a page size arriving from the URL', function () {
    actor();

    $first = Store::factory()->ownedBy(TEAM)->create(['name' => 'Aardvark Supplies']);
    $second = Store::factory()->ownedBy(TEAM)->create(['name' => 'Zebra Supplies']);

    // A shared link asking for one row gets one row; asking for a million gets
    // a hundred. Neither number is trusted because neither is ours.
    Livewire::test(StoreList::class)
        ->set('perPage', 1)
        ->assertSee($first->name)
        ->assertDontSee($second->name)
        ->set('perPage', 1_000_000)
        ->assertSee($first->name)
        ->assertSee($second->name);
});

it('offers a pager only when there is another page', function () {
    actor();

    Store::factory()->count(2)->ownedBy(TEAM)->create();

    Livewire::test(StoreList::class)
        ->set('perPage', 1)
        ->call('nextPage')
        ->assertHasNoErrors()
        ->call('previousPage')
        ->assertHasNoErrors();
});

it('redraws when another component moves a store', function () {
    actor();

    storeOwnedBy();

    Livewire::test(StoreList::class)
        ->call('refreshStores')
        ->assertHasNoErrors();
});
