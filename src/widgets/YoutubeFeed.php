<?php
/**
 * Instagram feed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed twitter feed.
 */
class InstagramFeed extends Widget {
	public function __construct( $args = '' ) {
		$this->type           = 'instagramfeed';
		$this->name           = 'Instagram Feed';
		$this->description    = 'Display your instagram feed.';
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-x-feed.png';

		$this->setup( $args );
	}
}
