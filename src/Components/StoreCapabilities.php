<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Actions\SetStoreCapability;
use Liberu\Ecommerce\CommerceCore\Enums\Capability;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * What a store is allowed to do.
 *
 * The switches are {@see Capability::cases()} and their current positions come
 * from the store's read model, so a capability added to the enum appears here
 * without this package being touched.
 */
class StoreCapabilities extends Component
{
    use InteractsWithCommerce;

    #[Locked]
    public int $storeId;

    public function mount(int $storeId): void
    {
        $this->storeId = $storeId;
        $this->guardStore('manageSettings', $storeId);
    }

    /** @return list<Capability> */
    #[Computed]
    public function capabilities(): array
    {
        return Capability::cases();
    }

    /** @return list<string> */
    #[Computed]
    public function enabled(): array
    {
        return $this->storeData($this->storeId)->capabilities;
    }

    public function toggle(string $capability, bool $enabled): void
    {
        $this->guardStore('manageSettings', $this->storeId);

        $subject = Capability::tryFrom($capability);

        if ($subject === null) {
            $this->addError('capability', __('module-ecommerce-commerce-core::commerce.capability.unknown'));

            return;
        }

        app(SetStoreCapability::class)->handle($this->storeModel($this->storeId), $subject, $enabled);

        $this->announce(__(
            $enabled
                ? 'module-ecommerce-commerce-core::commerce.capability.turned_on'
                : 'module-ecommerce-commerce-core::commerce.capability.turned_off',
            ['capability' => $subject->label()],
        ));

        $this->dispatch(
            'module-ecommerce-commerce-core.capability-changed',
            storeId: $this->storeId,
            capability: $subject->value,
            enabled: $enabled,
        );
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.store-capabilities');
    }
}
