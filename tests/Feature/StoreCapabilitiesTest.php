<?php

use Liberu\Ecommerce\CommerceCore\Enums\Capability;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreCapabilities;
use Liberu\Ecommerce\CommerceCore\Models\StoreCapability;
use Livewire\Livewire;

it('shows every capability the release knows about, off by default', function () {
    actor();

    $component = Livewire::test(StoreCapabilities::class, ['storeId' => storeOwnedBy()->getKey()]);

    foreach (Capability::cases() as $capability) {
        $component->assertSee($capability->label());
    }

    $component->assertSeeHtml('data-commerce-capability-state="off"')
        ->assertDontSeeHtml('data-commerce-capability-state="on"');
});

it('turns a capability on and back off', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(StoreCapabilities::class, ['storeId' => $store->getKey()])
        ->call('toggle', Capability::GuestCheckout->value, true)
        ->assertHasNoErrors()
        ->assertDispatched('module-ecommerce-commerce-core.capability-changed')
        ->assertSeeHtml('data-commerce-capability-state="on"')
        ->call('toggle', Capability::GuestCheckout->value, false)
        ->assertDontSeeHtml('data-commerce-capability-state="on"');

    expect(StoreCapability::query()
        ->where('store_id', $store->getKey())
        ->where('capability', Capability::GuestCheckout->value)
        ->value('enabled'))->toBeFalse();
});

it('rejects a capability this release has never heard of', function () {
    actor();

    Livewire::test(StoreCapabilities::class, ['storeId' => storeOwnedBy()->getKey()])
        ->call('toggle', 'time_travel', true)
        ->assertHasErrors('capability')
        ->assertNotDispatched('module-ecommerce-commerce-core.capability-changed');

    expect(StoreCapability::query()->count())->toBe(0);
});

it('denies capabilities on another team\'s store', function () {
    actor();

    Livewire::test(StoreCapabilities::class, ['storeId' => storeOwnedBy(9)->getKey()])->assertForbidden();
});
