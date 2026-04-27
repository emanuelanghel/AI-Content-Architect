<?php
/**
 * AI provider registry and shared settings helpers.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Provider_Registry {
	public const OPENAI_BASE_URL = 'https://api.openai.com/v1';
	public const DEFAULT_MODEL   = 'gpt-5.4-mini';

	public static function providers(): array {
		$providers = array(
			'mock'              => array(
				'label'           => __( 'Mock provider (development)', 'ai-content-architect' ),
				'description'     => __( 'No API key required. Returns varied sample models for local workflow testing.', 'ai-content-architect' ),
				'class'           => Mock_Provider::class,
				'supports_models' => true,
				'requires_key'    => false,
				'default_base_url'=> '',
			),
			'openai'            => array(
				'label'           => __( 'OpenAI', 'ai-content-architect' ),
				'description'     => __( 'Official OpenAI API provider with model refresh and connection testing.', 'ai-content-architect' ),
				'class'           => OpenAI_Provider::class,
				'supports_models' => true,
				'requires_key'    => true,
				'default_base_url'=> self::OPENAI_BASE_URL,
			),
			'openai_compatible' => array(
				'label'           => __( 'OpenAI-compatible', 'ai-content-architect' ),
				'description'     => __( 'Use gateways or providers that follow OpenAI-compatible chat and model-list APIs.', 'ai-content-architect' ),
				'class'           => OpenAI_Provider::class,
				'supports_models' => true,
				'requires_key'    => false,
				'default_base_url'=> self::OPENAI_BASE_URL,
			),
			'custom'            => array(
				'label'           => __( 'Custom provider', 'ai-content-architect' ),
				'description'     => __( 'Connect a custom OpenAI-compatible endpoint, or register a custom provider class with the aica_ai_providers filter.', 'ai-content-architect' ),
				'class'           => OpenAI_Provider::class,
				'supports_models' => true,
				'requires_key'    => false,
				'default_base_url'=> '',
			),
		);

		/**
		 * Register or modify AI Content Architect providers.
		 *
		 * Provider classes should implement AI_Provider_Interface and can optionally
		 * implement AI_Model_Provider_Interface for model listing and connection tests.
		 */
		return apply_filters( 'aica_ai_providers', $providers );
	}

	public static function normalize_provider( string $provider ): string {
		$provider = sanitize_key( $provider );
		$providers = self::providers();

		return isset( $providers[ $provider ] ) ? $provider : 'mock';
	}

	public static function provider( string $provider ): array {
		$providers = self::providers();
		$provider  = self::normalize_provider( $provider );

		return $providers[ $provider ];
	}

	public static function create_provider( array $settings ): AI_Provider_Interface {
		$provider_key = self::normalize_provider( (string) ( $settings['provider'] ?? 'mock' ) );
		$provider     = self::provider( $provider_key );
		$class        = (string) ( $provider['class'] ?? Mock_Provider::class );

		if ( ! class_exists( $class ) ) {
			$class = Mock_Provider::class;
		}

		$instance = Mock_Provider::class === $class ? new Mock_Provider() : new $class( $settings );

		if ( ! $instance instanceof AI_Provider_Interface ) {
			$instance = new Mock_Provider();
		}

		return apply_filters( 'aica_ai_provider_instance', $instance, $settings, $provider );
	}

	public static function fallback_models(): array {
		return array(
			array(
				'id'          => 'gpt-5.4-mini',
				'label'       => __( 'GPT-5.4 Mini', 'ai-content-architect' ),
				'description' => __( 'Recommended default for structured content generation.', 'ai-content-architect' ),
				'badge'       => __( 'Recommended', 'ai-content-architect' ),
			),
			array(
				'id'          => 'gpt-5.5',
				'label'       => __( 'GPT-5.5', 'ai-content-architect' ),
				'description' => __( 'Best quality for complex prompts.', 'ai-content-architect' ),
				'badge'       => __( 'Best quality', 'ai-content-architect' ),
			),
			array(
				'id'          => 'gpt-5.4-nano',
				'label'       => __( 'GPT-5.4 Nano', 'ai-content-architect' ),
				'description' => __( 'Lowest latency and cost for quick tests.', 'ai-content-architect' ),
				'badge'       => __( 'Budget', 'ai-content-architect' ),
			),
			array(
				'id'          => 'gpt-4.1-mini',
				'label'       => __( 'GPT-4.1 Mini', 'ai-content-architect' ),
				'description' => __( 'Legacy compatible fallback.', 'ai-content-architect' ),
				'badge'       => __( 'Legacy', 'ai-content-architect' ),
			),
		);
	}

	public static function cached_models( array $settings ): array {
		$cache = get_option( AICA_OPTION_PROVIDER_MODELS_CACHE, array() );
		if ( ! is_array( $cache ) ) {
			return array();
		}

		$provider = self::normalize_provider( (string) ( $settings['provider'] ?? 'mock' ) );
		$base_url = self::normalize_base_url( (string) ( $settings['base_url'] ?? '' ), $provider );

		if ( $provider !== ( $cache['provider'] ?? '' ) || $base_url !== ( $cache['base_url'] ?? '' ) ) {
			return array();
		}

		return is_array( $cache['models'] ?? null ) ? $cache : array();
	}

	public static function model_choices( array $settings ): array {
		$provider = self::normalize_provider( (string) ( $settings['provider'] ?? 'mock' ) );
		if ( 'mock' === $provider ) {
			return array(
				'models'       => array(
					array(
						'id'          => 'mock',
						'label'       => __( 'Mock provider samples', 'ai-content-architect' ),
						'description' => __( 'Model selection is ignored in mock mode.', 'ai-content-architect' ),
						'badge'       => __( 'No key required', 'ai-content-architect' ),
					),
				),
				'refreshed_at' => '',
				'source'       => 'mock',
			);
		}

		$cache = self::cached_models( $settings );
		if ( ! empty( $cache['models'] ) ) {
			return array(
				'models'       => $cache['models'],
				'refreshed_at' => (string) ( $cache['refreshed_at'] ?? '' ),
				'source'       => 'cache',
			);
		}

		return array(
			'models'       => self::fallback_models(),
			'refreshed_at' => '',
			'source'       => 'fallback',
		);
	}

	public static function normalize_base_url( string $base_url, string $provider = 'openai' ): string {
		$provider = self::normalize_provider( $provider );
		$base_url = trim( $base_url );

		if ( '' === $base_url ) {
			$base_url = (string) ( self::provider( $provider )['default_base_url'] ?? self::OPENAI_BASE_URL );
		}

		$base_url = untrailingslashit( esc_url_raw( $base_url ) );
		$base_url = preg_replace( '#/chat/completions$#', '', $base_url );
		$base_url = preg_replace( '#/responses$#', '', $base_url );
		$base_url = preg_replace( '#/models$#', '', $base_url );

		return untrailingslashit( (string) $base_url );
	}

	public static function model_label( string $model_id ): string {
		$model_id = trim( $model_id );
		if ( '' === $model_id ) {
			return '';
		}

		return ucwords( str_replace( array( '-', '_' ), ' ', $model_id ) );
	}
}
