<?php
/**
 * 'My Links' widget.
 * Add urls for your website & social profiles using this widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy\Widgets;

use Aws\ForecastQueryService\ForecastQueryServiceClient;

defined( 'ABSPATH' ) || exit;

/**
 * 'My Links' widget.
 */
class MyLinks extends WidgetType {
	/**
	 * Placeholder for field value.
	 *
	 * @var string
	 */
	public $placeholder_field_value = '{{FIELD_VALUE}}';

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->type        = 'mylinks';
		$this->name        = __( 'My Links', 'frontpage-buddy' );
		$this->description = __( 'Add urls for your website & social profiles.', 'frontpage-buddy' );
		$this->icon_image  = '<i class="gg-link"></i>';

		$this->description_admin = '<p>' . esc_html__( 'Enables your users to add urls for website & social profiles.', 'frontpage-buddy' ) . '</p>';

		parent::__construct();
	}

	/**
	 * Get the list of link types.
	 *
	 * @return array
	 */
	protected function get_link_types() {
		$types = array(
			'facebook'  => array(
				'label'       => 'Facebook',
				'value_type'  => 'username',
				'url_pattern' => 'https://www.facebook.com/' . $this->placeholder_field_value,
			),
			'instagram' => array(
				'label'       => 'Instagram',
				'value_type'  => 'username',
				'url_pattern' => 'https://www.instagram.com/' . $this->placeholder_field_value . '/',
			),
			'linkedin'  => array(
				'label'       => 'LinkedIn',
				'value_type'  => 'username',
				'url_pattern' => 'https://www.linkedin.com/in/' . $this->placeholder_field_value . '/',
			),
			'pinterest' => array(
				'label'       => 'Pinterest',
				'value_type'  => 'username',
				'url_pattern' => 'https://www.pinterest.com/' . $this->placeholder_field_value . '/',
			),
			'quora'     => array(
				'label'       => 'Quora',
				'value_type'  => 'username',
				'url_pattern' => 'https://www.quora.com/profile/' . $this->placeholder_field_value . '',
			),
			'reddit'    => array(
				'label'       => 'Reddit',
				'value_type'  => 'username',
				'url_pattern' => 'https://www.reddit.com/user/' . $this->placeholder_field_value . '/',
			),
			'telegram'  => array(
				'label'       => 'Telegram',
				'value_type'  => 'username',
				'url_pattern' => 'https://t.me/' . $this->placeholder_field_value . '/',
			),
			'x'         => array(
				'label'       => 'X/Twitter',
				'value_type'  => 'username',
				'url_pattern' => 'https://x.com/' . $this->placeholder_field_value . '/',
			),
			'youtube'   => array(
				'label'       => 'YouTube',
				'value_type'  => 'username',
				'url_pattern' => 'https://www.youtube.com/@' . $this->placeholder_field_value . '/',
			),
			'website'   => array(
				'label'      => 'Website',
				'value_type' => 'url',
			),
		);

		return apply_filters( 'frontpage_buddy_widget_mylinks_link_types', $types );
	}

	/**
	 * Get the fields for specific settings of this widget type, if any.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		$fields = parent::get_settings_fields();

		$link_type_options = array();
		foreach ( $this->get_link_types() as $k => $v ) {
			$link_type_options[ $k ] = $v['label'];
		}

		$fields['allowed_link_types'] = array(
			'type'    => 'checkbox',
			'label'   => __( 'Allowed link types', 'frontpage-buddy' ),
			'value'   => $this->get_option( 'allowed_link_types' ),
			'options' => $link_type_options,
		);

		return $fields;
	}

	/**
	 * Get all the data 'fields' for the settings/options screen for this widget.
	 *
	 * @param \FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 *
	 * @return array
	 */
	public function get_data_fields( $widget ) {
		$fields = $this->get_default_data_fields( $widget );

		if ( ! isset( $fields['heading']['attributes'] ) ) {
			$fields['heading']['attributes'] = array();
		}
		$fields['heading']['attributes']['placeholder'] = esc_html__( 'E.g: Fine me on', 'frontpage-buddy' );

		$all_link_types     = $this->get_link_types();
		$allowed_link_types = $this->get_option( 'allowed_link_types' );
		if ( empty( $allowed_link_types ) ) {
			return $fields;
		}

		foreach ( $allowed_link_types as $link_type ) {
			$link_details = isset( $all_link_types[ $link_type ] ) ? $all_link_types[ $link_type ] : false;
			if ( empty( $link_details ) ) {
				continue;
			}

			$link_field_name = 'link_' . $link_type;

			$label        = $link_details['label'];
			$before_input = '';
			$after_input  = '';
			if ( isset( $link_details['url_pattern'] ) && ! empty( $link_details['url_pattern'] ) ) {
				$placeholder_pos = strpos( $link_details['url_pattern'], $this->placeholder_field_value );
				if ( $placeholder_pos > 0 ) {
					$before_input = substr( $link_details['url_pattern'], 0, $placeholder_pos );
					if ( ! empty( $before_input ) ) {
						$before_input = '<span class="group-input-prepend"><span class="group-input-text">' . $before_input . '</span></span>';
					}

					$after_input = substr( $link_details['url_pattern'], ( $placeholder_pos + strlen( $this->placeholder_field_value ) ) );
					if ( ! empty( $after_input ) ) {
						$after_input = '<span class="group-input-append"><span class="group-input-text">' . $after_input . '</span></span>';
					}
				}
			}

			$placeholder = esc_html__( 'your-id', 'frontpage-buddy' );
			if ( 'url' === $link_details['value_type'] ) {
				$placeholder = 'https://example.com';
			}

			$fields[ $link_field_name ] = array(
				'type'          => 'text',
				'label'         => $label,
				'value'         => ! empty( $widget->get_data( $link_field_name, 'edit' ) ) ? $widget->get_data( $link_field_name, 'edit' ) : '',
				'wrapper_class' => 'field-link',
				'before'        => '<div class="field-group-input">' . $before_input,
				'after'         => $after_input . '</div>',
				'attributes'    => array(
					'placeholder' => $placeholder,
				),
			);
		}

		return $fields;
	}

	/**
	 * Get the html output for this widget.
	 *
	 * @param \FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 * @return string
	 */
	public function get_output( $widget ) {
		$html = '';

		$all_link_types     = $this->get_link_types();
		$allowed_link_types = $this->get_option( 'allowed_link_types' );
		if ( empty( $allowed_link_types ) ) {
			return '';
		}

		foreach ( $allowed_link_types as $link_type ) {
			$link_details = isset( $all_link_types[ $link_type ] ) ? $all_link_types[ $link_type ] : false;
			if ( empty( $link_details ) ) {
				continue;
			}

			$link_url = $this->sanitize_url( $widget->get_data( 'link_' . $link_type, 'view' ), $link_type, $link_details );
			if ( empty( $link_url ) ) {
				continue;
			}

			$html .= '<span class="mylinks link-' . esc_attr( $link_type ) . '">';
			$html .= sprintf(
				'<span class="link-details"><a href="%s" rel="nofollow noreferrer"><span class="link-label">%s</span><span class="link-link">%s</span></a></span>',
				esc_url( $link_url ),
				esc_html( $link_details['label'] ),
				esc_html( $link_url ),
			);
			$html .= '</span>';
		}

		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start( $widget ) . $html . $this->output_end( $widget ), $this, $widget );
	}

	/**
	 * Sanitize the url for display.
	 *
	 * @param string $raw_string Value as entered by the user.
	 * @param string $link_type Type of the link. Example: 'youtube'.
	 * @param array  $link_details Details of the link type.
	 * @return string
	 */
	public function sanitize_url( $raw_string, $link_type, $link_details ) {
		$formatted = '';
		if ( empty( $raw_string ) ) {
			return '';
		}

		$formatted = trim( $raw_string );
		switch ( $link_details['value_type'] ) {
			case 'url':
				// Add 'https://' ?
				if ( ! empty( $formatted ) ) {
					if ( ! str_starts_with( $formatted, 'https://' ) && ! str_starts_with( $formatted, 'http://' ) ) {
						$formatted = 'https://' . $formatted;
					}
				}

				$formatted = wp_http_validate_url( $formatted );
				break;

			default:
				/**
				 * We are dealing with usernames.
				 *
				 * 1. Remove slashes.
				 * 2. Remove any preceding '@'.
				 * 3. Ensure the remaining string has only the following:
				 *   - alphanumeric characters
				 *   - underscore, dash and dot
				 * 4. Replace the placeholder in url pattern with sanitized username.
				 */
				$formatted = trim( $formatted, '/' );
				if ( str_starts_with( $formatted, '@' ) ) {
					$formatted = substr( $formatted, 1 );
				}

				$formatted = preg_replace( '/[^a-zA-Z0-9._-]/', '', $formatted );
				if ( ! empty( $formatted ) ) {
					if ( isset( $link_details['url_pattern'] ) && ! empty( $link_details['url_pattern'] ) ) {
						$formatted = str_replace( $this->placeholder_field_value, $formatted, $link_details['url_pattern'] );
						$formatted = wp_http_validate_url( $formatted );
					} else {
						$formatted = '';
					}
				}

				break;
		}

		return $formatted;
	}
}
