<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire;

use Illuminate\Support\ServiceProvider;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelDomains;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelList;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\ChannelStatusControl;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\CommercialContextPanel;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\OrderNumbers;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreCapabilities;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreList;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreSettings;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\StoreStatusControl;
use Liberu\Ecommerce\CommerceCore\Livewire\Pages\Stores;
use Liberu\Ecommerce\CommerceCore\Livewire\Pages\StoreWorkspace;
use Livewire\Livewire;

/**
 * Registers this package's bounded Livewire namespace.
 *
 * Aliases are explicit rather than discovered. A directory scan resolves
 * whatever happens to be on disk, so moving a class or adding one silently
 * changes a public interface; this list is the interface, and changing it is a
 * diff somebody reviews.
 */
class CommerceCoreLivewireServiceProvider extends ServiceProvider
{
    /**
     * The one namespace this package owns, for components, views and
     * translations alike. It drops the `-livewire` suffix and keeps the
     * ownership prefix, per LIVEWIRE.md §3.
     */
    public const NAMESPACE = 'module-ecommerce-commerce-core';

    /**
     * The package's public component surface.
     *
     * @var array<string, class-string>
     */
    private const COMPONENTS = [
        'store-list' => StoreList::class,
        'store-status-control' => StoreStatusControl::class,
        'store-settings' => StoreSettings::class,
        'store-capabilities' => StoreCapabilities::class,
        'order-numbers' => OrderNumbers::class,
        'channel-list' => ChannelList::class,
        'channel-status-control' => ChannelStatusControl::class,
        'channel-domains' => ChannelDomains::class,
        'commercial-context' => CommercialContextPanel::class,
        'stores' => Stores::class,
        'store-workspace' => StoreWorkspace::class,
    ];

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', self::NAMESPACE);
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', self::NAMESPACE);

        foreach (self::COMPONENTS as $alias => $component) {
            Livewire::component(self::NAMESPACE.'::'.$alias, $component);
        }

        // Publishing views is how a theme overrides one without forking the
        // package. Translations publish separately because a deployment that
        // wants its own wording rarely wants its own markup as well.
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/'.self::NAMESPACE),
        ], self::NAMESPACE.'-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/'.self::NAMESPACE),
        ], self::NAMESPACE.'-translations');
    }
}
