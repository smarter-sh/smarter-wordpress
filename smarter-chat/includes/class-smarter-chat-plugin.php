<?php
/**
 * The plugin's hooks, all registered here.
 *
 * @package SmarterChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers every hook of the plugin.
 */
class Smarter_Chat_Plugin {

	/**
	 * Registers the hooks. Called once, by smarter-chat.php.
	 *
	 * @return void
	 */
	public static function boot() {
		add_action( 'init', array( 'Smarter_Chat_Shortcode', 'register' ) );
		add_action( 'wp_enqueue_scripts', array( 'Smarter_Chat_Embed', 'register' ), 5 );
		add_action( 'wp_enqueue_scripts', array( 'Smarter_Chat_Embed', 'enqueue_launcher' ) );
		add_action( 'admin_init', array( 'Smarter_Chat_Settings', 'register' ) );
		add_action( 'admin_init', array( 'Smarter_Chat_Privacy', 'register' ) );
		add_action( 'admin_menu', array( 'Smarter_Chat_Settings', 'add_page' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SMARTER_CHAT_FILE ), array( 'Smarter_Chat_Settings', 'action_links' ) );
	}
}
