<?php
/**
 * Tests of Smarter_Chat_Plugin and Smarter_Chat_Privacy.
 *
 * @package SmarterChat
 */

use Brain\Monkey\Functions;

/**
 * Every hook is registered, once, with the right priority.
 */
class PluginTest extends Smarter_Chat_Test_Case {

	public function test_boot_registers_every_hook() {
		Smarter_Chat_Plugin::boot();
		$this->assertSame( 10, has_action( 'init', array( 'Smarter_Chat_Shortcode', 'register' ) ) );
		$this->assertSame( 5, has_action( 'wp_enqueue_scripts', array( 'Smarter_Chat_Embed', 'register' ) ) );
		$this->assertSame( 10, has_action( 'wp_enqueue_scripts', array( 'Smarter_Chat_Embed', 'enqueue_launcher' ) ) );
		$this->assertSame( 10, has_action( 'admin_init', array( 'Smarter_Chat_Settings', 'register' ) ) );
		$this->assertSame( 10, has_action( 'admin_init', array( 'Smarter_Chat_Privacy', 'register' ) ) );
		$this->assertSame( 10, has_action( 'admin_menu', array( 'Smarter_Chat_Settings', 'add_page' ) ) );
		$this->assertSame( 10, has_filter( 'plugin_action_links_smarter-chat/smarter-chat.php', array( 'Smarter_Chat_Settings', 'action_links' ) ) );
	}

	public function test_privacy_adds_the_suggested_text() {
		Functions\expect( 'wp_add_privacy_policy_content' )->once()->with( 'Smarter Chat', \Mockery::on(
			function ( $html ) {
				return false !== strpos( $html, 'smarter_chat_' ) && false !== strpos( $html, 'local storage' );
			}
		) );
		Smarter_Chat_Privacy::register();
	}

	public function test_the_plugin_header_and_readme_agree_on_the_version() {
		$main   = file_get_contents( SMARTER_CHAT_FILE ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$readme = file_get_contents( SMARTER_CHAT_DIR . 'readme.txt' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		preg_match( '/^ \* Version:\s+(\S+)/m', $main, $header );
		preg_match( "/define\( 'SMARTER_CHAT_VERSION', '([^']+)' \)/", $main, $constant );
		preg_match( '/^Stable tag:\s+(\S+)/m', $readme, $stable );
		$this->assertSame( $header[1], $constant[1] );
		$this->assertSame( $header[1], $stable[1] );
		preg_match( "/define\( 'SMARTER_CHAT_EMBED_VERSION', '([^']+)' \)/", $main, $embed );
		$this->assertSame( SMARTER_CHAT_EMBED_VERSION, $embed[1] );
	}
}
