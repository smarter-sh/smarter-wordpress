<?php
/**
 * Tests of Smarter_Chat_Settings.
 *
 * @package SmarterChat
 */

use Brain\Monkey\Functions;

/**
 * The option is always valid and complete, and the settings screen escapes what it displays.
 */
class SettingsTest extends Smarter_Chat_Test_Case {

	public function test_get_returns_the_defaults_without_a_saved_option() {
		$this->option = false;
		$this->assertSame(
			array(
				'chatbot'  => '',
				'api_key'  => '',
				'launcher' => false,
				'position' => 'bottom-right',
				'label'    => 'Chat',
				'color'    => '#ff5733',
				'open'     => false,
			),
			Smarter_Chat_Settings::get()
		);
	}

	public function test_sanitize_keeps_valid_values() {
		$input = array(
			'chatbot'  => 'https://chat.example.com/',
			'api_key'  => 'abc123',
			'launcher' => '1',
			'position' => 'bottom-left',
			'label'    => 'Ask us',
			'color'    => '#123456',
			'open'     => '1',
		);
		$this->assertSame(
			array_merge(
				$input,
				array(
					'launcher' => true,
					'open'     => true,
				)
			),
			Smarter_Chat_Settings::sanitize( $input )
		);
	}

	public function test_sanitize_replaces_invalid_values_with_their_defaults() {
		$settings = Smarter_Chat_Settings::sanitize(
			array(
				'chatbot'  => 'javascript:alert(1)',
				'api_key'  => '"><script>',
				'position' => 'top',
				'label'    => '<b></b>',
				'color'    => 'red',
			)
		);
		$this->assertSame( '', $settings['chatbot'] );
		$this->assertSame( '', $settings['api_key'] );
		$this->assertFalse( $settings['launcher'] );
		$this->assertSame( 'bottom-right', $settings['position'] );
		$this->assertSame( 'Chat', $settings['label'] );
		$this->assertSame( '#ff5733', $settings['color'] );
		$this->assertSame( Smarter_Chat_Settings::get(), Smarter_Chat_Settings::sanitize( 'not an array' ) );
	}

	public function test_register_registers_the_option_its_sections_and_fields() {
		Functions\expect( 'register_setting' )->once()->with( 'smarter-chat', 'smarter_chat_settings', \Mockery::on(
			function ( $args ) {
				return array( 'Smarter_Chat_Settings', 'sanitize' ) === $args['sanitize_callback'] && 'array' === $args['type'];
			}
		) );
		Functions\expect( 'add_settings_section' )->times( 2 );
		Functions\expect( 'add_settings_field' )->times( 7 );
		Smarter_Chat_Settings::register();
	}

	public function test_add_page_and_action_links() {
		Functions\expect( 'add_options_page' )->once()->with( 'Smarter Chat', 'Smarter Chat', 'manage_options', 'smarter-chat', array( 'Smarter_Chat_Settings', 'render_page' ) );
		Smarter_Chat_Settings::add_page();
		$links = Smarter_Chat_Settings::action_links( array( '<a href="#">Deactivate</a>' ) );
		$this->assertSame( '<a href="https://www.example.com/wp-admin/options-general.php?page=smarter-chat">Settings</a>', $links[0] );
		$this->assertCount( 2, $links );
	}

	public function test_render_page_requires_manage_options() {
		Functions\when( 'current_user_can' )->justReturn( false );
		$this->expectOutputString( '' );
		Smarter_Chat_Settings::render_page();
	}

	public function test_render_page_warns_when_the_local_script_is_missing() {
		$this->save( array() );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_admin_page_title' )->justReturn( 'Smarter Chat' );
		Functions\expect( 'settings_fields' )->once()->with( 'smarter-chat' );
		Functions\expect( 'do_settings_sections' )->once()->with( 'smarter-chat' );
		Functions\expect( 'submit_button' )->once();
		Functions\when( 'esc_html_e' )->echoArg();
		ob_start();
		Smarter_Chat_Settings::render_page();
		$html = ob_get_clean();
		$this->assertStringContainsString( '<h1>Smarter Chat</h1>', $html );
		$this->assertStringContainsString( '<form action="options.php" method="post">', $html );
		$this->assertSame( ! Smarter_Chat_Embed::has_local_script(), false !== strpos( $html, 'notice-warning' ) );
	}

	public function test_the_chatbot_section_shows_the_origin_to_allow() {
		$this->expectOutputRegex( '#<code>https://www\.example\.com</code>#' );
		Smarter_Chat_Settings::render_chatbot_section();
	}

	public function test_site_origin() {
		$this->assertSame( 'https://www.example.com', Smarter_Chat_Settings::site_origin() );
		Functions\when( 'home_url' )->justReturn( 'http://LOCALHOST:8080/blog' );
		$this->assertSame( 'http://localhost:8080', Smarter_Chat_Settings::site_origin() );
		Functions\when( 'home_url' )->justReturn( 'not a url' );
		$this->assertSame( '', Smarter_Chat_Settings::site_origin() );
	}

	public function test_fields_escape_the_saved_values() {
		// values that sanitize() would never save, as a database edited by hand might hold: get() validates them again.
		$this->save(
			array(
				'chatbot' => 'https://chat.example.com/?a=1&b=2',
				'label'   => 'Ask "us"',
				'color'   => '#abcdef',
				'api_key' => 'secret-key',
			)
		);
		ob_start();
		Smarter_Chat_Settings::render_chatbot_field();
		Smarter_Chat_Settings::render_api_key_field();
		Smarter_Chat_Settings::render_label_field();
		Smarter_Chat_Settings::render_color_field();
		Smarter_Chat_Settings::render_position_field();
		Smarter_Chat_Settings::render_launcher_section();
		Smarter_Chat_Settings::render_checkbox_field(
			array(
				'key'         => 'launcher',
				'label_for'   => 'smarter-chat-launcher',
				'description' => 'On every page',
			)
		);
		$html = ob_get_clean();
		$this->assertStringContainsString( 'value="https://chat.example.com/?a=1&amp;b=2"', $html );
		$this->assertStringContainsString( 'type="password"', $html );
		$this->assertStringContainsString( 'value="Ask &quot;us&quot;"', $html );
		$this->assertStringContainsString( 'value="#abcdef"', $html );
		$this->assertStringContainsString( "<option value=\"bottom-right\"  selected='selected'>Bottom right</option>", $html );
		$this->assertStringContainsString( "checked='checked'", $html );
		$this->assertStringContainsString( '[smarter_chat]', $html );
	}
}
