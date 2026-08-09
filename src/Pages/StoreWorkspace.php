<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Data\StoreData;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * One store, and every surface that acts on it.
 *
 * The route parameter is untrusted input: it is authorized on mount and the
 * component is the thing that authorizes it, not the route definition.
 *
 * `channelId` is selectable and therefore re-authorized on every selection —
 * a channel id arriving from the browser says nothing about who may see it.
 */
class StoreWorkspace extends Component
{
    use InteractsWithCommerce;

    #[Locked]
    public int $storeId;

    #[Locked]
    public ?int $channelId = null;

    public function mount(int $storeId): void
    {
        $this->storeId = $storeId;
        $this->guardStore('view', $storeId);
    }

    #[Computed]
    public function store(): StoreData
    {
        return $this->storeData($this->storeId);
    }

    #[On('module-ecommerce-commerce-core.channel-selected')]
    public function selectChannel(?int $channelId): void
    {
        if ($channelId === null) {
            $this->channelId = null;

            return;
        }

        $this->guardChannel('view', $channelId);

        if ($this->channelData($channelId)->storeId !== $this->storeId) {
            abort(404);
        }

        $this->channelId = $channelId;
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.pages.store-workspace');
    }
}
