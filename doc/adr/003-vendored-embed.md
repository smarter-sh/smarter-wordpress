# ADR-003: The Embed Script Ships in the Plugin

## Status

Accepted

## Context

The embed script is published to npm, and so is served by jsDelivr's CDN. The WordPress.org plugin guidelines (guideline 8) don't allow a plugin to load executable code from a third-party CDN for no reason other than convenience. Sites also prefer scripts from their own domain, for their Content Security Policy and their privacy notice.

`embed.js` loads its other files, Mermaid's chunks and KaTeX's fonts, from an `embed/` directory beside itself, wherever it is served from.

## Decision

The plugin's release zip contains `assets/embed.js` and `assets/embed/`, copied by `scripts/embed.sh` from the **latest** release of `@smarter.sh/ui-chat` on npm (or a version given as `EMBED_VERSION`). The script writes that version into `SMARTER_CHAT_EMBED_VERSION`, which the release commits, so every release records the embed script that it ships. Every build, in CI and in a release, smoke tests the zip in WordPress before it is kept or published (ADR-010). They are build output: they are in `.gitignore`, and `make dist` copies them into the zip. WordPress serves them as the plugin's own static files, with `?ver=<version>` for cache busting.

The plugin always loads its own copy; it has no option to load the script from a CDN, which WordPress.org's reviewers would reject. When the local files are missing, e.g. in a git clone that hasn't run `make embed`, the chat can't load, and the settings screen says so.

## Alternatives Considered

- **jsDelivr only.** Simpler, and it caches across sites, but it isn't allowed on WordPress.org, and it adds a third party to every page.
- **A setting to choose jsDelivr.** Version 0.1.0 had one, **Script source**. It was removed before the plugin was submitted to WordPress.org, whose guideline 8 allows CDNs only for fonts.
- **Commit the built files.** Five megabytes of generated JavaScript in git, which would drift from `SMARTER_CHAT_EMBED_VERSION`.

## Consequences

- **Positive:**
  - The plugin works on WordPress.org, behind firewalls, and with a strict Content Security Policy for scripts.
  - The version that runs is the version that was tested.
  - Each release picks up the embed script's fixes without a change to the plugin.
- **Negative:**
  - The zip is about 2 MB (6 MB unpacked), most of it Mermaid, which browsers download only for diagrams.
  - A fix in the embed script reaches WordPress sites only in a release of the plugin.
  - A breaking change in the embed script's options would reach a release unless the smoke test catches it. Pin `EMBED_VERSION` in the release workflow if that happens.

## Related ADRs

- [ADR-002: The Embed Script Is the Chat](002-embed-script.md)
- [ADR-007: Privacy and External Services](007-privacy.md)
- [ADR-010: CI/CD and Releases](010-ci-cd.md)
