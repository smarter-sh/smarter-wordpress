#!/usr/bin/env bash
#------------------------------------------------------------------------------
# Copies @smarter.sh/ui-chat's embed script (dist/embed.js and dist/embed/) into
# smarter-chat/assets/, at the version of SMARTER_CHAT_EMBED_VERSION in
# smarter-chat/smarter-chat.php. See doc/adr/003-vendored-embed.md.
#
#   scripts/embed.sh                          # from npm
#   EMBED_SOURCE=../smarter-chat/dist scripts/embed.sh   # from a local build
#------------------------------------------------------------------------------
set -euo pipefail
cd "$(dirname "$0")/.."

VERSION=$(sed -n "s/^define( 'SMARTER_CHAT_EMBED_VERSION', '\([^']*\)' );/\1/p" smarter-chat/smarter-chat.php)
ASSETS=smarter-chat/assets
SOURCE=${EMBED_SOURCE:-}
LICENSE_FILE=""

if [[ -z "$SOURCE" ]]; then
  TMP=$(mktemp -d)
  trap 'rm -rf "$TMP"' EXIT
  echo "Downloading @smarter.sh/ui-chat@$VERSION from npm"
  (cd "$TMP" && npm pack --silent "@smarter.sh/ui-chat@$VERSION" >/dev/null && tar -xzf ./*.tgz)
  SOURCE="$TMP/package/dist"
  LICENSE_FILE="$TMP/package/LICENSE"
else
  echo "Copying the embed script from $SOURCE (not necessarily version $VERSION)"
  LICENSE_FILE="$SOURCE/../LICENSE"
fi

if [[ ! -f "$SOURCE/embed.js" || ! -d "$SOURCE/embed" ]]; then
  echo "error: $SOURCE has no embed.js and embed/. Build it with: npm run build:lib && npm run build:embed" >&2
  exit 1
fi

rm -rf "$ASSETS/embed.js" "$ASSETS/embed"
mkdir -p "$ASSETS"
cp "$SOURCE/embed.js" "$ASSETS/embed.js"
cp -R "$SOURCE/embed" "$ASSETS/embed"
if [[ -f "$LICENSE_FILE" ]]; then
  cp "$LICENSE_FILE" "$ASSETS/embed/LICENSE"
fi
echo "smarter-chat/assets: embed.js $(du -h "$ASSETS/embed.js" | cut -f1), embed/ $(du -sh "$ASSETS/embed" | cut -f1)"
