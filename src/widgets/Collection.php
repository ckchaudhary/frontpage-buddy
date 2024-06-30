<?php
/**
 * Widget Collection.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Widget Collections, to add settings screen, etc.
 */
class Collection {
	use \RecycleBin\FrontPageBuddy\TraitSingleton;

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
	 * Get the list of registered widgets.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_registered_widgets() {
		if ( ! empty( $this->widgets ) ) {
			return $this->widgets;
		}

		$this->widgets = apply_filters(
			'frontpage_buddy_registered_widgets',
			array(
				'richcontent'           => '\RecycleBin\FrontPageBuddy\Widgets\RichContent',
				'instagramprofileembed' => '\RecycleBin\FrontPageBuddy\Widgets\InstagramProfile',
				'facebookpageembed'     => '\RecycleBin\FrontPageBuddy\Widgets\FacebookPage',
			)
		);

		return $this->widgets;
	}

	/**
	 * Get the list of available widgets for given object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $object_type e.g: 'bp_groups' or 'bp_members'.
	 * @param int    $object_id group id or member id.
	 *
	 * @return array
	 */
	public function get_available_widgets( $object_type, $object_id ) {
		if ( empty( $object_id ) || empty( $object_type ) ) {
			return array();
		}

		$avl_widgets = array();

		$registered_widgets = $this->get_registered_widgets();
		if ( ! empty( $registered_widgets ) ) {
			foreach ( $registered_widgets as $widget_type => $widget_class ) {
				if ( ! class_exists( $widget_class ) ) {
					continue;
				}

				if ( $this->is_widget_enabled_for( $widget_type, $object_type, $object_id ) ) {
					$avl_widgets[] = new $widget_class();
				}
			}
		}

		return $avl_widgets;
	}

	/**
	 * Get settings for a given widget type.
	 *
	 * @param string $widget_type self explanatory.
	 * @return array
	 */
	public function get_widget_settings( $widget_type ) {
		$all_widget_settings = frontpage_buddy()->option( 'widget_settings' );
		return isset( $all_widget_settings[ $widget_type ] ) && ! empty( $all_widget_settings[ $widget_type ] ) ? $all_widget_settings[ $widget_type ] : array();
	}

	/**
	 * Is the given widget type enabled for given integration?
	 *
	 * @since 1.0.0
	 *
	 * @param string $widget_type E.g: 'richcontent'.
	 * @param string $integration_type E.g: 'bp_groups'.
	 * @param int    $target_id E.g: group id or member id.
	 * @return boolean
	 */
	public function is_widget_enabled_for( $widget_type, $integration_type, $target_id ) {
		$is_enabled = false;
		$widget_settings = $this->get_widget_settings( $widget_type );
		if ( isset( $widget_settings['enabled_for'] ) && in_array( $integration_type, $widget_settings['enabled_for'], true ) ) {
			$is_enabled = true;
		}

		return apply_filters( 'frontpage_buddy_is_widget_enabled_for', $is_enabled, $widget_type, $integration_type, $target_id );
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
		$updated_status = 'yes' == $updated_status ? 'yes' : 'no';
		$object_type = isset( $_POST['object_type'] ) && ! empty( $_POST['object_type'] ) ? sanitize_text_field( wp_unslash( $_POST['object_type'] ) ) : '';
		$object_id = isset( $_POST['object_id'] ) && ! empty( $_POST['object_id'] ) ? absint( $_POST['object_id'] ) : 0;

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
		$object_id = isset( $_POST['object_id'] ) && ! empty( $_POST['object_id'] ) ? absint( $_POST['object_id'] ) : 0;
		$layout_raw = isset( $_POST['layout'] ) && ! empty( $_POST['layout'] ) ? $_POST['layout'] : '';
		$layout_sanitized = array();
		if ( ! empty( $layout_raw ) ) {
			foreach ( $layout_raw as $row ) {
				$row_items = count( $row );
				for ( $i = 0; $i < $row_items; $i++ ) {
					if ( ! empty( $row[ $i ] ) ) {
						$row[ $i ] = sanitize_text_field( wp_unslash( $row[ $i ] ) );
					}
				}
				$layout_sanitized[] = $row;
			}
		}

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

		$integration->update_frontpage_layout( $object_id, $layout_sanitized );

		// Remove discarded widgets.
		$all_added = $integration->get_added_widgets( $object_id );
		if ( ! empty( $all_added ) ) {
			$temp = array();
			foreach ( $all_added as $old_widget ) {
				$found = false;
				foreach ( $layout_sanitized as $row ) {
					foreach ( $row as $new_widget_id ) {
						if ( $new_widget_id === $old_widget[ 'id' ] ) {
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

		if ( ! $this->is_widget_enabled_for( $widget_type, $object_type, $object_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Widget not available', 'frontpage-buddy' ) ) );
		}

		$prev_saved_options = array();
		$saved_widgets = $integration->get_added_widgets( $object_id );
		if ( ! empty( $saved_widgets ) ) {
			foreach ( $saved_widgets as $saved_widget ) {
				if ( $saved_widget['id'] === $widget_id ) {
					$prev_saved_options = $saved_widget['options'];
				}
			}
		}

		$registered_widgets = $this->get_registered_widgets();
		$widget_obj = false;
		$widget_class = isset( $registered_widgets[ $widget_type ] ) && ! empty( $registered_widgets[ $widget_type ] ) ? $registered_widgets[ $widget_type ] : false;
		if ( $widget_class && class_exists( $widget_class ) ) {
			$widget_obj = new $widget_class(
				array(
					'id'          => $widget_id,
					'object_type' => $object_type,
					'object_id'   => $object_id,
					'options'     => $prev_saved_options,
				)
			);
		}

		if ( ! $widget_obj ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request!', 'frontpage-buddy' ) ) );
		}

		ob_start();
		$widget_obj->settings_screen( array( 'state' => 'expanded' ) );
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
			wp_send_json_error( array( 'message' => __( 'Invalid request!0', 'frontpage-buddy' ) ) );
		}

		$integration = frontpage_buddy()->get_integration( $object_type );
		if ( ! $integration ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request1!', 'frontpage-buddy' ) ) );
		}

		$can_manage = $integration->can_manage( $object_id );
		if ( ! $can_manage ) {
			wp_send_json_error( array( 'message' => __( 'Access denied!', 'frontpage-buddy' ) ) );
		}

		if ( ! $this->is_widget_enabled_for( $widget_type, $object_type, $object_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Widget not available', 'frontpage-buddy' ) ) );
		}

		$registered_widgets = $this->get_registered_widgets();
		$widget_obj = false;
		$widget_class = isset( $registered_widgets[ $widget_type ] ) && ! empty( $registered_widgets[ $widget_type ] ) ? $registered_widgets[ $widget_type ] : false;
		if ( $widget_class && class_exists( $widget_class ) ) {
			$widget_obj = new $widget_class(
				array(
					'id'          => $widget_id,
					'object_type' => $object_type,
					'object_id'   => $object_id,
				)
			);
		}

		if ( ! $widget_obj ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request1!', 'frontpage-buddy' ) ) );
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
			'options'      => $widget_obj->options,
		);

		$existing = false;
		$saved_widgets = $integration->get_added_widgets( $object_id );
		$saved_widgets_count = count( $saved_widgets );
		if ( $saved_widgets_count > 0 ) {
			for ( $i = 0; $i < $saved_widgets_count; $i++ ) {
				$saved_widget = $saved_widgets[ $i ];
				if ( $saved_widget['id'] === $widget_data_new['id'] ) {
					$saved_widgets[ $i ] = $widget_data_new;
					$existing = true;
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

	/**
	 * Output
	 *
	 * @param [type] $object_id
	 * @param [type] $object_type
	 * @return void
	 */
	public function print_widgets_output( $object_id, $object_type ) {
		if ( empty( $object_id ) || empty( $object_type ) ) {
			return;
		}
	}
}
