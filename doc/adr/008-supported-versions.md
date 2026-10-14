# ADR-008: Supported WordPress and PHP Versions

## Status

Accepted

## Context

The plugin uses `wp_register_script`'s `strategy` argument (WordPress 6.3) and block.json's `render` and `apiVersion: 3` (WordPress 6.3). Most sites run a current WordPress, and PHP 7.4 or later.

## Decision

- **WordPress 6.5 or later** (`Requires at least: 6.5`), tested up to the current release (`Tested up to`), which a release of the plugin updates.
- **PHP 7.4 or later** (`Requires PHP: 7.4`). The code uses no syntax newer than 7.4, which PHPCompatibilityWP checks (ADR-012), and the tests run on 7.4, 8.1 and 8.3 (ADR-009).

## Alternatives Considered

- **PHP 8.1+.** Cleaner code (enums, readonly properties), but leaves out a real share of hosting.

## Consequences

- **Positive:**
  - Covers nearly every maintained WordPress site.
- **Negative:**
  - PHP 7.4's syntax only.

## Related ADRs

- [ADR-009: Testing](009-testing.md)
- [ADR-012: Code Quality](012-code-quality.md)
