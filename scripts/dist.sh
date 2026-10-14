#!/usr/bin/env bash
#------------------------------------------------------------------------------
# Builds build/smarter-chat-<version>.zip: the plugin directory, with the
# embed script (scripts/embed.sh), as WordPress installs it.
#------------------------------------------------------------------------------
set -euo pipefail
cd "$(dirname "$0")/.."

VERSION=$(sed -n 's/^ \* Version: *\(.*\)$/\1/p' smarter-chat/smarter-chat.php)
[[ -f smarter-chat/assets/embed.js ]] || scripts/embed.sh
rm -rf build/zip && mkdir -p build/zip
cp -R smarter-chat build/zip/smarter-chat
find build/zip -name '.DS_Store' -delete
(cd build/zip && zip -qr "../smarter-chat-$VERSION.zip" smarter-chat)
rm -rf build/zip
echo "build/smarter-chat-$VERSION.zip $(du -h "build/smarter-chat-$VERSION.zip" | cut -f1)"
