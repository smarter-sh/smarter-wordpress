# ADR-009: Testing

## Status

Accepted

Follows the Python SDK's [ADR-009: Testing](https://github.com/smarter-sh/smarter-python/blob/main/doc/adr/009-testing.md).

## Context

The plugin's logic is small, but its mistakes are visible on every page of a site: a broken script tag, an unescaped attribute, a launcher on a page that shouldn't have one. WordPress's own test suite needs a database and a WordPress checkout.

## Decision

- **Unit tests** with PHPUnit and [Brain Monkey](https://giuseppe-mazzapica.gitbook.io/brain-monkey/), which fakes WordPress's functions and hooks, so they run in seconds without WordPress. One test class per class in `smarter-chat/includes/`. Coverage at least 90%.
- **A smoke test**, `make smoke`, that starts WordPress and MariaDB in Docker, installs the plugin with WP-CLI, sets its options, and checks the rendered home page and a page with the shortcode for the script and the element. It is run before a release, not in CI's default path.
- The embed script itself is tested in `smarter-chat`, not here.
- PHP isn't required on the developer's machine: `make test` runs in the `composer` Docker image when `php` isn't installed.

## Alternatives Considered

- **The WordPress core test suite (`wp-env`, `WP_UnitTestCase`)** for every test. Slower, and it tests WordPress more than the plugin. The smoke test covers the integration.

## Consequences

- **Positive:**
  - Fast tests that cover escaping and validation thoroughly.
- **Negative:**
  - Brain Monkey's fakes are not WordPress: the smoke test is what proves the hooks are right.

## Related ADRs

- [ADR-010: CI/CD and Releases](010-ci-cd.md)
