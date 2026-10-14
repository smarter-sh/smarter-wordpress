# CLAUDE.md

Guidance for Claude Code (and other coding agents) working in this repository.

smarter-wordpress is **Smarter Chat for WordPress**, the WordPress plugin (slug
`smarter-chat`) that adds a Smarter chatbot to a WordPress site: a settings
screen, a site-wide chat launcher, a `[smarter_chat]` shortcode and a Smarter
Chat block. It has no chat code of its own. It loads the embed script of
[`@smarter.sh/ui-chat`](https://github.com/smarter-sh/smarter-chat)
(`../smarter-chat`), and writes the script's options into the page. The
chatbot itself is an LLMClient on a Smarter platform
([smarter-sh/smarter](https://github.com/smarter-sh/smarter), `../smarter`).

Detailed know-how lives in the Agent Skills under `skills/` (symlinked to
`.claude/skills`). **Load `smarter-wordpress-development` first for any
change.** This file holds only what applies to nearly every task.

## Hard rules

1. **Never run git write commands**, here, in `../smarter` or in
   `../smarter-chat`. "Write a git commit" means print the command as text.
   See `smarter-git-commits`.
2. **Run the tests yourself** after any change (`make test`), and the smoke
   test (`make smoke`) when you change a hook, the block or the enqueued
   script. Never ask the user to re-run them. Report failures honestly.
3. **Escape every output, validate every input**, with WordPress's functions.
   See `doc/adr/006-security.md`.
4. **The pre-commit hooks and PHPCS (WordPress Coding Standards) are the lint
   standard.** See `smarter-code-quality`.
5. **Coverage:** at least 90%, 95% where it takes moderate effort.
6. **This repository is public.** No api keys, tokens, account numbers,
   private hostnames, names or customer data in code, tests, docs or skills.
7. **Don't reimplement the chat.** A missing feature belongs in the embed
   script, in `../smarter-chat`, first.

## Repository map

| Path                            | Contents                                                         |
| ------------------------------- | ---------------------------------------------------------------- |
| `smarter-chat/`                 | The plugin, as it is installed in `wp-content/plugins/`          |
| `smarter-chat/smarter-chat.php` | The main plugin file: header, constants, bootstrap               |
| `smarter-chat/includes/`        | One class per concern: settings, embed, shortcode/block, privacy |
| `smarter-chat/blocks/chat/`     | The block's `block.json` and editor script (no build)            |
| `smarter-chat/assets/`          | `embed.js` and `embed/`, copied by `make embed` (git-ignored)    |
| `smarter-chat/readme.txt`       | The WordPress.org plugin page                                    |
| `tests/unit/`                   | PHPUnit + Brain Monkey unit tests                                |
| `tests/smoke/`                  | Docker WordPress smoke test                                      |
| `doc/adr/`                      | Architecture decision records                                    |
| `skills/`                       | Agent Skills                                                     |

The plugin's user documentation on docs.smarter.sh lives in the platform's
repository: `../smarter/docs/source/smarter-framework/smarter-wordpress.rst`.

## Common commands

```bash
make init        # composer and npm dev dependencies, pre-commit hooks
make embed       # copy embed.js and embed/ from @smarter.sh/ui-chat into smarter-chat/assets/
make test        # PHPUnit (in Docker if php isn't installed)
make coverage    # PHPUnit with coverage
make lint        # PHPCS, WordPress Coding Standards and PHPCompatibilityWP
make smoke       # WordPress + MariaDB in Docker: install, configure, check the pages
make dist        # build/smarter-chat-<version>.zip
git diff --name-only | xargs pre-commit run --files
```

## Definition of done

- The change follows an existing pattern and the ADRs in `doc/adr/`.
- Tests cover it, you ran them, and the smoke test passes when hooks changed.
- PHPCS and the pre-commit hooks pass on the changed files.
- `readme.txt` and docs.smarter.sh describe user-facing changes.
- Your summary says what changed, what you verified, and what you skipped.
