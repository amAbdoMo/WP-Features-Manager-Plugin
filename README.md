# WP Features Manager

WP Features Manager keeps optional modules and Elementor widgets disabled until an administrator explicitly enables them. Its custom admin screen separates Modules and Elementor Widgets into instant client-side tabs; search, status filters, and bulk actions apply only to the active tab.

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- Elementor 3.20 or later for bundled Elementor widgets
- The **ACF Elementor Gallery** module additionally requires active Advanced Custom Fields

## Modules and loading policy

Feature metadata is registered in `WPFeaturesManager\Plugin::module_catalog()` without loading module code. Module IDs are stored in the positive allowlist option `wp_features_manager_enabled_modules`. `WPFeaturesManager\Module_Loader` requires a module entry file only when that ID is enabled and its lightweight dependency/conflict status is available. Disabled and unavailable module PHP, hooks, and assets do not load.

The bundled `acf-elementor-gallery` module is disabled by default. It retains the public ACF field type `acfge_gallery`, Elementor dynamic tag name `acfge-gallery`, and attachment-ID value storage format used by the former standalone gallery plugin. It reports missing ACF/Elementor requirements and conflicts with an active standalone gallery plugin instead of running alongside it.

## Widget catalog

The two widgets migrated from Widgets Manager remain disabled by default:

- **Before/After Image** — an accessible image comparator.
- **Custom Video** — a plugin-owned HTML5 player for WordPress Media Library videos and direct video-file URLs. It does not use Elementor Pro, jQuery, or a third-party player library.

Widget IDs, frontend class names, CSS selectors, JavaScript data attributes, and Elementor control behavior are preserved from Widgets Manager. The old `widgets_manager_enabled_widgets` allowlist is copied into `wp_features_manager_enabled_widgets` on first use so existing local choices remain enabled.

## Enable and use a widget

1. Activate **WP Features Manager** and deactivate the old **Widgets Manager** plugin.
2. Open **Features Manager** in WordPress admin.
3. Enable the required widget and save changes.
4. Add it from Elementor's **WP Features Manager** category.

A disabled widget class is neither loaded nor registered with Elementor. Widget styles and scripts are registered with Elementor but not globally enqueued; each widget declares its own dependencies.

## Before/After Image

Before/After Image follows the displayed image aspect ratio by default, so its height scales automatically with the available width. A separate Custom responsive height mode remains available when intentional cropping is required. The revealed image stays full-size while the divider clips it, preventing the image from shrinking or stretching during interaction. Each image label is progressively clipped by the divider along with its visible image region, so both labels disappear smoothly rather than vanishing as a whole.

## Custom Video

Custom Video supports self-hosted or direct browser-playable HTML5 files, including optional fallback encodings of the same video, poster images, start/end times, native WebVTT subtitle or caption tracks, and a poster-first loading strategy. YouTube and Vimeo are intentionally not supported because their iframe controls and branding cannot be fully plugin-owned.

For broad browser support, encode MP4 files with H.264 video and AAC audio. A browser may play only audio when a file uses an unsupported video codec such as HEVC; provide a compatible MP4 or WebM fallback in that case.

The player has a custom overlay, safe default SVG play icon or Elementor Icons control, one selectable lightweight animation, reduced-motion support, and optional title/subtitle. Individual controls can be independently shown or hidden: play/pause, rewind, fast-forward, progress, buffered indication, current time, duration, mute, volume, speed, captions, Picture-in-Picture, fullscreen, and download link. Hiding the download link is not video protection.

Responsive controls use Elementor responsive controls and selectors, including custom breakpoints. Dimensions support aspect-ratio or custom-height modes, width, maximum width, minimum height, object fit, and focal position. At narrow player widths, the control bar docks below the video stage. Secondary controls move into an accessible three-dot overflow menu.

Autoplay is attempted once and leaves the poster/play state visible when browser policy rejects it. Browsers commonly require muted inline playback for autoplay. Autoplay is disabled in the Elementor editor by default. Native controls remain available before JavaScript initializes; custom controls take over afterward.

Keyboard support includes Space/K for playback, Left/Right for seeking, Up/Down for volume, M for mute, F for fullscreen, C for captions, and Escape for menus. Custom icon buttons have accessible names, and stateful controls synchronize `aria-pressed`.

Picture-in-Picture, fullscreen, browser-native HLS, and delivered video format support depend on the visitor's browser. No HLS/DASH library is bundled.

## Adding a bundled module

1. Add metadata to `WPFeaturesManager\Plugin::module_catalog()` with a stable `id`, admin `title` and `description`, and plugin-relative `file`.
2. Put the implementation under `modules/<module-id>/` and use its entry file only to register module hooks.
3. Declare supported ACF/Elementor requirements and standalone conflicts in metadata so availability is checked before implementation loads.
4. Add loader tests proving disabled and unavailable implementations stay unloaded.

The host owns settings, administration, updates, and release packaging. Bundled modules must not add separate updaters.

## Updates and releases

The host updater checks [GitHub Releases](https://github.com/amAbdoMo/WP-Widgets-Manager-Plugin/releases) for the exact `wp-features-manager.zip` asset. The release workflow packages it with the `wp-features-manager` archive root.

## Development checks

```sh
find . -path './vendor' -prune -o -name '*.php' -type f -print0 | xargs -0 -n1 php -l
node --check assets/js/admin.js
node --check assets/js/widgets/before-after-image.js
node --check assets/js/widgets/custom-video.js
WP_TESTS_DIR=/path/to/wordpress-tests-lib phpunit -c phpunit.xml.dist
phpcs -p --standard=phpcs.xml.dist
```

PHPUnit, the WordPress test library, PHPCS, and WPCS must be installed separately and available on `PATH`; they are not bundled in this repository.

## Uninstall

Uninstall deletes `wp_features_manager_enabled_modules`, `wp_features_manager_enabled_widgets`, and the migrated legacy `widgets_manager_enabled_widgets` option, including on every site in multisite.
