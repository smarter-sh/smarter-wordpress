<?php
/**
 * The plugin's settings: the option smarter_chat_settings, and its screen, Settings → Smarter Chat.
 *
 * See doc/adr/004-settings.md.
 *
 * @package SmarterChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The settings, and the settings screen.
 */
class Smarter_Chat_Settings {

	const OPTION     = 'smarter_chat_settings';
	const PAGE       = 'smarter-chat';
	const CAPABILITY = 'manage_options';
	const POSITIONS  = array( 'bottom-right', 'bottom-left' );
	const SOURCES    = array( 'local', 'cdn' );

	/**
	 * The default of every setting.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'chatbot'  => '',
			'api_key'  => '',
			'launcher' => true,
			'position' => 'bottom-right',
			'label'    => __( 'Chat', 'smarter-chat' ),
			'color'    => '#ff5733',
			'open'     => false,
			'source'   => 'local',
		);
	}

	/**
	 * The settings, valid and complete: each missing or invalid value is its default.
	 *
	 * @return array<string, mixed>
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		return self::sanitize( is_array( $saved ) ? $saved : array() );
	}

	/**
	 * Validates settings, as they are saved and as they are read.
	 *
	 * @param mixed $input The submitted or saved settings.
	 * @return array<string, mixed> The valid settings.
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = self::defaults();
		return array(
			'chatbot'  => Smarter_Chat_Validation::url( isset( $input['chatbot'] ) ? $input['chatbot'] : '' ),
			'api_key'  => Smarter_Chat_Validation::api_key( isset( $input['api_key'] ) ? $input['api_key'] : '' ),
			// a checkbox that is unchecked isn't submitted at all.
			'launcher' => isset( $input['launcher'] ) ? Smarter_Chat_Validation::boolean( $input['launcher'] ) : false,
			'position' => Smarter_Chat_Validation::choice( isset( $input['position'] ) ? $input['position'] : '', self::POSITIONS, $defaults['position'] ),
			'label'    => Smarter_Chat_Validation::text( isset( $input['label'] ) ? $input['label'] : '', $defaults['label'], 60 ),
			'color'    => Smarter_Chat_Validation::color( isset( $input['color'] ) ? $input['color'] : '', $defaults['color'] ),
			'open'     => isset( $input['open'] ) ? Smarter_Chat_Validation::boolean( $input['open'] ) : false,
			'source'   => Smarter_Chat_Validation::choice( isset( $input['source'] ) ? $input['source'] : '', self::SOURCES, $defaults['source'] ),
		);
	}

	/**
	 * Registers the option, its sanitize callback, and the screen's sections and fields. Hooked to admin_init.
	 *
	 * @return void
	 */
	public static function register() {
		register_setting(
			self::PAGE,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
				'show_in_rest'      => false,
			)
		);

		add_settings_section( 'smarter_chat_chatbot', __( 'Chatbot', 'smarter-chat' ), array( __CLASS__, 'render_chatbot_section' ), self::PAGE );
		add_settings_field( 'chatbot', __( 'Chatbot url', 'smarter-chat' ), array( __CLASS__, 'render_chatbot_field' ), self::PAGE, 'smarter_chat_chatbot', array( 'label_for' => 'smarter-chat-chatbot' ) );
		add_settings_field( 'api_key', __( 'Api key', 'smarter-chat' ), array( __CLASS__, 'render_api_key_field' ), self::PAGE, 'smarter_chat_chatbot', array( 'label_for' => 'smarter-chat-api-key' ) );

		add_settings_section( 'smarter_chat_launcher', __( 'Launcher', 'smarter-chat' ), array( __CLASS__, 'render_launcher_section' ), self::PAGE );
		add_settings_field(
			'launcher',
			__( 'Show on every page', 'smarter-chat' ),
			array( __CLASS__, 'render_checkbox_field' ),
			self::PAGE,
			'smarter_chat_launcher',
			array(
				'key'         => 'launcher',
				'label_for'   => 'smarter-chat-launcher',
				'description' => __( 'A chat button in a corner of every page of the site.', 'smarter-chat' ),
			)
		);
		add_settings_field( 'position', __( 'Position', 'smarter-chat' ), array( __CLASS__, 'render_position_field' ), self::PAGE, 'smarter_chat_launcher', array( 'label_for' => 'smarter-chat-position' ) );
		add_settings_field( 'label', __( 'Label', 'smarter-chat' ), array( __CLASS__, 'render_label_field' ), self::PAGE, 'smarter_chat_launcher', array( 'label_for' => 'smarter-chat-label' ) );
		add_settings_field( 'color', __( 'Button color', 'smarter-chat' ), array( __CLASS__, 'render_color_field' ), self::PAGE, 'smarter_chat_launcher', array( 'label_for' => 'smarter-chat-color' ) );
		add_settings_field(
			'open',
			__( 'Start open', 'smarter-chat' ),
			array( __CLASS__, 'render_checkbox_field' ),
			self::PAGE,
			'smarter_chat_launcher',
			array(
				'key'         => 'open',
				'label_for'   => 'smarter-chat-open',
				'description' => __( 'Open the chat when the page loads.', 'smarter-chat' ),
			)
		);

		add_settings_section( 'smarter_chat_advanced', __( 'Advanced', 'smarter-chat' ), '__return_false', self::PAGE );
		add_settings_field( 'source', __( 'Script source', 'smarter-chat' ), array( __CLASS__, 'render_source_field' ), self::PAGE, 'smarter_chat_advanced', array( 'label_for' => 'smarter-chat-source' ) );
	}

	/**
	 * Adds Settings → Smarter Chat. Hooked to admin_menu.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_options_page( __( 'Smarter Chat', 'smarter-chat' ), __( 'Smarter Chat', 'smarter-chat' ), self::CAPABILITY, self::PAGE, array( __CLASS__, 'render_page' ) );
	}

	/**
	 * Adds a Settings link to the plugin's row on the Plugins screen. Filters plugin_action_links_<plugin>.
	 *
	 * @param string[] $links The row's links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=' . self::PAGE );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'smarter-chat' ) . '</a>' );
		return $links;
	}

	/**
	 * The settings screen.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php if ( 'local' === self::get()['source'] && ! Smarter_Chat_Embed::has_local_script() ) : ?>
				<div class="notice notice-warning inline"><p>
					<?php esc_html_e( 'The embed script is not in this copy of the plugin, so it is loaded from jsDelivr. Install the plugin from its release zip, or run "make embed".', 'smarter-chat' ); ?>
				</p></div>
			<?php endif; ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * The Chatbot section's introduction: what to set up on the Smarter side.
	 *
	 * @return void
	 */
	public static function render_chatbot_section() {
		$origin = self::site_origin();
		echo '<p>' . esc_html__( 'Your chatbot is a deployed LLMClient on a Smarter platform. Its manifest must allow this site\'s origin, in spec.config.prompt.allowedOrigins:', 'smarter-chat' ) . ' <code>' . esc_html( $origin ) . '</code></p>';
	}

	/**
	 * The Launcher section's introduction.
	 *
	 * @return void
	 */
	public static function render_launcher_section() {
		echo '<p>' . esc_html__( 'To put the chat in a page instead, use the Smarter Chat block, or the [smarter_chat] shortcode.', 'smarter-chat' ) . '</p>';
	}

	/**
	 * The chatbot url field.
	 *
	 * @return void
	 */
	public static function render_chatbot_field() {
		printf(
			'<input type="url" class="regular-text code" id="smarter-chat-chatbot" name="%1$s[chatbot]" value="%2$s" placeholder="https://support.3141-5926-5359.api.example.com/" /><p class="description">%3$s</p>',
			esc_attr( self::OPTION ),
			esc_attr( self::get()['chatbot'] ),
			esc_html__( 'The url of the deployed LLMClient, or its custom domain.', 'smarter-chat' )
		);
	}

	/**
	 * The api key field. The saved key is never displayed: an empty field keeps it.
	 *
	 * @return void
	 */
	public static function render_api_key_field() {
		$saved = self::get()['api_key'];
		printf(
			'<input type="password" class="regular-text code" id="smarter-chat-api-key" name="%1$s[api_key]" value="%2$s" autocomplete="off" /><p class="description">%3$s</p>',
			esc_attr( self::OPTION ),
			esc_attr( $saved ),
			esc_html__( 'Only for an LLMClient that requires one. It is written into your pages, where any visitor can read it: use a key dedicated to this site, or an LLMClient without api keys and with a budget.', 'smarter-chat' )
		);
	}

	/**
	 * A checkbox field.
	 *
	 * @param array<string, string> $args The field's key, id and description.
	 * @return void
	 */
	public static function render_checkbox_field( $args ) {
		printf(
			'<label><input type="checkbox" id="%1$s" name="%2$s[%3$s]" value="1" %4$s /> %5$s</label>',
			esc_attr( $args['label_for'] ),
			esc_attr( self::OPTION ),
			esc_attr( $args['key'] ),
			checked( self::get()[ $args['key'] ], true, false ),
			esc_html( $args['description'] )
		);
	}

	/**
	 * The position field.
	 *
	 * @return void
	 */
	public static function render_position_field() {
		$labels = array(
			'bottom-right' => __( 'Bottom right', 'smarter-chat' ),
			'bottom-left'  => __( 'Bottom left', 'smarter-chat' ),
		);
		self::render_select( 'position', 'smarter-chat-position', $labels );
	}

	/**
	 * The script source field.
	 *
	 * @return void
	 */
	public static function render_source_field() {
		$labels = array(
			'local' => __( 'This site (the copy in the plugin)', 'smarter-chat' ),
			'cdn'   => __( 'jsDelivr CDN', 'smarter-chat' ),
		);
		self::render_select( 'source', 'smarter-chat-source', $labels );
		echo '<p class="description">' . esc_html(
			sprintf(
				/* translators: %s: the version of the embed script. */
				__( 'Where visitors\' browsers download the chat\'s script, @smarter.sh/ui-chat %s.', 'smarter-chat' ),
				SMARTER_CHAT_EMBED_VERSION
			)
		) . '</p>';
	}

	/**
	 * The label field.
	 *
	 * @return void
	 */
	public static function render_label_field() {
		printf(
			'<input type="text" class="regular-text" id="smarter-chat-label" name="%1$s[label]" value="%2$s" maxlength="60" /><p class="description">%3$s</p>',
			esc_attr( self::OPTION ),
			esc_attr( self::get()['label'] ),
			esc_html__( 'The button\'s name, for screen readers.', 'smarter-chat' )
		);
	}

	/**
	 * The color field.
	 *
	 * @return void
	 */
	public static function render_color_field() {
		printf(
			'<input type="color" id="smarter-chat-color" name="%1$s[color]" value="%2$s" />',
			esc_attr( self::OPTION ),
			esc_attr( self::get()['color'] )
		);
	}

	/**
	 * A select field.
	 *
	 * @param string                $key    The setting.
	 * @param string                $id     The element's id.
	 * @param array<string, string> $labels The options' labels, by value.
	 * @return void
	 */
	private static function render_select( $key, $id, array $labels ) {
		$current = self::get()[ $key ];
		printf( '<select id="%1$s" name="%2$s[%3$s]">', esc_attr( $id ), esc_attr( self::OPTION ), esc_attr( $key ) );
		foreach ( $labels as $value => $label ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	/**
	 * The site's origin, e.g. https://www.example.com, which the LLMClient must allow.
	 *
	 * @return string
	 */
	public static function site_origin() {
		$parts = wp_parse_url( home_url() );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			return '';
		}
		$scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : 'https';
		$port   = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
		return strtolower( $scheme . '://' . $parts['host'] . $port );
	}
}
