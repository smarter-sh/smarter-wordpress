<?php
/**
 * Deletes the plugin's settings when it is deleted from the Plugins screen.
 *
 * @package SmarterChat
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'smarter_chat_settings' );
