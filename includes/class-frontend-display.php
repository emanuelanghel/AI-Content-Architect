<?php
/**
 * Theme-compatible frontend field output.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend_Display {
	private Template_Manager $templates;

	public function __construct( Template_Manager $templates ) {
		$this->templates = $templates;
	}

	public function append_fields_to_content( string $content ): string {
		if ( is_admin() || is_feed() || ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$settings = wp_parse_args(
			get_option( AICA_OPTION_SETTINGS, array() ),
			array(
				'show_frontend_fields' => true,
			)
		);
		if ( empty( $settings['show_frontend_fields'] ) ) {
			return $content;
		}

		$post_type = get_post_type();
		$fields    = $this->templates->fields_for_post_type( (string) $post_type );

		if ( empty( $fields ) ) {
			return $content;
		}

		$output = $this->render_fields( get_the_ID(), $fields );
		return '' === $output ? $content : $content . $output;
	}

	private function render_fields( int $post_id, array $fields ): string {
		$rows = '';

		foreach ( $fields as $field ) {
			$value = get_post_meta( $post_id, aica_meta_key( $field['key'] ), true );

			if ( '' === (string) $value ) {
				continue;
			}

			$rows .= '<div class="aica-frontend-field aica-frontend-field-' . esc_attr( $field['key'] ) . '">';
			$rows .= '<dt>' . esc_html( $field['label'] ) . '</dt>';
			$rows .= '<dd>' . $this->format_value( $value, $field ) . '</dd>';
			$rows .= '</div>';
		}

		if ( '' === $rows ) {
			return '';
		}

		return '<section class="aica-frontend-fields"><h2>' . esc_html__( 'Details', 'ai-content-architect' ) . '</h2><dl>' . $rows . '</dl></section>';
	}

	private function format_value( $value, array $field ): string {
		$type = $field['type'] ?? 'text';

		if ( 'checkbox' === $type ) {
			return $value ? esc_html__( 'Yes', 'ai-content-architect' ) : esc_html__( 'No', 'ai-content-architect' );
		}

		if ( 'url' === $type ) {
			$url = esc_url( (string) $value );
			return '' === $url ? '' : '<a href="' . $url . '" rel="nofollow">' . esc_html( $url ) . '</a>';
		}

		if ( 'wysiwyg' === $type ) {
			return wp_kses_post( (string) $value );
		}

		return esc_html( (string) $value );
	}
}
