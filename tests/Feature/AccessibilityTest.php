<?php

use Liberu\Ecommerce\CommerceCore\Enums\Capability;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelDomains;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelList;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\OrderNumbers;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreCapabilities;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreList;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreSettings;
use Liberu\Ecommerce\CommerceCore\Livewire\Pages\StoreWorkspace;
use Liberu\Ecommerce\CommerceCore\Models\ChannelDomain;
use Liberu\Ecommerce\CommerceCore\Models\Store;
use Livewire\Livewire;

/**
 * Accessibility that is assertable from rendered output.
 *
 * These are the parts a theme can break by accident — a label detached from its
 * field, an error nobody is told about, a button whose only name is an arrow.
 * A published view that drops them fails here rather than in production.
 */
it('gives every field it renders a label of its own', function (string $component, array $parameters) {
    actor();

    $store = storeOwnedBy();
    $channel = channelOf($store);

    $bound = array_map(
        fn (string $subject): int => (int) ($subject === 'store' ? $store->getKey() : $channel->getKey()),
        $parameters,
    );

    expectEveryFieldToBeLabelled(Livewire::test($component, $bound)->html());
})->with([
    [StoreList::class, []],
    [ChannelList::class, ['storeId' => 'store']],
    [StoreSettings::class, ['storeId' => 'store']],
    [OrderNumbers::class, ['storeId' => 'store']],
    [ChannelDomains::class, ['channelId' => 'channel']],
]);

it('marks an invalid field invalid and points it at its own error', function () {
    actor();

    Livewire::test(StoreList::class)
        ->set('name', 'x')
        ->call('create')
        ->assertSeeHtml('aria-invalid="true"')
        ->assertSeeHtml('aria-describedby="commerce-store-name-error"')
        ->assertSeeHtml('id="commerce-store-name-error"');
});

it('leaves a field that is not invalid unmarked', function () {
    actor();

    Livewire::test(StoreList::class)->assertDontSeeHtml('aria-invalid');
});

it('associates each field with its own error and no other', function () {
    actor();

    Livewire::test(StoreSettings::class, ['storeId' => storeOwnedBy()->getKey()])
        ->set('key', 'Not A Key!')
        ->set('value', 'v3')
        ->call('save')
        ->assertSeeHtml('aria-describedby="commerce-setting-key-error"')
        ->assertDontSeeHtml('aria-describedby="commerce-setting-value-error"');
});

it('announces what an action did, not only that it finished', function () {
    actor();

    Livewire::test(StoreList::class)
        ->set('name', 'Harbour Goods')
        ->call('create')
        ->assertSet('announcement', 'Store Harbour Goods created.')
        ->assertSeeHtml('data-commerce-announcement')
        ->assertSee('Store Harbour Goods created.');
});

it('announces a hostname claim being accepted and a status moving', function () {
    actor();

    $channel = channelOf(storeOwnedBy());

    Livewire::test(ChannelDomains::class, ['channelId' => $channel->getKey()])
        ->set('host', 'Shop.Example.COM')
        ->call('add')
        ->assertSee(__('module-ecommerce-commerce-core::commerce.domain.added', ['host' => 'shop.example.com']));

    $domain = ChannelDomain::query()->where('host', 'shop.example.com')->firstOrFail();

    Livewire::test(ChannelDomains::class, ['channelId' => $channel->getKey()])
        ->call('remove', $domain->getKey())
        ->assertSee(__('module-ecommerce-commerce-core::commerce.domain.removed', ['host' => 'shop.example.com']));
});

it('announces a capability toggle in words rather than a changed colour', function () {
    actor();

    Livewire::test(StoreCapabilities::class, ['storeId' => storeOwnedBy()->getKey()])
        ->assertSee(__('module-ecommerce-commerce-core::commerce.capability.off'))
        ->call('toggle', Capability::GuestCheckout->value, true)
        ->assertSee(__('module-ecommerce-commerce-core::commerce.capability.turned_on', [
            'capability' => Capability::GuestCheckout->label(),
        ]))
        ->assertSee(__('module-ecommerce-commerce-core::commerce.capability.on'));
});

it('announces that a channel is now the one being managed', function () {
    actor();

    $store = storeOwnedBy();
    $channel = channelOf($store);

    Livewire::test(StoreWorkspace::class, ['storeId' => $store->getKey()])
        ->call('selectChannel', $channel->getKey())
        ->assertSee(__('module-ecommerce-commerce-core::commerce.channel.now_managing', ['name' => $channel->name]))
        ->call('selectChannel', null)
        ->assertSee(__('module-ecommerce-commerce-core::commerce.channel.deselected'));
});

it('puts the loading state inside a live region rather than beside it', function () {
    actor();

    expect(Livewire::test(StoreList::class)->html())
        ->toMatch('/<p role="status" aria-live="polite">\s*<span wire:loading/');
});

it('names the pager buttons, whose visible content is an arrow', function () {
    actor();

    Store::factory()->count(2)->ownedBy(TEAM)->create();

    Livewire::test(StoreList::class)
        ->set('perPage', 1)
        ->assertSeeHtml('aria-label="'.__('module-ecommerce-commerce-core::commerce.pagination.next').'"')
        ->assertSeeHtml('aria-label="'.__('module-ecommerce-commerce-core::commerce.pagination.previous').'"')
        ->assertSeeHtml('aria-hidden="true"');
});

it('names each repeated row control after the row it acts on', function () {
    actor();

    $store = storeOwnedBy();

    Livewire::test(StoreSettings::class, ['storeId' => $store->getKey()])
        ->set('key', 'checkout.terms')
        ->set('value', 'v3')
        ->call('save')
        ->assertSeeHtml('aria-label="'.__('module-ecommerce-commerce-core::commerce.setting.forget').': checkout.terms"');
});

it('keys every repeated row so focus survives a re-render', function () {
    actor();

    $store = storeOwnedBy();

    channelOf($store);

    Livewire::test(ChannelList::class, ['storeId' => $store->getKey()])
        ->assertSeeHtml('wire:key="commerce-channel-');
});
