<?php

use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelDomains;
use Liberu\Ecommerce\CommerceCore\Models\ChannelDomain;
use Livewire\Livewire;

it('says when a channel answers on nothing yet', function () {
    actor();

    $channel = channelOf(storeOwnedBy());

    Livewire::test(ChannelDomains::class, ['channelId' => $channel->getKey()])
        ->assertSee(__('module-ecommerce-commerce-core::commerce.domain.empty'));
});

it('adds a hostname and makes the first one primary', function () {
    actor();

    $channel = channelOf(storeOwnedBy());

    Livewire::test(ChannelDomains::class, ['channelId' => $channel->getKey()])
        ->set('host', 'Shop.Example.COM')
        ->call('add')
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.domain-added')
        ->assertSet('host', '')
        ->assertSee('shop.example.com');

    expect(ChannelDomain::query()->where('host', 'shop.example.com')->value('is_primary'))->toBeTrue();
});

it('shows a hostname another channel already claimed as a validation message', function () {
    actor();

    $mine = channelOf(storeOwnedBy());
    $theirs = channelOf(storeOwnedBy(9));

    ChannelDomain::factory()->create(['channel_id' => $theirs->getKey(), 'host' => 'shop.example.com']);

    Livewire::test(ChannelDomains::class, ['channelId' => $mine->getKey()])
        ->set('host', 'shop.example.com')
        ->call('add')
        ->assertHasErrors('host')
        ->assertNotDispatched('module-ecommerce-commerce-core.domain-added')
        ->assertSee(__('module-ecommerce-commerce-core::commerce.domain.claimed', ['host' => 'shop.example.com']));

    expect(ChannelDomain::query()->where('channel_id', $mine->getKey())->count())->toBe(0);
});

it('rejects a hostname that is not one', function () {
    actor();

    $channel = channelOf(storeOwnedBy());

    Livewire::test(ChannelDomains::class, ['channelId' => $channel->getKey()])
        ->set('host', 'not a host/at all')
        ->call('add')
        ->assertHasErrors('host');
});

it('promotes another hostname to primary', function () {
    actor();

    $channel = channelOf(storeOwnedBy());

    $first = ChannelDomain::factory()->primary()->create(['channel_id' => $channel->getKey(), 'host' => 'one.example.com']);
    $second = ChannelDomain::factory()->create(['channel_id' => $channel->getKey(), 'host' => 'two.example.com']);

    Livewire::test(ChannelDomains::class, ['channelId' => $channel->getKey()])
        ->call('promote', $second->getKey())
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.primary-domain-changed');

    expect(ChannelDomain::query()->whereKey($second->getKey())->value('is_primary'))->toBeTrue()
        ->and(ChannelDomain::query()->whereKey($first->getKey())->value('is_primary'))->toBeFalse();
});

it('lets the primary hostname be removed and promotes the survivor', function () {
    actor();

    $channel = channelOf(storeOwnedBy());

    $primary = ChannelDomain::factory()->primary()->create(['channel_id' => $channel->getKey(), 'host' => 'one.example.com']);
    $survivor = ChannelDomain::factory()->create(['channel_id' => $channel->getKey(), 'host' => 'two.example.com']);

    Livewire::test(ChannelDomains::class, ['channelId' => $channel->getKey()])
        ->call('remove', $primary->getKey())
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.domain-removed')
        // The row is gone; the hostname is still on the page, because the live
        // region is saying which one was removed.
        ->assertDontSeeHtml('data-commerce-domain="one.example.com"')
        ->assertSee(__('module-ecommerce-commerce-core::commerce.domain.removed', ['host' => 'one.example.com']));

    expect(ChannelDomain::query()->whereKey($primary->getKey())->exists())->toBeFalse()
        ->and(ChannelDomain::query()->whereKey($survivor->getKey())->value('is_primary'))->toBeTrue();
});

it('will not act on a hostname belonging to somebody else\'s channel', function () {
    actor();

    $mine = channelOf(storeOwnedBy());
    $theirs = channelOf(storeOwnedBy(9));

    $stranger = ChannelDomain::factory()->create(['channel_id' => $theirs->getKey(), 'host' => 'stranger.example.com']);

    Livewire::test(ChannelDomains::class, ['channelId' => $mine->getKey()])
        ->call('remove', $stranger->getKey())
        ->assertNotFound();
});

it('denies managing the hostnames of another team\'s channel', function () {
    actor();

    $theirs = channelOf(storeOwnedBy(9));

    Livewire::test(ChannelDomains::class, ['channelId' => $theirs->getKey()])->assertForbidden();
});
