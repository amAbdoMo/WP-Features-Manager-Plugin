=== Widgets Manager ===
Contributors: widgets-manager
Tags: elementor, widgets, admin
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage which Widgets Manager widgets are available in Elementor.

== Description ==

Widgets Manager provides an Elementor admin screen for enabling Widgets Manager widgets through a positive allowlist. Widget classes are loaded and registered only after an administrator enables their catalog entries.

Version 1.0.0 intentionally includes an empty metadata catalog. It does not ship demo widgets.

Elementor 3.20 or later must be installed and active. The plugin displays an administrator notice if Elementor is unavailable or incompatible.

== Installation ==

1. Upload the `widgets-manager` directory to `/wp-content/plugins/`.
2. Activate Widgets Manager through the Plugins screen.
3. Open Widgets Manager in the WordPress admin menu.

== Frequently Asked Questions ==

= Why are no widgets displayed? =

The initial catalog is intentionally empty. Production widget metadata appears here when Widgets Manager adds a widget.

= What is removed on uninstall? =

The `widgets_manager_enabled_widgets` option is removed. On multisite, it is removed from every site.

== Changelog ==

= 1.0.0 =
* Initial release with an empty widget catalog and Elementor allowlist controls.
