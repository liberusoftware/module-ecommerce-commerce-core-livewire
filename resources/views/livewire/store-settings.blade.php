<div>
    <h3>{{ __('module-ecommerce-commerce-core::commerce.setting.heading') }}</h3>

    <form wire:submit="save">
        <label for="commerce-setting-key">{{ __('module-ecommerce-commerce-core::commerce.setting.key') }}</label>
        <input id="commerce-setting-key" type="text" wire:model="key" autocomplete="off">

        @error('key')
            <p role="alert" data-commerce-error="key">{{ $message }}</p>
        @enderror

        <label for="commerce-setting-value">{{ __('module-ecommerce-commerce-core::commerce.setting.value') }}</label>
        <input id="commerce-setting-value" type="text" wire:model="value" autocomplete="off">

        @error('value')
            <p role="alert" data-commerce-error="value">{{ $message }}</p>
        @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="save">
            {{ __('module-ecommerce-commerce-core::commerce.setting.save') }}
        </button>
    </form>

    <p role="status" wire:loading data-commerce-loading>
        {{ __('module-ecommerce-commerce-core::commerce.loading') }}
    </p>

    @forelse ($this->settings as $settingKey => $settingValue)
        <div wire:key="commerce-setting-{{ $settingKey }}" data-commerce-setting="{{ $settingKey }}">
            <span>{{ $settingKey }}</span>
            <span>{{ is_scalar($settingValue) ? $settingValue : json_encode($settingValue) }}</span>

            <button type="button" wire:click="forget('{{ $settingKey }}')" wire:loading.attr="disabled">
                {{ __('module-ecommerce-commerce-core::commerce.setting.forget') }}
            </button>
        </div>
    @empty
        <p data-commerce-empty>{{ __('module-ecommerce-commerce-core::commerce.setting.empty') }}</p>
    @endforelse
</div>
