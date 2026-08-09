<?php

use Liberu\Ecommerce\CommerceCore\Livewire\Pages\Stores;
use Liberu\Ecommerce\CommerceCore\Livewire\Pages\StoreWorkspace;
use Livewire\Exceptions\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('composes the stores index from the package\'s own components', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(Stores::class)
        ->assertSee($store->name)
        ->assertSee(__('module-ecommerce-commerce-core::commerce.context.heading'));
});

it('denies the stores index to an actor with no team', function () {
    teamlessActor();

    Livewire::test(Stores::class);
})->throws(HttpException::class);

it('composes one store\'s workspace', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(StoreWorkspace::class, ['storeId' => $store->getKey()])
        ->assertSee($store->name)
        ->assertSee(__('module-ecommerce-commerce-core::commerce.setting.heading'))
        ->assertSee(__('module-ecommerce-commerce-core::commerce.capability.heading'))
        ->assertSee(__('module-ecommerce-commerce-core::commerce.order_number.heading'))
        ->assertSee(__('module-ecommerce-commerce-core::commerce.channel.heading'))
        ->assertSee(__('module-ecommerce-commerce-core::commerce.channel.none_selected'));
});

it('selects a channel and drops it again', function () {
    actor();

    $store = storeOwnedBy();
    $channel = channelOf($store);

    Livewire::test(StoreWorkspace::class, ['storeId' => $store->getKey()])
        ->call('selectChannel', $channel->getKey())
        ->assertSet('channelId', $channel->getKey())
        ->assertSee(__('module-ecommerce-commerce-core::commerce.domain.heading'))
        ->call('selectChannel', null)
        ->assertSet('channelId', null);
});

it('refuses a channel belonging to another store', function () {
    actor();

    $store = storeOwnedBy();
    $elsewhere = channelOf(storeOwnedBy());

    Livewire::test(StoreWorkspace::class, ['storeId' => $store->getKey()])
        ->call('selectChannel', $elsewhere->getKey());
})->throws(HttpException::class);

it('refuses a channel belonging to another team', function () {
    actor();

    $store = storeOwnedBy();
    $theirs = channelOf(storeOwnedBy(9));

    Livewire::test(StoreWorkspace::class, ['storeId' => $store->getKey()])
        ->call('selectChannel', $theirs->getKey());
})->throws(HttpException::class);

it('denies a workspace on another team\'s store', function () {
    actor();

    Livewire::test(StoreWorkspace::class, ['storeId' => storeOwnedBy(9)->getKey()]);
})->throws(HttpException::class);

it('will not let the browser point a workspace at another store', function () {
    actor();

    Livewire::test(StoreWorkspace::class, ['storeId' => storeOwnedBy()->getKey()])
        ->set('storeId', storeOwnedBy(9)->getKey());
})->throws(CannotUpdateLockedPropertyException::class);
