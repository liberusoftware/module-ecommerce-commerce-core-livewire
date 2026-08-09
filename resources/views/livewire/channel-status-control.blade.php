<div>
    <h2>{{ __('module-ecommerce-commerce-core::commerce.status.heading') }}</h2>

    <p data-commerce-status="{{ $this->channel->status->value }}">
        {{ __('module-ecommerce-commerce-core::commerce.status.current', ['status' => $this->channel->status->label()]) }}
    </p>

    @error('status')
        <p role="alert" data-commerce-error="status">{{ $message }}</p>
    @enderror

    <p role="status" aria-live="polite">
        <span wire:loading wire:target="changeTo" data-commerce-loading>{{ __('module-ecommerce-commerce-core::commerce.loading') }}</span>
        <span data-commerce-announcement>{{ $announcement }}</span>
    </p>

    @forelse ($this->transitions as $transition)
        <button
            type="button"
            wire:key="commerce-channel-status-{{ $transition->value }}"
            wire:click="changeTo('{{ $transition->value }}')"
            wire:loading.attr="disabled"
            wire:target="changeTo"
        >
            {{ __('module-ecommerce-commerce-core::commerce.status.move_to', ['status' => $transition->label()]) }}
        </button>
    @empty
        {{-- Silence would read as a rendering fault. A lifecycle with nowhere
             left to go says so. --}}
        <p data-commerce-terminal>{{ __('module-ecommerce-commerce-core::commerce.status.terminal') }}</p>
    @endforelse
</div>
