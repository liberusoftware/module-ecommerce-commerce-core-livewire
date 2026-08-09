<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Actions\AllocateOrderNumber;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Allocating an order number from a store's sequence.
 *
 * Allocation is a write and not idempotent — every call consumes a number — so
 * the component holds only the last one it was given, and the operator sees it
 * once. Nothing here reserves, retries or reuses; the sequence is the authority.
 */
class OrderNumbers extends Component
{
    use InteractsWithCommerce;

    #[Locked]
    public int $storeId;

    public string $prefix = '';

    /**
     * The number this component last allocated.
     *
     * Locked: it is written by the sequence and read by the operator, so a
     * value arriving from the browser could only ever be a lie about which
     * number was consumed.
     */
    #[Locked]
    public ?string $allocated = null;

    public function mount(int $storeId): void
    {
        $this->storeId = $storeId;
        $this->guardStore('view', $storeId);
    }

    public function allocate(): void
    {
        $this->guardStore('update', $this->storeId);

        $this->validate([
            'prefix' => ['nullable', 'string', 'max:16', 'regex:/^[A-Za-z0-9-]*$/'],
        ]);

        $this->allocated = app(AllocateOrderNumber::class)->handle(
            $this->storeModel($this->storeId),
            $this->prefix,
        );

        $this->dispatch(
            'module-ecommerce-commerce-core.order-number-allocated',
            storeId: $this->storeId,
            number: $this->allocated,
        );
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.order-numbers');
    }
}
