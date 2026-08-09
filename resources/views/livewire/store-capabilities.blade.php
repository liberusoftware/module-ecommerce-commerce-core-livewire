<div>
    <h3>{{ __('module-ecommerce-commerce-core::commerce.capability.heading') }}</h3>

    @error('capability')
        <p role="alert" data-commerce-error="capability">{{ $message }}</p>
    @enderror

    <p role="status" wire:loading data-commerce-loading>
        {{ __('module-ecommerce-commerce-core::commerce.loading') }}
    </p>

    @foreach ($this->capabilities as $capability)
        @php($isOn = in_array($capability->value, $this->enabled, true))

        <div wire:key="commerce-capability-{{ $capability->value }}" data-commerce-capability="{{ $capability->value }}">
            <span>{{ $capability->label() }}</span>

            <span data-commerce-capability-state="{{ $isOn ? 'on' : 'off' }}">
                {{ $isOn
                    ? __('module-ecommerce-commerce-core::commerce.capability.on')
                    : __('module-ecommerce-commerce-core::commerce.capability.off') }}
            </span>

            <button
                type="button"
                wire:click="toggle('{{ $capability->value }}', {{ $isOn ? 'false' : 'true' }})"
                wire:loading.attr="disabled"
            >
                {{ $isOn
                    ? __('module-ecommerce-commerce-core::commerce.capability.disable')
                    : __('module-ecommerce-commerce-core::commerce.capability.enable') }}
            </button>
        </div>
    @endforeach
</div>
