# ADR-005: Placement: Launcher, Shortcode and Block

## Status

Accepted

## Context

Site owners want the chat in one of two shapes: a launcher, a button in a corner of every page that opens the chat, or a chat in the content of one page, such as a Support page. WordPress has two ways to put something in content: shortcodes, which every theme and page builder supports, and blocks, which the block editor uses.

## Decision

- **The launcher** is on when its setting is on and a chatbot is set. The plugin enqueues the embed script on every front-end page, with `window.SmarterChatConfig` written before it by `wp_add_inline_script`. The embed script's `cookie-path` is `/`, so one conversation follows the visitor across the site.
- **The shortcode**, `[smarter_chat chatbot="..." height="600px" width="100%"]`, renders a `<smarter-chat>` element. `chatbot` defaults to the setting, so `[smarter_chat]` is enough.
- **The block**, `smarter/chat`, is a dynamic block: its `block.json` declares its attributes, and its `render` callback is the shortcode's. Its editor script is plain JavaScript that uses WordPress's globals (`wp.blocks`, `wp.blockEditor`, `wp.components`), so the plugin has no JavaScript build. In the editor it shows a placeholder, not the chat.
- The embed script is enqueued only on pages that use one of them, with `strategy => async`, in the footer.

## Alternatives Considered

- **A widget.** Classic widgets are legacy; block themes place blocks instead.
- **Render the chat in the editor with ServerSideRender.** The editor's iframe doesn't run the front end's scripts, and a live chat in the editor would send prompts while editing.
- **A block with a React build (`@wordpress/scripts`).** The block's editor UI is three fields; a build would be the plugin's only JavaScript toolchain.

## Consequences

- **Positive:**
  - Works with block themes, classic themes and page builders, through the shortcode.
  - Pages without a chat load nothing.
- **Negative:**
  - The block's editor preview is a placeholder.

## Related ADRs

- [ADR-002: The Embed Script Is the Chat](002-embed-script.md)
- [ADR-004: Settings](004-settings.md)
