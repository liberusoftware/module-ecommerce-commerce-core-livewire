<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Actions\ChangeChannelStatus;
use Liberu\Ecommerce\CommerceCore\Data\ChannelData;
use Liberu\Ecommerce\CommerceCore\Enums\ChannelStatus;
use Liberu\Ecommerce\CommerceCore\Exceptions\InvalidStatusTransition;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The channel half of the shared state machine — the same shape as
 * {@see StoreStatusControl}, against a shorter lifecycle.
 */
class ChannelStatusControl extends Component
{
    use InteractsWithCommerce;

    #[Locked]
    public int $channelId;

    public function mount(int $channelId): void
    {
        $this->channelId = $channelId;
        $this->guardChannel('view', $channelId);
    }

    #[Computed]
    public function channel(): ChannelData
    {
        return $this->channelData($this->channelId);
    }

    /** @return list<ChannelStatus> */
    #[Computed]
    public function transitions(): array
    {
        return $this->channel()->status->allowedTransitions();
    }

    public function changeTo(string $status): void
    {
        $this->guardChannel('update', $this->channelId);

        $target = ChannelStatus::tryFrom($status);

        if ($target === null) {
            $this->addError('status', __('module-ecommerce-commerce-core::commerce.status.unknown'));

            return;
        }

        try {
            app(ChangeChannelStatus::class)->handle($this->channelModel($this->channelId), $target);
        } catch (InvalidStatusTransition) {
            $this->addError('status', __('module-ecommerce-commerce-core::commerce.status.illegal', [
                'from' => $this->channel()->status->label(),
                'to' => $target->label(),
            ]));

            return;
        }

        $this->dispatch(
            'module-ecommerce-commerce-core.channel-status-changed',
            channelId: $this->channelId,
            status: $target->value,
        );
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.channel-status-control');
    }
}
