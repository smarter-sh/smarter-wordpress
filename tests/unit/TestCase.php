<?php
/**
 * The base class of the unit tests: Brain Monkey, and minimal, faithful versions of the WordPress functions that the
 * plugin uses to validate and escape, so that the tests of escaping mean something.
 *
 * @package SmarterChat
 */

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Sets up Brain Monkey around each test.
 */
abstract class Smarter_Chat_Test_Case extends TestCase {

	/**
	 * The saved option, which get_option returns.
	 *
	 * @var mixed
	 */
	protected $option = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$escape = static function ( $text ) {
			return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
		};
		Functions\stubs(
			array(
				'__'                  => null,
				'esc_html__'          => $escape,
				'esc_attr'            => $escape,
				'esc_html'            => $escape,
				'esc_url'             => static function ( $url ) use ( $escape ) {
					return preg_match( '#^https?://#i', (string) $url ) ? $escape( $url ) : '';
				},
				'esc_url_raw'         => null,
				'wp_kses_post'        => null,
				'wp_parse_url'        => static function ( $url, $component = -1 ) {
					return parse_url( $url, $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- this is wp_parse_url.
				},
				'wp_json_encode'      => static function ( $data, $flags = 0 ) {
					return json_encode( $data, $flags ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- this is wp_json_encode.
				},
				'sanitize_text_field' => static function ( $text ) {
					return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags_fake( $text ) ) );
				},
				'sanitize_hex_color'  => static function ( $color ) {
					return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ? $color : null;
				},
				'untrailingslashit'   => static function ( $value ) {
					return rtrim( $value, '/\\' );
				},
				'shortcode_atts'      => static function ( $pairs, $atts ) {
					$out = array();
					foreach ( $pairs as $name => $default ) {
						$out[ $name ] = array_key_exists( $name, $atts ) ? $atts[ $name ] : $default;
					}
					return $out;
				},
				'checked'             => static function ( $checked, $current, $display ) {
					return (string) $checked === (string) $current ? " checked='checked'" : '';
				},
				'selected'            => static function ( $selected, $current, $display ) {
					return (string) $selected === (string) $current ? " selected='selected'" : '';
				},
				'home_url'            => 'https://www.example.com',
				'admin_url'           => static function ( $path ) {
					return 'https://www.example.com/wp-admin/' . $path;
				},
				'plugin_basename'     => 'smarter-chat/smarter-chat.php',
			)
		);
		Functions\when( 'get_option' )->alias(
			function () {
				return $this->option;
			}
		);
	}

	protected function tearDown(): void {
		// Mockery's expectations, e.g. Functions\expect()->once(), are assertions too.
		$this->addToAssertionCount( \Mockery::getContainer()->mockery_getExpectationCount() );
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Saved settings, merged with the valid defaults.
	 *
	 * @param array<string, mixed> $settings The settings to save.
	 * @return void
	 */
	protected function save( array $settings ) {
		$this->option = array_merge(
			array(
				'chatbot'  => 'https://support.3141-5926-5359.api.example.com/',
				'launcher' => '1',
			),
			$settings
		);
	}
}

/**
 * Strips tags, as wp_strip_all_tags does, for the fake sanitize_text_field.
 *
 * @param string $text The text.
 * @return string
 */
function wp_strip_all_tags_fake( $text ) {
	return strip_tags( preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $text ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- this is wp_strip_all_tags.
}
