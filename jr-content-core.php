<?php
/**
 * Plugin Name: jr-content-core
 * Plugin URI: https://www.jaidynreiman.net
 * Description: Core content registration and shared content helpers for the JaidynReiman site stack.
 * Version: 0.1.0
 * Requires PHP: 7.4
 * Author: JaidynReiman
 * Author URI: https://www.jaidynreiman.net
 * Text Domain: jr-content-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JR_CONTENT_CORE_VERSION', '0.1.0' );
define( 'JR_CONTENT_CORE_FILE', __FILE__ );
define( 'JR_CONTENT_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'JR_CONTENT_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once JR_CONTENT_CORE_PATH . 'includes/helpers.php';
require_once JR_CONTENT_CORE_PATH . 'includes/post-types.php';
require_once JR_CONTENT_CORE_PATH . 'includes/taxonomies.php';
require_once JR_CONTENT_CORE_PATH . 'includes/meta.php';
require_once JR_CONTENT_CORE_PATH . 'includes/migrations.php';
require_once JR_CONTENT_CORE_PATH . 'includes/bootstrap.php';
require_once JR_CONTENT_CORE_PATH . 'includes/playlist-data.php';
if ( is_admin() ) {
	require_once JR_CONTENT_CORE_PATH . 'includes/admin.php';
}

register_activation_hook( JR_CONTENT_CORE_FILE, 'jr_content_core_activate' );

jr_content_core_bootstrap();
