# ADR-011: Semantic Versioning

## Status

Accepted

Follows the platform's semantic versioning, and the Python SDK's [ADR-015](https://github.com/smarter-sh/smarter-python/blob/main/doc/adr/015-versioning.md).

## Context

WordPress shows site owners the plugin's version, and updates it from the plugin directory. The plugin's behavior also depends on the embed script's version (ADR-002).

## Decision

- The plugin's version follows [semantic versioning](https://semver.org), set by semantic-release from conventional commit messages: `feat` is a minor release, `fix` a patch, and `BREAKING CHANGE` a major.
- The version is written in three places, by `release.config.js`: the `Version:` header and `SMARTER_CHAT_VERSION` in `smarter-chat.php`, and `Stable tag:` in `readme.txt`.
- Updating `SMARTER_CHAT_EMBED_VERSION` to a new minor version of the embed script is a `feat`; to a patch, a `fix`.
- A setting is never renamed or removed in a minor release. When one must go, its old value is migrated when the option is read.

## Consequences

- **Positive:**
  - Site owners can trust a minor update.
- **Negative:**
  - The version is in three files, kept in step by the release tooling.

## Related ADRs

- [ADR-002: The Embed Script Is the Chat](002-embed-script.md)
- [ADR-010: CI/CD and Releases](010-ci-cd.md)
