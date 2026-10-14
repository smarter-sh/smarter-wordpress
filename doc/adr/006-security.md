# ADR-006: Security

## Status

Accepted

Follows the platform's [ADR on security](https://github.com/smarter-sh/smarter/tree/main/docs/source/adr), adapted to a plugin that runs in other people's sites.

## Context

The plugin writes administrator-entered values into every front-end page, and into the page's JavaScript. A shortcode's attributes are written by anyone who can edit a post, including Contributors, who aren't trusted with unfiltered html.

## Decision

- **Capabilities.** Only users who can `manage_options` see or save the settings. The Settings API checks its nonce.
- **Validate on input.** `chatbot` must be an `http(s)` url (`wp_http_validate_url` is not used, because it rejects `localhost`, which developers need); `color` is a hex color (`sanitize_hex_color`); `position` and `source` are one of their values; sizes are css lengths matched by a strict pattern; text is `sanitize_text_field`. Shortcode and block attributes are validated by the same functions.
- **Escape on output.** Attributes with `esc_attr`, urls with `esc_url`, and `window.SmarterChatConfig` with `wp_json_encode` and `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT`, so that no value can close the script element.
- **The api key is public.** A Smarter api key in a page can be read by any visitor. The settings screen says so, recommends an LLMClient without api keys and with a budget, or a key dedicated to the site, and shows the saved key only in a password field. The plugin never sends the key anywhere itself.
- **Origins.** The LLMClient's manifest must list the site's origin in `spec.config.prompt.allowedOrigins`. The settings screen shows the site's origin, to copy.
- **No PHP requests to Smarter**, so the plugin has no SSRF surface.

## Alternatives Considered

- **Hide the api key behind a WordPress proxy.** See ADR-001.

## Consequences

- **Positive:**
  - A Contributor's shortcode can't inject markup or script.
- **Negative:**
  - Site owners must understand that an api key in a page is public.

## Related ADRs

- [ADR-001: Scope of the Plugin](001-scope.md)
- [ADR-004: Settings](004-settings.md)
- [ADR-007: Privacy and External Services](007-privacy.md)
