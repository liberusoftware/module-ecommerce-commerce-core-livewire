<?php

use Liberu\Ecommerce\CommerceCore\Livewire\Components\OrderNumbers;
use Livewire\Livewire;

it('allocates the next number in the store\'s sequence', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(OrderNumbers::class, ['storeId' => $store->getKey()])
        ->set('prefix', 'HG/2026')
        ->call('allocate')
        ->assertHasErrors('prefix');

    Livewire::test(OrderNumbers::class, ['storeId' => $store->getKey()])
        ->set('prefix', 'HG')
        ->call('allocate')
        ->assertHasNoErrors()
        ->assertSet('allocated', 'HG000001')
        ->assertDispatched('module-ecommerce-commerce-core.order-number-allocated')
        ->assertSee('HG000001')
        ->call('allocate')
        ->assertSet('allocated', 'HG000002');
});

it('allocates without a prefix', function () {
    actor();

    Livewire::test(OrderNumbers::class, ['storeId' => storeOwnedBy()->getKey()])
        ->call('allocate')
        ->assertSet('allocated', '000001');
});

it('will not allocate against an archived store', function () {
    actor();

    Livewire::test(OrderNumbers::class, ['storeId' => storeOwnedBy(state: 'archived')->getKey()])
        ->call('allocate')
        ->assertForbidden();
});

it('denies allocating against another team\'s store', function () {
    actor();

    Livewire::test(OrderNumbers::class, ['storeId' => storeOwnedBy(9)->getKey()])->assertForbidden();
});
