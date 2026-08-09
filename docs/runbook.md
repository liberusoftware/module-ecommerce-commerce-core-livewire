# Runbook

What this package looks like when it goes wrong in production, and what the
operator does about it. Every entry is a symptom first, because a symptom is
what arrives in the ticket.

This package renders; it decides nothing. Almost every failure here is either a
composition mistake in the host or a decision the domain module made that the
component is faithfully showing you.

---

## A component alias does not resolve

**Symptom.** A page 500s with:

```
Unable to find component: [module-ecommerce-commerce-core::store-list]
```

or the same for any other alias. It is always the whole component, never part
of one — a page that renders with an empty panel is a different problem.

**Why it can happen at all.** Aliases in this package are registered twice, and
the second half is the unusual one:

```php
Livewire::component($alias, $component);          // the name a class reports as
Livewire::resolveMissingComponent(fn ($name) => $aliases[$name] ?? null);
```

Livewire 4's component finder resolves a `namespace::name` only through
`Livewire::addNamespace()`, which maps one component namespace onto one PHP
namespace. This package deliberately has two — `Components\` for the reusable
components and `Pages\` for the routable ones — so `addNamespace()` cannot
express its inventory, and the alias table answers through
`resolveMissingComponent()` instead. That callback is the load-bearing part of
component resolution here.

**Check, in this order.**

1. Is the module enabled? `MODULES_ENABLED` must contain
   `ecommerce-commerce-core-livewire`. If the provider never boots, neither
   half of the registration happens. This is the cause the overwhelming majority
   of the time.
2. Was `MODULES_ENABLED` changed without clearing caches? `php artisan
   config:clear && php artisan cache:clear`, and on a deployment that ships a
   cached container, re-run whatever builds it.
3. Is another package also calling `Livewire::resolveMissingComponent()`?
   Resolvers are chained and each is consulted, but a resolver that **throws**
   or **aborts** rather than returning `null` for a name it does not own ends
   the chain before this package's is reached. The tell is that aliases from
   several packages break at once, not just these. Fix the greedy resolver; do
   not work around it here.
4. Is the alias spelled the way the package publishes it? The namespace is
   `module-ecommerce-commerce-core::` — no `-livewire` suffix. See
   [presentation.md](presentation.md) for the exact table.

**Do not** "fix" this by calling `Livewire::addNamespace()` in the host: it
would resolve one of the two PHP namespaces and silently keep the other half
broken.

---

## A signed-in operator sees an empty store list

**Symptom.** "No stores yet." for somebody who certainly has stores. No error,
no denial, nothing in the log.

**Cause.** The actor has no `current_team_id`. The component reads the tenant
off the actor and never off the wire:

```php
$teamId = data_get($this->actor(), 'current_team_id');
```

`null` scopes the query to nothing, and an empty list is the honest rendering of
"you are not working in any team".

**Check.** For the affected user, is `current_team_id` set? It is the host
application's column — this package does not own it, does not write it, and
cannot repair it. Typical causes: an account created by an importer or a seeder
that skipped team assignment; a team deleted without reassigning its members; a
switch-team flow that failed halfway.

**Fix.** Set the user's current team in the host. There is no configuration in
this package that changes the answer.

**Note the deliberate asymmetry.** A user with no team sees an empty *list*, but
is *denied* the components that require a specific store — because
`CommerceAccess` denies a subject it cannot resolve. Both are correct; they are
different questions.

---

## An action returns 403 or 404 instead of an error page

**Symptom.** A Livewire request comes back 403 or 404 and the panel disappears
or the browser shows the host's error page. Nothing is logged as an exception,
because nothing threw.

**This is the designed behaviour, not a fault.** Every guard in this package
ends in `abort()`:

- **403** — the actor may not do this. Wrong team, insufficient role, or a
  lifecycle rule (an archived store cannot be updated, so its settings,
  capabilities and order numbers are all denied).
- **404** — the subject is not there, or is not reachable from where the
  component is pointed: a store id that does not exist, a channel that belongs
  to another store, a domain row that belongs to another merchant's channel or
  was deleted between the render and the click.

`CommerceAccess` deliberately answers "denied" for a subject that does not
exist, so a probe cannot use the 403/404 split to discover which ids are real.

**When it is genuinely wrong.** If a legitimate operator gets 403 on their own
store, the policy is in the **domain** package
(`liberusoftware/ecommerce-commerce-core`), not here. Reproduce against the
domain's own test suite; this package has no policy of its own to misconfigure.

---

## "The hostname … is already claimed by another channel"

**Symptom.** Adding a hostname shows that message against the hostname field.

**Cause.** Hostnames are unique across the entire deployment — that is what
makes host-based storefront resolution possible. The domain refused the claim
by throwing `DomainAlreadyClaimed`, and the component turned it into a
validation message rather than an error page, because the recovery from a typo
should not be the back button.

**Operator response.** Find the claim:

```php
Liberu\Ecommerce\CommerceCore\Models\ChannelDomain::query()
    ->where('host', 'shop.example.com')   // already normalised: lowercase, trimmed
    ->with('channel.store')
    ->first();
```

The host is normalised before comparison, so `Shop.Example.COM` and
`shop.example.com` are the same claim. Then either remove it from the channel
that holds it — using this package's own hostname panel, which re-promotes a
survivor to primary automatically — or use a different hostname. There is no
force-take: taking a live hostname from another storefront is a decision that
needs a human on both sides of it.

---

## "A move from X to Y is not allowed"

**Symptom.** A status button that was on screen a moment ago produces that
message instead of moving.

**Cause.** The offered buttons come from `StoreStatus::allowedTransitions()` at
render time. The state machine is enforced again by the domain action when the
button is clicked. Between those two moments, somebody else — another operator,
a job, a console command — moved the subject, so the button that was legal when
it was drawn is illegal when it is pressed.

**Operator response.** Re-render (the panel already shows the new current
status) and choose from the moves now offered. Nothing is stuck and nothing
needs repair.

**When to escalate.** If a transition is refused that the current status
genuinely allows, the disagreement is between
`StoreStatus::allowedTransitions()` and the domain action that enforces it —
that is a domain-package bug, and this package is only the messenger. The
component is written to survive it: an illegal transition is a validation
message, never a 500.

---

## Version mismatch with the domain package

**Symptom.** Any of:

- `Class "Liberu\Ecommerce\CommerceCore\Actions\…" not found`
- `Call to undefined method …Query::…`
- a `TypeError` about a `…Data` object's property or constructor
- a status or capability enum case that this package renders but the domain
  does not know, or vice versa

**Cause.** This package consumes the domain module's public surface — actions,
queries, data objects, enums, exceptions and `CommerceAccess`. It requires
`liberusoftware/ecommerce-commerce-core: ^0.3`. A deployment that pinned the
domain elsewhere, or that vendored one of the two, can end up with halves that
do not match.

**Check.**

```bash
composer show liberusoftware/ecommerce-commerce-core
composer show liberusoftware/ecommerce-commerce-core-livewire
composer why liberusoftware/ecommerce-commerce-core
```

**Fix.** Bring the domain package back inside the constraint the installed
presentation package declares, then `php artisan config:clear`. Never patch the
presentation package to work around a domain surface that has moved — the
constraint is the contract, and widening it in a hotfix hides the next mismatch.

**Rolling back** this package alone is safe: it owns no tables, no migrations
and no state. Everything it can change lives in the domain module's tables and
survives the presentation package being removed entirely.
