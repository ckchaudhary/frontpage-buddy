<?php
/**
 * Edit screen manager.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy;

defined( 'ABSPATH' ) || exit;

/**
 *  Edit screen manager.
 */
class Editor {
	use \FrontPageBuddy\TraitSingleton;

	/**
	 * Check if the current user has permissions for current rest request.
	 *
	 * @since 1.0.3
	 *
	 * @param \WP_REST_Request $request Current request.
	 * @return bool
	 */
	public function rest_can_manage( \WP_REST_Request $request ) {
		$object_type = sanitize_text_field( wp_unslash( $request->get_param( 'object_type' ) ) );
		if ( empty( $object_type ) ) {
			return false;
		}

		$object_id = absint( $request->get_param( 'object_id' ) );
		if ( ! $object_id ) {
			return false;
		}

		$integration = frontpage_buddy()->get_integration( $object_type );
		if ( ! $integration ) {
			return false;
		}

		return $integration->can_manage( $object_id );
	}

	/**
	 * Handle request to get whether an object has a custom front page or not.
	 *
	 * @since 1.0.3
	 *
	 * @param \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response
	 */
	public function rest_get_status( \WP_REST_Request $request ) {
		$object_type = sanitize_text_field( wp_unslash( $request->get_param( 'object_type' ) ) );
		$object_id   = absint( $request->get_param( 'object_id' ) );
		$integration = frontpage_buddy()->get_integration( $object_type );
		if ( empty( $integration ) ) {
			return rest_send_error( __( 'Invalid request.', 'frontpage-buddy' ), 400 );
		}
		return rest_send_response( true, $integration->has_custom_front_page( $object_id ) );
	}

	/**
	 * Handle request to set whether an object has a custom front page or not.
	 *
	 * @since 1.0.3
	 *
	 * @param \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response
	 */
	public function rest_update_status( \WP_REST_Request $request ) {
		$object_type    = sanitize_text_field( wp_unslash( $request->get_param( 'object_type' ) ) );
		$object_id      = absint( $request->get_param( 'object_id' ) );
		$integration    = frontpage_buddy()->get_integration( $object_type );
		$updated_status = sanitize_text_field( wp_unslash( $request->get_param( 'updated_status' ) ) );
		$updated_status = 'yes' === $updated_status ? 'yes' : 'no';

		return rest_send_response( true, $integration->has_custom_front_page( $object_id, $updated_status ) );
	}

	/**
	 * Handle request to get the frontpage layout of given object.
	 *
	 * @since 1.0.3
	 *
	 * @param \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response
	 */
	public function rest_get_layout( \WP_REST_Request $request ) {
		$object_type = sanitize_text_field( wp_unslash( $request->get_param( 'object_type' ) ) );
		$object_id   = absint( $request->get_param( 'object_id' ) );
		$integration = frontpage_buddy()->get_integration( $object_type );
		return rest_send_response( true, $integration->get_frontpage_layout( $object_id ) );
	}

	/**
	 * Handle request to update the frontpage layout of given object.
	 *
	 * @since 1.0.3
	 *
	 * @param \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response
	 */
	public function rest_update_layout( \WP_REST_Request $request ) {
		$object_type = sanitize_text_field( wp_unslash( $request->get_param( 'object_type' ) ) );
		$object_id   = absint( $request->get_param( 'object_id' ) );
		$integration = frontpage_buddy()->get_integration( $object_type );
		$layout      = wp_unslash( $request->get_param( 'layout' ) );
		if ( ! empty( $layout ) ) {
			$layout = map_deep( $layout, 'sanitize_text_field' );
		}

		$integration->update_frontpage_layout( $object_id, $layout );

		// Remove discarded widgets.
		$all_added = $integration->get_added_widgets( $object_id );
		if ( ! empty( $all_added ) ) {
			$temp = array();
			foreach ( $all_added as $old_widget ) {
				$found = false;
				foreach ( $layout as $row ) {
					foreach ( $row as $new_widget_id ) {
						if ( $new_widget_id === $old_widget['id'] ) {
							$found = true;
							break 2;
						}
					}
				}

				if ( $found ) {
					$temp[] = $old_widget;
				}
			}

			$integration->update_added_widgets( $object_id, $temp );
		}

		return rest_send_response( true, $integration->get_frontpage_layout( $object_id ) );
	}

	/**
	 * Handle request to get the html for widget options.
	 *
	 * @since 1.0.3
	 *
	 * @param \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response
	 */
	public function rest_widget_opts_get( \WP_REST_Request $request ) {
		$object_type = sanitize_text_field( wp_unslash( $request->get_param( 'object_type' ) ) );
		$object_id   = absint( $request->get_param( 'object_id' ) );
		$widget_type = sanitize_text_field( wp_unslash( $request->get_param( 'widget_type' ) ) );
		$widget_id   = sanitize_text_field( wp_unslash( $request->get_param( 'widget_id' ) ) );

		if ( empty( $widget_type ) || empty( $widget_id ) ) {
			return rest_send_error( __( 'Invalid request.', 'frontpage-buddy' ) );
		}

		$integration = frontpage_buddy()->get_integration( $object_type );

		$widget_type_obj = frontpage_buddy()->get_widget_type( $widget_type );
		if ( ! $widget_type_obj->is_enabled_for( $object_type, $object_id ) ) {
			return rest_send_error( __( 'Widget not available.', 'frontpage-buddy' ) );
		}

		$prev_saved_data = array();
		$saved_widgets   = $integration->get_added_widgets( $object_id );
		if ( ! empty( $saved_widgets ) ) {
			foreach ( $saved_widgets as $saved_widget ) {
				if ( $saved_widget['id'] === $widget_id ) {
					if ( ! isset( $saved_widget['data'] ) && isset( $saved_widget['options'] ) ) {
						$saved_widget['data'] = $saved_widget['options'];
					}
					$prev_saved_data = $saved_widget['data'];
				}
			}
		}

		$widget_obj = $widget_type_obj->get_widget(
			array(
				'id'          => $widget_id,
				'object_type' => $object_type,
				'object_id'   => $object_id,
				'data'        => $prev_saved_data,
			)
		);

		if ( ! $widget_obj ) {
			return rest_send_error( __( 'Invalid request.', 'frontpage-buddy' ) );
		}

		ob_start();
		$widget_type_obj->widget_input_ui( $widget_obj );
		$html = ob_get_clean();
		return rest_send_response( true, array( 'html' => $html ) );
	}

	/**
	 * Handle request to update widget data/options.
	 *
	 * @since 1.0.3
	 *
	 * @param \WP_REST_Request $request Current request.
	 * @return \WP_REST_Response
	 */
	public function rest_widget_opts_update( \WP_REST_Request $request ) {
		$object_type = sanitize_text_field( wp_unslash( $request->get_param( 'object_type' ) ) );
		$object_id   = absint( $request->get_param( 'object_id' ) );
		$widget_type = sanitize_text_field( wp_unslash( $request->get_param( 'widget_type' ) ) );
		$widget_id   = sanitize_text_field( wp_unslash( $request->get_param( 'widget_id' ) ) );
		if ( empty( $widget_type ) || empty( $widget_id ) ) {
			return rest_send_error( __( 'Invalid request.', 'frontpage-buddy' ) );
		}

		$integration = frontpage_buddy()->get_integration( $object_type );

		$widget_type_obj = frontpage_buddy()->get_widget_type( $widget_type );
		if ( ! $widget_type_obj->is_enabled_for( $object_type, $object_id ) ) {
			return rest_send_error( __( 'Widget not available.', 'frontpage-buddy' ), 500 );
		}

		$widget_obj = $widget_type_obj->get_widget(
			array(
				'id'          => $widget_id,
				'object_type' => $object_type,
				'object_id'   => $object_id,
			)
		);

		if ( ! $widget_obj ) {
			return rest_send_error( __( 'Invalid request.', 'frontpage-buddy' ) );
		}

		$new_data      = array();
		$widget_fields = $widget_type_obj->get_data_fields( $widget_obj );
		if ( ! empty( $widget_fields ) ) {
			foreach ( $widget_fields as $widget_field_name => $widget_field_attr ) {
				$field_value = $request->get_param( $widget_field_name );
				if ( isset( $field_value ) ) {
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					$field_value = $widget_type_obj->sanitize_field_value_for_db( $widget_field_name, $field_value, $widget_field_attr );
				}

				$new_data[ $widget_field_name ] = $field_value;
			}
		}

		$update_status = $widget_obj->update( $new_data );
		if ( ! $update_status['status'] ) {
			// validation erorrs!
			return rest_send_error( $update_status['message'] );
		}

		$widget_data_new = array(
			'id'           => $widget_obj->id,
			'type'         => $widget_obj->type,
			'last_updated' => time(),
			'data'         => $widget_obj->get_all_data(),
		);

		$existing            = false;
		$saved_widgets       = $integration->get_added_widgets( $object_id );
		$saved_widgets_count = count( $saved_widgets );
		if ( $saved_widgets_count > 0 ) {
			for ( $i = 0; $i < $saved_widgets_count; $i++ ) {
				$saved_widget = $saved_widgets[ $i ];
				if ( $saved_widget['id'] === $widget_data_new['id'] ) {
					$saved_widgets[ $i ] = $widget_data_new;
					$existing            = true;
					break;
				}
			}
		}

		if ( ! $existing ) {
			$saved_widgets[] = $widget_data_new;
		}

		$integration->update_added_widgets( $object_id, $saved_widgets );
		return rest_send_response( true, null, __( 'Updated', 'frontpage-buddy' ) );
	}
}
