<?php
/**
 * Tests of Smarter_Chat_Shortcode.
 *
 * @package SmarterChat
 */

use Brain\Monkey\Functions;

/**
 * The shortcode and the block render an escaped <smarter-chat> element, and enqueue the embed script.
 */
class ShortcodeTest extends Smarter_Chat_Test_Case {

	/**
	 * The handles of the scripts that the test enqueued.
	 *
	 * @var string[]
	 */
	private $enqueued = array();

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'wp_script_is' )->justReturn( true );
		Functions\when( 'wp_enqueue_script' )->alias(
			function ( $handle ) {
				$this->enqueued[] = $handle;
			}
		);
	}

	public function test_renders_the_settings_chatbot_with_the_default_size() {
		$this->save( array() );
		$this->assertSame(
			'<smarter-chat chatbot="https://support.3141-5926-5359.api.example.com/" height="600px" width="100%"></smarter-chat>',
			Smarter_Chat_Shortcode::render( '' )
		);
		$this->assertSame( array( 'smarter-chat-embed' ), $this->enqueued );
	}

	public function test_renders_its_own_chatbot_and_size() {
		$this->save( array() );
		$this->assertSame(
			'<smarter-chat chatbot="https://sales.example.com/" height="70vh" width="400px"></smarter-chat>',
			Smarter_Chat_Shortcode::render(
				array(
					'chatbot' => 'https://sales.example.com/',
					'height'  => '70vh',
					'width'   => '400px',
				)
			)
		);
	}

	public function test_sends_the_api_key_only_to_the_settings_chatbot() {
		$this->save( array( 'api_key' => 'site-key' ) );
		$this->assertStringContainsString( 'api-key="site-key"', Smarter_Chat_Shortcode::render( array() ) );
		$this->assertStringContainsString( 'api-key="site-key"', Smarter_Chat_Shortcode::render( array( 'chatbot' => 'https://support.3141-5926-5359.api.example.com' ) ) );
		$this->assertStringNotContainsString( 'api-key', Smarter_Chat_Shortcode::render( array( 'chatbot' => 'https://evil.example.com/' ) ) );
	}

	public function test_a_contributors_hostile_attributes_are_neutralized() {
		$this->save( array() );
		$html = Smarter_Chat_Shortcode::render(
			array(
				'chatbot' => 'javascript:alert(1)',
				'height'  => '1px" onmouseover="alert(1)',
				'width'   => '<script>',
			)
		);
		$this->assertSame(
			'<smarter-chat chatbot="https://support.3141-5926-5359.api.example.com/" height="600px" width="100%"></smarter-chat>',
			$html
		);
	}

	public function test_without_a_chatbot_editors_see_a_notice_and_visitors_nothing() {
		$this->option = array();
		Functions\when( 'current_user_can' )->justReturn( true );
		$this->assertStringContainsString( 'smarter-chat-notice', Smarter_Chat_Shortcode::render( array() ) );
		Functions\when( 'current_user_can' )->justReturn( false );
		$this->assertSame( '', Smarter_Chat_Shortcode::render( array() ) );
		// no chat, no script.
		$this->assertSame( array(), $this->enqueued );
	}

	public function test_render_block_uses_the_blocks_attributes() {
		$this->save( array() );
		$this->assertSame(
			'<smarter-chat chatbot="https://block.example.com/" height="500px" width="100%"></smarter-chat>',
			Smarter_Chat_Shortcode::render_block(
				array(
					'chatbot' => 'https://block.example.com/',
					'height'  => '500px',
				)
			)
		);
		$this->assertStringContainsString( 'height="600px"', Smarter_Chat_Shortcode::render_block( null ) );
	}

	public function test_register_adds_the_shortcode_and_the_block() {
		Functions\expect( 'add_shortcode' )->once()->with( 'smarter_chat', array( 'Smarter_Chat_Shortcode', 'render' ) );
		Functions\expect( 'register_block_type' )->once()->with( SMARTER_CHAT_DIR . 'blocks/chat', array( 'render_callback' => array( 'Smarter_Chat_Shortcode', 'render_block' ) ) );
		Smarter_Chat_Shortcode::register();
	}

	public function test_block_json_matches_the_render_callback() {
		$block = json_decode( file_get_contents( SMARTER_CHAT_DIR . 'blocks/chat/block.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertSame( 'smarter/chat', $block['name'] );
		$this->assertSame( array( 'chatbot', 'height' ), array_keys( $block['attributes'] ) );
		$this->assertSame( '600px', $block['attributes']['height']['default'] );
		$this->assertSame( 'smarter-chat', $block['textdomain'] );
	}
}
