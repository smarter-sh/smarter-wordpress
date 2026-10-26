---
name: smarter-wordpress-development
description: Start here for any change to the smarter-wordpress repository, Smarter Chat for WordPress (the smarter-chat plugin), which adds a Smarter chatbot to a WordPress site with a settings screen, a site-wide launcher, a [smarter_chat] shortcode and a block, by loading @smarter.sh/ui-chat's embed script. Gives the rules, the plugin's architecture, how a setting flows from wp-admin to the embed script, how to add an option, how the embed script is vendored, and an index of the narrower skills.
---

# Smarter Chat for WordPress: Development

The plugin is a thin layer over the embed script of `@smarter.sh/ui-chat`
(`../smarter-chat/src/embed/`). The embed script is the chat: React, its css in
a Shadow DOM, the launcher, cookies and all. The plugin decides **where** the
chat appears and **with which options**, and nothing else (`doc/adr/001-scope.md`).

## Non-negotiable rules

1. **Never execute git write commands**. See `smarter-git-commits`.
2. **Run `make test` after every change**, and `make smoke` after changing a
   hook, the block, or how the script is enqueued. Report failures honestly.
3. **Validate input and escape output with WordPress's functions**
   (`doc/adr/006-security.md`). Contributors write shortcodes: treat
   shortcode and block attributes as untrusted.
4. **Never send a request to Smarter from PHP.** The browser talks to Smarter.
5. **A feature that the embed script lacks goes into `../smarter-chat` first**,
   then becomes a setting here, in a release that bumps
   `SMARTER_CHAT_EMBED_VERSION`.
6. **WordPress Coding Standards**: tabs, Yoda conditions, `snake_case`,
   PHPDoc on everything. PHP 7.4 syntax only (`doc/adr/008-supported-versions.md`).

## Architecture

| File                                            | Responsibility                                                                                               |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `smarter-chat/smarter-chat.php`                 | Plugin header, `SMARTER_CHAT_VERSION`, `SMARTER_CHAT_EMBED_VERSION`, requires, `Smarter_Chat_Plugin::boot()` |
| `includes/class-smarter-chat-plugin.php`        | Registers every hook, in one place                                                                           |
| `includes/class-smarter-chat-settings.php`      | The option `smarter_chat_settings`: defaults, `sanitize()`, `get()`, the settings screen                     |
| `includes/class-smarter-chat-validation.php`    | Pure validators shared by settings, shortcode and block: url, size, color, choice                            |
| `includes/class-smarter-chat-embed.php`         | Registers and enqueues the embed script; `config()`, the launcher's `window.SmarterChatConfig`               |
| `includes/class-smarter-chat-shortcode.php`     | `[smarter_chat]` and the block's render callback: a `<smarter-chat>` element                                 |
| `includes/class-smarter-chat-privacy.php`       | The privacy policy guide's suggested text                                                                    |
| `blocks/chat/block.json`, `blocks/chat/edit.js` | The dynamic block, with a build-free editor script                                                           |
| `uninstall.php`                                 | Deletes the option                                                                                           |

### How a setting reaches the page

1. The admin saves Settings → Smarter Chat. `Smarter_Chat_Settings::sanitize()`
   validates every field (`Smarter_Chat_Validation`), dropping invalid values to
   their defaults.
2. On `wp_enqueue_scripts`, `Smarter_Chat_Embed` registers the script
   (`assets/embed.js`, always the plugin's own copy: no CDN, per WordPress.org's
   guideline 8), with `strategy => async` and `in_footer => true`.
3. If the launcher is on, it enqueues the script and adds
   `window.SmarterChatConfig = {...}` **before** it with
   `wp_add_inline_script( ..., 'before' )`, encoded with `wp_json_encode` and the
   `JSON_HEX_*` flags.
4. The shortcode and the block render `<smarter-chat chatbot="..." height="...">`
   and enqueue the script themselves.

Option names: the plugin's settings are the embed script's options in
snake_case (`api_key` → `apiKey` in `SmarterChatConfig`, `api-key` as an
attribute). Keep that mapping in `Smarter_Chat_Embed::config()` only.

### Adding an option

1. Check that the embed script supports it (`../smarter-chat/src/embed/options.ts`),
   and at which version.
2. Add its default to `Smarter_Chat_Settings::defaults()`, its validation to
   `sanitize()`, and its field to the settings screen.
3. Map it in `Smarter_Chat_Embed::config()`, and, if it applies to inline chats,
   in the shortcode's attributes and `block.json`.
4. Tests for each step; `readme.txt`; the docs page
   (`../smarter/docs/source/smarter-framework/smarter-wordpress.rst`).

## smarter-chat-embed: the vendored embed script

`SMARTER_CHAT_EMBED_VERSION` in `smarter-chat.php` is the exact version of
`@smarter.sh/ui-chat` that the plugin ships and loads from its own `assets/`.
`make embed` (`scripts/embed.sh`) downloads the **latest** release from npm
(or `EMBED_VERSION=<version>`), copies `dist/embed.js` and `dist/embed/` into
`smarter-chat/assets/`, which is git-ignored, and writes the version into the
constant. `make dist` runs it on every build, and so do CI and each release,
which smoke test the zip and commit the constant. A local `make dist`
therefore changes `smarter-chat.php`; that change is expected. To
test against an unreleased embed script, build `../smarter-chat`
(`npm run build:lib && npm run build:embed`) and run
`make embed EMBED_SOURCE=../smarter-chat/dist`.

## Related skills

| Skill                       | Use it when                                               |
| --------------------------- | --------------------------------------------------------- |
| `smarter-wordpress-testing` | Writing or running the unit or smoke tests                |
| `smarter-code-quality`      | Before finishing: PHPCS and the pre-commit hooks          |
| `smarter-git-commits`       | Asked to write a commit message                           |
| `monthly-maintenance`       | Bumping the embed script, dependencies and "Tested up to" |

For the embed script itself, load `../smarter-chat`'s conventions (its README
and `src/embed/`); for the docs page, `../smarter/skills/smarter-sphinx-docs`.
