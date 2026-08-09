# Ecommerce: Commerce Core Livewire

> This optional Livewire 4 presentation package provides interactive server-driven components for exactly one independent domain module. Components coordinate public queries/actions and presentation state; they do not own persistence, authorization decisions, tenancy, business rules, or theme identity. The package has no dependency on application Ap

[Software](https://liberusoftware.com) ·
[Hosting](https://liberuhosting.com) ·
[Services](https://liberuservices.com) ·
[Liberu Group](https://liberugroup.com)

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white) ![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white) ![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9)
[![Latest release](https://img.shields.io/github/v/release/liberusoftware/module-ecommerce-commerce-core-livewire?sort=semver)](https://github.com/liberusoftware/module-ecommerce-commerce-core-livewire/releases/latest) [![Tests](https://github.com/liberusoftware/module-ecommerce-commerce-core-livewire/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/liberusoftware/module-ecommerce-commerce-core-livewire/actions/workflows/tests.yml)

## Features

- Fully compatible with **Laravel 13**, **PHP 8.5**, and **Pest 5**.
- Built following the domain-driven design guidelines of the Liberu architecture.
- Reusable, presenting a clean public contract and boundaries.
- Adheres to the strict database, security, and authorization standards of Liberu.

## Requirements

- **PHP 8.5**
- **Composer 2**
- A supported database (e.g. MySQL, PostgreSQL, SQLite)

## Quick start

The domain module this package presents is not published on Packagist, so a
composition adds its repository before requiring either:

```jsonc
"repositories": [
    { "type": "vcs", "url": "https://github.com/liberusoftware/module-ecommerce-commerce-core" }
]
```

```bash
composer require liberusoftware/ecommerce-commerce-core-livewire
```

Installing is not enabling. Both this package and the domain module it presents
are enabled by the module registry, which reads:

```dotenv
MODULES_ENABLED=ecommerce-commerce-core,ecommerce-commerce-core-livewire
```

Enabling only `ecommerce-commerce-core-livewire` installs components with no
domain behind them; enabling only `ecommerce-commerce-core` is a perfectly good
headless composition.

## Components

Every component is registered under one bounded namespace,
`module-ecommerce-commerce-core::`. The aliases are the package's public
interface — they are stable, and changing one is a breaking release.

| Alias | Props | What it is for |
| --- | --- | --- |
| `module-ecommerce-commerce-core::store-list` | — | The stores the actor's team owns, and the form that creates another. Page size is URL-bound. |
| `module-ecommerce-commerce-core::store-status-control` | `store-id` | Moves a store through its lifecycle, offering only the moves `StoreStatus::allowedTransitions()` allows. |
| `module-ecommerce-commerce-core::store-settings` | `store-id` | A store's key/value settings, with forget. |
| `module-ecommerce-commerce-core::store-capabilities` | `store-id` | Turns each `Capability` on or off. |
| `module-ecommerce-commerce-core::order-numbers` | `store-id` | Allocates the next number from a store's sequence. |
| `module-ecommerce-commerce-core::channel-list` | `store-id` | A store's channels, and the form that adds one. |
| `module-ecommerce-commerce-core::channel-status-control` | `channel-id` | The channel lifecycle, driven the same way. |
| `module-ecommerce-commerce-core::channel-domains` | `channel-id` | Add, promote and remove the hostnames a channel answers on. |
| `module-ecommerce-commerce-core::commercial-context` | — | What the current request resolves to commercially, read through `ResolvesCommercialContext`. |
| `module-ecommerce-commerce-core::stores` | — | Full page: the stores index. |
| `module-ecommerce-commerce-core::store-workspace` | `store-id` | Full page: one store and every surface that acts on it. |

Used from a Blade view or an application route:

```blade
<livewire:module-ecommerce-commerce-core::store-list />
<livewire:module-ecommerce-commerce-core::store-workspace :store-id="$storeId" />
```

```php
Route::get('/commerce/stores', Stores::class)->middleware(['auth'])->name('commerce.stores');
```

Routes, layouts, navigation and middleware belong to the application composing
this package; the full-page components declare no layout of their own and
authorize for themselves regardless of the route that reaches them.

### Events

Components dispatch presentation events mirroring the domain events they caused.
Payloads carry identifiers only.

`store-created`, `store-status-changed`, `channel-created`,
`channel-status-changed`, `channel-selected`, `domain-added`, `domain-removed`,
`primary-domain-changed`, `setting-changed`, `capability-changed` and
`order-number-allocated`, each prefixed `module-ecommerce-commerce-core.`.

## Overriding a view

The package ships functional, unstyled markup; a theme owns the final
presentation. Publish the views and edit the copy:

```bash
php artisan vendor:publish --tag=module-ecommerce-commerce-core-views
```

They land in `resources/views/vendor/module-ecommerce-commerce-core/`, and a
file placed there wins over the package's own — override one view without
forking the rest. Wording is separate:

```bash
php artisan vendor:publish --tag=module-ecommerce-commerce-core-translations
```

## Accessibility

The markup is unstyled but not unfinished. Every field has a real `<label for>`,
invalid fields carry `aria-invalid` and point at their message, each component
has one `role="status" aria-live="polite"` region carrying both the loading text
and a sentence saying what the last action did, status is written in words as
well as marked in a data attribute, every repeated row is `wire:key`ed so focus
survives a re-render, and every control is a real button or input reachable from
the keyboard.

A theme publishing these views owns the classes and the layout — but the label
associations, the `aria-*` wiring, the live region and the keys are behaviour,
not decoration. `tests/Feature/AccessibilityTest.php` asserts the parts that are
visible in rendered output.

## Development note

`liberusoftware/ecommerce-commerce-core` appears in both `require` and
`require-dev`. It is a runtime dependency, and the shared test bootstrap boots a
sibling module's service provider only when it is dev-required — a runtime
requirement deliberately never boots anything, because installing a module must
not enable it. `composer validate` warns about the duplicate; removing either
entry breaks something. The long version is in
[docs/adoption.md](docs/adoption.md#why-the-domain-package-is-in-both-require-and-require-dev).

## Documentation

- [Presentation documentation](docs/presentation.md) — component inventory and
  aliases, per-component state and what is URL-bound, events, the ability each
  action asks for, the domain action each write delegates to, theme overrides
  and translations.
- [Runbook](docs/runbook.md) — what this package looks like when it goes wrong
  in production, and what the operator does about it.
- [Adoption and upgrade guidance](docs/adoption.md) — installing, enabling,
  routing, dependency pinning and the 0.1.0 → 0.2.0 upgrade note.
- [Changelog](CHANGELOG.md) — release notes.
- [Liberu Main Documentation](https://github.com/liberusoftware/documentation)
- [Architecture & Standards Index](https://github.com/liberusoftware/documentation/tree/main/architecture)

## Related Liberu Projects

| Project | Repository | Purpose |
| --- | --- | --- |
| **Boilerplate** | [liberusoftware/boilerplate-laravel](https://github.com/liberusoftware/boilerplate-laravel) | Shared Laravel application foundation and reference composition |
| **CMS** | [liberu-cms/cms-laravel](https://github.com/liberu-cms/cms-laravel) | Structured content, publishing, media, multisite, and headless delivery |
| **CRM** | [liberu-crm/crm-laravel](https://github.com/liberu-crm/crm-laravel) | Customer data, sales, marketing, service, and customer success |
| **Billing** | [liberu-billing/billing-laravel](https://github.com/liberu-billing/billing-laravel) | Products, subscriptions, invoicing, payments, and provisioning |
| **Accounting** | [liberu-accounting/accounting-laravel](https://github.com/liberu-accounting/accounting-laravel) | Ledgers, banking, tax, expenses, close, and financial reporting |
| **Ecommerce** | [liberu-ecommerce/ecommerce-laravel](https://github.com/liberu-ecommerce/ecommerce-laravel) | Catalog, checkout, orders, fulfillment, returns, B2B, and omnichannel commerce |
| **Control Panel** | [liberu-control-panel/control-panel-laravel](https://github.com/liberu-control-panel/control-panel-laravel) | Hosting, infrastructure, DNS, mail, databases, backups, and security operations |
| **Automation** | [liberu-automation/automation-laravel](https://github.com/liberu-automation/automation-laravel) | Governed workflows, provider-neutral AI, approvals, and connectors |

## Security

Please do not report security vulnerabilities through public GitHub issues.
Follow our [Security Policy](https://github.com/liberusoftware/documentation/blob/main/architecture/SECURITY.md) for private reporting and supported versions.

## License

This project is open-source software. You may use, modify, and distribute it
under the terms described in [LICENSE.md](LICENSE.md).

The linked license text is authoritative; this summary is not legal advice.

## Feedback and contributing

Feedback and contributions are welcome. You can help by reporting reproducible
bugs, proposing focused enhancements, improving documentation or translations,
and submitting tested code changes.

Before contributing, please read [CONTRIBUTING.md](https://github.com/liberusoftware/documentation/blob/main/standards/CONTRIBUTING.md) and our
[Code of Conduct](https://github.com/liberusoftware/documentation/blob/main/architecture/CODE_OF_CONDUCT.md). Search existing issues first, then use
the appropriate issue template. Pull requests should explain the problem and
approach, remain focused, include or update tests, pass the required workflows,
and document user-visible or breaking changes.

## Contributors

Thank you to everyone who helps improve Liberu.

<a href="https://github.com/liberusoftware/module-ecommerce-commerce-core-livewire/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=liberusoftware/module-ecommerce-commerce-core-livewire" alt="Contributors to liberusoftware/module-ecommerce-commerce-core-livewire">
</a>

[View the full contributors graph](https://github.com/liberusoftware/module-ecommerce-commerce-core-livewire/graphs/contributors).
