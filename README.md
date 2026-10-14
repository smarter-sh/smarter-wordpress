# Smarter Chat for WordPress

> Add a [Smarter](https://smarter.sh) chatbot to a WordPress site: a chat launcher on every page, a Smarter Chat block,
> and a `[smarter_chat]` shortcode.

[![Test](https://github.com/smarter-sh/smarter-wordpress/actions/workflows/test.yml/badge.svg?branch=main)](https://github.com/smarter-sh/smarter-wordpress/actions/workflows/test.yml)
[![License: GPL v2 or later](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](LICENSE.txt)

The plugin is a settings screen and three ways to place the chat. The chat itself is the embed script of
[`@smarter.sh/ui-chat`](https://github.com/smarter-sh/smarter-chat), which the plugin ships, and which renders the chat
in a Shadow DOM so that the theme can't break it. The chatbot is an LLMClient that you build and deploy on a Smarter
platform.

**[User guide](https://docs.smarter.sh/en/latest/smarter-framework/smarter-wordpress.html)** ·
**[WordPress.org readme](smarter-chat/readme.txt)** · **[Changelog](CHANGELOG.md)** ·
**[Architecture decisions](doc/adr/000-intro.md)**

## Install

Download `smarter-chat-<version>.zip` from the [latest release](https://github.com/smarter-sh/smarter-wordpress/releases),
and upload it in Plugins → Add New → Upload Plugin. Then:

1. In the LLMClient's manifest, allow your site's origin, and apply it:

   ```yaml
   spec:
     config:
       prompt:
         allowedOrigins:
           - https://www.example.com
   ```

2. In Settings → Smarter Chat, enter the deployed LLMClient's url.

## Placing the chat

| Where             | How                                                                                     |
| ----------------- | --------------------------------------------------------------------------------------- |
| Every page        | Settings → Smarter Chat → **Show on every page** (on by default)                        |
| A page, in blocks | The **Smarter Chat** block, with an optional chatbot url and height                     |
| A page, anywhere  | `[smarter_chat]`, or `[smarter_chat chatbot="https://..." height="600px" width="100%"]` |

Developers can filter `smarter_chat_show_launcher` (whether the launcher appears on the current page) and
`smarter_chat_launcher_config` (the embed script's options).

## Development

PHP isn't required: the PHP targets run in Docker when `php` isn't installed.

```console
make init        # composer and npm dev dependencies, pre-commit hooks
make embed       # copy the embed script into smarter-chat/assets/ (EMBED_SOURCE=../smarter-chat/dist for a local build)
make test        # unit tests (PHPUnit + Brain Monkey)
make lint        # WordPress Coding Standards
make smoke       # a real WordPress in Docker, configured with WP-CLI, checked with curl
make dist        # build/smarter-chat-<version>.zip
```

The repository is developed with [Agent Skills](skills/README.md) for AI coding agents; start with
[CLAUDE.md](CLAUDE.md). Contributions are welcome: see [CONTRIBUTING.md](.github/CONTRIBUTING.md).

## License

The plugin is [GPL-2.0-or-later](LICENSE.txt), as WordPress is. The embed script that it ships is
AGPL-3.0-or-later, as Smarter is. See [doc/adr/013-licensing.md](doc/adr/013-licensing.md).
