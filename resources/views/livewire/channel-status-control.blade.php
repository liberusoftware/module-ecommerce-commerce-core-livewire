<div>
    <h3>{{ __('module-ecommerce-commerce-core::commerce.status.heading') }}</h3>

    <p data-commerce-status="{{ $this->channel->status->value }}">
        {{ __('module-ecommerce-commerce-core::commerce.status.current', ['status' => $this->channel->status->label()]) }}
    </p>

    @error('status')
        <p role="alert" data-commerce-error="status">{{ $message }}</p>
    @enderror

    <p role="status" wire:loading wire:target="changeTo" data-commerce-loading>
        {{ __('module-ecommerce-commerce-core::commerce.loading') }}
    </p>

    @foreach ($this->transitions as $transition)
        <button
            type="button"
            wire:key="commerce-channel-status-{{ $transition->value }}"
            wire:click="changeTo('{{ $transition->value }}')"
            wire:loading.attr="disabled"
            wire:target="changeTo"
        >
            {{ __('module-ecommerce-commerce-core::commerce.status.move_to', ['status' => $transition->label()]) }}
        </button>
    @endforeach
</div>
