<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The routable index of stores.
 *
 * It declares no layout. The application composing this package owns layouts,
 * routes and navigation (LIVEWIRE.md §10), and a package naming a layout view
 * would only install into an application that happens to have one by that name.
 *
 * It authorizes for itself rather than relying on the route it is mounted on:
 * navigation visibility is not authorization.
 */
class Stores extends Component
{
    use InteractsWithCommerce;

    public function mount(): void
    {
        $this->guardStore('viewAny');
    }

    #[Title('Stores')]
    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.pages.stores');
    }
}
