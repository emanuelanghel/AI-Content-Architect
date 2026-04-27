<?php
/**
 * Activation handler.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activator {
	public static function activate(): void {
		add_option( 'aica_version', AICA_VERSION );
		add_option(
			AICA_OPTION_SETTINGS,
			array(
				'provider'               => 'mock',
				'base_url'               => Provider_Registry::OPENAI_BASE_URL,
				'model'                  => Provider_Registry::DEFAULT_MODEL,
				'custom_model'           => '',
				'use_custom_model'       => false,
				'strict_validation'      => true,
				'prevent_slug_conflicts' => true,
				'require_review'         => true,
				'enable_templates'       => false,
				'show_frontend_fields'   => true,
			)
		);
		add_option( AICA_OPTION_MODELS, array() );
		flush_rewrite_rules();
	}
}
