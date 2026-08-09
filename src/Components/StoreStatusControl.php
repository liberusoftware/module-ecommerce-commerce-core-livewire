<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Actions\ChangeStoreStatus;
use Liberu\Ecommerce\CommerceCore\Data\StoreData;
use Liberu\Ecommerce\CommerceCore\Enums\StoreStatus;
use Liberu\Ecommerce\CommerceCore\Exceptions\InvalidStatusTransition;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The store half of the shared state machine.
 *
 * The offered moves come from {@see StoreStatus::allowedTransitions()} rather
 * than from a list kept here. A surface with its own copy of the table drifts
 * from the action that enforces it, and the drift shows up as a button that
 * 500s — so the illegal move is also caught and shown, because a transition
 * legal when the page rendered can be illegal by the time it is clicked.
 */
class StoreStatusControl extends Component
{
    use InteractsWithCommerce;

    #[Locked]
    public int $storeId;

    public function mount(int $storeId): void
    {
        $this->storeId = $storeId;
        $this->guardStore('view', $storeId);
    }

    #[Computed]
    public function store(): StoreData
    {
        return $this->storeData($this->storeId);
    }

    /** @return list<StoreStatus> */
    #[Computed]
    public function transitions(): array
    {
        return $this->store()->status->allowedTransitions();
    }

    public function changeTo(string $status): void
    {
        $this->guardStore('changeStatus', $this->storeId);

        $target = StoreStatus::tryFrom($status);

        if ($target === null) {
            $this->addError('status', __('module-ecommerce-commerce-core::commerce.status.unknown'));

            return;
        }

        try {
            app(ChangeStoreStatus::class)->handle($this->storeModel($this->storeId), $target);
        } catch (InvalidStatusTransition) {
            $this->addError('status', __('module-ecommerce-commerce-core::commerce.status.illegal', [
                'from' => $this->store()->status->label(),
                'to' => $target->label(),
            ]));

            return;
        }

        $this->announce(__('module-ecommerce-commerce-core::commerce.status.changed', [
            'status' => $target->label(),
        ]));

        $this->dispatch(
            'module-ecommerce-commerce-core.store-status-changed',
            storeId: $this->storeId,
            status: $target->value,
        );
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.store-status-control');
    }
}
