#!/usr/bin/env bash
#------------------------------------------------------------------------------
# Copies @smarter.sh/ui-chat's embed script (dist/embed.js and dist/embed/) into
# smarter-chat/assets/: by default the latest release on npm, whose version it
# writes into SMARTER_CHAT_EMBED_VERSION in smarter-chat/smarter-chat.php, so
# the plugin and its release commit record the version that they ship. See
# doc/adr/003-vendored-embed.md.
#
#   scripts/embed.sh                          # the latest release on npm
#   EMBED_VERSION=0.36.10 scripts/embed.sh    # a version, or an npm dist-tag
#   EMBED_SOURCE=../smarter-chat/dist scripts/embed.sh   # from a local build
#------------------------------------------------------------------------------
set -euo pipefail
cd "$(dirname "$0")/.."

MAIN=smarter-chat/smarter-chat.php
ASSETS=smarter-chat/assets
SOURCE=${EMBED_SOURCE:-}
LICENSE_FILE=""

if [[ -z "$SOURCE" ]]; then
  VERSION=$(npm view "@smarter.sh/ui-chat@${EMBED_VERSION:-latest}" version | tail -1)
  if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+ ]]; then
    echo "error: npm has no @smarter.sh/ui-chat@${EMBED_VERSION:-latest}" >&2
    exit 1
  fi
  TMP=$(mktemp -d)
  trap 'rm -rf "$TMP"' EXIT
  echo "Downloading @smarter.sh/ui-chat@$VERSION from npm"
  (cd "$TMP" && npm pack --silent "@smarter.sh/ui-chat@$VERSION" >/dev/null && tar -xzf ./*.tgz)
  SOURCE="$TMP/package/dist"
  LICENSE_FILE="$TMP/package/LICENSE"
else
  VERSION=$(sed -n "s/^define( 'SMARTER_CHAT_EMBED_VERSION', '\([^']*\)' );/\1/p" "$MAIN")
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
# record the version that the plugin ships: its ?ver= for browsers' caches, and the release commit's record of it.
sed -i.bak -E "s/^define\( 'SMARTER_CHAT_EMBED_VERSION', '[^']+' \);/define( 'SMARTER_CHAT_EMBED_VERSION', '$VERSION' );/" "$MAIN"
rm -f "$MAIN.bak"
echo "smarter-chat/assets: @smarter.sh/ui-chat $VERSION, embed.js $(du -h "$ASSETS/embed.js" | cut -f1), embed/ $(du -sh "$ASSETS/embed" | cut -f1)"
