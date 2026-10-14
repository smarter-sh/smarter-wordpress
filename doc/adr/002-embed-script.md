# ADR-002: The Embed Script Is the Chat

## Status

Accepted

## Context

The chat is `@smarter.sh/ui-chat`, a React 19 component. A WordPress site doesn't have React 19: WordPress bundles its own React for the block editor, of a version that WordPress chooses, and only in wp-admin. Themes and other plugins load their own scripts and styles, which can collide with the chat's.

`@smarter.sh/ui-chat` publishes `dist/embed.js`, a single classic script that includes React and the chat, renders it in a Shadow DOM so that the theme's css can't change it, and is configured with the attributes of a `<smarter-chat>` element, the `data-` attributes of its script tag, or `window.SmarterChatConfig`.

## Decision

The plugin renders nothing itself. It loads `embed.js`, and writes either `window.SmarterChatConfig` (the site-wide launcher) or a `<smarter-chat>` element (the shortcode and the block). The plugin's settings are the embed script's options, with the same names in snake_case, and the plugin passes them through unchanged, after validating them (ADR-006).

The plugin is built and tested with one version of the embed script, `SMARTER_CHAT_EMBED_VERSION` in `smarter-chat.php`, which `make embed` updates (ADR-003). A new option of the embed script reaches the plugin as a new setting, in a release of the plugin.

## Alternatives Considered

- **Bundle `@smarter.sh/ui-chat`'s React library with the plugin's own build.** Each platform plugin would repeat that build, and its React would be the plugin's problem.
- **Use WordPress's React (`wp-element`).** It is not loaded on the front end, and its version is WordPress's, not React 19.
- **An iframe of a page that the Smarter platform hosts.** Smarter has no such page, and an iframe can't be a launcher that floats over the site without a second script anyway.

## Consequences

- **Positive:**
  - The plugin has no JavaScript build. Its PHP is the whole plugin.
  - The theme's css can't break the chat (Shadow DOM).
- **Negative:**
  - Each page that shows the chat loads about 330 kB (gzipped) of JavaScript, React included, once, and cached after that. Mermaid and KaTeX's fonts load only when a message needs them.

## Related ADRs

- [ADR-001: Scope of the Plugin](001-scope.md)
- [ADR-003: The Embed Script Ships in the Plugin](003-vendored-embed.md)
