<?php

use Liberu\Ecommerce\CommerceCore\Enums\StoreStatus;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreStatusControl;
use Liberu\Ecommerce\CommerceCore\Models\Store;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('offers exactly the moves the enum allows', function () {
    actor();

    $store = storeOwnedBy(state: 'draft');

    $component = Livewire::test(StoreStatusControl::class, ['storeId' => $store->getKey()]);

    foreach (StoreStatus::Draft->allowedTransitions() as $transition) {
        $component->assertSee($transition->label());
    }

    // Suspended is not reachable from draft, so it is not offered.
    $component->assertDontSeeHtml("changeTo('suspended')");
});

it('moves a store through an allowed transition', function () {
    actor();

    $store = storeOwnedBy(state: 'draft');

    Livewire::test(StoreStatusControl::class, ['storeId' => $store->getKey()])
        ->call('changeTo', 'active')
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.store-status-changed');

    expect(Store::query()->whereKey($store->getKey())->value('status'))->toBe(StoreStatus::Active);
});

it('turns an illegal transition into a validation message rather than an error page', function () {
    actor();

    $store = storeOwnedBy(state: 'draft');

    Livewire::test(StoreStatusControl::class, ['storeId' => $store->getKey()])
        ->call('changeTo', 'suspended')
        ->assertHasErrors('status')
        ->assertNotDispatched('module-ecommerce-commerce-core.store-status-changed')
        ->assertSee(__('module-ecommerce-commerce-core::commerce.status.illegal', [
            'from' => StoreStatus::Draft->label(),
            'to' => StoreStatus::Suspended->label(),
        ]));

    expect(Store::query()->whereKey($store->getKey())->value('status'))->toBe(StoreStatus::Draft);
});

it('rejects a status this release has never heard of', function () {
    actor();

    $store = storeOwnedBy(state: 'draft');

    Livewire::test(StoreStatusControl::class, ['storeId' => $store->getKey()])
        ->call('changeTo', 'incinerated')
        ->assertHasErrors('status')
        ->assertSee(__('module-ecommerce-commerce-core::commerce.status.unknown'));
});

it('says an archived store has nowhere left to go', function () {
    actor();

    $store = storeOwnedBy(state: 'archived');

    Livewire::test(StoreStatusControl::class, ['storeId' => $store->getKey()])
        ->assertSee(__('module-ecommerce-commerce-core::commerce.status.terminal'));
});

it('denies mounting against another team\'s store', function () {
    actor();

    $theirs = storeOwnedBy(9);

    Livewire::test(StoreStatusControl::class, ['storeId' => $theirs->getKey()]);
})->throws(HttpException::class);

it('refuses to let the browser retarget the component', function () {
    actor();

    $mine = storeOwnedBy(state: 'draft');
    $theirs = storeOwnedBy(9, 'draft');

    Livewire::test(StoreStatusControl::class, ['storeId' => $mine->getKey()])
        ->set('storeId', $theirs->getKey());
})->throws(CannotUpdateLockedPropertyException::class);
