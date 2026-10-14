---
name: smarter-code-quality
description: Use before finishing any change to the smarter-wordpress plugin, to lint and format it - PHPCS with the WordPress Coding Standards and PHPCompatibilityWP, Prettier, codespell, the generic pre-commit hooks and commitlint. Explains which tools are authoritative, how to run them under zsh, and WordPress-specific traps (escaping sniffs, nonces, Yoda conditions, text domain).
---

# Smarter Chat for WordPress: Code Quality

`.pre-commit-config.yaml` and `phpcs.xml.dist` are the standard. If a hook
passes, the code meets it; if it fails, fix the code. Never bypass a hook
(`--no-verify`, `SKIP=`), and never add a `phpcs:ignore` without a comment
that says why it is safe.

| Tool      | Configuration                         | Notes                                                                          |
| --------- | ------------------------------------- | ------------------------------------------------------------------------------ |
| PHPCS     | `phpcs.xml.dist`                      | `WordPress` ruleset, `PHPCompatibilityWP` for 7.4+, text domain `smarter-chat` |
| Prettier  | `.prettierrc.json`, `.prettierignore` | Markdown, YAML, JSON, the block's JavaScript                                   |
| codespell | `codespell.txt`                       | Real project words go in `codespell.txt`                                       |

## How to run

```console
make lint                                              # PHPCS (Docker if php isn't installed)
make lint-fix                                          # PHPCBF, then PHPCS
git diff --name-only | xargs venv/bin/pre-commit run --files
git ls-files --others --exclude-standard | xargs venv/bin/pre-commit run --files   # new files
```

Use `xargs`: under zsh, `--files $FILES` is one argument.

## WordPress traps

- `WordPress.Security.EscapeOutput`: escape at the point of output, not
  earlier. A function that returns html must escape inside it, and its caller
  echoes it with a `// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render()`.
- `WordPress.WP.I18n`: every string a user sees is translatable, with the
  text domain `smarter-chat`, as a literal (not a variable).
- Yoda conditions (`'cdn' === $source`) and `array()` vs `[]`: follow the
  ruleset; PHPCBF fixes most of it.
- Prefix every global: functions, constants, options and hooks start with
  `smarter_chat_` / `SMARTER_CHAT_` / `Smarter_Chat_`.
