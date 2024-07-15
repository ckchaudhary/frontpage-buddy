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
	 * The hook suffix generated by call to add_submenu_page function.
	 *
	 * @var string
	 */
	private $generate_hook_sufix = '';

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
		$this->generate_hook_sufix = add_submenu_page(
			$this->settings_page,
			__( 'FrontPage Buddy', 'frontpage-buddy' ),
			__( 'FrontPage Buddy', 'frontpage-buddy' ),
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
			<h2><?php echo esc_html( \get_admin_page_title() ); ?></h2>
			<form method="post" action="<?php echo esc_attr( $this->form_action ); ?>">

				<?php
				// phpcs:ignore
				if ( frontpage_buddy()->network_activated && isset( $_GET['updated'] ) ) {
					echo '<div class="updated"><p>' . esc_attr__( 'Settings updated.', 'frontpage-buddy' ) . '</p></div>';
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
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );

		register_setting( $this->option_name, $this->option_name, array( $this, 'plugin_options_validate' ) );

		add_settings_section( 'section_integration', __( 'Integrations', 'frontpage-buddy' ), array( $this, 'section_integration_desc' ), __FILE__ );
		add_settings_field( 'integrations', '', array( $this, 'integrations' ), __FILE__, 'section_integration' );

		add_settings_section( 'section_widgets', __( 'Widgets', 'frontpage-buddy' ), array( $this, 'section_widgets_desc' ), __FILE__ );
		add_settings_field( 'widgets', '', array( $this, 'widgets' ), __FILE__, 'section_widgets' );

		add_settings_section( 'section_theme', __( 'Appearance', 'frontpage-buddy' ), array( $this, 'section_theme_desc' ), __FILE__ );
		add_settings_field( 'editor_theme_settings', __( 'Edit front page', 'frontpage-buddy' ), array( $this, 'editor_theme_settings' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_bg', '', array( $this, 'editor_color_bg' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_text', '', array( $this, 'editor_color_text' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_primary', '', array( $this, 'editor_color_primary' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_primary_contrast', '', array( $this, 'editor_color_primary_contrast' ), __FILE__, 'section_theme' );
	}

	/**
	 * Load css and js files.
	 *
	 * @param string $hook_suffix hook suffix for current screen.
	 * @return boolean
	 */
	public function load_admin_assets( $hook_suffix ) {
		if ( $hook_suffix !== $this->generate_hook_sufix ) {
			return false;
		}

		wp_enqueue_style( 'frontpage-buddy-admin', FPBUDDY_PLUGIN_URL . 'assets/css/admin.css', array( 'wp-color-picker' ), FPBUDDY_PLUGIN_VERSION );
		wp_enqueue_script( 'frontpage-buddy-admin', FPBUDDY_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), FPBUDDY_PLUGIN_VERSION, array( 'in_footer' => true ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function section_integration_desc() {
		// Nothing yet.
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function section_widgets_desc() {
		// Nothing yet.
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function section_theme_desc() {
		// Nothing yet.
	}

	/**
	 * Validate plugin options.
	 *
	 * @param mixed $input array of options.
	 * @return mixed
	 */
	public function plugin_options_validate( $input ) {
		if ( empty( $input ) ) {
			return $input;
		}

		foreach ( $input as $field_name => $field_value ) {
			switch ( $field_name ) {
				case 'enabled_for':
					if ( ! empty( $field_value ) ) {
						$val_count = count( $field_value );
						for ( $i = 0; $i < $val_count; $i++ ) {
							$field_value[ $i ] = sanitize_title( $field_value[ $i ] );
						}
					}
					break;

				case 'editor_color_bg':
				case 'editor_color_text':
				case 'editor_color_primary':
				case 'editor_color_primary_contrast':
					$field_value = sanitize_hex_color( $field_value );
					break;

				case 'integrations':
					if ( ! empty( $field_value ) ) {
						foreach ( $field_value as $integration_type => $integration_fields ) {
							$integration = frontpage_buddy()->get_integration( $integration_type );
							if ( empty( $integration ) ) {
								$field_value = false;
							} else {
								$registered_fields = $integration->get_settings_fields();
								if ( empty( $registered_fields ) ) {
									$field_value = false;
								} else {
									foreach ( $integration_fields as $i_field_name => $entered_value ) {
										if ( isset( $registered_fields[ $i_field_name ] ) ) {
											$integration_fields[ $i_field_name ] = sanitize_field( $entered_value, $registered_fields[ $i_field_name ] );
										} else {
											unset( $field_value[ $i_field_name ] );
										}
									}
								}
							}

							$field_value[ $integration_type ] = $integration_fields;
						}
					}
					break;

				// @todo: sanitize widget prototype settings.
			}

			$input[ $field_name ] = $field_value;
		}

		return $input; // No validations for now.
	}

	/**
	 * Undocumented function
	 *
	 * @return boolean
	 */
	public function integrations() {
		$main_setting_name = __FUNCTION__;

		$enabled_objects = $this->option( 'enabled_for' );
		if ( empty( $enabled_objects ) ) {
			$enabled_objects = array(); // make sure its an array.
		}

		$all_integrations = frontpage_buddy()->get_all_integrations();

		if ( empty( $all_integrations ) ) {
			?>
			<div class='notice notice-error inline'>
				<?php
				printf(
					/* translators: %s: list of plugins frontpage-buddy works with. */
					'<p>' . esc_html__( 'Frontpage buddy can only work when either of the following plugins are active: %s', 'frontpage-buddy' ) . '.</p>'
					. '<p>' . esc_html__( 'Not much it can do for now!', 'frontpage-buddy' ) . '</p>',
					'BuddyPress, BuddyBoss, bbPress, UltimateMember'
				);
				?>
			</div>
			<?php
			return false;
		}

		foreach ( $all_integrations as $integration_type => $integration_obj ) {
			echo '<table class="table widefat striped form-table integration">';
			echo '<thead><tr class="integration-title"><td colspan="100%">';
			printf( '<h3>%s</h3>', esc_html( $integration_obj->get_integration_name() ) );
			echo '</td></tr></thead>';

			echo '<tbody>';

			echo '<tr class="integration-desc"><td colspan="100%" >';
			echo wp_kses( $integration_obj->get_admin_description(), basic_html_allowed_tags() );
			echo '</td></tr>';

			$attributes = array();
			if ( in_array( $integration_type, $enabled_objects, true ) ) {
				$attributes['checked'] = 'checked';
			}
			$fields = array(
				$this->option_name . '[enabled_for][]' => array(
					'type'       => 'switch',
					'label'      => __( 'Enable this integration?', 'frontpage-buddy' ),
					'value'      => $integration_type,
					'label_off'  => __( 'No', 'frontpage-buddy' ),
					'label_on'   => __( 'Yes', 'frontpage-buddy' ),
					'attributes' => $attributes,
				),
			);

			$settings_fields = $integration_obj->get_settings_fields();
			if ( ! empty( $settings_fields ) ) {
				$settings_fields_mod = array();
				foreach ( $settings_fields as $field_name => $v ) {
					$field_name                         = $this->option_name . '[' . $main_setting_name . '][' . $integration_type . '][' . $field_name . ']';
					$settings_fields_mod[ $field_name ] = $v;
				}

				$fields = array_merge( $fields, $settings_fields_mod );
			}

			// phpcs:ignore WordPress.Security.EscapeOutput
			echo generate_form_fields(
				$fields,
				array(
					'before_field' => '<tr class="{{FIELD_CLASS}}">',
					'after_field'  => '</tr><!-- .field -->',
					'before_label' => '<th>',
					'after_label'  => '</th>',
					'before_input' => '<td>',
					'after_input'  => '</td>',
				)
			);

			echo '</tbody></table>';
		}
	}

	/**
	 * Settings for each registered widget.
	 *
	 * @return boolean
	 */
	public function widgets() {
		$field_name         = __FUNCTION__;
		$field_value        = $this->option( $field_name );
		$registered_widgets = frontpage_buddy()->widget_collection()->get_registered_widgets();

		$all_integrations = frontpage_buddy()->get_all_integrations();
		if ( empty( $all_integrations ) ) {
			return false;
		}

		foreach ( $registered_widgets as $widget_type => $widget_class ) {
			$this_widget_settings = isset( $field_value[ $widget_type ] ) ? $field_value[ $widget_type ] : array();
			$obj                  = new $widget_class( $widget_type );
			echo '<table class="table widefat form-table">';
			echo '<thead><tr><td colspan="100%"><strong>' . esc_html( $obj->name ) . '</strong></td></tr></thead>';
			echo '<tbody>';

			echo '<tr><td colspan="100%"><p class="description">' . wp_kses( $obj->get_description_admin(), array( 'a' => array( 'href' => true ) ) ) . '</p></td></tr>';

			echo '<tr><td>Enabled for</td><td>';
			foreach ( $all_integrations as $integration_type => $integration_obj ) {
				$cb_name = $this->option_name . '[' . $field_name . '][' . $widget_type . '][enabled_for][]';
				$checked = '';
				if ( isset( $this_widget_settings['enabled_for'] ) && ! empty( $this_widget_settings['enabled_for'] ) ) {
					$checked = in_array( $integration_type, $this_widget_settings['enabled_for'], true ) ? 'checked' : '';
				}

				printf(
					"<label><input type='checkbox' value='%s' name='%s' %s> %s</label>&nbsp;&nbsp;",
					esc_attr( $integration_type ),
					esc_attr( $cb_name ),
					// phpcs:ignore WordPress.Security.EscapeOutput
					$checked,
					esc_html( $integration_obj->get_integration_name() )
				);
			}
			echo '</td></tr>';

			// widget specific settings.

			echo '</tbody>';
			echo '</table>';
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function editor_theme_settings() {
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Settings for the screen where your website\'s users can manage/edit the front page.', 'frontpage-buddy' )
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function editor_color_bg() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		echo '<table><tr><th>' . esc_html__( 'Background Color', 'frontpage-buddy' ) . '</th><td>';
		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);
		echo '</td></tr></table>';

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Background color of the entire area.', 'frontpage-buddy' )
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function editor_color_text() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		echo '<table><tr><th>' . esc_html__( 'Text Color', 'frontpage-buddy' ) . '</th><td>';
		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);
		echo '</td></tr></table>';

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Text color of the entire area.', 'frontpage-buddy' )
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function editor_color_primary() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		echo '<table><tr><th>' . esc_html__( 'Primary Color', 'frontpage-buddy' ) . '</th><td>';
		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);
		echo '</td></tr></table>';

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Used as: border colors, button colors, etc.', 'frontpage-buddy' )
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function editor_color_primary_contrast() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		echo '<table><tr><th>' . esc_html__( 'Primary Color - Contrast', 'frontpage-buddy' ) . '</th><td>';
		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);
		echo '</td></tr></table>';

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Constrast color of the primary color', 'frontpage-buddy' )
		);
	}

	/**
	 * Save settings in
	 *
	 * @return void
	 */
	public function save_network_settings_page() {
		if ( ! check_admin_referer( $this->option_name . '-options' ) ) {
			return;
		}

		if ( ! current_user_can( $this->capability ) ) {
			die( 'Access denied!' );
		}

		if ( isset( $_POST['frontpage_buddy_submit'] ) && isset( $_POST[ $this->option_name ] ) ) {
			$submitted = stripslashes_deep( $_POST[ $this->option_name ] );//phpcs:ignore
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

	/**
	 * Add plugins settings link etc on plugins listing page.
	 *
	 * @param array  $links existing links.
	 * @param string $file plugin base file name.
	 * @return array
	 */
	public function add_action_links( $links, $file ) {
		// Return normal links if not this plugin.
		if ( plugin_basename( basename( constant( 'FPBUDDY_PLUGIN_DIR' ) ) . '/loader.php' ) !== $file ) {
			return $links;
		}

		$mylinks = array(
			'<a href="' . esc_url( $this->plugin_settings_url ) . '">' . esc_html__( 'Settings', 'frontpage-buddy' ) . '</a>',
		);

		return array_merge( $links, $mylinks );
	}
}
