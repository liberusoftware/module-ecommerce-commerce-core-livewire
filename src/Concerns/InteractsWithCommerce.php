<?php

namespace Liberu\Ecommerce\CommerceCore\Livewire\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Liberu\Ecommerce\CommerceCore\Data\ChannelData;
use Liberu\Ecommerce\CommerceCore\Data\StoreData;
use Liberu\Ecommerce\CommerceCore\Models\Channel;
use Liberu\Ecommerce\CommerceCore\Models\Store;
use Liberu\Ecommerce\CommerceCore\Queries\ChannelQuery;
use Liberu\Ecommerce\CommerceCore\Queries\StoreQuery;
use Liberu\Ecommerce\CommerceCore\Services\CommerceAccess;

/**
 * The four things every component in this package does before it does anything
 * else: find the actor, find the actor's team, ask the domain whether the actor
 * may, and read the domain's read model.
 *
 * It holds no decisions of its own. `CommerceAccess` is the authority and this
 * only calls it — a presentation package that computed its own answer would be
 * a second copy of the policies, drifting from the first.
 */
trait InteractsWithCommerce
{
    /**
     * The authenticated actor.
     *
     * Fails closed rather than returning null. A component reached without an
     * actor is a composition mistake in the host, and rendering something
     * harmless-looking would hide it until the mistake was in production.
     */
    protected function actor(): Authenticatable
    {
        $actor = auth()->user();

        if ($actor === null) {
            abort(403);
        }

        return $actor;
    }

    /**
     * The team the actor is working in, read from the actor rather than from
     * the browser.
     *
     * `data_get` rather than `->current_team_id` because the actor is only ever
     * an `Authenticatable` here: the user model belongs to the host, and naming
     * it would tie this package to one application.
     */
    protected function teamId(): ?int
    {
        $teamId = data_get($this->actor(), 'current_team_id');

        return is_numeric($teamId) ? (int) $teamId : null;
    }

    protected function guardStore(string $ability, ?int $storeId = null): void
    {
        if (! app(CommerceAccess::class)->toStore($this->actor(), $ability, $storeId)) {
            abort(403);
        }
    }

    protected function guardChannel(string $ability, ?int $channelId = null): void
    {
        if (! app(CommerceAccess::class)->toChannel($this->actor(), $ability, $channelId)) {
            abort(403);
        }
    }

    protected function storeData(int $storeId): StoreData
    {
        $store = app(StoreQuery::class)->find($storeId);

        if ($store === null) {
            abort(404);
        }

        return $store;
    }

    protected function channelData(int $channelId): ChannelData
    {
        $channel = app(ChannelQuery::class)->find($channelId);

        if ($channel === null) {
            abort(404);
        }

        return $channel;
    }

    /**
     * The model a domain action needs, fetched only after the id was authorized.
     *
     * Actions take models; the read side takes ids. This is the one place the
     * two meet, and it never reads a column — everything a component displays
     * comes from the read model above.
     */
    protected function storeModel(int $storeId): Store
    {
        return Store::query()->findOrFail($storeId);
    }

    protected function channelModel(int $channelId): Channel
    {
        return Channel::query()->findOrFail($channelId);
    }
}
