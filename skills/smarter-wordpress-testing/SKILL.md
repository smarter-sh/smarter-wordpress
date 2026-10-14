---
name: smarter-wordpress-testing
description: Use when writing, fixing or running the smarter-wordpress plugin's tests, or raising coverage. Covers the PHPUnit + Brain Monkey unit tests (faking WordPress functions, hooks and escaping), running them in Docker when php isn't installed, the Docker WordPress smoke test with WP-CLI, coverage, and what never to do in a test.
---

# Smarter Chat for WordPress: Testing

## Unit tests

`tests/unit/`, one `*Test.php` per class in `smarter-chat/includes/`, run by
`make test`. They use PHPUnit 9 (for PHP 7.4) and
[Brain Monkey](https://giuseppe-mazzapica.gitbook.io/brain-monkey/), so they
need no WordPress and no database.

- Every test class extends `Smarter_Chat_Test_Case` (`tests/unit/TestCase.php`),
  which calls `Brain\Monkey\setUp()`/`tearDown()` and stubs WordPress's escaping
  and sanitizing functions with faithful, minimal versions (`esc_attr`,
  `esc_url`, `esc_html`, `sanitize_text_field`, `sanitize_hex_color`,
  `wp_json_encode`, `__`, ...). Use those, rather than `Functions\stubs()`
  with identity functions, so that tests of escaping mean something.
- Fake an option with `Functions\when( 'get_option' )->justReturn( [...] )`.
- Assert hooks with `has_action()`/`has_filter()` after `Smarter_Chat_Plugin::boot()`,
  and enqueues with `Functions\expect( 'wp_enqueue_script' )->once()->with( ... )`.
- Test hostile input: every validator and every rendered attribute gets a
  test with `"><script>`, `javascript:` urls and css injection
  (`red; background:url(...)`).

`make test` runs `vendor/bin/phpunit` with the local `php` if there is one,
and otherwise in Docker (`composer:2` image, which includes PHP), mounting the
repository. `make coverage` adds `--coverage-text` (needs Xdebug or PCOV; the
Docker target installs PCOV).

## Smoke test

`make smoke` (`tests/smoke/run.sh`) starts `wordpress` and `mariadb` with
Docker Compose on an unused port, installs WordPress with the `wordpress:cli`
image, activates the plugin from the working tree, sets
`smarter_chat_settings`, creates a page with `[smarter_chat]` and a page with
the block, and checks with `curl` that:

- the home page has the embed script tag and `window.SmarterChatConfig` (launcher on),
- the shortcode's page has a `<smarter-chat chatbot="...">` element,
- hostile values set through WP-CLI come out escaped,
- turning the launcher off removes the script from pages without a chat.

It tears everything down at the end, even on failure. It never contacts a
Smarter platform: the chatbot url is `https://chat.example.com/`.

## Never

- Never point a test at a real Smarter platform or a real api key.
- Never leave the smoke test's containers running.
