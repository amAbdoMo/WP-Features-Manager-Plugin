<?php
/**
 * Plugin Name: WP Features Manager
 * Plugin URI: https://github.com/amAbdoMo/WP-Features-Manager-Plugin
 * Description: Enables optional WordPress features and Elementor widgets through positive allowlists.
 * Version: 2.1.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Abdo
 * Author URI: https://github.com/amAbdoMo
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-features-manager
 * Domain Path: /languages
 * Update URI: https://github.com/amAbdoMo/WP-Features-Manager-Plugin
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_FEATURES_MANAGER_VERSION', '2.1.0' );
define( 'WP_FEATURES_MANAGER_FILE', __FILE__ );
define( 'WP_FEATURES_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_FEATURES_MANAGER_URL', plugin_dir_url( __FILE__ ) );

require_once WP_FEATURES_MANAGER_PATH . 'includes/class-catalog.php';
require_once WP_FEATURES_MANAGER_PATH . 'includes/class-enabled-widgets.php';
require_once WP_FEATURES_MANAGER_PATH . 'includes/class-module-catalog.php';
require_once WP_FEATURES_MANAGER_PATH . 'includes/class-enabled-modules.php';
require_once WP_FEATURES_MANAGER_PATH . 'includes/class-module-loader.php';
require_once WP_FEATURES_MANAGER_PATH . 'includes/class-elementor-adapter.php';
require_once WP_FEATURES_MANAGER_PATH . 'includes/class-admin-page.php';
require_once WP_FEATURES_MANAGER_PATH . 'includes/class-self-updater.php';
require_once WP_FEATURES_MANAGER_PATH . 'includes/class-plugin.php';

add_action( 'plugins_loaded', array( Self_Updater::class, 'register' ), 1 );
Plugin::instance()->boot();
