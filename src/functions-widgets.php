<?php
/**
 * Utility functions related to custom front page and widgets.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy;

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

	$registered_widgets = frontpage_buddy()->widget_collection()->get_registered_widgets();

	if ( ! empty( $layout ) ) {
		foreach ( $layout as $layout_row ) {
			$row = array();

			foreach ( $layout_row as $widget_id ) {
				$found = false;
				$widget_id = trim( $widget_id );
				if ( ! empty( $widgets ) ) {
					foreach ( $widgets as $widget ) {
						if ( $widget['id'] === $widget_id ) {
							$found = $widget;
							break;
						}
					}
				}

				if ( $found && ! frontpage_buddy()->widget_collection()->is_widget_enabled_for( $widget['type'], $integration_type, $target_id ) ) {
					$found = false;
				}

				if ( $found ) {
					$widget_obj = false;
					$widget_class = isset( $registered_widgets[ $widget['type'] ] ) && ! empty( $registered_widgets[ $widget['type'] ] ) ? $registered_widgets[ $widget['type'] ] : false;
					if ( $widget_class && class_exists( $widget_class ) ) {
						$widget_obj = new $widget_class(
							$widget['type'],
							array(
								'id'          => $widget['id'],
								'object_type' => $integration_type,
								'object_id'   => $target_id,
								'options'     => $widget['options'],
							)
						);

						$widget_output = $widget_obj->get_output();
						if ( ! empty( $widget_output ) ) {
							$row[] = $widget_output;
						} else {
							$html .= 'one';
						}
					}
				}
			}

			if ( ! empty( $row ) ) {
				$col_count = count( $row );
				$html .= sprintf( "<div class='fpbuddy-widget-row has-%d-fpcols'>", $col_count );

				for ( $i = 0; $i < $col_count; $i++ ) {
					$this_col_num = $i + 1;
					$html .= sprintf( "<div class='fp-col fp-col-%d-of-%d'><div class='fp-col-contents'>%s</div></div>", $this_col_num, $col_count, stripslashes( $row[ $i ] ) );
				}

				$html .= '</div>';
			}
		}
	}

	return $html;
}

/**
 * Get the list of html tags( and their attributes ) allowed.
 * This is used to sanitize the contents of richcontent widget.
 *
 * @since 1.0.0
 * @return array
 */
function visual_editor_allowed_html_tags() {
	return apply_filters(
		'fronpage_buddy_visual_editor_allowed_html_tags',
		array(
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'p' => array(),
			'br' => array(),
			'em' => array(),
			'strong' => array(),
			'del' => array(),
			'a' => array(
				'href'  => array(),
				'title' => array(),
			),
			'img' => array(
				'src' => array(),
				'alt' => array(),
			),
			'ul' => array(),
			'ol' => array(),
			'hr' => array(),
		)
	);
}

add_filter( 'frontpage_buddy_widget_title_for_manage_screen', '\RecycleBin\FrontPageBuddy\widget_title_for_manage_screen', 10, 2 );
/**
 * Filters the title for a widget when displayed on manage widgets screens.
 *
 * @param  string $title Existing value, if any.
 * @param  array  $widget Widget details like 'type', 'options' etc.
 * @return string
 */
function widget_title_for_manage_screen( $title, $widget ) {
	$widget_type = isset( $widget['type'] ) ? $widget['type'] : '';
	switch ( $widget_type ) {
		case 'richcontent':
			$content = isset( $widget['options'] ) && ! empty( $widget['options'] ) && isset( $widget['options']['content'] ) && ! empty( $widget['options']['content'] ) ? wp_strip_all_tags( $widget['options']['content'] ) : '';
			$title   = substr( $content, 0, 100 );
			break;

		case 'instagramprofileembed':
			$content = isset( $widget['options'] ) && ! empty( $widget['options'] ) && isset( $widget['options']['insta_id'] ) && ! empty( $widget['options']['insta_id'] ) ? wp_strip_all_tags( $widget['options']['insta_id'] ) : '';
			if ( $content ) {
				$content = trim( $content, ' @' );
				$title   = '@' . $content . ' - instagram';
			}
			break;

		case 'twitterprofile':
			$content = isset( $widget['options'] ) && ! empty( $widget['options'] ) && isset( $widget['options']['username'] ) && ! empty( $widget['options']['username'] ) ? wp_strip_all_tags( $widget['options']['username'] ) : '';
			if ( $content ) {
				$content = trim( $content, ' @' );
				$title   = '@' . $content . ' - X';
			}
			break;
	}
	return $title;
}
