# ADR-007: Privacy and External Services

## Status

Accepted

## Context

The WordPress.org guidelines (guideline 7) require a plugin to disclose any external service that it uses, and to send nothing to it without the site owner's consent. The chat sends each visitor's messages to the Smarter platform that hosts the LLMClient, and from there to an LLM provider. The embed script stores a session cookie in the visitor's browser.

## Decision

- Nothing is sent anywhere until an administrator enters a chatbot url, which is the consent.
- `readme.txt` has an **External services** section that names the Smarter platform at the configured url, what is sent (the visitor's messages, the session key, and the page's origin), when (when the visitor opens the chat and sends a message), and tells the site owner to link the terms and privacy policy of the platform's operator (a Smarter platform can be self-hosted, so the plugin can't name one).
- The plugin adds a suggested paragraph to WordPress's privacy policy guide (`wp_add_privacy_policy_content`), naming the session cookie (`smarter_chat_<host>`) and the localStorage keys that the embed script uses.
- The launcher loads the chat only when it is opened, so a visitor who never opens it never contacts the Smarter platform.

## Alternatives Considered

- **A consent banner of our own.** Sites already have one, and the chat contacts Smarter only when the visitor opens it.

## Consequences

- **Positive:**
  - Meets WordPress.org's disclosure rules, and gives site owners the text for their privacy policy.
- **Negative:**
  - The disclosure must be kept in step with the embed script's cookies and storage.

## Related ADRs

- [ADR-003: The Embed Script Ships in the Plugin](003-vendored-embed.md)
- [ADR-006: Security](006-security.md)
