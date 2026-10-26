#!/usr/bin/env bash
#------------------------------------------------------------------------------
# Builds build/smarter-chat-<version>.zip: the plugin directory, with the
# latest embed script (scripts/embed.sh), as WordPress installs it.
#
#   scripts/dist.sh                           # with the latest @smarter.sh/ui-chat
#   EMBED_VERSION=0.36.10 scripts/dist.sh     # with a given version
#   SKIP_EMBED=1 scripts/dist.sh              # with the embed script already in assets/
#------------------------------------------------------------------------------
set -euo pipefail
cd "$(dirname "$0")/.."

VERSION=$(sed -n 's/^ \* Version: *\(.*\)$/\1/p' smarter-chat/smarter-chat.php)
[[ -n "${SKIP_EMBED:-}" && -f smarter-chat/assets/embed.js ]] || scripts/embed.sh
rm -rf build/zip && mkdir -p build/zip
cp -R smarter-chat build/zip/smarter-chat
# WordPress.org's Plugin Check rejects hidden files, e.g. the .gitkeep files that keep empty folders in git.
find build/zip -name '.*' -type f -delete
rm -f "build/smarter-chat-$VERSION.zip" # zip -r adds to an existing archive, keeping files that are gone.
(cd build/zip && zip -qr "../smarter-chat-$VERSION.zip" smarter-chat)
rm -rf build/zip
echo "build/smarter-chat-$VERSION.zip $(du -h "build/smarter-chat-$VERSION.zip" | cut -f1)"
