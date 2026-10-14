<?php
/**
 * The embed script of @smarter.sh/ui-chat, which is the chat: its registration, and the launcher's configuration.
 *
 * See doc/adr/002-embed-script.md and doc/adr/003-vendored-embed.md.
 *
 * @package SmarterChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads the embed script, and configures the site-wide launcher.
 */
class Smarter_Chat_Embed {

	const HANDLE = 'smarter-chat-embed';

	/**
	 * The flags with which values are written into the page's JavaScript, so that none can close its script element.
	 */
	const JSON_FLAGS = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES;

	/**
	 * Whether the plugin has its copy of the embed script, which `make embed` and the release zip provide.
	 *
	 * @return bool
	 */
	public static function has_local_script() {
		return file_exists( SMARTER_CHAT_DIR . 'assets/embed.js' );
	}

	/**
	 * The embed script's url on jsDelivr, of the version that the plugin ships.
	 *
	 * @return string
	 */
	public static function cdn_url() {
		return 'https://cdn.jsdelivr.net/npm/@smarter.sh/ui-chat@' . SMARTER_CHAT_EMBED_VERSION . '/dist/embed.js';
	}

	/**
	 * The url that the browser loads the embed script from: the plugin's copy, unless the source is the CDN, or the
	 * copy is missing.
	 *
	 * @param array<string, mixed> $settings The settings.
	 * @return string
	 */
	public static function script_url( array $settings ) {
		if ( 'local' === $settings['source'] && self::has_local_script() ) {
			return SMARTER_CHAT_URL . 'assets/embed.js';
		}
		return self::cdn_url();
	}

	/**
	 * Registers the embed script, async, in the footer. Hooked to wp_enqueue_scripts, early, so that the shortcode
	 * and the block can enqueue it.
	 *
	 * @return void
	 */
	public static function register() {
		$settings = Smarter_Chat_Settings::get();
		$url      = self::script_url( $settings );
		wp_register_script(
			self::HANDLE,
			$url,
			array(),
			// the CDN's url has its version. The plugin's copy has it as ?ver=, for browsers' caches.
			self::cdn_url() === $url ? null : SMARTER_CHAT_EMBED_VERSION,
			array(
				'strategy'  => 'async',
				'in_footer' => true,
			)
		);
	}

	/**
	 * The launcher's window.SmarterChatConfig: the embed script's options, in camelCase, from the settings. Options
	 * that are their embed script defaults are left out.
	 *
	 * @param array<string, mixed> $settings The settings.
	 * @return array<string, mixed>
	 */
	public static function config( array $settings ) {
		$config = array(
			'chatbot'  => $settings['chatbot'],
			'mode'     => 'launcher',
			'position' => $settings['position'],
			'label'    => $settings['label'],
			'color'    => $settings['color'],
		);
		if ( '' !== $settings['api_key'] ) {
			$config['apiKey'] = $settings['api_key'];
		}
		if ( $settings['open'] ) {
			$config['open'] = true;
		}
		/**
		 * Filters the launcher's configuration, the embed script's options, before it is written into the page.
		 *
		 * @param array<string, mixed> $config   The options, e.g. chatbot, position, label, color.
		 * @param array<string, mixed> $settings The plugin's settings.
		 */
		return apply_filters( 'smarter_chat_launcher_config', $config, $settings );
	}

	/**
	 * The inline script that sets window.SmarterChatConfig, before the embed script runs.
	 *
	 * @param array<string, mixed> $config The launcher's configuration.
	 * @return string
	 */
	public static function config_script( array $config ) {
		return 'window.SmarterChatConfig = ' . wp_json_encode( $config, self::JSON_FLAGS ) . ';';
	}

	/**
	 * Enqueues the embed script with the launcher's configuration, on the front end, when the launcher is on and a
	 * chatbot is set. Hooked to wp_enqueue_scripts.
	 *
	 * @return void
	 */
	public static function enqueue_launcher() {
		$settings = Smarter_Chat_Settings::get();
		if ( '' === $settings['chatbot'] || ! $settings['launcher'] ) {
			return;
		}
		/**
		 * Filters whether the launcher appears on the current page.
		 *
		 * @param bool $show Whether to show it. Default true.
		 */
		if ( ! apply_filters( 'smarter_chat_show_launcher', true ) ) {
			return;
		}
		wp_enqueue_script( self::HANDLE );
		wp_add_inline_script( self::HANDLE, self::config_script( self::config( $settings ) ), 'before' );
	}

	/**
	 * Enqueues the embed script, for a chat in the page's content.
	 *
	 * @return void
	 */
	public static function enqueue() {
		if ( ! wp_script_is( self::HANDLE, 'registered' ) ) {
			self::register();
		}
		wp_enqueue_script( self::HANDLE );
	}
}
