<?php

use Liberu\Ecommerce\CommerceCore\Contracts\ResolvesCommercialContext;
use Liberu\Ecommerce\CommerceCore\Livewire\Components\CommercialContextPanel;
use Liberu\Ecommerce\CommerceCore\Values\CommercialContext;
use Livewire\Livewire;

it('reports an unresolved context as a state rather than a failure', function () {
    actor();

    Livewire::test(CommercialContextPanel::class)
        ->assertSee(__('module-ecommerce-commerce-core::commerce.context.unresolved'))
        ->assertSeeHtml('data-commerce-context="unresolved"');
});

it('presents whatever the host resolves, through the contract', function () {
    actor();

    $store = storeOwnedBy();

    // Swapped rather than staged through a hostname: the component depends on
    // the published contract, so a host that resolves context some other way is
    // presented by the same component.
    app()->bind(ResolvesCommercialContext::class, fn (): ResolvesCommercialContext => new class($store) implements ResolvesCommercialContext
    {
        public function __construct(private readonly object $store) {}

        public function current(): CommercialContext
        {
            return new CommercialContext(
                storeId: (int) $this->store->getKey(),
                channelId: 42,
                teamId: TEAM,
                currency: 'GBP',
                locale: 'en_GB',
                timezone: 'Europe/London',
            );
        }
    });

    Livewire::test(CommercialContextPanel::class)
        ->assertSeeHtml('data-commerce-context="resolved"')
        ->assertSee('GBP')
        ->assertSee('Europe/London')
        ->assertSee('42');
});

it('denies an actor with no team', function () {
    teamlessActor();

    Livewire::test(CommercialContextPanel::class)->assertForbidden();
});
