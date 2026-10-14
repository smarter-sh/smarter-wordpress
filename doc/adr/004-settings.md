# ADR-004: Settings

## Status

Accepted

## Context

The plugin needs a handful of settings, edited by a site administrator, and read on every front-end page.

## Decision

- The settings are one option, `smarter_chat_settings`, an array, registered with the Settings API (`register_setting`), with one sanitize callback, and autoloaded, because the front end reads it on every page.
- The settings screen is Settings → Smarter Chat, built with the Settings API's sections and fields, for users who can `manage_options`.
- The fields are the embed script's options that a site owner chooses: `chatbot` (the LLMClient's url, required), `api_key`, `launcher` (on or off), `position`, `label`, `color`, `open`, and `source` (ADR-003). The rest keep the embed script's defaults.
- Defaults live in one place, `Smarter_Chat_Settings::defaults()`. A missing or invalid value is replaced by its default when it is saved, and again when it is read.
- `uninstall.php` deletes the option.

## Alternatives Considered

- **One option per setting.** More rows, more autoloaded queries, and no single sanitize callback.
- **The Customizer.** Its live preview would need the embed script in the preview frame, and it is being replaced by the Site Editor in block themes.
- **Settings in the block only.** A launcher appears on every page, so it needs a site-wide setting.

## Consequences

- **Positive:**
  - One place to validate, one row to read, one row to delete.
- **Negative:**
  - Network-wide (multisite) settings aren't supported: each site of a network has its own.

## Related ADRs

- [ADR-005: Placement: Launcher, Shortcode and Block](005-placement.md)
- [ADR-006: Security](006-security.md)
