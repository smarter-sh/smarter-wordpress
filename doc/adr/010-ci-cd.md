# ADR-010: CI/CD and Releases

## Status

Accepted

Follows the Python SDK's [ADR-010: CI/CD](https://github.com/smarter-sh/smarter-python/blob/main/doc/adr/010-ci-cd.md).

## Context

Every change should be tested, and every release built the same way, with the embed script that it was tested with.

## Decision

GitHub Actions only. `test.yml` runs PHPCS and the unit tests on PHP 7.4, 8.1 and 8.3 for every push and pull request, and builds the zip with the latest embed script (`scripts/dist.sh`), smoke tests it in WordPress (`tests/smoke/run.sh`), and keeps it as the run's artifact. `pushMain.yml` runs semantic-release on `main`, `beta` and `alpha` (ADR-011), which writes the version into the plugin's header and `readme.txt`, builds and smoke tests the zip the same way, commits the embed version that it ships, and attaches `smarter-chat-<version>.zip` to the GitHub release.

The first submission to the WordPress.org plugin directory is done by hand, from the release zip. Once the plugin is accepted, `pushMain.yml`'s `wordpress-org` job deploys that same zip, from the release job's artifact, to the directory's Subversion repository with [10up/action-wordpress-plugin-deploy](https://github.com/10up/action-wordpress-plugin-deploy): the plugin directory as `trunk/` and `tags/<version>/`, and `.wordpress-org/` (banner, icon, screenshots) as `assets/`. The job deploys when it makes sense, and otherwise leaves a notice on the run that says why not: the release is a stable `x.y.z` from `main` (prereleases from `alpha` and `beta` never are), WordPress.org has accepted the plugin (its Subversion repository exists), the secrets `SVN_USERNAME` and `SVN_PASSWORD` are set, and WordPress.org doesn't have the version yet. So it starts deploying by itself with the first release after the plugin's acceptance. The repository variable `WPORG_DEPLOY=false` turns it off.

## Alternatives Considered

- **Deploy to WordPress.org by hand with svn.** Error-prone: the version in the header, `Stable tag` and the tag directory must all agree.

## Consequences

- **Positive:**
  - Every release has a zip that a site owner can upload in Plugins → Add New.
- **Negative:**
  - The first WordPress.org submission, and its review, are manual.

## Related ADRs

- [ADR-003: The Embed Script Ships in the Plugin](003-vendored-embed.md)
- [ADR-009: Testing](009-testing.md)
- [ADR-011: Semantic Versioning](011-versioning.md)
