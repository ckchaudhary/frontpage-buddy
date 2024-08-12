<?php
/**
 * Main plugin class.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy;

/**
 * The main plugin class.
 */
class Plugin {
	use TraitSingleton;

	/**
	 * Default options for the plugin.
	 * After the user saves options the first time they are loaded from the DB.
	 *
	 * @var array
	 */
	private $default_options = array(
		'editor_color_bg'                 => '#ffffff',
		'editor_color_text'               => '#333333',
		'editor_color_primary'            => '#235789',
		'editor_color_primary_contrast'   => '#ffffff',
		'editor_color_secondary'          => '#b9d6f2',
		'editor_color_secondary_contrast' => '#000000',
	);

	/**
	 * Final options for the plugin, after the default options have been overwritten by values from settings.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Is the plugin activated network wide?
	 *
	 * @var boolean
	 */
	public $network_activated = false;

	/**
	 * The object of Admin class
	 *
	 * @var \RB\FrontPageBuddy\Admin
	 */
	private $admin;

	/**
	 * The edit screen manager.
	 *
	 * @var \RB\FrontPageBuddy\Editor
	 */
	private $editor;

	/**
	 * Integrations.
	 *
	 * @var array
	 */
	private $integrations;

	/**
	 * Widget Types.
	 *
	 * @var array
	 */
	private $widget_types;

	/**
	 * Get the Admin object.
	 *
	 * @return \RB\FrontPageBuddy\Admin
	 */
	public function admin() {
		return $this->admin;
	}

	/**
	 * Get the edit screen manager.
	 *
	 * @return \RB\FrontPageBuddy\Editor
	 */
	public function editor() {
		return $this->editor;
	}

	/**
	 * Get all registered integrations.
	 *
	 * @return array
	 */
	public function get_all_integrations() {
		return $this->integrations;
	}

	/**
	 * Get a registered integration.
	 *
	 * @param string $type identifier of the integration.
	 * @return mixed \RB\FrontPageBuddy\Integration if found. null otherwise.
	 */
	public function get_integration( $type ) {
		return isset( $this->integrations[ $type ] ) ? $this->integrations[ $type ] : null;
	}

	/**
	 * Register an integration.
	 *
	 * @param string                         $type identifier of the integration.
	 * @param \RB\FrontPageBuddy\Integration $obj an object of type \RB\FrontPageBuddy\Integration.
	 * @return \WP_Error|void \WP_Error if registration failed.
	 */
	public function register_integration( $type, $obj ) {
		if ( ! empty( $this->integrations ) && isset( $this->integrations[ $type ] ) ) {
			return new \WP_Error( 'duplicate_integration', __( 'Please use a unique type.', 'frontpage-buddy' ) );
		}

		if ( ! \is_a( $obj, '\RB\FrontPageBuddy\Integration' ) ) {
			return new \WP_Error( 'invalid_type', __( 'The integration must extend \RB\FrontPageBuddy\Integration.', 'frontpage-buddy' ) );
		}

		$this->integrations[ $type ] = $obj;
	}

	/**
	 * Get all registered widget types.
	 *
	 * @return array()
	 */
	public function get_all_widget_types() {
		return $this->widget_types;
	}

	/**
	 * Get a registered widget type.
	 *
	 * @param string $type identifier of the widget type.
	 * @return \RB\FrontPageBuddy\WidgetType if found. null otherwise.
	 */
	public function get_widget_type( $type ) {
		return isset( $this->widget_types[ $type ] ) ? $this->widget_types[ $type ] : null;
	}

	/**
	 * Register a widget type.
	 *
	 * @param \RB\FrontPageBuddy\WidgetType $obj an object of type \RB\FrontPageBuddy\WidgetType.
	 * @return \WP_Error|void \WP_Error if registration failed.
	 */
	public function register_widget_type( $obj ) {
		$type = $obj->type;
		if ( ! empty( $this->widget_types ) && isset( $this->widget_types[ $type ] ) ) {
			return new \WP_Error( 'duplicate_widget_type', __( 'Please use a unique type.', 'frontpage-buddy' ) );
		}

		if ( ! \is_a( $obj, '\RB\FrontPageBuddy\Widgets\WidgetType' ) ) {
			return new \WP_Error( 'invalid_type', __( 'The widget type must extend \RB\FrontPageBuddy\Widgets\WidgetType.', 'frontpage-buddy' ) );
		}

		$this->widget_types[ $type ] = $obj;
	}

	/**
	 * Get the value of one of the plugin options(settings).
	 *
	 * @since 1.0.0
	 * @param string $key Name of the option(setting).
	 * @return mixed
	 */
	public function option( $key ) {
		$key    = strtolower( $key );
		$option = isset( $this->options[ $key ] ) ? $this->options[ $key ] : null;

		return apply_filters( 'fpbuddy_option', $option, $key );
	}

	/**
	 * Initiazlie the plugin.
	 *
	 * @return void
	 */
	protected function init() {
		// Setup globals.
		add_action( 'frontpage_buddy_load', array( $this, 'setup_globals' ), 2 );

		// Load textdomain.
		add_action( 'frontpage_buddy_load', array( $this, 'load_plugin_textdomain' ), 4 );

		// Load integrations.
		add_action( 'frontpage_buddy_load', array( $this, 'load_integrations' ), 6 );

		// Load widget types.
		add_action( 'frontpage_buddy_load', array( $this, 'load_widget_types' ), 8 );

		// Custom load hook, to notify dependent plugins.
		do_action( 'frontpage_buddy_load' );

		// init hook.
		add_action( 'init', array( $this, 'on_init' ) );
	}

	/**
	 * Setup globals.
	 *
	 * @return void
	 */
	public function setup_globals() {
		$this->network_activated = $this->is_network_activated();

		$saved_options = $this->network_activated ? get_site_option( 'frontpage_buddy_options' ) : get_option( 'frontpage_buddy_options' );
		$saved_options = maybe_unserialize( $saved_options );

		$this->options = wp_parse_args( $saved_options, $this->default_options );
	}

	/**
	 * Check if the plugin is activated network wide(in multisite)
	 *
	 * @return boolean
	 */
	private function is_network_activated() {
		$network_activated = false;
		if ( is_multisite() ) {
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}

			if ( is_plugin_active_for_network( 'frontpage-buddy/loader.php' ) ) {
				$network_activated = true;
			}
		}

		return $network_activated;
	}

	/**
	 * Loads the textdomain for the plugin.
	 * Language files are used in this order of preference:
	 *    - WP_LANG_DIR/plugins/frontpage-buddy-LOCALE.mo
	 *    - WP_PLUGIN_DIR/frontpage-buddy/languages/frontpage-buddy-LOCALE.mo
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		/*
		 * As of WP 4.6, WP has, by this point in the load order, already
		 * automatically added language files in this location:
		 * wp-content/languages/plugins/frontpage-buddy-es_ES.mo
		 * load_plugin_textdomain() also looks for language files in that location,
		 * then it falls back to translations in the plugin's /languages folder, like
		 * wp-content/frontpage-buddy/languages/frontpage-buddy-es_ES.mo
		 */
		load_plugin_textdomain( 'frontpage-buddy', false, FPBUDDY_PLUGIN_DIR . 'languages' );
	}

	/**
	 * Detect and load suitable integrations.
	 *
	 * @return void
	 */
	public function load_integrations() {
		$enabled_for = $this->option( 'enabled_for' );

		$buddypress_active = false;
		$buddyboss_active  = false;
		if ( function_exists( '\buddypress' ) ) {
			$buddypress_active = true;
			if ( isset( \buddypress()->buddyboss ) ) {
				$buddypress_active = false;
				$buddyboss_active  = true;
			}
		}

		if ( $buddypress_active ) {
			$this->register_integration( 'bp_groups', new Integrations\BuddyPress\Groups( 'bp_groups', 'BuddyPress Groups' ) );
			// buddypress groups helper.
			if ( ! empty( $enabled_for ) && in_array( 'bp_groups', $enabled_for, true ) ) {
				if ( \bp_is_active( 'groups' ) ) {
					bp_register_group_extension( '\RB\FrontPageBuddy\Integrations\BuddyPress\GroupExtension' );
				}
			}

			$this->register_integration( 'bp_members', new Integrations\BuddyPress\Profiles( 'bp_members', 'BuddyPress Member Profiles' ) );
			// buddypress member profiles helper.
			if ( ! empty( $enabled_for ) && in_array( 'bp_members', $enabled_for, true ) ) {
				Integrations\BuddyPress\MemberProfilesHelper::get_instance();
			}
		}

		if ( $buddyboss_active ) {
			$this->register_integration( 'buddyboss_members', new Integrations\BuddyBoss\Profiles( 'buddyboss_members', 'BuddyBoss Member Profiles' ) );
			// buddypress member profiles helper.
			if ( ! empty( $enabled_for ) && in_array( 'buddyboss_members', $enabled_for, true ) ) {
				Integrations\BuddyBoss\MemberProfilesHelper::get_instance();
			}

			$this->register_integration( 'buddyboss_groups', new Integrations\BuddyBoss\Groups( 'buddyboss_groups', 'BuddyBoss Social Groups' ) );
			// group extension.
			if ( ! empty( $enabled_for ) && in_array( 'buddyboss_groups', $enabled_for, true ) ) {
				if ( \bp_is_active( 'groups' ) ) {
					bp_register_group_extension( '\RB\FrontPageBuddy\Integrations\BuddyBoss\GroupExtension' );
				}
			}
		}

		// bbpress plugin.
		if ( ! $buddyboss_active && function_exists( '\bbpress' ) ) {
			// Register integration.
			$this->register_integration( 'bbp_profiles', new Integrations\BBPress\Profiles( 'bbp_profiles', 'bbPress User Profiles' ) );
			// Load helper.
			if ( ! empty( $enabled_for ) && in_array( 'bbp_profiles', $enabled_for, true ) ) {
				Integrations\BBPress\ProfilesHelper::get_instance();
			}
		}

		// ultimate-member plugin.
		if ( function_exists( '\UM' ) ) {
			// Register integration.
			$this->register_integration( 'um_member_profiles', new Integrations\UltimateMember\Profiles( 'um_member_profiles', 'UltimateMember Profiles' ) );
			// Load helper.
			if ( ! empty( $enabled_for ) && in_array( 'um_member_profiles', $enabled_for, true ) ) {
				Integrations\UltimateMember\ProfilesHelper::get_instance();
			}
		}
	}

	/**
	 * Load all available widget types.
	 *
	 * @return void
	 */
	public function load_widget_types() {
		if ( ! empty( $this->widget_types ) ) {
			return;
		}

		$all_types = apply_filters(
			'frontpage_buddy_registered_widgets',
			array(
				'\RB\FrontPageBuddy\Widgets\RichContent',
				'\RB\FrontPageBuddy\Widgets\InstagramProfile',
				'\RB\FrontPageBuddy\Widgets\FacebookPage',
				'\RB\FrontPageBuddy\Widgets\YoutubeEmbed',
				'\RB\FrontPageBuddy\Widgets\TwitterProfile',
			)
		);

		if ( ! empty( $all_types ) ) {
			foreach ( $all_types as $widget_type_class ) {
				if ( class_exists( $widget_type_class ) ) {
					$this->register_widget_type( new $widget_type_class() );
				}
			}
		}
	}

	/**
	 * Run code on on_init hook
	 *
	 * @return void
	 */
	public function on_init() {
		if ( ( is_admin() || is_network_admin() ) && current_user_can( 'manage_options' ) ) {
			$this->admin = new Admin();
		}

		$this->editor = Editor::get_instance();

		// Front End Assets.
		if ( ! is_admin() && ! is_network_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		}
	}

	/**
	 * Load javascript, css file etc.
	 *
	 * @return void
	 */
	public function assets() {
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		$is_edit_widgets_screen = apply_filters( 'frontpage_buddy_is_widgets_edit_screen', false );

		// assets for edit-widgets screen.
		if ( $is_edit_widgets_screen ) {
			wp_enqueue_script( 'trumbowyg', FPBUDDY_PLUGIN_URL . 'assets/trumbowyg/trumbowyg.min.js', array( 'jquery' ), '2.27.3', array( 'in_footer' => true ) );
			wp_enqueue_style( 'trumbowyg', FPBUDDY_PLUGIN_URL . 'assets/trumbowyg/ui/trumbowyg.min.css', array(), '2.27.3' );

			wp_enqueue_style( 'jquery-ui-theme-smoothness', FPBUDDY_PLUGIN_URL . 'assets/css/jquery-ui.min.css', array(), '1.13.3' );

			wp_enqueue_script( 'frontpage-buddy-editor', FPBUDDY_PLUGIN_URL . 'assets/js/editor' . $min . '.js', array( 'jquery', 'jquery-form', 'jquery-ui-sortable' ), FPBUDDY_PLUGIN_VERSION, array( 'in_footer' => true ) );

			$data = apply_filters(
				'frontpage_buddy_script_data',
				array(
					'config'      => array(
						'ajaxurl'     => admin_url( 'admin-ajax.php' ),
						'req'         => array(
							'change_status'   => array(
								'action' => 'frontpage_buddy_change_status',
								'nonce'  => wp_create_nonce( 'frontpage_buddy_change_status' ),
							),

							'update_layout'   => array(
								'action' => 'frontpage_buddy_update_layout',
								'nonce'  => wp_create_nonce( 'frontpage_buddy_update_layout' ),
							),

							'widget_opts_get' => array(
								'action' => 'frontpage_buddy_widget_opts_get',
								'nonce'  => wp_create_nonce( 'frontpage_buddy_widget_opts_get' ),
							),
						),
						'img_spinner' => FPBUDDY_PLUGIN_URL . 'assets/images/spinner.gif',
					),

					'lang'        => array(
						'invalid'                => __( 'Invalid', 'frontpage-buddy' ),
						'add_section'            => __( 'add section', 'frontpage-buddy' ),
						'drag_move'              => __( 'Drag to move up or down', 'frontpage-buddy' ),
						'confirm_delete_section' => __( 'Are you sure you want to delete this section?', 'frontpage-buddy' ),
						'confirm_delete_widget'  => __( 'Are you sure you want to delete this content?', 'frontpage-buddy' ),
						'choose_widget'          => __( 'Select the type of content.', 'frontpage-buddy' ),
					),

					'object_type' => '',
					'object_id'   => 0,
				)
			);
			wp_localize_script( 'frontpage-buddy-editor', 'FRONTPAGE_BUDDY', $data );

			wp_enqueue_style( 'frontpage-buddy-editor', FPBUDDY_PLUGIN_URL . 'assets/css/editor' . $min . '.css', array(), FPBUDDY_PLUGIN_VERSION );
			$css  = '.fpbuddy_manage_widgets {';
			$css .= '--fpbuddy-editor-color-bg: ' . esc_attr( frontpage_buddy()->option( 'editor_color_bg' ) ) . ';';
			$css .= '--fpbuddy-editor-color-text: ' . esc_attr( frontpage_buddy()->option( 'editor_color_text' ) ) . ';';
			$css .= '--fpbuddy-editor-color-main: ' . esc_attr( frontpage_buddy()->option( 'editor_color_primary' ) ) . ';';
			$css .= '--fpbuddy-editor-color-main-contrast: ' . esc_attr( frontpage_buddy()->option( 'editor_color_primary_contrast' ) ) . ';';
			$css .= '--fpbuddy-editor-color-secondary: ' . esc_attr( frontpage_buddy()->option( 'editor_color_secondary' ) ) . ';';
			$css .= '--fpbuddy-editor-color-secondary-contrast: ' . esc_attr( frontpage_buddy()->option( 'editor_color_secondary_contrast' ) ) . ';';
			$css .= '}';
			wp_add_inline_style(
				'frontpage-buddy-editor',
				$css
			);
		}

		// Assets for view(front page) screen.
		$is_custom_front_page_screen = apply_filters( 'frontpage_buddy_is_custom_front_page_screen', false );
		if ( $is_custom_front_page_screen ) {
			wp_enqueue_style( 'frontpage-buddy-view', FPBUDDY_PLUGIN_URL . 'assets/css/view.css', array(), FPBUDDY_PLUGIN_VERSION );
		}
	}
}
