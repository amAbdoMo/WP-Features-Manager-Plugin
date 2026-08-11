# Widgets Manager

Widgets Manager is an Elementor-only control screen for Widgets Manager widgets. It keeps plugin widgets disabled until an administrator explicitly enables them.

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- Elementor 3.20 or later, installed and active

The plugin shows an administrator notice when Elementor is unavailable or below the required version. The management screen remains available so its saved allowlist can be reviewed.

## Current catalog

Version 1.0.0 intentionally ships with an empty metadata catalog. It does not include demo widgets, widget classes, or widget assets. The admin screen displays an empty-catalog state until a production widget is added.

## Use

1. Install the plugin directory as `widgets-manager` in `wp-content/plugins/`.
2. Activate **Widgets Manager**.
3. Open **Widgets Manager** in the WordPress admin menu.
4. Enable the widgets you want available in Elementor, then select **Save changes**.

The form uses WordPress's normal POST flow. A successful save redirects back to the screen and confirms that settings were saved.

## Widget loading policy

Enabled IDs are stored in the `widgets_manager_enabled_widgets` option as a positive allowlist. `WidgetsManager\Elementor_Adapter` checks that allowlist before requiring a widget file or creating its Elementor widget instance. A disabled widget is therefore neither loaded nor registered.

Future production catalog entries belong in `WidgetsManager\Plugin::widget_catalog()` and use the metadata keys `id`, `provider`, `class`, `file`, `title`, and `description`. IDs are provider-qualified, such as `elementor/testimonial`. The Elementor adapter reads only entries whose `provider` is `elementor`.

## Updates

The plugin checks [GitHub Releases](https://github.com/amAbdoMo/WP-Widgets-Manager-Plugin/releases) for the exact `widgets-manager.zip` release asset. It does not install GitHub source archives. Plugin Update Checker 5.7 is bundled for this purpose.

## Development checks

From the plugin directory, validate syntax with:

```sh
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

`phpcs.xml.dist` provides WordPress Coding Standards rules for environments that install PHPCS and WPCS separately. `phpunit.xml.dist` is a baseline configuration for a WordPress test environment; the repository does not bundle WordPress or PHPUnit.

## Uninstall

Uninstalling the plugin removes the `widgets_manager_enabled_widgets` option. On multisite it removes that option from every site.
