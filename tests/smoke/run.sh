#!/usr/bin/env bash
#------------------------------------------------------------------------------
# The smoke test: a real WordPress, in Docker, with the plugin from the working
# tree. It installs WordPress, activates the plugin, configures it with WP-CLI,
# and checks the rendered pages with curl. It never contacts a Smarter platform.
# See skills/smarter-wordpress-testing.
#
#   tests/smoke/run.sh                       # WordPress latest, on port 8089
#   WORDPRESS_VERSION=6.5 SMOKE_PORT=8090 tests/smoke/run.sh
#------------------------------------------------------------------------------
set -euo pipefail
cd "$(dirname "$0")"
export SMOKE_PORT=${SMOKE_PORT:-8089}
URL="http://localhost:$SMOKE_PORT"
COMPOSE="docker compose -f docker-compose.yml"
CHATBOT="https://chat.example.com/"
FAILURES=0

cleanup() { $COMPOSE --profile cli down -v --remove-orphans >/dev/null 2>&1 || true; }
trap cleanup EXIT

wp() { $COMPOSE run --rm -T cli wp "$@"; }
check() { # check <description> <url> <grep -E pattern> [absent]
  local html
  html=$(curl -s "$2")
  if [[ "${4:-}" == "absent" ]]; then
    if grep -Eq "$3" <<<"$html"; then echo "FAIL: $1"; FAILURES=$((FAILURES + 1)); else echo "ok:   $1"; fi
  else
    if grep -Eq "$3" <<<"$html"; then echo "ok:   $1"; else
      echo "FAIL: $1"; FAILURES=$((FAILURES + 1))
      grep -Eo '<script[^>]*smarter-chat[^>]*>|<smarter-chat[^>]*>' <<<"$html" | sed 's/^/      /' || true
    fi
  fi
}

$COMPOSE up -d --wait db wordpress >/dev/null
for _ in $(seq 1 60); do curl -s -o /dev/null "$URL" && break; sleep 1; done

wp core install --url="$URL" --title="Smoke" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email >/dev/null
wp plugin activate smarter-chat >/dev/null
wp option update smarter_chat_settings "{\"chatbot\":\"$CHATBOT\",\"launcher\":\"1\",\"label\":\"Ask <b>us</b>\",\"position\":\"bottom-left\",\"color\":\"#123456\"}" --format=json >/dev/null
PAGE=$(wp post create --post_type=page --post_status=publish --post_title=Support --porcelain \
  --post_content='[smarter_chat height="500px" chatbot="https://sales.example.com/"] [smarter_chat height="1px\" onload=\"alert(1)"]')
BLOCK=$(wp post create --post_type=page --post_status=publish --post_title=Block --porcelain \
  --post_content='<!-- wp:smarter/chat {"chatbot":"https://block.example.com/","height":"70vh"} /-->')

echo "WordPress $(wp core version), plugin $(wp plugin get smarter-chat --field=version)"
check "the launcher's configuration is in the home page" "$URL/" 'window\.SmarterChatConfig = \{"chatbot":"https://chat\.example\.com/","mode":"launcher","position":"bottom-left"'
check "the embed script is loaded, async" "$URL/" '<script[^>]* async[^>]*src="[^"]+(assets/embed\.js\?ver=|ui-chat@)|<script[^>]+src="[^"]+(assets/embed\.js\?ver=|ui-chat@)[^>]* async'
check "the label is sanitized" "$URL/" '"label":"Ask us"'
check "the shortcode renders the element" "$URL/?page_id=$PAGE" '<smarter-chat chatbot="https://sales\.example\.com/" height="500px" width="100%"></smarter-chat>'
check "a hostile shortcode attribute falls back to the default" "$URL/?page_id=$PAGE" '<smarter-chat chatbot="https://chat\.example\.com/" height="600px" width="100%"></smarter-chat>'
check "no attribute injection" "$URL/?page_id=$PAGE" 'onload=.alert' absent
check "the block renders the element" "$URL/?page_id=$BLOCK" '<smarter-chat chatbot="https://block\.example\.com/" height="70vh" width="100%"></smarter-chat>'

wp option patch update smarter_chat_settings launcher "" >/dev/null
check "without the launcher, the home page has no script" "$URL/" 'smarter-chat-embed|SmarterChatConfig' absent
check "without the launcher, a page with a chat still loads the script" "$URL/?page_id=$PAGE" 'id="smarter-chat-embed-js"'
check "the settings screen is registered" "$URL/wp-login.php" 'wp-submit'

if [[ -f ../../smarter-chat/assets/embed.js ]]; then
  code=$(curl -s -o /dev/null -w '%{http_code}' "$URL/wp-content/plugins/smarter-chat/assets/embed/mermaid/mermaid.js")
  [[ "$code" == "200" ]] && echo "ok:   the embed script's files are served beside it" || { echo "FAIL: embed/mermaid/mermaid.js is http $code"; FAILURES=$((FAILURES + 1)); }
fi

if [[ $FAILURES -gt 0 ]]; then echo "$FAILURES check(s) failed"; exit 1; fi
echo "all checks passed"
