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

class OpenAI_Provider implements AI_Provider_Interface {
	private array $settings;

	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	public function generate_content_model( string $user_prompt ): array {
		$api_key  = (string) ( $this->settings['api_key'] ?? '' );
		$base_url = esc_url_raw( (string) ( $this->settings['base_url'] ?? 'https://api.openai.com/v1/chat/completions' ) );
		$model    = sanitize_text_field( (string) ( $this->settings['model'] ?? 'gpt-4.1-mini' ) );

		if ( '' === $api_key ) {
			return array(
				'error'   => true,
				'message' => __( 'Missing API key. Add one in Settings or use the mock provider for local testing.', 'ai-content-architect' ),
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

		$response = wp_remote_post(
			$base_url,
			array(
				'timeout' => 45,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
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

	private function system_prompt(): string {
		return 'You are an AI content model architect for WordPress. Return ONLY valid JSON, no markdown, no PHP, no code fences. Generate a configuration object with keys: model_name, description, intended_use_case, warnings, custom_post_types, taxonomies, fields, admin_columns, templates, sample_content. Allowed field types: text, textarea, number, email, url, date, checkbox, select, radio, image, gallery, wysiwyg. Custom post type supports may include title, editor, thumbnail, excerpt, author, comments, revisions. Use safe lowercase keys and slugs. Do not include script tags, PHP, HTML event handlers, or executable code. Prefer taxonomies for classification and fields for scalar data. Every field must reference a generated post_type. Every taxonomy must reference generated post_types.';
	}
}
