<?php
/**
 * Admin class, to add settings screen, etc.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy;

defined( 'ABSPATH' ) || exit;

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
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( frontpage_buddy()->network_activated && isset( $_GET['updated'] ) ) {
					echo '<div class="updated"><p>' . esc_html__( 'Settings updated.', 'frontpage-buddy' ) . '</p></div>';
				}
				?>

				<?php settings_fields( $this->option_name ); ?>
				<?php do_settings_sections( __FILE__ ); ?>

				<span style="display:none; visibility:hidden;" class="rb_tabify_marker" data-id="frontpage-buddy" data-style="nav"></span>

				<p class="submit">
					<input name="frontpage_buddy_submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'frontpage-buddy' ); ?>" />
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

		register_setting(
			$this->option_name,
			$this->option_name,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'plugin_options_validate' ),
			)
		);

		$label = sprintf(
			/* translators: %s: html for dashicons-admin-plugins */
			__( '%s Integrations', 'frontpage-buddy' ),
			'<span class="dashicons dashicons-admin-plugins"></span> '
		);
		add_settings_section( 'section_integration', $label, array( $this, 'section_integration_desc' ), __FILE__ );

		add_settings_field( 'integrations', '', array( $this, 'integrations' ), __FILE__, 'section_integration', array( 'class' => 'hide_field_heading' ) );

		$label = sprintf(
			/* translators: %s: html for dashicons-screenoptions */
			__( '%s Widgets', 'frontpage-buddy' ),
			'<span class="dashicons dashicons-screenoptions"></span> '
		);

		add_settings_section( 'section_widgets', $label, array( $this, 'section_widgets_desc' ), __FILE__ );
		add_settings_field( 'widgets', '', array( $this, 'widgets' ), __FILE__, 'section_widgets', array( 'class' => 'hide_field_heading' ) );

		$label = sprintf(
			/* translators: %s: html for dashicons-admin-appearance */
			__( '%s Appearance', 'frontpage-buddy' ),
			'<span class="dashicons dashicons-admin-appearance"></span> '
		);
		add_settings_section( 'section_theme', $label, array( $this, 'section_theme_desc' ), __FILE__ );
		add_settings_field( 'editor_color_bg', __( 'Background Color', 'frontpage-buddy' ), array( $this, 'editor_color_bg' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_text', __( 'Text Color', 'frontpage-buddy' ), array( $this, 'editor_color_text' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_primary', __( 'Primary Color', 'frontpage-buddy' ), array( $this, 'editor_color_primary' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_primary_contrast', __( 'Primary Color - Contrast', 'frontpage-buddy' ), array( $this, 'editor_color_primary_contrast' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_secondary', __( 'Secondary Color', 'frontpage-buddy' ), array( $this, 'editor_color_secondary' ), __FILE__, 'section_theme' );
		add_settings_field( 'editor_color_secondary_contrast', __( 'Secondary Color - Contrast', 'frontpage-buddy' ), array( $this, 'editor_color_secondary_contrast' ), __FILE__, 'section_theme' );
		add_settings_field( 'custom_styling', '', array( $this, 'custom_styling' ), __FILE__, 'section_theme' );
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

		wp_enqueue_style( 'frontpage-buddy-admin', FRONTPAGE_BUDDY_PLUGIN_URL . 'assets/css/admin.css', array( 'wp-color-picker' ), FRONTPAGE_BUDDY_PLUGIN_VERSION );
		wp_enqueue_script( 'frontpage-buddy-admin', FRONTPAGE_BUDDY_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker', 'wp-tinymce' ), FRONTPAGE_BUDDY_PLUGIN_VERSION, array( 'in_footer' => true ) );
	}

	/**
	 * Section description.
	 *
	 * @return void
	 */
	public function section_integration_desc() {
		echo '<div class="notice notice-info inline"><p>';
		esc_html_e( 'FrontPage Buddy extends other plugins\' functionality, by providing better profile pages for users and groups.', 'frontpage-buddy' );
		echo '<br>';

		printf(
			/* translators: %s: list of plugins frontpage-buddy works with. */
			esc_html__( 'Currently it works with the following plugins: %s. It automatically detects if any of those plugins are active on your website and shows you relevant options.', 'frontpage-buddy' ),
			'BuddyPress, BuddyBoss, bbPress & UltimateMember'
		);

		echo '<br>';
		printf(
			/* translators: %s: link to plugin documentation. */
			esc_html__( 'Check %s for more details.', 'frontpage-buddy' ),
			'<a href="https://www.recycleb.in/frontpage-buddy/integrations/about/" rel="noreferrer">' . esc_html__( 'plugin documentation', 'frontpage-buddy' ) . '</a>'
		);

		echo '</p></div>';
	}

	/**
	 * Section description.
	 *
	 * @return void
	 */
	public function section_widgets_desc() {
		echo '<div class="notice notice-info inline"><p>';
		esc_html_e( 'Widgets are individual blocks of content that can be added on front pages.', 'frontpage-buddy' );
		echo '<br>';
		esc_html_e( 'These are completely unrelated to WordPress widgets.', 'frontpage-buddy' );

		echo '<br>';
		printf(
			/* translators: %s: link to plugin documentation. */
			esc_html__( 'Check %s for more details.', 'frontpage-buddy' ),
			'<a href="https://www.recycleb.in/frontpage-buddy/widgets/about/" rel="noreferrer">' . esc_html__( 'plugin documentation', 'frontpage-buddy' ) . '</a>'
		);

		echo '</p></div>';
	}

	/**
	 * Section description.
	 *
	 * @return void
	 */
	public function section_theme_desc() {
		echo '<div class="notice notice-info inline"><p>';
		esc_html_e( 'Settings for the screen where your website\'s users can manage/edit the front page.', 'frontpage-buddy' );
		echo '<br>';
		printf(
			/* translators: %s: link to plugin documentation. */
			esc_html__( 'Check %s for more details.', 'frontpage-buddy' ),
			'<a href="https://www.recycleb.in/frontpage-buddy/styling/" rel="noreferrer">' . esc_html__( 'plugin documentation', 'frontpage-buddy' ) . '</a>'
		);
		printf(
			'&nbsp;<button class="button button-secondary button-small" id="btn_color_scheme_reset" data-confirm="%s">%s</button>',
			esc_attr( esc_html__( 'This will reset all color options to default values. Are you sure?', 'frontpage-buddy' ) ),
			esc_html__( 'Reset', 'frontpage-buddy' )
		);
		echo '</p></div>';
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
				case 'editor_color_secondary':
				case 'editor_color_secondary_contrast':
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

				case 'widgets':
					if ( ! empty( $field_value ) ) {
						foreach ( $field_value as $widget_type => $widget_type_fields ) {
							$widget_type_obj = frontpage_buddy()->get_widget_type( $widget_type );
							if ( empty( $widget_type_obj ) ) {
								$field_value = false;
							} else {
								$registered_fields = $widget_type_obj->get_settings_fields();
								if ( empty( $registered_fields ) ) {
									$field_value = false;
								} else {
									foreach ( $widget_type_fields as $i_field_name => $entered_value ) {
										if ( isset( $registered_fields[ $i_field_name ] ) ) {
											$widget_type_fields[ $i_field_name ] = sanitize_field( $entered_value, $registered_fields[ $i_field_name ] );
										} else {
											unset( $field_value[ $i_field_name ] );
										}
									}
								}
							}
						}
					}
					break;
			}

			$input[ $field_name ] = $field_value;
		}

		return $input; // No validations for now.
	}

	/**
	 * List available integrations.
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
			<div class="notice notice-error notice-alt inline fpbuddy-notice-style1">
				<h3 class="notice-title"><?php esc_html_e( 'No compatible plugins were found(active).', 'frontpage-buddy' ); ?></h3>
				<p>
					<?php
					printf(
						/* translators: %s: Name of the plugin 'Frontpage buddy'. */
						esc_html__( '%s will have no effect.', 'frontpage-buddy' ),
						'Frontpage buddy'
					);
					?>
				</p>
			</div>
			<?php
			return false;
		}

		foreach ( $all_integrations as $integration_type => $integration_obj ) {
			echo '<table class="table widefat striped form-table fpbuddy-box integration integration-' . esc_attr( $integration_type ) . '">';
			echo '<thead><tr class="integration-title"><td colspan="100%">';
			printf( '<h3 class="fpbuddy-box-title">%s</h3>', esc_html( $integration_obj->get_integration_name() ) );
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

			$fields_html = generate_form_fields(
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

			$allowed_tags = wp_parse_args( basic_html_allowed_tags(), form_elements_allowed_tags() );
			echo wp_kses( $fields_html, $allowed_tags );

			echo '</tbody></table>';
		}
	}

	/**
	 * Settings for each registered widget.
	 *
	 * @return void
	 */
	public function widgets() {
		$settings_name      = __FUNCTION__;
		$registered_widgets = frontpage_buddy()->get_all_widget_types();
		$all_integrations   = frontpage_buddy()->get_all_integrations();
		if ( empty( $all_integrations ) ) {
			?>
			<div class="notice notice-error notice-alt inline fpbuddy-notice-style1">
				<p>
					<?php esc_html_e( 'No compatible plugins were found(active).', 'frontpage-buddy' ); ?>
					<br>
					<?php esc_html_e( 'List of widgets is unavailable.', 'frontpage-buddy' ); ?>
				</p>
			</div>
			<?php
			return;
		}

		foreach ( $registered_widgets as $widget_type => $widget_type_obj ) {
			echo '<table class="table widefat form-table fpbuddy-box widget-' . esc_attr( $widget_type ) . '">';
			echo '<thead><tr><td colspan="100%"><h3 class="fpbuddy-box-title">' . esc_html( $widget_type_obj->name ) . '</h3></td></tr></thead>';
			echo '<tbody>';

			echo '<tr><td colspan="100%"><div class="description">' . wp_kses( $widget_type_obj->get_admin_description(), basic_html_allowed_tags() ) . '</div></td></tr>';

			// widget specific settings.
			$settings_fields = $widget_type_obj->get_settings_fields();
			if ( ! empty( $settings_fields ) ) {
				$settings_fields_mod = array();
				foreach ( $settings_fields as $field_name => $v ) {
					$field_name                         = $this->option_name . '[' . $settings_name . '][' . $widget_type . '][' . $field_name . ']';
					$settings_fields_mod[ $field_name ] = $v;
				}

				$settings_fields = $settings_fields_mod;

				$fields_html = generate_form_fields(
					$settings_fields,
					array(
						'before_field' => '<tr class="{{FIELD_CLASS}}">',
						'after_field'  => '</tr><!-- .field -->',
						'before_label' => '<th>',
						'after_label'  => '</th>',
						'before_input' => '<td>',
						'after_input'  => '</td>',
					)
				);

				$allowed_tags = wp_parse_args( basic_html_allowed_tags(), form_elements_allowed_tags() );
				echo wp_kses( $fields_html, $allowed_tags );
			}

			echo '</tbody>';
			echo '</table>';
		}

		echo '<div class="frontpage-buddy-notice-style1">';
		echo '<h3 class="notice-title">';
		esc_html_e( 'Need more widgets for your website?', 'frontpage-buddy' );
		echo '</h3>';
		echo '<p>';
		printf(
			'<a href="https://www.recycleb.in/frontpage-buddy/widgets/custom/" class="button button-hero button-primary button-link-external" target="_blank" rel="noreferrer">%s <sup><span class="dashicons dashicons-external"></span></sup></a>',
			esc_html__( 'Contact this plugin\'s developer', 'frontpage-buddy' )
		);
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Background color.
	 *
	 * @return void
	 */
	public function editor_color_bg() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s" data-default="#ffffff">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Background color of the entire area.', 'frontpage-buddy' )
		);
	}

	/**
	 * Text color.
	 *
	 * @return void
	 */
	public function editor_color_text() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s" data-default="#333333">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Text color of the entire area.', 'frontpage-buddy' )
		);
	}

	/**
	 * Primary color.
	 *
	 * @return void
	 */
	public function editor_color_primary() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s" data-default="#235789">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Used as: border colors, button colors, etc. for widgets/columns.', 'frontpage-buddy' )
		);
	}

	/**
	 * Primary color - contrast.
	 *
	 * @return void
	 */
	public function editor_color_primary_contrast() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s" data-default="#ffffff">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Contrast color of the primary color', 'frontpage-buddy' )
		);
	}

	/**
	 * Secondary color.
	 *
	 * @return void
	 */
	public function editor_color_secondary() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s" data-default="#b9d6f2">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Used as: border colors, button colors, etc. for sections/rows.', 'frontpage-buddy' )
		);
	}

	/**
	 * Secondary color - contrast.
	 *
	 * @return void
	 */
	public function editor_color_secondary_contrast() {
		$field_name  = __FUNCTION__;
		$field_value = $this->option( $field_name );

		printf(
			'<input type="text" class="fpbuddy-color-picker" name="%s" value="%s" data-default="#000000">',
			esc_attr( $this->option_name . '[' . $field_name . ']' ),
			esc_attr( $field_value )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Contrast color of the secondary color', 'frontpage-buddy' )
		);
	}

	/**
	 * Custom styling information.
	 *
	 * @return void
	 */
	public function custom_styling() {
		echo '<div class="frontpage-buddy-notice-style1">';
		echo '<h3 class="notice-title">';
		esc_html_e( 'Need custom styling', 'frontpage-buddy' );
		echo '<br>&nbsp;';
		echo '<small>' . esc_html__( 'for frontpage and editor tailored to your website?', 'frontpage-buddy' ) . '</small>';
		echo '</h3>';
		echo '<p>';
		printf(
			'<a href="https://www.recycleb.in/frontpage-buddy/styling/" class="button button-hero button-primary button-link-external" target="_blank" rel="noreferrer">%s <sup><span class="dashicons dashicons-external"></span></sup></a>',
			esc_html__( 'Contact this plugin\'s developer', 'frontpage-buddy' )
		);
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Save settings when activated network wide.
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
			$sanitized = sanitize_option( $this->option_name, wp_unslash( $_POST[ $this->option_name ] ) );
			update_site_option( $this->option_name, $sanitized );
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
		if ( plugin_basename( basename( constant( 'FRONTPAGE_BUDDY_PLUGIN_DIR' ) ) . '/loader.php' ) !== $file ) {
			return $links;
		}

		$mylinks = array(
			'<a href="' . esc_url( $this->plugin_settings_url ) . '">' . esc_html__( 'Settings', 'frontpage-buddy' ) . '</a>',
		);

		return array_merge( $links, $mylinks );
	}
}
