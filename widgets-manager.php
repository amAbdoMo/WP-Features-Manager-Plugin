<?php
/**
 * Plugin Name: Widgets Manager
 * Plugin URI: https://github.com/amAbdoMo/WP-Widgets-Manager-Plugin
 * Description: Controls which Widgets Manager Elementor widgets are available in the editor.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Widgets Manager
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: widgets-manager
 * Domain Path: /languages
 * Update URI: https://github.com/amAbdoMo/WP-Widgets-Manager-Plugin
 *
 * @package WidgetsManager
 */

namespace WidgetsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WIDGETS_MANAGER_VERSION', '1.0.0' );
define( 'WIDGETS_MANAGER_FILE', __FILE__ );
define( 'WIDGETS_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WIDGETS_MANAGER_URL', plugin_dir_url( __FILE__ ) );

require_once WIDGETS_MANAGER_PATH . 'includes/class-catalog.php';
require_once WIDGETS_MANAGER_PATH . 'includes/class-enabled-widgets.php';
require_once WIDGETS_MANAGER_PATH . 'includes/class-elementor-adapter.php';
require_once WIDGETS_MANAGER_PATH . 'includes/class-admin-page.php';
require_once WIDGETS_MANAGER_PATH . 'includes/class-self-updater.php';
require_once WIDGETS_MANAGER_PATH . 'includes/class-plugin.php';

add_action( 'plugins_loaded', array( Self_Updater::class, 'register' ), 1 );

Plugin::instance()->boot();
