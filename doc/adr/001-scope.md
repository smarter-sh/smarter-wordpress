# ADR-001: Scope of the Plugin

## Status

Accepted

## Context

A Smarter chatbot is an LLMClient, declared in a YAML manifest, applied to a Smarter platform, and deployed to its own host. Website owners who run WordPress (a large share of the web) want that chatbot on their site, but most of them don't write JavaScript, and can't add a React application to their theme. `@smarter.sh/ui-chat` publishes an embed script that renders the chat from a single script tag (ADR-002), so the remaining work is WordPress's: a settings screen, and the places in a site where the chat can appear.

## Decision

The plugin, `smarter-chat`, does three things, and nothing else:

- a **settings screen** (Settings → Smarter Chat) for the chatbot's url and the launcher's options (ADR-004),
- a **site-wide launcher**, a chat button in a corner of every page, when it is turned on (ADR-005),
- a **shortcode**, `[smarter_chat]`, and a **block**, Smarter Chat, that put the chat in a post or a page (ADR-005).

It has no chat code, makes no requests to Smarter from PHP, stores no conversations, and doesn't manage LLMClients. Building, styling and deploying the chatbot happen on the Smarter platform, with its manifest.

## Alternatives Considered

- **A full Smarter client in PHP**, which manages LLMClients from wp-admin. That duplicates the web console and the cli, and needs a Smarter api key with write access in WordPress's database.
- **Proxying prompts through WordPress (admin-ajax or the REST api)**, which hides the api key. It doubles the latency of every prompt, breaks the chat's streaming, and makes each site's PHP workers part of the chat's availability. LLMClients that need a secret are better served by Smarter's own authentication (ADR-006).
- **Only documenting a theme snippet.** Many site owners can't edit `functions.php`, and a snippet has no settings screen.

## Consequences

- **Positive:**
  - The plugin is small, and has little to maintain: its behavior is the embed script's.
  - The chat on a WordPress site is the same as on every other platform.
- **Negative:**
  - A chatbot that needs an api key exposes it in the page, as every browser-side chat does (ADR-006).

## Related ADRs

- [ADR-002: The Embed Script Is the Chat](002-embed-script.md)
- [ADR-004: Settings](004-settings.md)
- [ADR-005: Placement: Launcher, Shortcode and Block](005-placement.md)
