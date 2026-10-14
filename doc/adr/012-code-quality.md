# ADR-012: Code Quality

## Status

Accepted

Follows the platform's pre-commit standard, and the Python SDK's [ADR-014](https://github.com/smarter-sh/smarter-python/blob/main/doc/adr/014-code-quality.md).

## Context

WordPress.org reviews plugins against the WordPress Coding Standards, and its Plugin Check flags unescaped output, missing nonces and unsupported functions.

## Decision

- **PHP:** [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) (`WordPress` ruleset) and [PHPCompatibilityWP](https://github.com/PHPCompatibility/PHPCompatibilityWP) for PHP 7.4+, configured in `phpcs.xml.dist`, run by `make lint` and by pre-commit.
- **Everything else:** Prettier (Markdown, YAML, JSON, JavaScript), codespell, the generic pre-commit hooks, and commitlint, as in every Smarter repository.
- Before a release to WordPress.org, run the [Plugin Check](https://wordpress.org/plugins/plugin-check/) plugin in the smoke test's WordPress.

## Consequences

- **Positive:**
  - The code is what WordPress.org's reviewers expect.
- **Negative:**
  - WordPress's style (tabs, Yoda conditions, snake_case) differs from Smarter's other repositories.

## Related ADRs

- [ADR-008: Supported WordPress and PHP Versions](008-supported-versions.md)
