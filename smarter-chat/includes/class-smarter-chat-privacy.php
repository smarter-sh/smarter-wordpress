<?php
/**
 * The plugin's suggested text for the site's privacy policy. See doc/adr/007-privacy.md.
 *
 * @package SmarterChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The privacy policy guide's content.
 */
class Smarter_Chat_Privacy {

	/**
	 * The suggested text, as html.
	 *
	 * @return string
	 */
	public static function content() {
		return '<p>' . esc_html__( 'This site uses Smarter Chat, a chatbot hosted on a Smarter platform. When you open the chat and send a message, your messages, a chat session key and this site\'s address are sent to the Smarter platform that hosts the chatbot, which may send them to a large language model provider to answer you.', 'smarter-chat' ) . '</p>'
			. '<p>' . esc_html__( 'The chat stores its session key in a cookie named smarter_chat_ followed by the chatbot\'s host name, for one day, so that the conversation continues as you move between pages. It also stores the chat\'s layout in your browser\'s local storage. It sets no other cookies, and does not track you.', 'smarter-chat' ) . '</p>';
	}

	/**
	 * Adds the text to the privacy policy guide. Hooked to admin_init.
	 *
	 * @return void
	 */
	public static function register() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content( __( 'Smarter Chat', 'smarter-chat' ), wp_kses_post( self::content() ) );
		}
	}
}
