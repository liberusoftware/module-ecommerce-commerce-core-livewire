<?php

use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreSettings;
use Liberu\Ecommerce\CommerceCore\Models\StoreSetting;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('says when a store has no settings yet', function () {
    actor();

    Livewire::test(StoreSettings::class, ['storeId' => storeOwnedBy()->getKey()])
        ->assertSee(__('module-ecommerce-commerce-core::commerce.setting.empty'));
});

it('saves a setting and shows it back', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(StoreSettings::class, ['storeId' => $store->getKey()])
        ->set('key', 'checkout.terms')
        ->set('value', 'v3')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.setting-changed')
        ->assertSet('key', '')
        ->assertSee('checkout.terms')
        ->assertSee('v3');

    expect(StoreSetting::query()->where('store_id', $store->getKey())->where('key', 'checkout.terms')->exists())->toBeTrue();
});

it('rejects a key the domain would have to guess at', function () {
    actor();

    Livewire::test(StoreSettings::class, ['storeId' => storeOwnedBy()->getKey()])
        ->set('key', 'Not A Key!')
        ->set('value', 'v3')
        ->call('save')
        ->assertHasErrors('key');

    expect(StoreSetting::query()->count())->toBe(0);
});

it('forgets a setting', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(StoreSettings::class, ['storeId' => $store->getKey()])
        ->set('key', 'checkout.terms')
        ->set('value', 'v3')
        ->call('save')
        ->call('forget', 'checkout.terms')
        ->assertHasNoErrors()
        ->assertSee(__('module-ecommerce-commerce-core::commerce.setting.empty'));

    expect(StoreSetting::query()->where('store_id', $store->getKey())->count())->toBe(0);
});

it('denies settings on an archived store', function () {
    actor();

    // `manageSettings` follows `update`, and an archived store cannot be updated.
    Livewire::test(StoreSettings::class, ['storeId' => storeOwnedBy(state: 'archived')->getKey()]);
})->throws(HttpException::class);

it('denies settings on another team\'s store', function () {
    actor();

    Livewire::test(StoreSettings::class, ['storeId' => storeOwnedBy(9)->getKey()]);
})->throws(HttpException::class);
