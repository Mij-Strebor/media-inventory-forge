<?php
// Test bootstrap — not loaded in production; ABSPATH defined below for WP stubs.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Prevent `defined('ABSPATH') || exit` guards in plugin files from firing
define('MINVF_PLUGIN_DIR', dirname(__DIR__) . '/');
define('MINVF_VERSION', '5.1.0');
// phpcs:ignore PluginCheck.CodeAnalysis.Localhost.Found -- Test-only constant, not shipped to production
define('MINVF_PLUGIN_URL', 'http://localhost/');

// WP_Mock — call bootstrap() once so any test that extends WP_Mock\Tools\TestCase
// can set up WordPress function stubs without errors.
WP_Mock::setUsePatchwork(false);
WP_Mock::bootstrap();

// Load utility classes (no WordPress dependencies — safe to require here)
require_once MINVF_PLUGIN_DIR . 'includes/utilities/class-file-utils.php';
require_once MINVF_PLUGIN_DIR . 'includes/utilities/class-media-type-info.php';
