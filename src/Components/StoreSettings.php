<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Actions\SetStoreSetting;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Liberu\Ecommerce\CommerceCore\Models\StoreSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * A store's key/value settings.
 *
 * Values are read back through `pluck`, which applies the model's cast without
 * this package reading a column off a model it does not own.
 */
class StoreSettings extends Component
{
    use InteractsWithCommerce;

    #[Locked]
    public int $storeId;

    public string $key = '';

    public string $value = '';

    public function mount(int $storeId): void
    {
        $this->storeId = $storeId;
        $this->guardStore('manageSettings', $storeId);
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function settings(): array
    {
        return StoreSetting::query()
            ->where('store_id', $this->storeId)
            ->orderBy('key')
            ->pluck('value', 'key')
            ->all();
    }

    public function save(): void
    {
        $this->guardStore('manageSettings', $this->storeId);

        $this->validate([
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/'],
            'value' => ['nullable', 'string', 'max:1000'],
        ]);

        app(SetStoreSetting::class)->handle($this->storeModel($this->storeId), $this->key, $this->value);

        $this->announce(__('module-ecommerce-commerce-core::commerce.setting.saved', ['key' => $this->key]));

        $this->dispatch(
            'module-ecommerce-commerce-core.setting-changed',
            storeId: $this->storeId,
            key: $this->key,
        );

        $this->key = '';
        $this->value = '';
    }

    public function forget(string $key): void
    {
        $this->guardStore('manageSettings', $this->storeId);

        app(SetStoreSetting::class)->forget($this->storeModel($this->storeId), $key);

        $this->announce(__('module-ecommerce-commerce-core::commerce.setting.forgotten', ['key' => $key]));

        $this->dispatch(
            'module-ecommerce-commerce-core.setting-changed',
            storeId: $this->storeId,
            key: $key,
        );
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.store-settings');
    }
}
