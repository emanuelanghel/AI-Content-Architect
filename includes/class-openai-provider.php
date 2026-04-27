<?php
/**
 * OpenAI-compatible provider.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OpenAI_Provider implements AI_Provider_Interface, AI_Model_Provider_Interface {
	private array $settings;

	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	public function generate_content_model( string $user_prompt ): array {
		$api_key  = (string) ( $this->settings['api_key'] ?? '' );
		$provider = Provider_Registry::normalize_provider( (string) ( $this->settings['provider'] ?? 'openai' ) );
		$base_url = Provider_Registry::normalize_base_url( (string) ( $this->settings['base_url'] ?? Provider_Registry::OPENAI_BASE_URL ), $provider );
		$model    = sanitize_text_field( (string) ( $this->settings['model'] ?? Provider_Registry::DEFAULT_MODEL ) );

		if ( 'openai' === $provider && '' === $api_key ) {
			return array(
				'error'   => true,
				'message' => __( 'Missing API key. Add one in Settings or use the mock provider for local testing.', 'ai-content-architect' ),
			);
		}

		if ( '' === $base_url ) {
			return array(
				'error'   => true,
				'message' => __( 'Missing provider base URL.', 'ai-content-architect' ),
			);
		}

		$body = array(
			'model'       => $model,
			'temperature' => 0.2,
			'messages'    => array(
				array(
					'role'    => 'system',
					'content' => $this->system_prompt(),
				),
				array(
					'role'    => 'user',
					'content' => $user_prompt,
				),
			),
		);

		$headers = array(
			'Content-Type' => 'application/json',
		);
		if ( '' !== $api_key ) {
			$headers['Authorization'] = 'Bearer ' . $api_key;
		}

		$response = wp_remote_post(
			$this->endpoint( 'chat/completions', $base_url ),
			array(
				'timeout' => 45,
				'headers' => $headers,
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'error' => true, 'message' => $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 > $code || 299 < $code ) {
			return array(
				'error'   => true,
				'message' => sprintf( /* translators: %d: HTTP status. */ __( 'AI provider returned HTTP %d.', 'ai-content-architect' ), $code ),
			);
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		$content = $decoded['choices'][0]['message']['content'] ?? '';
		$content = trim( (string) $content );
		$content = preg_replace( '/^```(?:json)?|```$/m', '', $content );
		$config  = json_decode( trim( (string) $content ), true );

		if ( ! is_array( $config ) ) {
			return array(
				'error'   => true,
				'message' => __( 'AI provider returned invalid JSON.', 'ai-content-architect' ),
			);
		}

		return $config;
	}

	public function list_models(): array {
		$api_key  = (string) ( $this->settings['api_key'] ?? '' );
		$provider = Provider_Registry::normalize_provider( (string) ( $this->settings['provider'] ?? 'openai' ) );
		$base_url = Provider_Registry::normalize_base_url( (string) ( $this->settings['base_url'] ?? Provider_Registry::OPENAI_BASE_URL ), $provider );

		if ( 'openai' === $provider && '' === $api_key ) {
			return array(
				'valid'   => false,
				'message' => __( 'Add an API key before refreshing provider models.', 'ai-content-architect' ),
				'models'  => Provider_Registry::fallback_models(),
			);
		}

		if ( '' === $base_url ) {
			return array(
				'valid'   => false,
				'message' => __( 'Add a provider base URL before refreshing models.', 'ai-content-architect' ),
				'models'  => Provider_Registry::fallback_models(),
			);
		}

		$headers = array();
		if ( '' !== $api_key ) {
			$headers['Authorization'] = 'Bearer ' . $api_key;
		}

		$response = wp_remote_get(
			$this->endpoint( 'models', $base_url ),
			array(
				'timeout' => 20,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'valid'   => false,
				'message' => $response->get_error_message(),
				'models'  => Provider_Registry::fallback_models(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 > $code || 299 < $code ) {
			return array(
				'valid'   => false,
				'message' => sprintf( /* translators: %d: HTTP status. */ __( 'Provider model list returned HTTP %d.', 'ai-content-architect' ), $code ),
				'models'  => Provider_Registry::fallback_models(),
			);
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		$items   = is_array( $decoded['data'] ?? null ) ? $decoded['data'] : array();
		$models  = array();

		foreach ( $items as $item ) {
			$id = sanitize_text_field( (string) ( $item['id'] ?? '' ) );
			if ( '' === $id || ( 'openai' === $provider && ! $this->is_generation_model( $id ) ) ) {
				continue;
			}

			$models[] = array(
				'id'          => $id,
				'label'       => Provider_Registry::model_label( $id ),
				'description' => sanitize_text_field( (string) ( $item['owned_by'] ?? '' ) ),
				'badge'       => $this->model_badge( $id ),
			);
		}

		$models = $this->sort_models( $models );

		if ( empty( $models ) ) {
			return array(
				'valid'   => false,
				'message' => __( 'No text generation models were found. You can still enter a custom model ID.', 'ai-content-architect' ),
				'models'  => Provider_Registry::fallback_models(),
			);
		}

		return array(
			'valid'  => true,
			'models' => $models,
		);
	}

	public function test_connection(): array {
		$result = $this->list_models();

		if ( empty( $result['valid'] ) ) {
			return array(
				'valid'   => false,
				'message' => sanitize_text_field( (string) ( $result['message'] ?? __( 'Provider connection failed.', 'ai-content-architect' ) ) ),
			);
		}

		return array(
			'valid'   => true,
			'message' => sprintf(
				/* translators: %d: available model count. */
				__( 'Connection successful. Found %d available text models.', 'ai-content-architect' ),
				count( (array) ( $result['models'] ?? array() ) )
			),
		);
	}

	private function system_prompt(): string {
		return 'You are an AI content model architect for WordPress. Return ONLY valid JSON, no markdown, no PHP, no code fences. Generate a configuration object with keys: model_name, description, intended_use_case, warnings, custom_post_types, taxonomies, fields, admin_columns, templates, sample_content. Allowed field types: text, textarea, number, email, url, date, checkbox, select, radio, image, gallery, wysiwyg. Custom post type supports may include title, editor, thumbnail, excerpt, author, comments, revisions. Use safe lowercase keys and slugs. Do not include script tags, PHP, HTML event handlers, or executable code. Prefer taxonomies for classification and fields for scalar data. Every field must reference a generated post_type. Every taxonomy must reference generated post_types.';
	}

	private function endpoint( string $path, string $base_url ): string {
		return untrailingslashit( $base_url ) . '/' . ltrim( $path, '/' );
	}

	private function is_generation_model( string $id ): bool {
		if ( preg_match( '/(embedding|moderation|tts|transcribe|whisper|realtime|audio|image|sora|search|computer-use)/i', $id ) ) {
			return false;
		}

		return (bool) preg_match( '/^(gpt|o[0-9]|chatgpt|ft:)/i', $id );
	}

	private function model_badge( string $id ): string {
		if ( preg_match( '/mini/i', $id ) ) {
			return __( 'Recommended', 'ai-content-architect' );
		}

		if ( preg_match( '/nano/i', $id ) ) {
			return __( 'Budget', 'ai-content-architect' );
		}

		if ( preg_match( '/gpt-5\\.5|pro/i', $id ) ) {
			return __( 'Best quality', 'ai-content-architect' );
		}

		if ( preg_match( '/gpt-4|legacy|deprecated/i', $id ) ) {
			return __( 'Legacy', 'ai-content-architect' );
		}

		return '';
	}

	private function sort_models( array $models ): array {
		usort(
			$models,
			static function ( array $a, array $b ): int {
				$a_id = (string) ( $a['id'] ?? '' );
				$b_id = (string) ( $b['id'] ?? '' );

				$score = static function ( string $id ): int {
					if ( 'gpt-5.4-mini' === $id ) {
						return 1000;
					}
					if ( preg_match( '/gpt-5\\.[0-9]+-mini/i', $id ) ) {
						return 950;
					}
					if ( preg_match( '/gpt-5\\.5/i', $id ) ) {
						return 900;
					}
					if ( preg_match( '/gpt-5/i', $id ) ) {
						return 850;
					}
					if ( preg_match( '/mini/i', $id ) ) {
						return 800;
					}
					if ( preg_match( '/nano/i', $id ) ) {
						return 700;
					}
					if ( preg_match( '/gpt-4\\.1/i', $id ) ) {
						return 600;
					}
					return 100;
				};

				return $score( $b_id ) <=> $score( $a_id ) ?: strcmp( $a_id, $b_id );
			}
		);

		return $models;
	}
}
