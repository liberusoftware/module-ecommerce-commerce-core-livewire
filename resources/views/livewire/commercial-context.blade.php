<div>
    <h3>{{ __('module-ecommerce-commerce-core::commerce.context.heading') }}</h3>

    <p role="status" wire:loading data-commerce-loading>
        {{ __('module-ecommerce-commerce-core::commerce.loading') }}
    </p>

    @if ($this->context->isResolved())
        <dl data-commerce-context="resolved">
            <dt>{{ __('module-ecommerce-commerce-core::commerce.context.store') }}</dt>
            <dd>{{ $this->context->storeId }}</dd>

            <dt>{{ __('module-ecommerce-commerce-core::commerce.context.channel') }}</dt>
            <dd>{{ $this->context->channelId ?? '—' }}</dd>

            <dt>{{ __('module-ecommerce-commerce-core::commerce.context.team') }}</dt>
            <dd>{{ $this->context->teamId ?? '—' }}</dd>

            <dt>{{ __('module-ecommerce-commerce-core::commerce.context.currency') }}</dt>
            <dd>{{ $this->context->currency }}</dd>

            <dt>{{ __('module-ecommerce-commerce-core::commerce.context.locale') }}</dt>
            <dd>{{ $this->context->locale }}</dd>

            <dt>{{ __('module-ecommerce-commerce-core::commerce.context.timezone') }}</dt>
            <dd>{{ $this->context->timezone }}</dd>
        </dl>
    @else
        {{-- Unresolved is a state, not an error: a panel, a console command and a
             queued job all have no storefront to resolve. --}}
        <p data-commerce-context="unresolved">
            {{ __('module-ecommerce-commerce-core::commerce.context.unresolved') }}
        </p>
    @endif
</div>
