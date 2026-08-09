<?php

use Livewire\Livewire;

/**
 * The aliases are the package's public interface, so they are asserted by name.
 * Renaming one is a breaking change, and this is where it stops being a silent
 * one.
 */
it('registers every documented component alias', function (string $alias, array $parameters) {
    actor();

    $store = storeOwnedBy();
    $channel = channelOf($store);

    $bound = array_map(
        fn (string $subject): int => (int) ($subject === 'store' ? $store->getKey() : $channel->getKey()),
        $parameters,
    );

    Livewire::test('module-ecommerce-commerce-core::'.$alias, $bound)->assertHasNoErrors();
})->with([
    ['store-list', []],
    ['store-status-control', ['storeId' => 'store']],
    ['store-settings', ['storeId' => 'store']],
    ['store-capabilities', ['storeId' => 'store']],
    ['order-numbers', ['storeId' => 'store']],
    ['channel-list', ['storeId' => 'store']],
    ['channel-status-control', ['channelId' => 'channel']],
    ['channel-domains', ['channelId' => 'channel']],
    ['commercial-context', []],
    ['stores', []],
    ['store-workspace', ['storeId' => 'store']],
]);

it('serves its views and translations from its own namespace', function () {
    expect(view()->exists('module-ecommerce-commerce-core::livewire.store-list'))->toBeTrue()
        ->and(view()->exists('module-ecommerce-commerce-core::livewire.pages.store-workspace'))->toBeTrue()
        ->and(__('module-ecommerce-commerce-core::commerce.store.heading'))->toBe('Stores')
        ->and(__('module-ecommerce-commerce-core::commerce.store.saved'))->toBe('Store saved.');
});
