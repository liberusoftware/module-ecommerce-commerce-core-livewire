<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Actions\CreateChannel;
use Liberu\Ecommerce\CommerceCore\Data\ChannelData;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Liberu\Ecommerce\CommerceCore\Queries\ChannelQuery;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * A store's channels, and the form that adds one.
 *
 * Creating a channel is authorized as `update` on the store, not as `create` on
 * the channel: the domain deliberately publishes no channel `create` ability,
 * because a channel without a store is not a thing anyone can own.
 */
class ChannelList extends Component
{
    use InteractsWithCommerce;
    use WithPagination;

    #[Locked]
    public int $storeId;

    public string $name = '';

    public string $theme = 'theme-ecommerce';

    public function mount(int $storeId): void
    {
        $this->storeId = $storeId;
        $this->guardStore('view', $storeId);
    }

    /** @return LengthAwarePaginator<int, ChannelData> */
    #[Computed]
    public function channels(): LengthAwarePaginator
    {
        return app(ChannelQuery::class)->paginateForStore($this->storeId, 25);
    }

    public function create(): void
    {
        $this->guardStore('update', $this->storeId);

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'theme' => ['required', 'string', 'max:120'],
        ]);

        $channel = app(CreateChannel::class)->handle(
            $this->storeModel($this->storeId),
            $this->name,
            $this->theme,
        );

        $this->announce(__('module-ecommerce-commerce-core::commerce.channel.created', ['name' => $this->name]));

        $this->name = '';
        $this->resetPage();

        $this->dispatch(
            'module-ecommerce-commerce-core.channel-created',
            channelId: (int) $channel->getKey(),
        );
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.channel-list');
    }
}
