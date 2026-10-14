<?php
/**
 * Validators shared by the settings, the shortcode and the block.
 *
 * Every value that reaches a page goes through one of these. They return a valid value, or the default, so that
 * an invalid value never reaches the page. See doc/adr/006-security.md.
 *
 * @package SmarterChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pure validators: no WordPress state, no side effects.
 */
class Smarter_Chat_Validation {

	/**
	 * A chatbot url: http or https, with a host, and without credentials, spaces or quotes.
	 *
	 * @param mixed $value The value to validate.
	 * @return string The url, or '' if it isn't valid.
	 */
	public static function url( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}
		$value = trim( $value );
		if ( '' === $value || preg_match( '/[\s"\'<>\\\\]/', $value ) ) {
			return '';
		}
		$parts = wp_parse_url( $value );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || empty( $parts['scheme'] ) ) {
			return '';
		}
		if ( ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
			return '';
		}
		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
			return '';
		}
		return esc_url_raw( $value, array( 'http', 'https' ) );
	}

	/**
	 * A css length: a number with a unit, or a percentage, e.g. 600px, 70vh, 100%.
	 *
	 * @param mixed  $value   The value to validate.
	 * @param string $fallback The value to use if it isn't valid.
	 * @return string The length, or the default.
	 */
	public static function length( $value, $fallback ) {
		if ( is_string( $value ) && preg_match( '/^\d{1,5}(\.\d{1,3})?(px|em|rem|vh|vw|svh|dvh|%)$/', trim( $value ) ) ) {
			return trim( $value );
		}
		return $fallback;
	}

	/**
	 * A hex color, e.g. #ff5733.
	 *
	 * @param mixed  $value   The value to validate.
	 * @param string $fallback The value to use if it isn't valid.
	 * @return string The color, or the default.
	 */
	public static function color( $value, $fallback ) {
		$color = is_string( $value ) ? sanitize_hex_color( trim( $value ) ) : null;
		return $color ? $color : $fallback;
	}

	/**
	 * One of a set of values.
	 *
	 * @param mixed    $value   The value to validate.
	 * @param string[] $choices The valid values.
	 * @param string   $fallback The value to use if it isn't one of them.
	 * @return string The value, or the default.
	 */
	public static function choice( $value, array $choices, $fallback ) {
		return is_string( $value ) && in_array( $value, $choices, true ) ? $value : $fallback;
	}

	/**
	 * A single line of text, without tags, of at most $max characters.
	 *
	 * @param mixed  $value   The value to validate.
	 * @param string $fallback The value to use if it is empty.
	 * @param int    $max     The maximum length.
	 * @return string The text, or the default.
	 */
	public static function text( $value, $fallback, $max = 100 ) {
		$text = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
		$text = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $max ) : substr( $text, 0, $max );
		return '' === $text ? $fallback : $text;
	}

	/**
	 * A Smarter api key: letters, digits and a few separators, or ''.
	 *
	 * @param mixed $value The value to validate.
	 * @return string The key, or ''.
	 */
	public static function api_key( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';
		return preg_match( '/^[A-Za-z0-9_.:-]{1,256}$/', $value ) ? $value : '';
	}

	/**
	 * A boolean, from a checkbox, a shortcode attribute or an option.
	 *
	 * @param mixed $value The value to validate.
	 * @return bool True for true, 1, '1', 'true', 'yes' and 'on'.
	 */
	public static function boolean( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		return in_array( strtolower( trim( (string) $value ) ), array( '1', 'true', 'yes', 'on' ), true );
	}
}
