<div>
    <h3>{{ __('module-ecommerce-commerce-core::commerce.domain.heading') }}</h3>

    <form wire:submit="add">
        <label for="commerce-domain-host">{{ __('module-ecommerce-commerce-core::commerce.domain.host') }}</label>
        <input id="commerce-domain-host" type="text" wire:model="host" autocomplete="off" inputmode="url">

        <label for="commerce-domain-primary">
            <input id="commerce-domain-primary" type="checkbox" wire:model="primary">
            {{ __('module-ecommerce-commerce-core::commerce.domain.primary') }}
        </label>

        @error('host')
            <p role="alert" data-commerce-error="host">{{ $message }}</p>
        @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="add">
            {{ __('module-ecommerce-commerce-core::commerce.domain.add') }}
        </button>
    </form>

    <p role="status" wire:loading data-commerce-loading>
        {{ __('module-ecommerce-commerce-core::commerce.loading') }}
    </p>

    @forelse ($this->channel->domains as $domain)
        <div wire:key="commerce-domain-{{ $domain->id }}" data-commerce-domain="{{ $domain->host }}">
            <span>{{ $domain->host }}</span>

            @if ($domain->isPrimary)
                <span data-commerce-primary>{{ __('module-ecommerce-commerce-core::commerce.domain.primary') }}</span>
            @else
                <button type="button" wire:click="promote({{ $domain->id }})" wire:loading.attr="disabled">
                    {{ __('module-ecommerce-commerce-core::commerce.domain.make_primary') }}
                </button>
            @endif

            <button type="button" wire:click="remove({{ $domain->id }})" wire:loading.attr="disabled">
                {{ __('module-ecommerce-commerce-core::commerce.domain.remove') }}
            </button>
        </div>
    @empty
        <p data-commerce-empty>{{ __('module-ecommerce-commerce-core::commerce.domain.empty') }}</p>
    @endforelse
</div>
