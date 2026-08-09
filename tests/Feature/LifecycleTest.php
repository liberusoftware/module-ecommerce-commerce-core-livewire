<?php

use Liberu\Ecommerce\CommerceCore\Livewire\CommerceCoreLivewireServiceProvider;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelDomains;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreList;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreSettings;
use Liberu\Ecommerce\CommerceCore\Models\ChannelDomain;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

/**
 * What a component carries between requests, and who may change it.
 *
 * Public properties survive a re-render because they travel to the browser and
 * back, which makes every one of them an input. This is the inventory of which
 * are the browser's to set — asserted rather than described, because the
 * description is the thing that rots.
 */
it('binds exactly one property to the URL, and bounds it', function () {
    $bound = [];

    foreach ((new CommerceCoreLivewireServiceProvider(app()))->aliases() as $component) {
        foreach ((new ReflectionClass($component))->getProperties() as $property) {
            if ($property->getAttributes(Url::class) !== []) {
                $bound[] = $component.'::$'.$property->getName();
            }
        }
    }

    // A URL-bound property is the most exposed input a component has: it
    // arrives from a link somebody else wrote. There is one, and StoreListTest
    // holds it to a hundred rows.
    // `WithPagination`'s own page state is Livewire's URL surface, not this
    // package's; what this asserts is the surface this package chose to expose.
    $bound = array_values(array_filter($bound, fn (string $property): bool => ! str_ends_with($property, '::$paginators')));

    expect($bound)->toBe([StoreList::class.'::$perPage']);
});

it('locks every identifier and every announcement against the browser', function () {
    $unlocked = [];

    foreach ((new CommerceCoreLivewireServiceProvider(app()))->aliases() as $component) {
        foreach ((new ReflectionClass($component))->getProperties() as $property) {
            $shouldLock = in_array($property->getName(), ['storeId', 'channelId', 'announcement', 'allocated'], true);

            if ($shouldLock && $property->getAttributes(Locked::class) === []) {
                $unlocked[] = $component.'::$'.$property->getName();
            }
        }
    }

    expect($unlocked)->toBe([]);
});

it('will not let the browser put words in the live region', function () {
    actor();

    Livewire::test(StoreList::class)->set('announcement', 'Your session has expired. Sign in again at…');
})->throws(CannotUpdateLockedPropertyException::class);

it('announces for exactly one render and then stops', function () {
    actor();

    // Otherwise the next unrelated request re-announces a sentence about
    // something that happened two clicks ago.
    Livewire::test(StoreList::class)
        ->set('name', 'Harbour Goods')
        ->call('create')
        ->assertSet('announcement', 'Store Harbour Goods created.')
        ->call('refreshStores')
        ->assertSet('announcement', '');
});

it('denies an action against a store deleted since the component mounted', function () {
    actor();

    $store = storeOwnedBy();

    $component = Livewire::test(StoreSettings::class, ['storeId' => $store->getKey()]);

    $store->delete();

    // The guard runs per action, not once at mount, so the subject vanishing
    // between the two is a denial rather than an unhandled model-not-found.
    $component
        ->set('key', 'checkout.terms')
        ->set('value', 'v3')
        ->call('save')
        ->assertForbidden();
});

it('refuses a row that vanished between the render and the click', function () {
    actor();

    $channel = channelOf(storeOwnedBy());

    $domain = ChannelDomain::factory()->primary()->create([
        'channel_id' => $channel->getKey(),
        'host' => 'one.example.com',
    ]);

    $component = Livewire::test(ChannelDomains::class, ['channelId' => $channel->getKey()]);

    $domain->delete();

    $component->call('remove', $domain->getKey())->assertNotFound();
});
