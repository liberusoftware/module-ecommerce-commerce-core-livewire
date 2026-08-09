# Presentation documentation

The complete public surface of this package: what it registers, what state each
component holds, what it dispatches and listens for, what it asks permission
for, and which domain action does the actual work.

Nothing here is a decision this package makes. Every ability named below is
answered by `CommerceAccess` in `liberusoftware/ecommerce-commerce-core`, and
every write goes to a domain action.

## Identity

| | |
| --- | --- |
| Package | `liberusoftware/ecommerce-commerce-core-livewire` |
| Module name | `ecommerce-commerce-core-livewire` |
| Category | `presentation` |
| Provider | `Liberu\Ecommerce\CommerceCore\Livewire\CommerceCoreLivewireServiceProvider` |
| Component / view / translation namespace | `module-ecommerce-commerce-core` |
| Domain module presented | `liberusoftware/ecommerce-commerce-core` `^0.3` |

The namespace drops the `-livewire` suffix and keeps the ownership prefix: it
names the *bounded context*, not the technology presenting it. Components,
views and translations all share it.

## Component inventory

Aliases are the public interface. They are registered from an explicit table in
the service provider rather than discovered from the directory layout, so
moving a class is not a breaking change and renaming an alias is.

| Alias | Class | Mount parameters |
| --- | --- | --- |
| `module-ecommerce-commerce-core::store-list` | `Components\StoreList` | — |
| `module-ecommerce-commerce-core::store-status-control` | `Components\StoreStatusControl` | `storeId` |
| `module-ecommerce-commerce-core::store-settings` | `Components\StoreSettings` | `storeId` |
| `module-ecommerce-commerce-core::store-capabilities` | `Components\StoreCapabilities` | `storeId` |
| `module-ecommerce-commerce-core::order-numbers` | `Components\OrderNumbers` | `storeId` |
| `module-ecommerce-commerce-core::channel-list` | `Components\ChannelList` | `storeId` |
| `module-ecommerce-commerce-core::channel-status-control` | `Components\ChannelStatusControl` | `channelId` |
| `module-ecommerce-commerce-core::channel-domains` | `Components\ChannelDomains` | `channelId` |
| `module-ecommerce-commerce-core::commercial-context` | `Components\CommercialContextPanel` | — |
| `module-ecommerce-commerce-core::stores` | `Pages\Stores` | — |
| `module-ecommerce-commerce-core::store-workspace` | `Pages\StoreWorkspace` | `storeId` |

The last two are full-page components. They declare no layout — routes, layouts
and navigation belong to the application — and they authorize for themselves
regardless of the route that reached them.

## State

Every public property travels to the browser and back, which makes every one of
them an input. `#[Locked]` marks the ones the browser may not change; the rest
are form fields, and each is validated at the boundary before any domain action
sees it.

| Component | Property | Type | URL-bound | Locked | Notes |
| --- | --- | --- | --- | --- | --- |
| `store-list` | `perPage` | `int` | **yes** | no | Clamped to 1–100 on every read |
| | `name` | `string` | no | no | `required, min:2, max:120` |
| `store-status-control` | `storeId` | `int` | no | **yes** | |
| `store-settings` | `storeId` | `int` | no | **yes** | |
| | `key` | `string` | no | no | `required, max:120, /^[a-z0-9]+(?:[._-][a-z0-9]+)*$/` |
| | `value` | `string` | no | no | `nullable, max:1000` |
| `store-capabilities` | `storeId` | `int` | no | **yes** | |
| `order-numbers` | `storeId` | `int` | no | **yes** | |
| | `prefix` | `string` | no | no | `nullable, max:16, /^[A-Za-z0-9-]*$/` |
| | `allocated` | `?string` | no | **yes** | Written by the sequence, never by the browser |
| `channel-list` | `storeId` | `int` | no | **yes** | |
| | `name` | `string` | no | no | `required, min:2, max:120` |
| | `theme` | `string` | no | no | `required, max:120` |
| `channel-status-control` | `channelId` | `int` | no | **yes** | |
| `channel-domains` | `channelId` | `int` | no | **yes** | |
| | `host` | `string` | no | no | `required, max:255, /^[A-Za-z0-9.-]+$/` |
| | `primary` | `bool` | no | no | |
| `store-workspace` | `storeId` | `int` | no | **yes** | |
| | `channelId` | `?int` | no | **yes** | Changed only through `selectChannel()`, which re-authorizes |
| *all components* | `announcement` | `string` | no | **yes** | The live region's text; see [Accessibility](#accessibility) |

Two more inputs are URL-bound without appearing above:

- `page`, from `WithPagination`, on `store-list` and `channel-list`. It is
  Livewire's, not this package's, and it is safe because it only indexes a
  query that is already scoped.
- Nothing else. There is no tenant in the URL, no tenant in a property, and no
  tenant in an event payload. The team is read from the actor
  (`data_get($actor, 'current_team_id')`) on every request.

## Actions, abilities and delegation

The ability is asked of `CommerceAccess` — by id, before the model is fetched —
and the write is then handed to a domain action. This package contains no
business rule, no policy and no query beyond the two read-model query objects.

| Component | Method | Ability asked | Delegates to |
| --- | --- | --- | --- |
| `store-list` | `mount()` | store `viewAny` | `StoreQuery::paginate()` |
| | `create()` | store `create` | `Actions\CreateStore` |
| `store-status-control` | `mount()` | store `view` | `StoreQuery::find()` |
| | `changeTo()` | store `changeStatus` | `Actions\ChangeStoreStatus` |
| `store-settings` | `mount()` | store `manageSettings` | — |
| | `save()` | store `manageSettings` | `Actions\SetStoreSetting::handle()` |
| | `forget()` | store `manageSettings` | `Actions\SetStoreSetting::forget()` |
| `store-capabilities` | `mount()` | store `manageSettings` | — |
| | `toggle()` | store `manageSettings` | `Actions\SetStoreCapability` |
| `order-numbers` | `mount()` | store `view` | — |
| | `allocate()` | store `update` | `Actions\AllocateOrderNumber` |
| `channel-list` | `mount()` | store `view` | `ChannelQuery::paginateForStore()` |
| | `create()` | store **`update`** | `Actions\CreateChannel` |
| `channel-status-control` | `mount()` | channel `view` | `ChannelQuery::find()` |
| | `changeTo()` | channel `update` | `Actions\ChangeChannelStatus` |
| `channel-domains` | `mount()` | channel `view` | `ChannelQuery::find()` |
| | `add()` | channel `manageDomains` | `Actions\AddChannelDomain` |
| | `promote()` | channel `manageDomains` | `Actions\PromoteDomainToPrimary` |
| | `remove()` | channel `manageDomains` | `Actions\RemoveChannelDomain` |
| `commercial-context` | `mount()` | store `viewAny` | `Contracts\ResolvesCommercialContext` |
| `stores` | `mount()` | store `viewAny` | — |
| `store-workspace` | `mount()` | store `view` | `StoreQuery::find()` |
| | `selectChannel()` | channel `view` | `ChannelQuery::find()` |

Three of those are worth reading twice:

- **Creating a channel asks for `update` on its store**, not `create` on the
  channel. The domain publishes no channel `create` ability, because a channel
  without a store is not something anyone can own.
- **Every action re-authorizes.** Mounting is not a ticket; a subject deleted,
  archived or transferred between mount and click is denied at the click.
- **`selectChannel()` authorizes and then checks the channel belongs to this
  workspace's store**, because the id arrives in an event payload the browser
  wrote.

The one place this package touches a model for reading is `StoreSettings`,
which `pluck`s a store's settings so the model's own cast applies. Everything
else it displays comes from `StoreData` / `ChannelData` read models.

## Events

All names are prefixed `module-ecommerce-commerce-core.`. Payloads carry
identifiers and short scalars only — never a model, never a data object, never
anything an untrusted listener should not see.

| Event | Dispatched by | Payload |
| --- | --- | --- |
| `store-created` | `store-list` | `storeId` |
| `store-status-changed` | `store-status-control` | `storeId`, `status` |
| `setting-changed` | `store-settings` | `storeId`, `key` |
| `capability-changed` | `store-capabilities` | `storeId`, `capability`, `enabled` |
| `order-number-allocated` | `order-numbers` | `storeId`, `number` |
| `channel-created` | `channel-list` | `channelId` |
| `channel-status-changed` | `channel-status-control` | `channelId`, `status` |
| `channel-selected` | `channel-list`'s view, from the browser | `channelId` |
| `domain-added` | `channel-domains` | `channelId`, `host` |
| `domain-removed` | `channel-domains` | `channelId`, `host` |
| `primary-domain-changed` | `channel-domains` | `channelId`, `host` |

| Listener | Event | What it does |
| --- | --- | --- |
| `store-list::refreshStores()` | `store-status-changed` | Nothing at all — the redraw is the point, and the computed property is not cached across requests |
| `store-workspace::selectChannel()` | `channel-selected` | Authorizes the id, checks it belongs to this store, then selects it |

These are presentation events mirroring domain events. A consumer that needs the
*domain* event should listen to the domain module's, not to these — these fire
only when the change happened through one of these components.

## Loading and failure states

- **Loading.** Each component renders one `role="status" aria-live="polite"`
  region containing a `wire:loading` span. Disabling the submit button is not
  enough on its own: an operator not watching the screen is told nothing by a
  greyed-out control.
- **Refused by validation.** A message against the field, with
  `aria-invalid="true"` and `aria-describedby` pointing at it.
- **Refused by the domain.** `DomainAlreadyClaimed` and
  `InvalidStatusTransition` become validation messages, not error pages — a
  typo should not cost the operator their unsaved form.
- **Refused by authorization.** `abort(403)`.
- **Not there.** `abort(404)`, including for an id that exists but is not
  reachable from where this component is pointed.

## Accessibility

The package ships unstyled markup, so most of what is here is structure a theme
must not throw away when it publishes a view:

- Every field has a real `<label for>`. A placeholder is not a label.
- Invalid fields carry `aria-invalid="true"` and `aria-describedby` naming the
  element holding the message; the message itself is `role="alert"`.
- Each component has exactly one live region, which carries both the loading
  text and the sentence describing what the last action did ("Store Harbour
  Goods created.", "Hostname shop.example.com removed."). It is populated from
  the `announcement` property, which is `#[Locked]` — it is spoken verbatim, so
  a string the browser could set would be a string an attacker could put in the
  operator's ear. It lasts exactly one render.
- Status is words. `data-commerce-status`, `data-commerce-capability-state` and
  friends are hooks for themes and tests; the state is also always written out,
  so a theme that conveys it with colour alone is adding to the text rather
  than replacing it.
- Controls whose visible content is a glyph — the two pager buttons — carry an
  `aria-label`, with the glyph itself `aria-hidden`. Repeated row controls
  ("Remove", "Enable", "Select") are labelled with what they act on, so a list
  of them is not a list of identical names.
- Every repeated row has a `wire:key`, so Livewire morphs rows in place and
  focus survives a re-render instead of being thrown back to the document.
- A routable page starts at `<h1>`; each component starts at `<h2>` and its
  rows at `<h3>`, so the heading list is the page outline.
- Everything is reachable and operable from the keyboard: every control is a
  real `<button>` or `<input>`, and there is no click handler on a `<div>`.

`tests/Feature/AccessibilityTest.php` asserts the parts of this that are
visible in rendered output, including a sweep that fails if any component ever
renders a field without a label.

## Lifecycle

What a consumer can get wrong:

- **Mount authorizes; it does not license.** Each action authorizes again.
- **Locked properties throw, they do not silently ignore.** A browser attempting
  to retarget `storeId` raises `CannotUpdateLockedPropertyException`, which
  surfaces as a 500 in the host — that is a tampering attempt, and it is meant
  to be loud.
- **`perPage` arrives from a link somebody else may have written.** It is
  clamped to 1–100 on every read, not on assignment: nothing can leave it
  unclamped.
- **Computed properties are per-request.** `#[Computed]` results are not cached
  between Livewire requests, so a listener that only needs a redraw can have an
  empty body.
- **`allocated` survives a re-render, and an allocated number is spent.**
  Allocation is not idempotent: every call consumes the next number in the
  store's sequence. Nothing here reserves, retries or reuses.
- **A subject can vanish between mount and click.** A store deleted mid-session
  makes the next action a 403 (`CommerceAccess` denies what it cannot resolve),
  and a domain row deleted mid-session makes the next click a 404. Neither is
  an exception page. Both are covered in `tests/Feature/LifecycleTest.php`.
- **A transition legal at render can be illegal at click.** Shown as a
  validation message; see the runbook.

## Overriding a view

The package ships functional, unstyled markup and a theme owns the final
presentation:

```bash
php artisan vendor:publish --tag=module-ecommerce-commerce-core-views
```

Views land in `resources/views/vendor/module-ecommerce-commerce-core/`, and a
file placed there wins over the package's own — override one view without
forking the rest. Keep the label associations, the `aria-*` wiring, the live
region and the `wire:key`s: they are behaviour.

Wording publishes separately, because a deployment that wants its own copy
rarely wants its own markup:

```bash
php artisan vendor:publish --tag=module-ecommerce-commerce-core-translations
```

The catalogue lands in `lang/vendor/module-ecommerce-commerce-core/en/commerce.php`.
Add a locale by creating a sibling directory; Laravel falls back to the
package's own file for any key a published file does not define, so a partial
translation is a valid one.
