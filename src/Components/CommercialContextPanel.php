<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Contracts\ResolvesCommercialContext;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Liberu\Ecommerce\CommerceCore\Values\CommercialContext;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * What the current request resolves to commercially.
 *
 * Read only, and read through the contract rather than the resolver, so a host
 * that resolves context differently — a console command, a job, a test — is
 * presented by the same component.
 *
 * Unresolved is a state, not a failure: off a storefront there is no channel to
 * resolve, and saying so is more useful than an empty panel.
 */
class CommercialContextPanel extends Component
{
    use InteractsWithCommerce;

    public function mount(): void
    {
        $this->guardStore('viewAny');
    }

    #[Computed]
    public function context(): CommercialContext
    {
        return app(ResolvesCommercialContext::class)->current();
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.commercial-context');
    }
}
