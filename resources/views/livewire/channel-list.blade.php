<div>
    <h2>{{ __('module-ecommerce-commerce-core::commerce.channel.heading') }}</h2>

    <form wire:submit="create">
        <label for="commerce-channel-name">{{ __('module-ecommerce-commerce-core::commerce.channel.name') }}</label>
        <input
            id="commerce-channel-name"
            type="text"
            wire:model="name"
            autocomplete="off"
            @error('name') aria-invalid="true" aria-describedby="commerce-channel-name-error" @enderror
        >

        @error('name')
            <p id="commerce-channel-name-error" role="alert" data-commerce-error="name">{{ $message }}</p>
        @enderror

        <label for="commerce-channel-theme">{{ __('module-ecommerce-commerce-core::commerce.channel.theme') }}</label>
        <input
            id="commerce-channel-theme"
            type="text"
            wire:model="theme"
            autocomplete="off"
            @error('theme') aria-invalid="true" aria-describedby="commerce-channel-theme-error" @enderror
        >

        @error('theme')
            <p id="commerce-channel-theme-error" role="alert" data-commerce-error="theme">{{ $message }}</p>
        @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="create">
            {{ __('module-ecommerce-commerce-core::commerce.channel.create') }}
        </button>
    </form>

    <p role="status" aria-live="polite">
        <span wire:loading data-commerce-loading>{{ __('module-ecommerce-commerce-core::commerce.loading') }}</span>
        <span data-commerce-announcement>{{ $announcement }}</span>
    </p>

    @forelse ($this->channels as $channel)
        <article wire:key="commerce-channel-{{ $channel->id }}" data-commerce-channel="{{ $channel->id }}">
            <h3>{{ $channel->name }}</h3>

            <dl>
                <dt>{{ __('module-ecommerce-commerce-core::commerce.status.heading') }}</dt>
                <dd>{{ $channel->status->label() }}</dd>

                <dt>{{ __('module-ecommerce-commerce-core::commerce.channel.theme') }}</dt>
                <dd>{{ $channel->theme }}</dd>

                <dt>{{ __('module-ecommerce-commerce-core::commerce.channel.primary_host') }}</dt>
                <dd>{{ $channel->primaryHost ?? __('module-ecommerce-commerce-core::commerce.channel.no_primary_host') }}</dd>
            </dl>

            {{-- An event rather than `$parent.`: this component is reusable on its
                 own, and reaching into a parent would make it require one.

                 The name says which channel it selects, because a column of
                 buttons all called "Select" is a list of identical controls to
                 anyone reading them out of context. --}}
            <button
                type="button"
                wire:click="$dispatch('module-ecommerce-commerce-core.channel-selected', { channelId: {{ $channel->id }} })"
                aria-label="{{ __('module-ecommerce-commerce-core::commerce.channel.select') }}: {{ $channel->name }}"
            >
                {{ __('module-ecommerce-commerce-core::commerce.channel.select') }}
            </button>
        </article>
    @empty
        <p data-commerce-empty>{{ __('module-ecommerce-commerce-core::commerce.channel.empty') }}</p>
    @endforelse

    @if ($this->channels->hasPages())
        <nav aria-label="{{ __('module-ecommerce-commerce-core::commerce.channel.heading') }}">
            <button
                type="button"
                wire:click="previousPage"
                aria-label="{{ __('module-ecommerce-commerce-core::commerce.pagination.previous') }}"
                @disabled($this->channels->onFirstPage())
            >
                <span aria-hidden="true">&laquo;</span>
            </button>

            <button
                type="button"
                wire:click="nextPage"
                aria-label="{{ __('module-ecommerce-commerce-core::commerce.pagination.next') }}"
                @disabled(! $this->channels->hasMorePages())
            >
                <span aria-hidden="true">&raquo;</span>
            </button>
        </nav>
    @endif
</div>
