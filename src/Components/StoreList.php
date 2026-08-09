<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Actions\CreateStore;
use Liberu\Ecommerce\CommerceCore\Data\StoreData;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Liberu\Ecommerce\CommerceCore\Queries\StoreQuery;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The stores the actor's team owns, and the form that creates another.
 *
 * The team comes from the actor, never from a property: a tenant identifier
 * that arrives over the wire is an identifier the browser chose.
 */
class StoreList extends Component
{
    use InteractsWithCommerce;
    use WithPagination;

    /**
     * Page size, bound to the URL so a link can be shared.
     *
     * Untrusted like any other public property — `int` stops the obvious abuse
     * and {@see perPage()} stops the rest, because a shared link asking for
     * 100000 rows is a denial of service with a bookmark.
     */
    #[Url]
    public int $perPage = 25;

    public string $name = '';

    public function mount(): void
    {
        $this->guardStore('viewAny');
    }

    /** @return LengthAwarePaginator<int, StoreData> */
    #[Computed]
    public function stores(): LengthAwarePaginator
    {
        return app(StoreQuery::class)->paginate($this->teamId(), $this->perPage());
    }

    public function create(): void
    {
        $this->guardStore('create');

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $store = app(CreateStore::class)->handle($this->name, $this->teamId());

        $this->name = '';
        $this->resetPage();

        $this->dispatch(
            'module-ecommerce-commerce-core.store-created',
            storeId: (int) $store->getKey(),
        );
    }

    /**
     * Redraw when something else in the composition moved a store.
     *
     * The computed property is not cached across requests, so listening is all
     * this needs to do — the next render reads the new status.
     */
    #[On('module-ecommerce-commerce-core.store-status-changed')]
    public function refreshStores(): void
    {
        //
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.store-list');
    }

    private function perPage(): int
    {
        return max(1, min(100, $this->perPage));
    }
}
