<?php
/**
 * Plugin Name: FrontPage Buddy
 * Plugin URI: https://blogs.recycleb.in/?p=47
 * Description: Allow group admins and members to add a custom front page to the group and profile. Which can then be customized by adding text, images, embedding videos, twitter/facebook feed widgets etc.
 * Version: 1.0.0
 * Author: ckchaudhary
 * Author URI: https://www.recycleb.in/u/chandan/
 * Text Domain: fontpage-buddy
 * Domain Path: /languages
 *
 * @package FrontPage Buddy
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) ? '' : exit();

require __DIR__ . '/vendor/autoload.php';

// Directory.
if ( ! defined( 'FPBUDDY_PLUGIN_DIR' ) ) {
	define( 'FPBUDDY_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url.
if ( ! defined( 'FPBUDDY_PLUGIN_URL' ) ) {
	$plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );

	// If we're using https, update the protocol.
	if ( is_ssl() ) {
		$plugin_url = str_replace( 'http://', 'https://', $plugin_url );
	}

	define( 'FPBUDDY_PLUGIN_URL', $plugin_url );
}

if ( ! defined( 'FPBUDDY_PLUGIN_VERSION' ) ) {
	define( 'FPBUDDY_PLUGIN_VERSION', '1.0.0' );
}

/**
 * Returns the main plugin object.
 *
 * @since 1.0.0
 *
 * @return \RecycleBin\FrontPageBuddy\Plugin
 */
function frontpage_buddy() {
	return \RecycleBin\FrontPageBuddy\Plugin::get_instance();
}

// Instantiate the main plugin object.
\add_action( 'plugins_loaded', 'frontpage_buddy' );
