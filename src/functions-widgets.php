<?php
/**
 * Utility functions related to custom front page and widgets.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy;

defined( 'ABSPATH' ) ? '' : exit();

/**
 * Show/Print the output for custom front page.
 *
 * @param array  $layout Rows and columns.
 * @param array  $widgets All added widgets.
 * @param string $integration_type E.g: bp_groups.
 * @param mixed  $target_id E.g: group id.
 * @return void
 */
function show_output( $layout, $widgets, $integration_type, $target_id ) {
	// The output of each individual widget is validated and escaped by those widgets individually.
	// phpcs:ignore WordPress.Security.EscapeOutput
	echo get_output( $layout, $widgets, $integration_type, $target_id );
}

/**
 * Get the output for custom front page.
 *
 * @param array  $layout Rows and columns.
 * @param array  $widgets All added widgets.
 * @param string $integration_type E.g: bp_groups.
 * @param mixed  $target_id E.g: group id.
 * @return string html
 */
function get_output( $layout, $widgets, $integration_type, $target_id ) {
	$html = '';

	if ( ! empty( $layout ) ) {
		foreach ( $layout as $layout_row ) {
			$row = array();

			foreach ( $layout_row as $widget_id ) {
				$widget_type_obj = false;
				$found           = false;
				$widget_id       = trim( $widget_id );
				if ( ! empty( $widgets ) ) {
					foreach ( $widgets as $widget ) {
						if ( $widget['id'] === $widget_id ) {
							$found = $widget;
							break;
						}
					}
				}

				if ( $found ) {
					$widget_type_obj = frontpage_buddy()->get_widget_type( $found['type'] );
					if ( is_null( $widget_type_obj ) || ! $widget_type_obj->is_enabled_for( $integration_type ) ) {
						$found = false;
					}
				}

				if ( $found ) {
					$widget_obj = $widget_type_obj->get_widget(
						array(
							'id'          => $widget['id'],
							'object_type' => $integration_type,
							'object_id'   => $target_id,
							'data'        => $widget['data'],
						)
					);

					// This is already escaped and sanitized.
					$widget_output = $widget_type_obj->get_output( $widget_obj );
					if ( ! empty( $widget_output ) ) {
						$row[] = $widget_output;
					}
				}
			}

			if ( ! empty( $row ) ) {
				$col_count = count( $row );
				$html     .= sprintf( "<div class='fpbuddy-widget-row has-%d-fpcols'>", $col_count );

				for ( $i = 0; $i < $col_count; $i++ ) {
					$this_col_num = $i + 1;
					$html        .= sprintf( "<div class='fp-col fp-col-%d-of-%d'><div class='fp-col-contents'>%s</div></div>", $this_col_num, $col_count, $row[ $i ] );
				}

				$html .= '</div>';
			}
		}
	}

	return $html;
}

add_filter( 'frontpage_buddy_widget_title_for_manage_screen', '\RB\FrontPageBuddy\widget_title_for_manage_screen', 10, 2 );
/**
 * Filters the title for a widget when displayed on manage widgets screens.
 *
 * @param  string $title Existing value, if any.
 * @param  array  $widget Widget details like 'type', 'options' etc.
 * @return string
 */
function widget_title_for_manage_screen( $title, $widget ) {
	$title = isset( $widget['data'] ) && ! empty( $widget['data'] ) && isset( $widget['data']['heading'] ) && ! empty( $widget['data']['heading'] ) ? wp_strip_all_tags( $widget['data']['heading'] ) : '';
	if ( ! empty( $title ) ) {
		$title = substr( $title, 0, 100 );
	} else {
		$widget_type = isset( $widget['type'] ) ? $widget['type'] : '';
		switch ( $widget_type ) {
			case 'richcontent':
				$content = isset( $widget['data'] ) && ! empty( $widget['data'] ) && isset( $widget['data']['content'] ) && ! empty( $widget['data']['content'] ) ? wp_strip_all_tags( $widget['data']['content'] ) : '';
				$title   = substr( $content, 0, 100 );
				break;

			case 'instagramprofile':
				$content = isset( $widget['data'] ) && ! empty( $widget['data'] ) && isset( $widget['data']['insta_id'] ) && ! empty( $widget['data']['insta_id'] ) ? wp_strip_all_tags( $widget['data']['insta_id'] ) : '';
				if ( $content ) {
					$content = trim( $content, ' @' );
					$title   = '@' . $content . ' - instagram';
				}
				break;

			case 'twitterprofile':
				$content = isset( $widget['data'] ) && ! empty( $widget['data'] ) && isset( $widget['data']['username'] ) && ! empty( $widget['data']['username'] ) ? wp_strip_all_tags( $widget['data']['username'] ) : '';
				if ( $content ) {
					$content = trim( $content, ' @' );
					$title   = '@' . $content . ' - X';
				}
				break;
		}
	}
	return $title;
}
