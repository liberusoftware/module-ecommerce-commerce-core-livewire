{{-- Functional and theme-ready: structure and labels only. A theme publishes
     this view and owns the classes, tokens and layout. What it must not drop
     is the accessible plumbing — the label association, the error wiring and
     the live region are behaviour, not decoration. --}}
<div>
    <h2>{{ __('module-ecommerce-commerce-core::commerce.store.heading') }}</h2>

    <form wire:submit="create">
        <label for="commerce-store-name">{{ __('module-ecommerce-commerce-core::commerce.store.name') }}</label>
        <input
            id="commerce-store-name"
            type="text"
            wire:model="name"
            autocomplete="off"
            @error('name') aria-invalid="true" aria-describedby="commerce-store-name-error" @enderror
        >

        @error('name')
            <p id="commerce-store-name-error" role="alert" data-commerce-error="name">{{ $message }}</p>
        @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="create">
            {{ __('module-ecommerce-commerce-core::commerce.store.create') }}
        </button>
    </form>

    {{-- One live region per component: what it is doing now, and what it just
         did. A `wire:loading` that only disables a button is invisible to a
         screen reader, and so is a row that quietly appears in the list. --}}
    <p role="status" aria-live="polite">
        <span wire:loading data-commerce-loading>{{ __('module-ecommerce-commerce-core::commerce.loading') }}</span>
        <span data-commerce-announcement>{{ $announcement }}</span>
    </p>

    @forelse ($this->stores as $store)
        <article wire:key="commerce-store-{{ $store->id }}" data-commerce-store="{{ $store->id }}">
            <h3>{{ $store->name }}</h3>

            <dl>
                <dt>{{ __('module-ecommerce-commerce-core::commerce.store.slug') }}</dt>
                <dd>{{ $store->slug }}</dd>

                <dt>{{ __('module-ecommerce-commerce-core::commerce.status.heading') }}</dt>
                <dd>{{ $store->status->label() }}</dd>

                <dt>{{ __('module-ecommerce-commerce-core::commerce.store.currency') }}</dt>
                <dd>{{ $store->currency }}</dd>

                <dt>{{ __('module-ecommerce-commerce-core::commerce.store.locale') }}</dt>
                <dd>{{ $store->locale }}</dd>
            </dl>
        </article>
    @empty
        <p data-commerce-empty>{{ __('module-ecommerce-commerce-core::commerce.store.empty') }}</p>
    @endforelse

    {{-- A hand-rolled pager rather than $paginator->links(): the package ships
         no styling, and the two controls it needs are two buttons. They are
         buttons and not glyphs in a span so that they are tab-reachable and
         Enter/Space-operable, and each carries a name of its own because
         "«" is not one. --}}
    @if ($this->stores->hasPages())
        <nav aria-label="{{ __('module-ecommerce-commerce-core::commerce.store.heading') }}">
            <button
                type="button"
                wire:click="previousPage"
                aria-label="{{ __('module-ecommerce-commerce-core::commerce.pagination.previous') }}"
                @disabled($this->stores->onFirstPage())
            >
                <span aria-hidden="true">&laquo;</span>
            </button>

            <button
                type="button"
                wire:click="nextPage"
                aria-label="{{ __('module-ecommerce-commerce-core::commerce.pagination.next') }}"
                @disabled(! $this->stores->hasMorePages())
            >
                <span aria-hidden="true">&raquo;</span>
            </button>
        </nav>
    @endif
</div>
