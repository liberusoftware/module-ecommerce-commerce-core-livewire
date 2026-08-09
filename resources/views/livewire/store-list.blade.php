{{-- Functional and theme-ready: structure and labels only. A theme publishes
     this view and owns the classes, tokens and layout. --}}
<div>
    <h2>{{ __('module-ecommerce-commerce-core::commerce.store.heading') }}</h2>

    <form wire:submit="create">
        <label for="commerce-store-name">{{ __('module-ecommerce-commerce-core::commerce.store.name') }}</label>
        <input id="commerce-store-name" type="text" wire:model="name" autocomplete="off">

        @error('name')
            <p role="alert" data-commerce-error="name">{{ $message }}</p>
        @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="create">
            {{ __('module-ecommerce-commerce-core::commerce.store.create') }}
        </button>
    </form>

    <p role="status" wire:loading data-commerce-loading>
        {{ __('module-ecommerce-commerce-core::commerce.loading') }}
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
         no styling, and the two controls it needs are two buttons. --}}
    @if ($this->stores->hasPages())
        <nav aria-label="{{ __('module-ecommerce-commerce-core::commerce.store.heading') }}">
            <button type="button" wire:click="previousPage" @disabled($this->stores->onFirstPage())>&laquo;</button>
            <button type="button" wire:click="nextPage" @disabled(! $this->stores->hasMorePages())>&raquo;</button>
        </nav>
    @endif
</div>
