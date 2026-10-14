<?php
/**
 * A chat in a page's content: the [smarter_chat] shortcode, and the Smarter Chat block, which renders the same way.
 *
 * See doc/adr/005-placement.md.
 *
 * @package SmarterChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a <smarter-chat> element.
 */
class Smarter_Chat_Shortcode {

	const TAG = 'smarter_chat';

	/**
	 * The shortcode's attributes, and their defaults.
	 *
	 * @return array<string, string>
	 */
	public static function defaults() {
		return array(
			'chatbot' => '',
			'height'  => '600px',
			'width'   => '100%',
		);
	}

	/**
	 * Renders [smarter_chat chatbot="..." height="..." width="..."].
	 *
	 * The chatbot defaults to the settings'. The site's api key is sent only to the settings' chatbot, never to a
	 * chatbot that a post names.
	 *
	 * @param array<string, string>|string $atts The shortcode's attributes, or '' if it has none.
	 * @return string The element, escaped, or a notice for editors when there is no chatbot.
	 */
	public static function render( $atts ) {
		$defaults = self::defaults();
		$atts     = shortcode_atts( $defaults, is_array( $atts ) ? $atts : array(), self::TAG );
		$settings = Smarter_Chat_Settings::get();
		$chatbot  = Smarter_Chat_Validation::url( $atts['chatbot'] );
		if ( '' === $chatbot ) {
			$chatbot = $settings['chatbot'];
		}
		if ( '' === $chatbot ) {
			return current_user_can( 'edit_posts' )
				? '<p class="smarter-chat-notice">' . esc_html__( 'Smarter Chat: set the chatbot url in Settings → Smarter Chat, or in the block.', 'smarter-chat' ) . '</p>'
				: '';
		}

		$attributes = array(
			'chatbot' => $chatbot,
			'height'  => Smarter_Chat_Validation::length( $atts['height'], $defaults['height'] ),
			'width'   => Smarter_Chat_Validation::length( $atts['width'], $defaults['width'] ),
		);
		if ( '' !== $settings['api_key'] && untrailingslashit( $chatbot ) === untrailingslashit( $settings['chatbot'] ) ) {
			$attributes['api-key'] = $settings['api_key'];
		}

		Smarter_Chat_Embed::enqueue();
		$html = '<smarter-chat';
		foreach ( $attributes as $name => $value ) {
			$html .= ' ' . $name . '="' . ( 'chatbot' === $name ? esc_url( $value ) : esc_attr( $value ) ) . '"';
		}
		return $html . '></smarter-chat>';
	}

	/**
	 * The Smarter Chat block's render callback.
	 *
	 * @param array<string, mixed> $attributes The block's attributes: chatbot and height.
	 * @return string
	 */
	public static function render_block( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : array();
		return self::render(
			array(
				'chatbot' => isset( $attributes['chatbot'] ) ? (string) $attributes['chatbot'] : '',
				'height'  => isset( $attributes['height'] ) ? (string) $attributes['height'] : '600px',
			)
		);
	}

	/**
	 * Registers the shortcode and the block. Hooked to init.
	 *
	 * @return void
	 */
	public static function register() {
		add_shortcode( self::TAG, array( __CLASS__, 'render' ) );
		register_block_type(
			SMARTER_CHAT_DIR . 'blocks/chat',
			array( 'render_callback' => array( __CLASS__, 'render_block' ) )
		);
	}
}
