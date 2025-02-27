<?php
/**
 * Define REST API endpoints etc..
 *
 * @package FrontPage Buddy
 * @since 1.0.3
 */

namespace FrontPageBuddy;

defined( 'ABSPATH' ) || exit;

define( 'FRONTPAGE_BUDDY_REST_ROUTE_NAMESPACE', 'frontpage-buddy/v1' );

/**
 * Initialize REST API.
 *
 * @return void
 */
function on_rest_api_init() {
	$editor = Editor::get_instance();
	register_rest_route(
		FRONTPAGE_BUDDY_REST_ROUTE_NAMESPACE,
		'/status',
		array(
			array(
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => array( $editor, 'rest_get_status' ),
				'permission_callback' => '__return_true',
			),
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $editor, 'rest_update_status' ),
				'permission_callback' => array( $editor, 'rest_can_manage' ),
			),
		),
	);

	register_rest_route(
		FRONTPAGE_BUDDY_REST_ROUTE_NAMESPACE,
		'/layout',
		array(
			array(
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => array( $editor, 'rest_get_layout' ),
				'permission_callback' => '__return_true',
			),
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $editor, 'rest_update_layout' ),
				'permission_callback' => array( $editor, 'rest_can_manage' ),
			),
		),
	);

	register_rest_route(
		FRONTPAGE_BUDDY_REST_ROUTE_NAMESPACE,
		'/widget-opts',
		array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $editor, 'rest_widget_opts_get' ),
				'permission_callback' => array( $editor, 'rest_can_manage' ),
			),
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $editor, 'rest_widget_opts_update' ),
				'permission_callback' => array( $editor, 'rest_can_manage' ),
			),
		),
	);

	do_action( 'frontpage_buddy_rest_api_init', FRONTPAGE_BUDDY_REST_ROUTE_NAMESPACE );
}

add_action( 'rest_api_init', '\FrontPageBuddy\on_rest_api_init' );

/**
 * Standard success response.
 *
 * @param boolean $success Whether the request was successful.
 * @param mixed   $data Data to return.
 * @param string  $message Message to return.
 * @param integer $status HTTP status code. Default 200.
 * @return \WP_REST_Response
 */
function rest_send_response( $success = true, $data = null, $message = '', $status = 200 ) {
	$response = array(
		'success' => $success,
		'message' => $message,
		'data'    => $data,
	);

	return new \WP_REST_Response( $response, $status );
}

/**
 * Standard error response.
 *
 * @param string  $message Message to return.
 * @param integer $status HTTP status code. Default 400.
 * @param array   $additional_data Additional data to return.
 * @return \WP_REST_Response
 */
function rest_send_error( $message, $status = 400, $additional_data = array() ) {
	$data = array_merge(
		array(
			'success' => false,
			'message' => $message,
		),
		$additional_data
	);

	return new \WP_REST_Response( $data, $status );
}
