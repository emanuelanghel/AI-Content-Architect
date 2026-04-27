<?php
/**
 * Plugin Name: AI Content Architect
 * Description: Turns plain-English prompts into reviewable WordPress content model configurations.
 * Version: 0.1.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: AI Content Architect
 * Text Domain: ai-content-architect
 * Domain Path: /languages
 *
 * @package AIContentArchitect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AICA_VERSION', '0.1.0' );
define( 'AICA_FILE', __FILE__ );
define( 'AICA_PATH', plugin_dir_path( __FILE__ ) );
define( 'AICA_URL', plugin_dir_url( __FILE__ ) );
define( 'AICA_OPTION_MODELS', 'aica_content_models' );
define( 'AICA_OPTION_SETTINGS', 'aica_settings' );
define( 'AICA_OPTION_NEEDS_REWRITE_FLUSH', 'aica_needs_rewrite_flush' );
define( 'AICA_OPTION_PROVIDER_MODELS_CACHE', 'aica_provider_models_cache' );

require_once AICA_PATH . 'includes/helpers.php';

spl_autoload_register(
	static function ( $class ) {
		$prefix = 'AIContentArchitect\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$relative = strtolower( str_replace( '_', '-', $relative ) );
		$file     = AICA_PATH . 'includes/class-' . $relative . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

register_activation_hook( __FILE__, array( 'AIContentArchitect\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AIContentArchitect\\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'ai-content-architect', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		AIContentArchitect\Plugin::instance()->run();
	}
);
