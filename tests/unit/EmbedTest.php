<?php
/**
 * Tests of Smarter_Chat_Embed.
 *
 * @package SmarterChat
 */

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * The embed script is registered async, in the footer, and the launcher's configuration can't close its script.
 */
class EmbedTest extends Smarter_Chat_Test_Case {

	/**
	 * The plugin's local copy of the embed script, created by tests that need it.
	 *
	 * @var string
	 */
	private $local_script;

	protected function setUp(): void {
		parent::setUp();
		$this->local_script = SMARTER_CHAT_DIR . 'assets/embed.js';
	}

	protected function tearDown(): void {
		if ( file_exists( $this->local_script . '.test' ) ) {
			unlink( $this->local_script . '.test' );
			unlink( $this->local_script );
		}
		parent::tearDown();
	}

	/**
	 * Creates the local copy of the embed script, unless `make embed` has.
	 */
	private function with_local_script() {
		if ( ! file_exists( $this->local_script ) ) {
			file_put_contents( $this->local_script, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $this->local_script . '.test', '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	public function test_script_url_is_the_plugins_copy() {
		$this->with_local_script();
		$this->assertTrue( Smarter_Chat_Embed::has_local_script() );
		$this->assertSame( 'https://www.example.com/wp-content/plugins/smarter-chat/assets/embed.js', Smarter_Chat_Embed::script_url() );
	}

	public function test_register_registers_the_script_async_in_the_footer() {
		$this->with_local_script();
		$this->save( array() );
		Functions\expect( 'wp_register_script' )->once()->with(
			'smarter-chat-embed',
			'https://www.example.com/wp-content/plugins/smarter-chat/assets/embed.js',
			array(),
			'0.9.0',
			array(
				'strategy'  => 'async',
				'in_footer' => true,
			)
		);
		Smarter_Chat_Embed::register();
	}

	public function test_sanitize_drops_the_retired_script_source_setting() {
		// 0.1.0 saved a "source" setting, local or cdn. The plugin now always loads its own copy.
		$this->assertArrayNotHasKey( 'source', Smarter_Chat_Settings::sanitize( array( 'source' => 'cdn' ) ) );
	}

	public function test_config_is_the_embed_scripts_options() {
		$settings = Smarter_Chat_Settings::sanitize(
			array(
				'chatbot'  => 'https://chat.example.com/',
				'position' => 'bottom-left',
				'label'    => 'Help',
				'color'    => '#000000',
			)
		);
		$this->assertSame(
			array(
				'chatbot'  => 'https://chat.example.com/',
				'mode'     => 'launcher',
				'position' => 'bottom-left',
				'label'    => 'Help',
				'color'    => '#000000',
			),
			Smarter_Chat_Embed::config( $settings )
		);
		$settings['api_key'] = 'k';
		$settings['open']    = true;
		$config              = Smarter_Chat_Embed::config( $settings );
		$this->assertSame( 'k', $config['apiKey'] );
		$this->assertTrue( $config['open'] );
	}

	public function test_config_can_be_filtered() {
		Filters\expectApplied( 'smarter_chat_launcher_config' )->once()->andReturnUsing(
			function ( $config ) {
				return array_merge( $config, array( 'height' => '500px' ) );
			}
		);
		$config = Smarter_Chat_Embed::config( Smarter_Chat_Settings::sanitize( array( 'chatbot' => 'https://chat.example.com/' ) ) );
		$this->assertSame( '500px', $config['height'] );
	}

	public function test_config_script_cannot_close_its_script_element() {
		$script = Smarter_Chat_Embed::config_script( array( 'label' => '</script><script>alert("x")</script>&\'' ) );
		$this->assertStringStartsWith( 'window.SmarterChatConfig = {', $script );
		$this->assertStringNotContainsString( '<', $script );
		$this->assertStringNotContainsString( '"x"', $script );
		$this->assertStringContainsString( "\\u003C/script\\u003E", $script );
		$this->assertStringContainsString( 'https://cdn', Smarter_Chat_Embed::config_script( array( 'chatbot' => 'https://cdn' ) ) );
	}

	public function test_enqueue_launcher_writes_the_configuration_before_the_script() {
		$this->save( array( 'label' => 'Help' ) );
		Functions\expect( 'wp_enqueue_script' )->once()->with( 'smarter-chat-embed' );
		Functions\expect( 'wp_add_inline_script' )->once()->with(
			'smarter-chat-embed',
			\Mockery::on(
				function ( $script ) {
					return 0 === strpos( $script, 'window.SmarterChatConfig = {"chatbot":"https://support.3141-5926-5359.api.example.com/","mode":"launcher"' );
				}
			),
			'before'
		);
		Smarter_Chat_Embed::enqueue_launcher();
	}

	public function test_enqueue_launcher_does_nothing_without_a_chatbot_or_when_it_is_off() {
		Functions\expect( 'wp_enqueue_script' )->never();
		$this->save( array( 'chatbot' => '' ) );
		Smarter_Chat_Embed::enqueue_launcher();
		$this->save( array( 'launcher' => '0' ) );
		$this->option = array( 'chatbot' => 'https://chat.example.com/' );
		Smarter_Chat_Embed::enqueue_launcher();
	}

	public function test_enqueue_launcher_respects_the_show_launcher_filter() {
		$this->save( array() );
		Filters\expectApplied( 'smarter_chat_show_launcher' )->once()->andReturn( false );
		Functions\expect( 'wp_enqueue_script' )->never();
		Smarter_Chat_Embed::enqueue_launcher();
	}

	public function test_enqueue_registers_the_script_if_needed() {
		$this->save( array() );
		Functions\when( 'wp_script_is' )->justReturn( false );
		Functions\expect( 'wp_register_script' )->once();
		Functions\expect( 'wp_enqueue_script' )->once()->with( 'smarter-chat-embed' );
		Smarter_Chat_Embed::enqueue();
		Functions\when( 'wp_script_is' )->justReturn( true );
		Functions\expect( 'wp_enqueue_script' )->once();
		Smarter_Chat_Embed::enqueue();
	}
}
