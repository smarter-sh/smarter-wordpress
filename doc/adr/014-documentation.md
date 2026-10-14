# ADR-014: Documentation

## Status

Accepted

Follows the Python SDK's [ADR-012](https://github.com/smarter-sh/smarter-python/blob/main/doc/adr/012-documentation.md).

## Context

The plugin has three audiences: site owners, who read WordPress.org's plugin page; Smarter users, who read docs.smarter.sh; and contributors, who read this repository.

## Decision

- **`smarter-chat/readme.txt`** is the WordPress.org plugin page: description, installation, FAQ, external services (ADR-007), and changelog, in WordPress's readme format.
- **docs.smarter.sh** has the plugin's page, `smarter-framework/smarter-wordpress.rst` in the platform's repository, listed in Smarter APIs and SDKs, with the LLMClient side (deploy, `allowedOrigins`) that WordPress users need.
- **`README.md`** is for contributors: what the plugin is, how to build, test and release it.
- PHP has PHPDoc comments on every class, method and function, as the WordPress Coding Standards require.
- Prose is evergreen: no "new in 1.2" sections.

## Consequences

- **Positive:**
  - Each audience has one place to look.
- **Negative:**
  - The settings are described in two places (readme.txt and docs.smarter.sh), kept in step by hand.

## Related ADRs

- [ADR-007: Privacy and External Services](007-privacy.md)
