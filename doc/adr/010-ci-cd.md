# ADR-010: CI/CD and Releases

## Status

Accepted

Follows the Python SDK's [ADR-010: CI/CD](https://github.com/smarter-sh/smarter-python/blob/main/doc/adr/010-ci-cd.md).

## Context

Every change should be tested, and every release built the same way, with the embed script that it was tested with.

## Decision

GitHub Actions only. `test.yml` runs PHPCS and the unit tests on PHP 7.4, 8.1 and 8.3 for every push and pull request. `pushMain.yml` runs semantic-release on `main`, `beta` and `alpha` (ADR-011), which writes the version into the plugin's header and `readme.txt`, runs `make dist`, and attaches `smarter-chat-<version>.zip` to the GitHub release.

Publishing to the WordPress.org plugin directory (its Subversion repository) is done by hand, from the release zip, until the plugin is accepted there; then a workflow can deploy each release's tag.

## Alternatives Considered

- **Deploy to WordPress.org on every release.** Not possible before the plugin is reviewed and accepted.

## Consequences

- **Positive:**
  - Every release has a zip that a site owner can upload in Plugins → Add New.
- **Negative:**
  - WordPress.org publishing is manual for now.

## Related ADRs

- [ADR-003: The Embed Script Ships in the Plugin](003-vendored-embed.md)
- [ADR-009: Testing](009-testing.md)
- [ADR-011: Semantic Versioning](011-versioning.md)
