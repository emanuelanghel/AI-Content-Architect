<?php
/**
 * Basic fallback template manager.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Template_Manager {
	private Model_Store $store;

	public function __construct( Model_Store $store ) {
		$this->store = $store;
	}

	public function single_template( string $template ): string {
		if ( ! $this->enabled() || ! is_singular() ) {
			return $template;
		}

		return $this->is_generated_post_type( get_post_type() ) ? $this->locate_template( 'single-dynamic-content.php' ) : $template;
	}

	public function archive_template( string $template ): string {
		if ( ! $this->enabled() || ! is_post_type_archive() ) {
			return $template;
		}

		return $this->is_generated_post_type( get_query_var( 'post_type' ) ) ? $this->locate_template( 'archive-dynamic-content.php' ) : $template;
	}

	public function fields_for_post_type( string $post_type ): array {
		$fields = array();
		foreach ( $this->store->applied() as $model ) {
			foreach ( (array) ( $model['config']['fields'] ?? array() ) as $field ) {
				if ( $post_type === ( $field['post_type'] ?? '' ) ) {
					$fields[] = $field;
				}
			}
		}
		return $fields;
	}

	private function enabled(): bool {
		$settings = get_option( AICA_OPTION_SETTINGS, array() );
		return ! empty( $settings['enable_templates'] );
	}

	public function locate_template( string $template_name ): string {
		$template_name = ltrim( $template_name, '/\\' );
		$theme_template = locate_template( 'ai-content-architect/' . $template_name );

		if ( $theme_template ) {
			return $theme_template;
		}

		return AICA_PATH . 'templates/' . $template_name;
	}

	private function is_generated_post_type( $post_type ): bool {
		$post_type = is_array( $post_type ) ? reset( $post_type ) : $post_type;
		foreach ( $this->store->applied() as $model ) {
			foreach ( (array) ( $model['config']['custom_post_types'] ?? array() ) as $item ) {
				if ( $post_type === ( $item['key'] ?? '' ) ) {
					return true;
				}
			}
		}
		return false;
	}
}
