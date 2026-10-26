<?php
/**
 * Plugin Name:       Smarter Chat
 * Plugin URI:        https://github.com/smarter-sh/smarter-wordpress
 * Description:       Add a Smarter chatbot to your site: a chat launcher on every page, a [smarter_chat] shortcode, and a Smarter Chat block.
 * Version:           0.2.0-alpha.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Smarter
 * Author URI:        https://smarter.sh
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smarter-chat
 * Domain Path:       /languages
 *
 * @package SmarterChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The plugin's version. Managed by semantic-release (release.config.js).
 */
define( 'SMARTER_CHAT_VERSION', '0.2.0-alpha.1' );

/**
 * The version of @smarter.sh/ui-chat whose embed script the plugin ships in assets/ and loads from there.
 * `make embed` copies it. See doc/adr/003-vendored-embed.md.
 */
define( 'SMARTER_CHAT_EMBED_VERSION', '0.36.10' );

define( 'SMARTER_CHAT_FILE', __FILE__ );
define( 'SMARTER_CHAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMARTER_CHAT_URL', plugin_dir_url( __FILE__ ) );

require_once SMARTER_CHAT_DIR . 'includes/class-smarter-chat-validation.php';
require_once SMARTER_CHAT_DIR . 'includes/class-smarter-chat-settings.php';
require_once SMARTER_CHAT_DIR . 'includes/class-smarter-chat-embed.php';
require_once SMARTER_CHAT_DIR . 'includes/class-smarter-chat-shortcode.php';
require_once SMARTER_CHAT_DIR . 'includes/class-smarter-chat-privacy.php';
require_once SMARTER_CHAT_DIR . 'includes/class-smarter-chat-plugin.php';

Smarter_Chat_Plugin::boot();
