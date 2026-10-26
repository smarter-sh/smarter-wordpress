=== Smarter Chat ===
Contributors: lpm0073
Tags: chatbot, chat, ai, llm, assistant
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.2.0-alpha.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add your Smarter chatbot to your site: a chat button on every page, a block, and a shortcode.

== Description ==

[Smarter](https://smarter.sh) is a platform for building and running chatbots and other applications
of large language models. You declare a chatbot, an LLMClient, in a short YAML file, with its model,
instructions, tools and look, and deploy it. This plugin puts that chatbot on your WordPress site.

* **A chat button on every page.** It opens the chat in a panel, and the conversation follows your visitors from page to page.
* **The Smarter Chat block** and the **[smarter_chat] shortcode** put the chat in a page, e.g. a Support page.
* **Your theme can't break it.** The chat renders in its own Shadow DOM, with its own styles. Its colors, fonts, logo and avatars come from the chatbot's manifest, so you change its look on Smarter, not in WordPress.
* Markdown, highlighted code, math and diagrams in answers, and the progress of the chatbot's tool calls while it works.
* Nothing loads until a visitor opens the chat.

You need a deployed LLMClient on a Smarter platform. See [Smarter's documentation](https://docs.smarter.sh/en/latest/smarter-framework/smarter-wordpress.html).

== Installation ==

1. Install and activate the plugin.
2. In your LLMClient's manifest, allow your site's origin (shown on the settings screen), and apply it:

    spec:
      config:
        prompt:
          allowedOrigins:
            - https://www.example.com

3. In **Settings → Smarter Chat**, enter the LLMClient's url, e.g. `https://support.3141-5926-5359.api.example.com/`, and save.

The chat button appears on every page. To put the chat in a page instead, turn off **Show on every page**, and add the Smarter Chat block, or `[smarter_chat height="600px"]`.

== Frequently Asked Questions ==

= The chat says it is not available, or the browser console says "blocked by CORS policy" =

Your site's origin is not in the LLMClient's `allowedOrigins`. It must match exactly: scheme, host and port.

= Does my chatbot need an api key? =

Only if the LLMClient has one. A key entered here is written into your pages, where any visitor can read it. Prefer an LLMClient without api keys, with a budget, or a key that you use only for this site.

= Can I show the button only on some pages? =

Yes, with the `smarter_chat_show_launcher` filter, e.g. `add_filter( 'smarter_chat_show_launcher', 'is_front_page' );`.

= Can I change the launcher's options in code? =

Filter `smarter_chat_launcher_config`. Its value is the embed script's options, e.g. `chatbot`, `position`, `label`, `color`, `width` and `height`.

== External services ==

This plugin connects your visitors' browsers to the **Smarter platform** that hosts the chatbot whose url you enter in its settings. Nothing is sent until you enter that url. When a visitor opens the chat, their browser requests the chatbot's configuration; when they send a message, it sends the conversation, a chat session key and your site's origin. The platform may send the conversation to the large language model provider that the chatbot uses. The Smarter platform is operated by whoever runs it: you, if you run your own, or the operator of the hosted platform that you use (see [smarter.sh](https://smarter.sh)). Read that operator's terms and privacy policy, and link them from your own.

== Third-party code ==

The chat itself is the embed script of [@smarter.sh/ui-chat](https://www.npmjs.com/package/@smarter.sh/ui-chat), in `assets/`, licensed under the AGPL-3.0-or-later, which includes React (MIT), Mermaid (MIT) and KaTeX (MIT). The plugin ships it compiled and minified. Its human-readable source, and the instructions to build it, are at https://github.com/smarter-sh/smarter/tree/main/smarter/react/packages/smarter.sh-chat (`npm run build:lib && npm run build:embed`). The plugin's own source is at https://github.com/smarter-sh/smarter-wordpress.

== Changelog ==

See [the changelog](https://github.com/smarter-sh/smarter-wordpress/blob/main/CHANGELOG.md).
