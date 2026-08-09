<?php

use Liberu\Ecommerce\CommerceCore\Enums\ChannelStatus;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelList;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelStatusControl;
use Liberu\Ecommerce\CommerceCore\Models\Channel;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('lists a store\'s channels and says when there are none', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(ChannelList::class, ['storeId' => $store->getKey()])
        ->assertSee(__('module-ecommerce-commerce-core::commerce.channel.empty'));

    $channel = channelOf($store);

    Livewire::test(ChannelList::class, ['storeId' => $store->getKey()])
        ->assertSee($channel->name)
        ->assertSee(__('module-ecommerce-commerce-core::commerce.channel.no_primary_host'));
});

it('creates a channel as an update to its store', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(ChannelList::class, ['storeId' => $store->getKey()])
        ->set('name', 'Marketplace')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.channel-created')
        ->assertSet('name', '');

    expect(Channel::query()->where('name', 'Marketplace')->value('store_id'))->toBe($store->getKey());
});

it('validates the channel form before it reaches the domain', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(ChannelList::class, ['storeId' => $store->getKey()])
        ->set('name', '')
        ->set('theme', '')
        ->call('create')
        ->assertHasErrors(['name', 'theme']);

    expect(Channel::query()->count())->toBe(0);
});

it('refuses to create a channel on an archived store', function () {
    actor();

    // `update` is denied once a store is archived, and creating a channel is an
    // update to the store rather than a create on the channel.
    $store = storeOwnedBy(state: 'archived');

    Livewire::test(ChannelList::class, ['storeId' => $store->getKey()])
        ->set('name', 'Marketplace')
        ->call('create');
})->throws(HttpException::class);

it('denies listing another team\'s channels', function () {
    actor();

    Livewire::test(ChannelList::class, ['storeId' => storeOwnedBy(9)->getKey()]);
})->throws(HttpException::class);

it('moves a channel through an allowed transition', function () {
    actor();

    $channel = channelOf(storeOwnedBy(), 'draft');

    Livewire::test(ChannelStatusControl::class, ['channelId' => $channel->getKey()])
        ->assertSee(ChannelStatus::Active->label())
        ->call('changeTo', 'active')
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.channel-status-changed');

    expect(Channel::query()->whereKey($channel->getKey())->value('status'))->toBe(ChannelStatus::Active);
});

it('turns an illegal channel transition into a validation message', function () {
    actor();

    $channel = channelOf(storeOwnedBy(), 'draft');

    Livewire::test(ChannelStatusControl::class, ['channelId' => $channel->getKey()])
        ->call('changeTo', 'disabled')
        ->assertHasErrors('status')
        ->assertNotDispatched('module-ecommerce-commerce-core.channel-status-changed');

    expect(Channel::query()->whereKey($channel->getKey())->value('status'))->toBe(ChannelStatus::Draft);
});

it('rejects a channel status this release has never heard of', function () {
    actor();

    $channel = channelOf(storeOwnedBy(), 'draft');

    Livewire::test(ChannelStatusControl::class, ['channelId' => $channel->getKey()])
        ->call('changeTo', 'mothballed')
        ->assertHasErrors('status');
});

it('denies a channel belonging to another team', function () {
    actor();

    $channel = channelOf(storeOwnedBy(9));

    Livewire::test(ChannelStatusControl::class, ['channelId' => $channel->getKey()]);
})->throws(HttpException::class);
