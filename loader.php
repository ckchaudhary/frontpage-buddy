<?php
/**
 * Plugin Name: FrontPage Buddy
 * Plugin URI: https://www.recycleb.in/frontpage-buddy/
 * Description: Personalized front pages for buddypress & buddyboss members & groups, bbpress profiles and 'Ultimate Member' profiles.
 * Version: 1.0.2
 * Author: ckchaudhary
 * Author URI: https://www.recycleb.in/u/chandan/
 * Text Domain: frontpage-buddy
 * Domain Path: /languages
 *
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package FrontPage Buddy
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

require __DIR__ . '/vendor/autoload.php';

// Directory.
if ( ! defined( 'FRONTPAGE_BUDDY_PLUGIN_DIR' ) ) {
	define( 'FRONTPAGE_BUDDY_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url.
if ( ! defined( 'FRONTPAGE_BUDDY_PLUGIN_URL' ) ) {
	$plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );

	// If we're using https, update the protocol.
	if ( is_ssl() ) {
		$plugin_url = str_replace( 'http://', 'https://', $plugin_url );
	}

	define( 'FRONTPAGE_BUDDY_PLUGIN_URL', $plugin_url );
}

if ( ! defined( 'FRONTPAGE_BUDDY_PLUGIN_VERSION' ) ) {
	define( 'FRONTPAGE_BUDDY_PLUGIN_VERSION', '1.0.2' );
}

/**
 * Returns the main plugin object.
 *
 * @since 1.0.0
 *
 * @return \FrontPageBuddy\Plugin
 */
function frontpage_buddy() {
	return \FrontPageBuddy\Plugin::get_instance();
}

// Instantiate the main plugin object.
\add_action( 'plugins_loaded', 'frontpage_buddy' );
