<?php
/**
 * Edit screen manager.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Edit screen manager.
 */
class Editor {
	use \RB\FrontPageBuddy\TraitSingleton;

	/**
	 * The array of all registered widgets.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $widgets = array();

	/**
	 * Setup everything.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'wp_ajax_frontpage_buddy_change_status', array( $this, 'ajax_change_status' ) );
		add_action( 'wp_ajax_frontpage_buddy_update_layout', array( $this, 'ajax_update_layout' ) );
		add_action( 'wp_ajax_frontpage_buddy_widget_opts_get', array( $this, 'ajax_widget_opts_get' ) );
		add_action( 'wp_ajax_frontpage_buddy_widget_opts_update', array( $this, 'ajax_widget_opts_update' ) );
	}

	/**
	 * Handle ajax request to set whether an object has a custom front page or not.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_change_status() {
		check_ajax_referer( 'frontpage_buddy_change_status' );

		$updated_status = isset( $_POST['updated_status'] ) && ! empty( $_POST['updated_status'] ) ? sanitize_text_field( wp_unslash( $_POST['updated_status'] ) ) : '';
		$updated_status = 'yes' === $updated_status ? 'yes' : 'no';
		$object_type    = isset( $_POST['object_type'] ) && ! empty( $_POST['object_type'] ) ? sanitize_text_field( wp_unslash( $_POST['object_type'] ) ) : '';
		$object_id      = isset( $_POST['object_id'] ) && ! empty( $_POST['object_id'] ) ? absint( $_POST['object_id'] ) : 0;

		if ( empty( $object_id ) || empty( $object_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		$integration = frontpage_buddy()->get_integration( $object_type );
		if ( ! $integration ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		$can_manage = $integration->can_manage( $object_id );
		if ( ! $can_manage ) {
			wp_send_json_error( array( 'message' => __( 'Access denied!', 'frontpage-buddy' ) ) );
		}

		$integration->has_custom_front_page( $object_id, $updated_status );

		wp_send_json_success();
	}

	/**
	 * Handle ajax request to update the layout of the custom front page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_update_layout() {
		check_ajax_referer( 'frontpage_buddy_update_layout' );

		$object_type = isset( $_POST['object_type'] ) && ! empty( $_POST['object_type'] ) ? sanitize_text_field( wp_unslash( $_POST['object_type'] ) ) : '';
		$object_id   = isset( $_POST['object_id'] ) && ! empty( $_POST['object_id'] ) ? absint( $_POST['object_id'] ) : 0;
		$layout      = isset( $_POST['layout'] ) && ! empty( $_POST['layout'] ) ? map_deep( wp_unslash( $_POST['layout'] ), 'sanitize_text_field' ) : '';

		if ( empty( $object_id ) || empty( $object_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Error', 'frontpage-buddy' ) ) );
		}

		$integration = frontpage_buddy()->get_integration( $object_type );

		if ( ! $integration ) {
			wp_send_json_error( array( 'message' => __( 'Error', 'frontpage-buddy' ) ) );
		}

		$can_manage = $integration->can_manage( $object_id );

		if ( ! $can_manage ) {
			wp_send_json_error( array( 'message' => __( 'Error', 'frontpage-buddy' ) ) );
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

		wp_send_json_success();
	}

	/**
	 * Handle ajax request to get settings form for a given widget.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_widget_opts_get() {
		check_ajax_referer( 'frontpage_buddy_widget_opts_get' );

		$widget_type = isset( $_REQUEST['widget_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['widget_type'] ) ) : '';
		$widget_id   = isset( $_REQUEST['widget_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['widget_id'] ) ) : '';
		$object_type = isset( $_REQUEST['object_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['object_type'] ) ) : '';
		$object_id   = isset( $_REQUEST['object_id'] ) ? absint( wp_unslash( $_REQUEST['object_id'] ) ) : 0;
		if ( empty( $widget_type ) || empty( $object_type ) || empty( $object_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		$integration = frontpage_buddy()->get_integration( $object_type );
		if ( ! $integration ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		$can_manage = $integration->can_manage( $object_id );
		if ( ! $can_manage ) {
			wp_send_json_error( array( 'message' => __( 'Access denied!', 'frontpage-buddy' ) ) );
		}

		$widget_type_obj = frontpage_buddy()->get_widget_type( $widget_type );
		if ( ! $widget_type_obj->is_enabled_for( $object_type, $object_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Widget not available', 'frontpage-buddy' ) ) );
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
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		ob_start();
		$widget_type_obj->widget_input_ui( $widget_obj );
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Handle ajax request to update settings for a given widget.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_widget_opts_update() {
		check_ajax_referer( 'frontpage_buddy_widget_opts_update' );

		$widget_type = isset( $_REQUEST['widget_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['widget_type'] ) ) : '';
		$widget_id   = isset( $_REQUEST['widget_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['widget_id'] ) ) : '';
		$object_type = isset( $_REQUEST['object_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['object_type'] ) ) : '';
		$object_id   = isset( $_REQUEST['object_id'] ) ? absint( wp_unslash( $_REQUEST['object_id'] ) ) : 0;
		if ( empty( $widget_type ) || empty( $widget_id ) || empty( $object_type ) || empty( $object_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		$integration = frontpage_buddy()->get_integration( $object_type );
		if ( ! $integration ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		$can_manage = $integration->can_manage( $object_id );
		if ( ! $can_manage ) {
			wp_send_json_error( array( 'message' => __( 'Access denied!', 'frontpage-buddy' ) ) );
		}

		$widget_type_obj = frontpage_buddy()->get_widget_type( $widget_type );
		if ( ! $widget_type_obj->is_enabled_for( $object_type, $object_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Widget not available', 'frontpage-buddy' ) ) );
		}

		$widget_obj = $widget_type_obj->get_widget(
			array(
				'id'          => $widget_id,
				'object_type' => $object_type,
				'object_id'   => $object_id,
			)
		);

		if ( ! $widget_obj ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		$update_status = $widget_obj->update();
		if ( ! $update_status['status'] ) {
			// validation erorrs!
			wp_send_json_error( array( 'message' => $update_status['message'] ) );
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
		wp_send_json_success( array( 'message' => __( 'Updated', 'frontpage-buddy' ) ) );
	}
}
