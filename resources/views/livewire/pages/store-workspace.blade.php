<div>
    <h1>{{ $this->store->name }}</h1>

    {{-- Selecting a channel adds a whole section further down the page. The
         page says so here, because nothing else does. --}}
    <p role="status" aria-live="polite">
        <span data-commerce-announcement>{{ $announcement }}</span>
    </p>

    <livewire:module-ecommerce-commerce-core::store-status-control :store-id="$storeId" />

    <livewire:module-ecommerce-commerce-core::store-settings :store-id="$storeId" />

    <livewire:module-ecommerce-commerce-core::store-capabilities :store-id="$storeId" />

    <livewire:module-ecommerce-commerce-core::order-numbers :store-id="$storeId" />

    <livewire:module-ecommerce-commerce-core::channel-list :store-id="$storeId" />

    @if ($channelId === null)
        <p data-commerce-no-channel>
            {{ __('module-ecommerce-commerce-core::commerce.channel.none_selected') }}
        </p>
    @else
        <section wire:key="commerce-selected-channel-{{ $channelId }}">
            <h2>{{ __('module-ecommerce-commerce-core::commerce.channel.selected') }}</h2>

            <livewire:module-ecommerce-commerce-core::channel-status-control :channel-id="$channelId" />

            <livewire:module-ecommerce-commerce-core::channel-domains :channel-id="$channelId" />

            <button type="button" wire:click="selectChannel(null)">
                {{ __('module-ecommerce-commerce-core::commerce.channel.clear_selection') }}
            </button>
        </section>
    @endif
</div>
