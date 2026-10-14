# ADR-013: Licensing

## Status

Accepted

## Context

Smarter and `@smarter.sh/ui-chat` are licensed under the AGPL-3.0-or-later. WordPress is GPL-2.0-or-later, and the WordPress.org plugin directory requires plugins to be "GPLv2 or later" compatible.

## Decision

The plugin's own code (PHP, its block's JavaScript, its css) is licensed **GPL-2.0-or-later**, as WordPress is. The embed script that the plugin ships (ADR-003) keeps its own license, AGPL-3.0-or-later, which is stated in `readme.txt` and in `assets/embed/LICENSE` of the zip. The two are combined under the terms of version 3 of the GPL, which version 2-or-later permits, and with which the AGPL v3 is explicitly compatible.

## Alternatives Considered

- **AGPL-3.0-or-later for the plugin too.** Consistent with Smarter, but not "GPLv2 or later" compatible, which WordPress.org asks for.

## Consequences

- **Positive:**
  - Acceptable to WordPress.org.
- **Negative:**
  - The zip has two licenses. WordPress.org's reviewers may ask about the bundled AGPL file; if they object, ADR-003's CDN option becomes the only one on WordPress.org.

## Related ADRs

- [ADR-003: The Embed Script Ships in the Plugin](003-vendored-embed.md)
