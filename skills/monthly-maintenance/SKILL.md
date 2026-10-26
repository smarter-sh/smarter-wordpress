---
name: monthly-maintenance
description: Use when asked to do smarter-wordpress's monthly maintenance, or part of it - bump the vendored @smarter.sh/ui-chat embed script to its latest release and pick up its new options, update "Tested up to" for the current WordPress release, update the composer and npm dev dependencies, retest (unit and smoke), and write a "fix" or "feat" git commit.
---

# Monthly Maintenance

1. **The embed script.** Every build already ships the latest release
   (`scripts/embed.sh`), so the version takes care of itself. Compare
   `SMARTER_CHAT_EMBED_VERSION` in `smarter-chat/smarter-chat.php` with
   `npm view @smarter.sh/ui-chat version` only to find new options: read the
   embed script's changelog between them, especially changes to
   `src/embed/options.ts` (new or changed options) and to the cookies and
   localStorage keys, which `readme.txt`'s privacy text and
   `Smarter_Chat_Privacy` describe. Run `make embed`, and add settings for new options worth exposing (see "Adding an option" in
   `smarter-wordpress-development`). A new minor version is a `feat`, a patch a
   `fix` (`doc/adr/011-versioning.md`).
2. **WordPress.** Set `Tested up to:` in `smarter-chat/readme.txt` to the
   current major release (`curl -s https://api.wordpress.org/core/version-check/1.7/`),
   after the smoke test passes with the `wordpress:latest` image.
3. **Dependencies.** `composer update` and `npm update`, in Docker for
   composer if needed; `venv/bin/pre-commit autoupdate`.
4. **Test.** `make lint test smoke`. All must pass before a commit.
5. **Commit.** Load `smarter-git-commits`, and name the embed version tested.
