<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Components;

use Illuminate\Contracts\View\View;
use Liberu\Ecommerce\CommerceCore\Actions\AddChannelDomain;
use Liberu\Ecommerce\CommerceCore\Actions\PromoteDomainToPrimary;
use Liberu\Ecommerce\CommerceCore\Actions\RemoveChannelDomain;
use Liberu\Ecommerce\CommerceCore\Data\ChannelData;
use Liberu\Ecommerce\CommerceCore\Data\ChannelDomainData;
use Liberu\Ecommerce\CommerceCore\Exceptions\DomainAlreadyClaimed;
use Liberu\Ecommerce\CommerceCore\Livewire\Concerns\InteractsWithCommerce;
use Liberu\Ecommerce\CommerceCore\Models\ChannelDomain;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The hostnames a channel answers on.
 *
 * Hosts are unique across every channel on the deployment, so adding one is a
 * claim that can be refused. A refusal is shown against the field the operator
 * typed into — the alternative is an error page for a typo, from which the only
 * recovery is the back button.
 */
class ChannelDomains extends Component
{
    use InteractsWithCommerce;

    #[Locked]
    public int $channelId;

    public string $host = '';

    public bool $primary = false;

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

    public function add(): void
    {
        $this->guardChannel('manageDomains', $this->channelId);

        $this->validate([
            'host' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.-]+$/'],
        ]);

        try {
            app(AddChannelDomain::class)->handle(
                $this->channelModel($this->channelId),
                $this->host,
                $this->primary,
            );
        } catch (DomainAlreadyClaimed) {
            $this->addError('host', __('module-ecommerce-commerce-core::commerce.domain.claimed', [
                'host' => ChannelDomain::normalise($this->host),
            ]));

            return;
        }

        $this->dispatch(
            'module-ecommerce-commerce-core.domain-added',
            channelId: $this->channelId,
            host: ChannelDomain::normalise($this->host),
        );

        $this->host = '';
        $this->primary = false;
    }

    public function promote(int $domainId): void
    {
        $this->guardChannel('manageDomains', $this->channelId);

        $domain = $this->ownDomain($domainId);

        app(PromoteDomainToPrimary::class)->handle(ChannelDomain::query()->findOrFail($domain->id));

        $this->dispatch(
            'module-ecommerce-commerce-core.primary-domain-changed',
            channelId: $this->channelId,
            host: $domain->host,
        );
    }

    /**
     * Removing the primary is allowed. The domain promotes the oldest survivor
     * rather than leaving the channel unreachable, so this does not have to ask
     * the operator to reorder first.
     */
    public function remove(int $domainId): void
    {
        $this->guardChannel('manageDomains', $this->channelId);

        $domain = $this->ownDomain($domainId);

        app(RemoveChannelDomain::class)->handle(ChannelDomain::query()->findOrFail($domain->id));

        $this->dispatch(
            'module-ecommerce-commerce-core.domain-removed',
            channelId: $this->channelId,
            host: $domain->host,
        );
    }

    public function render(): View
    {
        return view('module-ecommerce-commerce-core::livewire.channel-domains');
    }

    /**
     * A domain id from the browser is a number somebody could have changed.
     *
     * Resolved against this channel's own read model rather than looked up
     * directly, so an id belonging to another merchant's channel is not there
     * to be found at all.
     */
    private function ownDomain(int $domainId): ChannelDomainData
    {
        foreach ($this->channel()->domains as $domain) {
            if ($domain->id === $domainId) {
                return $domain;
            }
        }

        abort(404);
    }
}
