# Adoption and upgrade guidance

## Requirements

PHP 8.5, Laravel 13, Livewire 4, and `liberusoftware/ecommerce-commerce-core`
`^0.3` — the domain module this package presents. It owns no tables and ships
no migrations; everything it can change lives in the domain module.

## Installing

The domain module is **not on Packagist yet**, so a composition has to tell
Composer where to find it before requiring anything:

```jsonc
// composer.json, in the consuming application
"repositories": [
    { "type": "vcs", "url": "https://github.com/liberusoftware/module-ecommerce-commerce-core" }
]
```

```bash
composer require liberusoftware/ecommerce-commerce-core-livewire
```

The presentation package requires the domain module, so both arrive together.

**Remove that `repositories` entry once the domain module is published to
Packagist** — it is a workaround for the gap, not part of the composition. A VCS
repository left in place after publication pins resolution to a Git remote,
which is slower, needs credentials the CI runner may not have, and can silently
serve a branch rather than a release. This package's own `composer.json` carries
the same entry for the same reason and will drop it in the same release.

## Enabling

Installing is not enabling. Both packages are enabled by the module registry:

```dotenv
MODULES_ENABLED=ecommerce-commerce-core,ecommerce-commerce-core-livewire
```

- Enabling only `ecommerce-commerce-core-livewire` gives you components with no
  domain behind them.
- Enabling only `ecommerce-commerce-core` is a perfectly good headless
  composition.

`default_enabled` is `false` in `module.json`: a module that installs itself
into every deployment that happens to pull it in is a module nobody chose.

After changing `MODULES_ENABLED`, clear caches — a stale cached config is the
most common cause of "unable to find component"; see
[runbook.md](runbook.md).

## Routing the full-page components

The package declares no routes, no layout and no navigation. The application
composes them:

```php
use Liberu\Ecommerce\CommerceCore\Livewire\Pages\Stores;
use Liberu\Ecommerce\CommerceCore\Livewire\Pages\StoreWorkspace;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/commerce/stores', Stores::class)->name('commerce.stores');
    Route::get('/commerce/stores/{storeId}', StoreWorkspace::class)->name('commerce.store');
});
```

Both components authorize for themselves; route middleware is defence in depth,
not the authorization. Reusable components go straight into a Blade view:

```blade
<livewire:module-ecommerce-commerce-core::store-list />
<livewire:module-ecommerce-commerce-core::store-workspace :store-id="$storeId" />
```

The full inventory is in [presentation.md](presentation.md).

## Why the domain package is in both `require` and `require-dev`

`composer validate` warns about this, and it is deliberate. **Do not "fix" it —
removing the `require-dev` entry turns the test suite red.**

```jsonc
"require":     { "liberusoftware/ecommerce-commerce-core": "^0.3" },
"require-dev": { "liberusoftware/ecommerce-commerce-core": "^0.3" }
```

The two entries mean different things to different readers:

- **`require`** is the runtime truth: this package cannot render without the
  domain module's actions, queries, data objects and enums.
- **`require-dev`** is what the shared test bootstrap reads. The testbench boots
  a sibling module's service provider **only when that module is dev-required**,
  because a runtime requirement must never boot anything — installing a module
  is not enabling it, and a package that booted whatever it required would
  enable modules behind the operator's back.

So `require` gets the class files on the autoloader, and `require-dev` is the
signal that says "in *this* package's test run, boot the domain module's
provider too". Composer resolves the same constraint once; nothing is installed
twice.

If you remove the dev entry, the domain module's provider stops booting under
test and every component test fails at the first `CommerceAccess` call. If you
remove the runtime entry, the package installs into a production application
that has no domain module and 500s on first render.

When either constraint moves, **both entries move together** — they are the same
constraint written twice for two different consumers of the manifest.

## Version pinning

`composer.json`'s `version` and `module.json`'s `version` are kept exactly
equal, and `module.json`'s `requires.packages` is exactly the `liberusoftware/*`
entries of `require`. The module registry reads `module.json`; Composer reads
`composer.json`; a deployment where they disagree is a deployment where the
registry and the autoloader disagree about what is installed.

## Upgrading

### 0.1.0 → 0.2.0

No breaking changes. Aliases, mount parameters, event names and payloads are
unchanged, and there are no migrations.

**If you have not published any views**, upgrade and stop reading — you get the
accessibility work for free.

**If you have published views** (`resources/views/vendor/module-ecommerce-commerce-core/`),
your copies are older than the package's and will keep rendering exactly as they
did. To pick up the accessibility work, re-publish over them and re-apply your
styling:

```bash
php artisan vendor:publish --tag=module-ecommerce-commerce-core-views --force
```

What changed in the views, if you would rather merge by hand:

| Change | Why it matters |
| --- | --- |
| The standalone `<p role="status" wire:loading>` became one live region per component, containing both the loading text and `{{ $announcement }}` | Announces the *outcome* of an action, not only that one is in flight |
| Fields render `aria-invalid="true"` and `aria-describedby` when invalid; each error `<p>` gained an `id` | Ties an error to the field it belongs to |
| The pager buttons gained `aria-label`, and their `«` / `»` are `aria-hidden` inside a span | They had no accessible name at all |
| Repeated row controls gained an `aria-label` naming the row they act on | "Remove", "Enable" and "Select" are otherwise identical to a screen reader |
| Component headings are `<h2>` (they were a mix of `<h2>` and `<h3>`), rows `<h3>`, and `pages/stores` gained an `<h1>` | Heading order is the page outline |
| `channel-status-control` now renders the terminal-status message its store counterpart already had | Silence read as a rendering fault |
| The workspace's deselect button says "Stop managing this channel" rather than repeating the empty-state sentence | It is a control, and it now has a control's name |

**If you have published translations**, `lang/vendor/module-ecommerce-commerce-core/en/commerce.php`
is missing the new keys. Laravel falls back to the package's own file per key,
so nothing breaks; add them when you next touch the file:
`pagination.previous`, `pagination.next`, `store.created`, `channel.created`,
`channel.now_managing`, `channel.deselected`, `channel.clear_selection`,
`domain.added`, `domain.promoted`, `domain.removed`, `setting.saved`,
`setting.forgotten`, `capability.turned_on`, `capability.turned_off`,
`status.changed`.

**If you subclass a component** — which is not a supported extension point, but
is possible — note that `announcement` is a new `#[Locked]` public property and
`allocated` on `order-numbers` became `#[Locked]`. Neither is settable from the
browser any more, and `hydrateInteractsWithCommerce()` now clears
`announcement` on every request.

**Nothing to do** for routes, migrations, queued jobs, cached views (Laravel
recompiles changed Blade files itself) or the domain module.
