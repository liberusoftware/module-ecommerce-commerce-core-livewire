# Changelog

All notable changes to `liberusoftware/ecommerce-commerce-core-livewire` are
documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this package adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.2.0 — 2026-08-09

Accessibility and documentation. No breaking changes: aliases, mount
parameters, event names and payloads are unchanged, and there is nothing to
migrate. See [docs/adoption.md](docs/adoption.md#010--020) for the upgrade note
— the only consumers with anything to do are those who have published views or
translations.

### Added

- An accessible name for every control whose visible content is a glyph or is
  repeated: the pager buttons, and the per-row Remove, Enable, Disable, Make
  primary and Select controls.
- `aria-invalid` and `aria-describedby` on invalid fields, tying each message to
  the field it belongs to.
- One `role="status" aria-live="polite"` region per component, carrying both the
  loading text and a sentence describing what the last action did. Populated
  from a new `#[Locked] public string $announcement`, cleared on every
  subsequent request so it is announced exactly once.
- A terminal-status message on `channel-status-control`, which its store
  counterpart already had.
- `docs/runbook.md` — the production failure modes and the operator's response:
  an alias failing to resolve, an actor with no `current_team_id`, a denial
  arriving as 403/404, a hostname already claimed, an illegal status transition,
  and a domain-package version mismatch.
- `docs/presentation.md` — the component inventory, per-component state and
  which of it is URL-bound, the events, the ability each action asks for, the
  domain action each write delegates to, and how a theme overrides a view or
  publishes the translation catalogue.
- `docs/adoption.md` — installing and enabling, the VCS `repositories` entry the
  domain package still needs and when to remove it, why that package appears in
  both `require` and `require-dev`, and the 0.1.0 → 0.2.0 upgrade note.
- `tests/Feature/AccessibilityTest.php` and `tests/Feature/LifecycleTest.php`,
  including a sweep that fails if any component renders a field without a label
  and a reflection check on which properties are URL-bound and which are locked.

### Changed

- Component headings are now consistently `<h2>` with their rows at `<h3>`, and
  the `stores` page renders an `<h1>`, so a heading list is the page outline.
- `order-numbers` announces the allocated number through the component's live
  region rather than a second one beside it.
- The workspace's deselect control is named for what it does rather than
  repeating the empty-state sentence.

### Security

- `order-numbers`' `allocated` property is now `#[Locked]`. It is written by the
  sequence and read by the operator; a value arriving from the browser could
  only ever be a lie about which number was consumed. The new `announcement`
  property is locked for the same reason: it is announced verbatim.

## 0.1.0 — 2026-08-09

### Added

- Nine reusable Livewire components and two full-page components covering the
  capabilities of `liberusoftware/ecommerce-commerce-core`: stores, channels,
  commercial context, order numbering, shared states, settings, capabilities and
  domain events.
- A bounded component namespace, `module-ecommerce-commerce-core::`, registered
  through explicit aliases.
- A theme-overridable view namespace and an English translation catalogue, both
  published under `module-ecommerce-commerce-core`.
