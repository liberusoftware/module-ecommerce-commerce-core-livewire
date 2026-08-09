<div>
    <h2>{{ __('module-ecommerce-commerce-core::commerce.domain.heading') }}</h2>

    <form wire:submit="add">
        <label for="commerce-domain-host">{{ __('module-ecommerce-commerce-core::commerce.domain.host') }}</label>
        <input
            id="commerce-domain-host"
            type="text"
            wire:model="host"
            autocomplete="off"
            inputmode="url"
            @error('host') aria-invalid="true" aria-describedby="commerce-domain-host-error" @enderror
        >

        <label for="commerce-domain-primary">
            <input id="commerce-domain-primary" type="checkbox" wire:model="primary">
            {{ __('module-ecommerce-commerce-core::commerce.domain.primary') }}
        </label>

        @error('host')
            <p id="commerce-domain-host-error" role="alert" data-commerce-error="host">{{ $message }}</p>
        @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="add">
            {{ __('module-ecommerce-commerce-core::commerce.domain.add') }}
        </button>
    </form>

    <p role="status" aria-live="polite">
        <span wire:loading data-commerce-loading>{{ __('module-ecommerce-commerce-core::commerce.loading') }}</span>
        <span data-commerce-announcement>{{ $announcement }}</span>
    </p>

    @forelse ($this->channel->domains as $domain)
        <div wire:key="commerce-domain-{{ $domain->id }}" data-commerce-domain="{{ $domain->host }}">
            <span>{{ $domain->host }}</span>

            {{-- Primary is stated in words. A theme is free to add a colour, but
                 the word has to survive it: "which one is primary" cannot be a
                 question only a sighted operator can answer. --}}
            @if ($domain->isPrimary)
                <span data-commerce-primary>{{ __('module-ecommerce-commerce-core::commerce.domain.primary') }}</span>
            @else
                <button
                    type="button"
                    wire:click="promote({{ $domain->id }})"
                    wire:loading.attr="disabled"
                    aria-label="{{ __('module-ecommerce-commerce-core::commerce.domain.make_primary') }}: {{ $domain->host }}"
                >
                    {{ __('module-ecommerce-commerce-core::commerce.domain.make_primary') }}
                </button>
            @endif

            <button
                type="button"
                wire:click="remove({{ $domain->id }})"
                wire:loading.attr="disabled"
                aria-label="{{ __('module-ecommerce-commerce-core::commerce.domain.remove') }}: {{ $domain->host }}"
            >
                {{ __('module-ecommerce-commerce-core::commerce.domain.remove') }}
            </button>
        </div>
    @empty
        <p data-commerce-empty>{{ __('module-ecommerce-commerce-core::commerce.domain.empty') }}</p>
    @endforelse
</div>
