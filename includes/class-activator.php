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
				'base_url'               => 'https://api.openai.com/v1/chat/completions',
				'model'                  => 'gpt-4.1-mini',
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
