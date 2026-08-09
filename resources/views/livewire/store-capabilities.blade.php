<div>
    <h2>{{ __('module-ecommerce-commerce-core::commerce.capability.heading') }}</h2>

    @error('capability')
        <p role="alert" data-commerce-error="capability">{{ $message }}</p>
    @enderror

    <p role="status" aria-live="polite">
        <span wire:loading data-commerce-loading>{{ __('module-ecommerce-commerce-core::commerce.loading') }}</span>
        <span data-commerce-announcement>{{ $announcement }}</span>
    </p>

    @foreach ($this->capabilities as $capability)
        @php($isOn = in_array($capability->value, $this->enabled, true))

        <div wire:key="commerce-capability-{{ $capability->value }}" data-commerce-capability="{{ $capability->value }}">
            <span>{{ $capability->label() }}</span>

            {{-- On and off are words. The data attribute is a hook for themes and
                 tests, not the way the state is conveyed. --}}
            <span data-commerce-capability-state="{{ $isOn ? 'on' : 'off' }}">
                {{ $isOn
                    ? __('module-ecommerce-commerce-core::commerce.capability.on')
                    : __('module-ecommerce-commerce-core::commerce.capability.off') }}
            </span>

            {{-- A column of buttons all called "Enable" names nothing. Each says
                 what it enables. --}}
            <button
                type="button"
                wire:click="toggle('{{ $capability->value }}', {{ $isOn ? 'false' : 'true' }})"
                wire:loading.attr="disabled"
                aria-label="{{ $isOn
                    ? __('module-ecommerce-commerce-core::commerce.capability.disable')
                    : __('module-ecommerce-commerce-core::commerce.capability.enable') }}: {{ $capability->label() }}"
            >
                {{ $isOn
                    ? __('module-ecommerce-commerce-core::commerce.capability.disable')
                    : __('module-ecommerce-commerce-core::commerce.capability.enable') }}
            </button>
        </div>
    @endforeach
</div>
