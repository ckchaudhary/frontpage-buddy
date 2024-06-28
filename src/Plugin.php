<?php
/**
 * Main plugin class.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy;

/**
 * The main plugin class.
 */
class Plugin {
	use TraitSingleton;

	/**
	 * Default options for the plugin.
	 * After the user saves options the first time they are loaded from the DB.
	 *
	 * @todo: redo this
	 *
	 * @var array
	 */
	private $default_options = array(
		'enabled_for'     => array( 'bp_members' ),
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
	 * @var \RecycleBin\FrontPageBuddy\Admin
	 */
	private $admin;

	/**
	 * The object of Widget\Collection class
	 *
	 * @var \RecycleBin\FrontPageBuddy\Widgets\Collection
	 */
	private $widget_collection;

	/**
	 * Components that may have a front page.
	 *
	 * @var array
	 */
	private $components;

	/**
	 * Get the Admin object.
	 *
	 * @return \RecycleBin\FrontPageBuddy\Admin
	 */
	public function admin() {
		return $this->admin;
	}

	/**
	 * Get the Widget Collections object.
	 *
	 * @return \RecycleBin\FrontPageBuddy\Widgets\Collection
	 */
	public function widget_collection() {
		return $this->widget_collection;
	}

	/**
	 * Groups helper.
	 *
	 * @return \RecycleBin\FrontPageBuddy\Components\BPGroups
	 */
	public function bp_groups() {
		return $this->components['bp_groups'];
	}

	/**
	 * Member profiles helper.
	 *
	 * @return \RecycleBin\FrontPageBuddy\Components\BPProfiles
	 */
	public function bp_member_profiles() {
		return $this->components['bp_members'];
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
		// Custom load hook, to notify dependent plugins.
		add_action( 'plugins_loaded', array( $this, 'load_hook' ), 20 );

		// Setup globals.
		add_action( 'frontpage_buddy_load', array( $this, 'setup_globals' ), 2 );

		// Load textdomain.
		add_action( 'frontpage_buddy_load', array( $this, 'load_plugin_textdomain' ), 4 );

		// Load groups and member profile helpers.
		add_action( 'frontpage_buddy_load', array( $this, 'load_components' ), 8 );

		// bp_init hook.
		add_action( 'bp_init', array( $this, 'bp_init' ) );
	}

	/**
	 * Custom load hook, to notify dependent plugins.
	 *
	 * @return void
	 */
	public function load_hook() {
		do_action( 'frontpage_buddy_load' );
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
	 * Register group extension if enabled.
	 *
	 * @return void
	 */
	public function load_components() {
		$enabled_for = $this->option( 'enabled_for' );

		// Components that may have a front page.
		$this->components['bp_members'] = new Components\BPProfiles( 'bp_members', 'Member Profiles' );
		$this->components['bp_groups']  = new Components\BPGroups( 'bp_groups', 'Groups' );

		// buddypress groups helper.
		if ( ! empty( $enabled_for ) && in_array( 'bp_groups', $enabled_for ) ) {
			if ( \bp_is_active( 'groups' ) ) {
				bp_register_group_extension( '\RecycleBin\FrontPageBuddy\GroupExtension' );
			}
		}

		// buddypress member profiles helper.
		if ( ! empty( $enabled_for ) && in_array( 'bp_members', $enabled_for ) ) {
			new MemberProfiles();

			// We need to load our own template file for member's custom front pages.
			if ( function_exists( 'bp_register_template_stack' ) ) {
				// add new location in template stack
				// 13 is between theme and buddypress's template directory
				bp_register_template_stack( array( $this, 'register_template_stack' ), 13 );

				add_filter( 'bp_get_template_stack', array( $this, 'maybe_remove_template_stack' ) );
			}
		}
	}

	/**
	 * Run code on bp_init hook
	 *
	 * @return void
	 */
	public function bp_init() {
		if ( ( is_admin() || is_network_admin() ) && current_user_can( 'manage_options' ) ) {
			$this->admin = new Admin();
		}

		$this->widget_collection = Widgets\Collection::get_instance();

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
			wp_enqueue_script( 'trumbowyg', 'https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js', array( 'jquery' ), '2.27.3', true );
			wp_enqueue_style( 'trumbowyg', 'https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css', '2.27.3', true );

			// wp_enqueue_script( 'frontpage_buddy', FPBUDDY_PLUGIN_URL . 'assets/script' . $min . '.js', array( 'jquery', 'jquery-form' ), '1.0.0', true );
			wp_enqueue_script( 'frontpage_buddy', FPBUDDY_PLUGIN_URL . 'assets/script.js', array( 'jquery', 'jquery-form' ), time(), true );

			$data = apply_filters(
				'frontpage_buddy_script_data',
				array(
					'config'       => array(
						'req' => array(
							'change_status' => array(
								'action' => 'frontpage_buddy_change_status',
								'nonce'  => wp_create_nonce( 'frontpage_buddy_change_status' ),
							),

							'update_layout' => array(
								'action' => 'frontpage_buddy_update_layout',
								'nonce'  => wp_create_nonce( 'frontpage_buddy_update_layout' ),
							),

							'widget_opts_get' => array(
								'action' => 'frontpage_buddy_widget_opts_get',
								'nonce'  => wp_create_nonce( 'frontpage_buddy_widget_opts_get' ),
							),
						),
						'img_spinner' => network_home_url( 'wp-includes/images/spinner.gif' ),
					),
					'object_type' => '',
					'object_id' => 0,
				)
			);
			wp_localize_script( 'frontpage_buddy', 'FRONTPAGE_BUDDY', $data );

			// wp_enqueue_style( 'frontpage_buddy', FPBUDDY_PLUGIN_URL . 'assets/style' . $min . '.css', array(), '0.1' );
			wp_enqueue_style( 'frontpage_buddy', FPBUDDY_PLUGIN_URL . 'assets/style.css', array(), time() );
		}

		// assets for view(front page) screen
		if ( ( bp_is_user() && 'front' === bp_current_component() ) ||
			( bp_is_active( 'groups' ) && bp_is_group() && 'home' == bp_current_action() )
		) {
			// wp_enqueue_style( 'frontpage_buddy', FPBUDDY_PLUGIN_URL . 'assets/style' . $min . '.css', array(), '0.1' );
		}
	}

	public function register_template_stack() {
		return FPBUDDY_PLUGIN_DIR . 'templates';
	}

	function maybe_remove_template_stack( $stack ) {
		$need_template_stack = false;
		$enabled_for         = $this->option( 'enabled_for' );

		if ( bp_is_user() && ! empty( $enabled_for ) && in_array( $this->bp_member_profiles()->get_component_type(), $enabled_for ) ) {
			// Does the current user want to have a custom front page template?
			$need_template_stack = $this->bp_member_profiles()->has_custom_front_page( bp_displayed_user_id() );
		}

		if ( ! $need_template_stack ) {
			// Remove this plugin's template stack.
			$new_stack = array();
			foreach ( $stack as $filepath ) {
				if ( strpos( $filepath, FPBUDDY_PLUGIN_DIR ) === false ) {
					$new_stack[] = $filepath;
				}
			}

			return $new_stack;
		}

		return $stack;
	}
}
