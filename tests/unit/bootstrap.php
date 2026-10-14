<?php
/**
 * The unit tests' bootstrap: Composer's autoloader, the constants that smarter-chat.php defines, and the plugin's
 * classes, without WordPress. See tests/unit/TestCase.php.
 *
 * @package SmarterChat
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

define( 'ABSPATH', '/var/www/html/' );
define( 'SMARTER_CHAT_VERSION', '0.1.0' );
define( 'SMARTER_CHAT_EMBED_VERSION', '0.9.0' );
define( 'SMARTER_CHAT_FILE', dirname( __DIR__, 2 ) . '/smarter-chat/smarter-chat.php' );
define( 'SMARTER_CHAT_DIR', dirname( __DIR__, 2 ) . '/smarter-chat/' );
define( 'SMARTER_CHAT_URL', 'https://www.example.com/wp-content/plugins/smarter-chat/' );

foreach ( array( 'validation', 'settings', 'embed', 'shortcode', 'privacy', 'plugin' ) as $smarter_chat_class ) {
	require_once SMARTER_CHAT_DIR . "includes/class-smarter-chat-{$smarter_chat_class}.php";
}

require_once __DIR__ . '/TestCase.php';
