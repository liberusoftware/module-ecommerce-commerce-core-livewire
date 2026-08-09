<div>
    <h3>{{ __('module-ecommerce-commerce-core::commerce.status.heading') }}</h3>

    <p data-commerce-status="{{ $this->store->status->value }}">
        {{ __('module-ecommerce-commerce-core::commerce.status.current', ['status' => $this->store->status->label()]) }}
    </p>

    @error('status')
        <p role="alert" data-commerce-error="status">{{ $message }}</p>
    @enderror

    <p role="status" wire:loading wire:target="changeTo" data-commerce-loading>
        {{ __('module-ecommerce-commerce-core::commerce.loading') }}
    </p>

    @forelse ($this->transitions as $transition)
        <button
            type="button"
            wire:key="commerce-store-status-{{ $transition->value }}"
            wire:click="changeTo('{{ $transition->value }}')"
            wire:loading.attr="disabled"
            wire:target="changeTo"
        >
            {{ __('module-ecommerce-commerce-core::commerce.status.move_to', ['status' => $transition->label()]) }}
        </button>
    @empty
        <p data-commerce-terminal>{{ __('module-ecommerce-commerce-core::commerce.status.terminal') }}</p>
    @endforelse
</div>
