<?php
/**
 * Admin class, to add settings screen, etc.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Admin class, to add settings screen, etc.
 */
class Admin {
	/**
	 * Plugin options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Settings screen slug.
	 *
	 * @var string
	 */
	private $plugin_slug = 'frontpage_buddy';

	/**
	 * Name of the options key
	 *
	 * @var string
	 */
	private $option_name = 'frontpage_buddy_options';

	/**
	 * Menu hook.
	 *
	 * @var string
	 */
	private $menu_hook = 'admin_menu';

	/**
	 * Settings page.
	 *
	 * @var string
	 */
	private $settings_page = 'options-general.php';

	/**
	 * User capability to access settings screen.
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * Where does the settings form submit to?
	 *
	 * @var string
	 */
	private $form_action = 'options.php';

	/**
	 * Url for plugin settings screen.
	 *
	 * @var string
	 */
	private $plugin_settings_url = '';

	/**
	 * Get a settings value.
	 *
	 * @param string $key settings name.
	 * @return mixed
	 */
	public function option( $key ) {
		$value = frontpage_buddy()->option( $key );
		return $value;
	}

	/**
	 * Empty constructor function to ensure a single instance
	 */
	public function __construct() {
		if ( ( ! is_admin() && ! is_network_admin() ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->plugin_settings_url = admin_url( 'options-general.php?page=' . $this->plugin_slug );

		// if the plugin is activated network wide in multisite, we need to override few variables.
		if ( \frontpage_buddy()->network_activated ) {
			// Main settings page - menu hook.
			$this->menu_hook = 'network_admin_menu';

			// Main settings page - parent page.
			$this->settings_page = 'settings.php';

			// Main settings page - Capability.
			$this->capability = 'manage_network_options';

			// Settins page - form's action attribute.
			$this->form_action = 'edit.php?action=' . $this->plugin_slug;

			// Plugin settings page url.
			$this->plugin_settings_url = network_admin_url( 'settings.php?page=' . $this->plugin_slug );
		}

		// If the plugin is activated network wide in multisite, we need to process settings form submit ourselves.
		if ( \frontpage_buddy()->network_activated ) {
			add_action( 'network_admin_edit_' . $this->plugin_slug, array( $this, 'save_network_settings_page' ) );
		}

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( $this->menu_hook, array( $this, 'admin_menu' ) );

		add_filter( 'plugin_action_links', array( $this, 'add_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'add_action_links' ), 10, 2 );
	}

	/**
	 * Add admin menu item.
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page(
			$this->settings_page,
			__( 'FrontPage Buddy', 'fontpage-buddy' ),
			__( 'FrontPage Buddy', 'fontpage-buddy' ),
			$this->capability,
			$this->plugin_slug,
			array( $this, 'options_page' ),
		);
	}

	/**
	 * Load the main settings screen.
	 *
	 * @return void
	 */
	public function options_page() {
		?>
		<div class="wrap">
			<h2><?php echo \get_admin_page_title(); ?></h2>
			<form method="post" action="<?php echo esc_attr( $this->form_action ); ?>">

				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
				if ( frontpage_buddy()->network_activated && isset( $_GET['updated'] ) ) {
					echo '<div class="updated"><p>' . esc_attr__( 'Settings updated.', 'bp-msgat' ) . '</p></div>';
				}
				?>

				<?php settings_fields( $this->option_name ); ?>
				<?php do_settings_sections( __FILE__ ); ?>

				<p class="submit">
					<input name="frontpage_buddy_submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Setup admin stuff
	 *
	 * @return void
	 */
	public function admin_init() {
		register_setting( $this->option_name, $this->option_name, array( $this, 'plugin_options_validate' ) );

		add_settings_section( 'general_section', '', array( $this, 'section_general' ), __FILE__ );
		add_settings_field( 'enabled_for', __( 'Enable landing pages for', 'frontpage-buddy' ), array( $this, 'enabled_for' ), __FILE__, 'general_section' );
		add_settings_field( 'widget_settings', __( 'Widget Settings', 'frontpage-buddy' ), array( $this, 'widget_settings' ), __FILE__, 'general_section' );

		// add_settings_section( 'other_section', '', array( $this, 'other_section' ), __FILE__ );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function section_general() {
		// Nothing yet.
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $input
	 * @return void
	 */
	public function plugin_options_validate( $input ) {
		return $input; // no validations for now
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enabled_for() {
		$enabled_objects = $this->option( 'enabled_for' );
		if ( empty( $enabled_objects ) ) {
			$enabled_objects = array(); // make sure its an array.
		}

		if ( function_exists( '\buddypress' ) ) {
			// Member profiles are always enabled.
			$component_type = frontpage_buddy()->bp_member_profiles()->get_component_type();
			$component_name = frontpage_buddy()->bp_member_profiles()->get_component_name();
			echo "<div class='component'>";

			$checked = in_array( $component_type, $enabled_objects ) ? 'checked' : '';
			printf(
				"<label><input type='checkbox' name='%s' value='%s' %s>%s</label>",
				esc_attr( $this->option_name ) . '[enabled_for][]',
				esc_attr( $component_type ),
				// phpcs:ignore WordPress.Security.EscapeOutput
				$checked,
				esc_html( $component_name )
			);

			if ( ! function_exists( '\bp_nouveau_get_appearance_settings' ) || ! \bp_nouveau_get_appearance_settings( 'user_front_page' ) ) {
				echo '<div class="notice notice-warning inline"><p>';
				printf(
					/* translators: 1: 'Member front page' */
					esc_html__( 'Custom front pages for members is not enabled. Please, go to Appearance > Customize > BuddyPress Nouveau > %s and enable it first.', 'frontpage-buddy' ),
					esc_html__( 'Member front page', 'buddypress' ),
				);
				echo '</p></div>';
			};

			echo '</div>';
		}

		if ( function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) ) {
			$component_type = frontpage_buddy()->bp_groups()->get_component_type();
			$component_name = frontpage_buddy()->bp_groups()->get_component_name();
			echo "<div class='component'>";

			$checked = in_array( $component_type, $enabled_objects ) ? 'checked' : '';
			printf(
				"<label><input type='checkbox' name='%s' value='%s' %s>%s</label>",
				esc_attr( $this->option_name ) . '[enabled_for][]',
				esc_attr( $component_type ),
				// phpcs:ignore WordPress.Security.EscapeOutput
				$checked,
				esc_html( $component_name )
			);

			if ( ! function_exists( '\bp_nouveau_get_appearance_settings' ) || ! \bp_nouveau_get_appearance_settings( 'group_front_page' ) ) {
				echo '<div class="notice notice-warning inline"><p>';
				printf(
					/* translators: 1: 'Group front page' */
					esc_html__( 'Custom front pages for members is not enabled. Please, go to Appearance > Customize > BuddyPress Nouveau > %s and enable it first.', 'frontpage-buddy' ),
					esc_html__( 'Group front page', 'buddypress' ),
				);
				echo '</p></div>';
			};

			echo '</div>';
		}
	}

	public function widget_settings() {
		$field_name  = __FUNCTION__;
        $field_value = $this->option( $field_name );
		$registered_widgets = frontpage_buddy()->widget_collection()->get_registered_widgets();

		$components = array(
			frontpage_buddy()->bp_member_profiles()->get_component_type() => frontpage_buddy()->bp_member_profiles()->get_component_name(),
		);

		if ( function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) ) {
			$components[ frontpage_buddy()->bp_groups()->get_component_type() ] = frontpage_buddy()->bp_groups()->get_component_name();
		}

		foreach ( $registered_widgets as $widget_type => $widget_class ) {
			$this_widget_settings = isset( $field_value[ $widget_type ] ) ? $field_value[ $widget_type ] : array();
			$obj = new $widget_class();
			echo '<table class="table widefat form-table">';
			echo '<thead><tr><td colspan="100%"><strong>' . esc_html( $obj->name ) . '</strong></td></tr></thead>';
			echo '<tbody>';

			echo '<tr><td colspan="100%"><p class="description">' . wp_kses( $obj->get_description_admin(), array( 'a' => array( 'href' => true ) ) ) . '</p></td></tr>';

			echo '<tr><td>Enabled for</td><td>';
			foreach ( $components as $component_type => $component_name ) {
				$cb_name = $this->option_name . '[' . $field_name . '][' . $widget_type . '][enabled_for][]';
				$checked = '';
				if ( isset( $this_widget_settings['enabled_for'] ) && ! empty( $this_widget_settings['enabled_for'] ) ) {
					$checked = in_array( $component_type, $this_widget_settings['enabled_for'], true ) ? 'checked' : '';
				}

				printf( "<label><input type='checkbox' value='%s' name='%s' %s> %s</label>&nbsp;&nbsp;", esc_attr( $component_type ), esc_attr( $cb_name ), $checked, esc_html( $component_name ) );
			}
			echo '</td></tr>';

			// widget specific settings.

			echo '</tbody>';
			echo '</table>';
		}
	}

	public function save_network_settings_page() {
		if ( ! check_admin_referer( $this->option_name . '-options' ) ) {
			return;
		}

		if ( ! current_user_can( $this->capability ) ) {
			die( 'Access denied!' );
		}

		if ( isset( $_POST['frontpage_buddy_submit'] ) ) {
			$submitted = stripslashes_deep( $_POST[ $this->option_name ] );
			$submitted = $this->plugin_options_validate( $submitted );

			update_site_option( $this->option_name, $submitted );
		}

		// Where are we redirecting to?
		$base_url     = trailingslashit( network_admin_url() ) . 'settings.php';
		$redirect_url = add_query_arg(
			array(
				'page'    => $this->plugin_slug,
				'updated' => 'true',
			),
			$base_url
		);

		// Redirect.
		wp_safe_redirect( $redirect_url );
		die();
	}

	public function add_action_links( $links, $file ) {
		// Return normal links if not this plugin
		if ( FPBUDDY_PLUGIN_DIR . 'loader.php' !== $file ) {
			return $links;
		}

		$mylinks = array(
			'<a href="' . esc_url( $this->plugin_settings_url ) . '">' . __( 'Settings', 'frontpage-buddy' ) . '</a>',
		);
		return array_merge( $links, $mylinks );
	}
}
