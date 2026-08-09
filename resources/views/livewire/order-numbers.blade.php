<div>
    <h2>{{ __('module-ecommerce-commerce-core::commerce.order_number.heading') }}</h2>

    <form wire:submit="allocate">
        <label for="commerce-order-prefix">{{ __('module-ecommerce-commerce-core::commerce.order_number.prefix') }}</label>
        <input
            id="commerce-order-prefix"
            type="text"
            wire:model="prefix"
            autocomplete="off"
            @error('prefix') aria-invalid="true" aria-describedby="commerce-order-prefix-error" @enderror
        >

        @error('prefix')
            <p id="commerce-order-prefix-error" role="alert" data-commerce-error="prefix">{{ $message }}</p>
        @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="allocate">
            {{ __('module-ecommerce-commerce-core::commerce.order_number.allocate') }}
        </button>
    </form>

    {{-- One live region, not two: the allocated number is the outcome of the
         action, so it is announced by the same region that announced the wait.
         A number is consumed once and never comes back, which is why it also
         stays on screen rather than only being spoken. --}}
    <p role="status" aria-live="polite">
        <span wire:loading wire:target="allocate" data-commerce-loading>{{ __('module-ecommerce-commerce-core::commerce.loading') }}</span>

        @if ($allocated !== null)
            <span data-commerce-allocated="{{ $allocated }}">
                {{ __('module-ecommerce-commerce-core::commerce.order_number.allocated', ['number' => $allocated]) }}
            </span>
        @endif
    </p>
</div>
