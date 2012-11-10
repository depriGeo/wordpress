<?php

if ( ! class_exists( 'Facebook_Social_Plugin_Button_Settings' ) )
	require_once( dirname(__FILE__) . '/settings-social-plugin-button.php' );

/**
 * Site settings for the Facebook Send Button social plugin
 *
 * @since 1.1
 */
class Facebook_Subscribe_Button_Settings extends Facebook_Social_Plugin_Button_Settings {

	/**
	 * Setting page identifier
	 *
	 * @since 1.1
	 * @var string
	 */
	const PAGE_SLUG = 'facebook-subscribe-button';

	/**
	 * Define our option array value
	 *
	 * @since 1.1
	 * @var string
	 */
	const OPTION_NAME = 'facebook_subscribe_button';

	/**
	 * Initialize with an options array
	 *
	 * @since 1.1
	 * @param array $options existing options
	 * @param string $page associate settings with this page slug
	 */
	public function __construct( $options = array() ) {
		if ( is_array( $options ) && ! empty( $options ) )
			$this->existing_options = $options;
		else
			$this->existing_options = array();
	}

	/**
	 * Reference the social plugin by name
	 *
	 * @since 1.1
	 * @return string social plugin name
	 */
	public static function social_plugin_name() {
		return __( 'Subscribe Button', 'facebook' );
	}

	/**
	 * Evaluate the Facebook_Subscribe_Button class file if it is not already loaded
	 *
	 * @since 1.1
	 */
	public static function require_subscribe_button_builder() {
		if ( ! class_exists( 'Facebook_Subscribe_Button' ) )
			require_once( dirname( dirname(__FILE__) ) . '/social-plugins/class-facebook-subscribe-button.php' );
	}

	/**
	 * Navigate to the settings page through the Facebook top-level menu item
	 *
	 * @since 1.1
	 * @uses add_submenu_page()
	 * @param string $parent_slug Facebook top-level menu item slug
	 * @return string submenu hook suffix
	 */
	public static function add_submenu_item( $parent_slug ) {
		$subscribe_button_settings = new Facebook_Subscribe_Button_Settings();

		$hook_suffix = add_submenu_page(
			$parent_slug,
			self::social_plugin_name(),
			self::social_plugin_name(),
			'manage_options',
			self::PAGE_SLUG,
			array( &$subscribe_button_settings, 'settings_page' )
		);

		if ( $hook_suffix ) {
			$subscribe_button_settings->hook_suffix = $hook_suffix;
			register_setting( $hook_suffix, self::OPTION_NAME, 'Facebook_Subscribe_Button_Settings::sanitize_options' );
			add_action( 'load-' . $hook_suffix, array( &$subscribe_button_settings, 'onload' ) );
		}

		return $hook_suffix;
	}

	/**
	 * Load stored options and scripts on settings page view
	 *
	 * @since 1.1
	 */
	public function onload() {
		$options = get_option( self::OPTION_NAME );
		if ( ! is_array( $options ) )
			$options = array();
		$this->existing_options = $options;

		$this->settings_api_init();
	}

	/**
	 * Load the page
	 *
	 * @since 1.1
	 */
	public function settings_page() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		Facebook_Settings::settings_page_template( $this->hook_suffix, sprintf( __( '%s Settings', 'facebook' ), self::social_plugin_name() ) );
	}

	/**
	 * Hook into the settings API
	 *
	 * @since 1.1
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 * @param string $options_group target grouping
	 */
	private function settings_api_init() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		$section = 'facebook-subscribe-button';
		add_settings_section(
			$section,
			'', // no title for main section
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		// when, where
		add_settings_field(
			'facebook-subscribe-show-on',
			__( 'Show on', 'facebook' ),
			array( &$this, 'display_show_on' ),
			$this->hook_suffix,
			$section
		);
		add_settings_field(
			'facebook-subscribe-position',
			__( 'Position', 'facebook' ),
			array( &$this, 'display_position' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'facebook-subscribe-position' )
		);

		// subscribe button options
		add_settings_field(
			'facebook-subscribe-layout',
			__( 'Layout', 'facebook' ),
			array( &$this, 'display_layout' ),
			$this->hook_suffix,
			$section
		);
		add_settings_field(
			'facebook-subscribe-show-faces',
			__( 'Show faces', 'facebook' ),
			array( &$this, 'display_show_faces' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'facebook-subscribe-show-faces' )
		);
		add_settings_field(
			'facebook-subscribe-width',
			__( 'Width', 'facebook' ),
			array( &$this, 'display_width' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'facebook-subscribe-width' )
		);
		add_settings_field(
			'facebook-subscribe-font',
			__( 'Font', 'facebook' ),
			array( &$this, 'display_font' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'facebook-subscribe-font' )
		);
		add_settings_field(
			'facebook-subscribe-colorscheme',
			__( 'Color scheme', 'facebook' ),
			array( &$this, 'display_colorscheme' ),
			$this->hook_suffix,
			$section
		);
	}

	/**
	 * Introduce publishers to the Subscribe Button social plugin
	 *
	 * @since 1.1
	 */
	public function section_header() {
		echo '<p>' . esc_html( sprintf( __( "Encourage visitors to subscribe to public updates from an author's %s account.", 'facebook' ), 'Facebook' ) ) . ' <a href="https://developers.facebook.com/docs/reference/plugins/subscribe/" title="' . esc_attr( sprintf( __( '%s social plugin documentation', 'facebook' ), 'Facebook ' . self::social_plugin_name() ) ) . '">' . esc_html( __( 'Read more...', 'facebook' ) ) . '</a></p>';
	}

	/**
	 * Where should the button appear?
	 *
	 * @since 1.1
	 * @param array $extra_attributes custom form attributes
	 */
	public function display_show_on( $extra_attributes = array() ) {
		$key = 'show_on';

		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'facebook-subscribe-show-on',
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<fieldset id="' . $id . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo '>' . self::show_on_choices( $name, self::get_display_conditionals_by_feature( 'subscribe', 'all' ), 'all' ) . '</fieldset>';
		echo '<p class="description">' . esc_html( self::show_on_description( self::social_plugin_name() ) ) . '</p>';
	}

	/**
	 * Describe layout choices
	 *
	 * @since 1.1
	 * @return array layout descriptions keyed by layout choice
	 */
	public static function layout_descriptions() {
		$subscribe_plural = __( 'subscribers', 'facebook' );
		return array(
			'standard' => __( 'Display social text next to the button.', 'facebook' ),
			'button_count' => sprintf( __( 'Display total number of %s next to the button.', 'facebook' ), $subscribe_plural ),
			'box_count' => sprintf( __( 'Display total number of %s above the button.', 'facebook' ), $subscribe_plural )
		);
	}

	/**
	 * Choose a Subscribe Button layout option
	 *
	 * @since 1.1
	 * @param array $extra_attributes custom form attributes
	 */
	public function display_layout( $extra_attributes = array() ) {
		$key = 'layout';

		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'facebook-subscribe-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );
		$name = esc_attr( $name );

		self::require_subscribe_button_builder();

		if ( isset( $this->existing_options[$key] ) && in_array( $this->existing_options[$key], Facebook_Subscribe_Button::$layout_choices ) )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = 'standard';

		$descriptions = self::layout_descriptions();

		$choices = array();
		foreach( Facebook_Subscribe_Button::$layout_choices as $layout ) {
			$choice = '<label><input type="radio" name="' . $name . '" value="' . $layout . '"';
			$choice .= checked( $layout, $existing_value, false );
			$choice .= ' /> ';

			$choice .= $layout;
			if ( isset( $descriptions[$layout] ) )
				$choice .= esc_html( ' — ' . $descriptions[$layout] );
			$choice .= '</label>';

			$choices[] = $choice;
			unset( $choice );
		}

		if ( ! empty( $choices ) ) {
			echo '<fieldset id="' . $id . '"';
			if ( isset( $class ) && $class )
				echo ' class="' . $class . '"';
			echo '><div>';
			echo implode( '</div><div>', $choices );
			echo '</div></fieldset>';
		}
	}

	/**
	 * Option to display faces of friends below the Subscribe Button
	 *
	 * @since 1.1
	 * @param array $extra_attributes custom form attributes
	 */
	public function display_show_faces( $extra_attributes = array() ) {
		$key = 'show_faces';

		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'facebook-subscribe-show-faces',
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '" id="' . $id . '" value="1"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		checked( isset( $this->existing_options[$key] ) );
		echo ' /> ' . esc_html( __( "Show profile photos of the viewer's friends who have already subscribed.", 'facebook' ) ) . '</label>';
	}

	/**
	 * Allow the publisher to customize the width of the Subscribe Button
	 *
	 * @since 1.1
	 * @param array $extra_attributes custom form attributes
	 */
	public function display_width( $extra_attributes = array() ) {
		global $content_width;

		$key = 'width';
		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'facebook-subscribe-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		if ( isset( $this->existing_options[$key] ) )
			$existing_value = absint( $this->existing_options[$key] );
		else if ( isset( $content_width ) )
			$existing_value = absint( $content_width );

		if ( ! isset( $existing_value ) || $existing_value < 55 )
			$existing_value = 450; // match social plugin config page default

		echo '<input type="number" name="' . esc_attr( $name ) . '" id="' . $id . '" size="5" min="55" step="1" value="' . $existing_value . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo ' />';
		if ( isset( $content_width ) && $name === self::OPTION_NAME . '[' . $key . ']' ) // hide on widget
			echo ' ' . esc_html( sprintf( __( 'Content width: %u', 'facebook' ), absint( $content_width ) ) );

		echo '<p class="description">' . esc_html( sprintf( __( 'The width of the %s display area in whole pixels.', 'facebook' ), self::social_plugin_name() ) ) . '</p>';
	}

	/**
	 * Customize the color scheme
	 *
	 * @since 1.1
	 * @param array $extra_attributes custom form attributes
	 */
	public function display_colorscheme( $extra_attributes = array() ) {
		$key = 'colorscheme';

		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'facebook-subscribe-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<fieldset id="' . $id . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo '>' . self::color_scheme_choices( $name, isset( $this->existing_options[$key] ) ? $this->existing_options[$key] : '' ) . '</fieldset>';
	}

	/**
	 * Choose a custom font
	 *
	 * @since 1.1
	 * @param array $extra_attributes custom form attributes
	 */
	public function display_font( $extra_attributes = array() ) {
		$key = 'font';

		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'facebook-subscribe-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo '>' . self::font_choices( isset( $this->existing_options[$key] ) ? $this->existing_options[$key] : '' ) . '</select>';
	}

	/**
	 * Where would you like it?
	 *
	 * @since 1.1
	 * @param array $extra_attributes custom form attributes
	 */
	public function display_position( $extra_attributes = array() ) {
		$key = 'position';

		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'facebook-subscribe-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo '>' . self::position_choices( isset( $this->existing_options[$key] ) ? $this->existing_options[$key] : '' ) . '</select>';
	}

	/**
	 * Translate HTML data response returned from Facebook social plugin builder into underscored keys and PHP values before saving
	 *
	 * @since 1.1
	 * @param array $options data-* options returned from Facebook social plugin builder
	 * @return array $options options to store in WordPress
	 */
	public static function html_data_to_options( $options ) {
		if ( ! is_array( $options ) )
			return array();

		if ( isset( $options['show-faces'] ) ) {
			if ( $options['show-faces'] === 'true' )
				$options['show_faces'] = true;
			else
				$options['show_faces'] = false;
			unset( $options['show-faces'] );
		}

		if ( isset( $options['width'] ) )
			$options['width'] = absint( $options['width'] );

		return $options;
	}

	/**
	 * Sanitize Subscribe Button settings before they are saved to the database
	 *
	 * @since 1.1
	 * @param array $options Subscribe Button options
	 * @return array clean option sets. note: we remove Subscribe Button social plugin default options, storing only custom settings (e.g. dark color scheme stored, light is default and therefore not stored)
	 */
	public static function sanitize_options( $options ) {
		if ( ! is_array( $options ) || empty( $options ) )
			return array();

		$clean_options = array();

		self::require_subscribe_button_builder();

		// Handle display preferences first
		$clean_options = parent::sanitize_options( $options );
		if ( isset( $clean_options['show_on'] ) ) {
			self::update_display_conditionals( 'subscribe', $clean_options['show_on'], self::get_show_on_choices( 'all' ) );
			unset( $clean_options['show_on'] );
		}
		unset( $options['show_on'] );

		$subscribe_button = Facebook_Subscribe_Button::fromArray( $options );
		if ( $subscribe_button )
			return array_merge( $clean_options, self::html_data_to_options( $subscribe_button->toHTMLDataArray() ) );

		return $clean_options;
	}
}

?>