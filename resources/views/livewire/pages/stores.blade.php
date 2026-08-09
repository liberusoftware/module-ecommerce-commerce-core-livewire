<div>
    {{-- A routable page starts at h1; the components it composes start at h2,
         so a screen reader's heading list is the page's outline. --}}
    <h1>{{ __('module-ecommerce-commerce-core::commerce.store.heading') }}</h1>

    <livewire:module-ecommerce-commerce-core::commercial-context />

    <livewire:module-ecommerce-commerce-core::store-list />
</div>
