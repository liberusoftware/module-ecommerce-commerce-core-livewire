<div>
    <h3>{{ __('module-ecommerce-commerce-core::commerce.order_number.heading') }}</h3>

    <form wire:submit="allocate">
        <label for="commerce-order-prefix">{{ __('module-ecommerce-commerce-core::commerce.order_number.prefix') }}</label>
        <input id="commerce-order-prefix" type="text" wire:model="prefix" autocomplete="off">

        @error('prefix')
            <p role="alert" data-commerce-error="prefix">{{ $message }}</p>
        @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="allocate">
            {{ __('module-ecommerce-commerce-core::commerce.order_number.allocate') }}
        </button>
    </form>

    <p role="status" wire:loading wire:target="allocate" data-commerce-loading>
        {{ __('module-ecommerce-commerce-core::commerce.loading') }}
    </p>

    @if ($allocated !== null)
        <p role="status" data-commerce-allocated="{{ $allocated }}">
            {{ __('module-ecommerce-commerce-core::commerce.order_number.allocated', ['number' => $allocated]) }}
        </p>
    @endif
</div>
