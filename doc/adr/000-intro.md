# ADR Introduction

Architectural Design Records (ADRs) are short documents that capture a single architectural or design decision, its context, and its consequences. Less is more: keep them brief.

These are the ADRs of Smarter Chat for WordPress, the WordPress plugin that adds a Smarter chatbot to a WordPress site. They follow the Smarter platform's own [ADRs](https://github.com/smarter-sh/smarter/tree/main/docs/source/adr) and those of the [Python SDK](https://github.com/smarter-sh/smarter-python/tree/main/doc/adr), from which this repository's layout is copied. Where the plugin applies one of their decisions, its ADR says so, and adapts it to a plugin that runs inside other people's WordPress sites.

See: [Michael Nygard's ADRs](https://cognitect.com/blog/2011/11/15/documenting-architecture-decisions.html)

## Guidelines for writing ADRs

- **One decision per ADR.**
- **Be concise.** Focus on clarity and brevity.
- **State the context:** the forces that led to the decision.
- **List the alternatives**, and why they weren't chosen.
- **Explain the decision**, and **describe its consequences**, positive and negative.
- **Use simple language**, for future contributors without today's context.
- **Use the same format** as the others, and **reference related ADRs**.

## Index

- [ADR-001: Scope of the Plugin](001-scope.md)
- [ADR-002: The Embed Script Is the Chat](002-embed-script.md)
- [ADR-003: The Embed Script Ships in the Plugin](003-vendored-embed.md)
- [ADR-004: Settings](004-settings.md)
- [ADR-005: Placement: Launcher, Shortcode and Block](005-placement.md)
- [ADR-006: Security](006-security.md)
- [ADR-007: Privacy and External Services](007-privacy.md)
- [ADR-008: Supported WordPress and PHP Versions](008-supported-versions.md)
- [ADR-009: Testing](009-testing.md)
- [ADR-010: CI/CD and Releases](010-ci-cd.md)
- [ADR-011: Semantic Versioning](011-versioning.md)
- [ADR-012: Code Quality](012-code-quality.md)
- [ADR-013: Licensing](013-licensing.md)
- [ADR-014: Documentation](014-documentation.md)
