<?php
/**
 * Content model schema validator and sanitizer.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Schema_Validator {
	private array $errors = array();
	private array $warnings = array();

	private array $allowed_field_types = array( 'text', 'textarea', 'number', 'email', 'url', 'date', 'checkbox', 'select', 'radio', 'image', 'gallery', 'wysiwyg' );
	private array $allowed_supports    = array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'revisions', 'page-attributes', 'custom-fields' );
	private array $reserved_slugs      = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'wp_block', 'wp_template', 'wp_template_part', 'user', 'author', 'category', 'tag', 'taxonomy', 'admin', 'login', 'search', 'feed', 'embed', 'rest', 'api', 'wp-json' );

	public function validate( array $config, bool $check_conflicts = true ): array {
		$this->errors   = array();
		$this->warnings = array();
		$clean          = Content_Model::defaults( $config );

		$clean['model_name']        = $this->clean_text( (string) $clean['model_name'] );
		$clean['description']       = $this->clean_textarea( (string) $clean['description'] );
		$clean['intended_use_case'] = $this->clean_textarea( (string) ( $clean['intended_use_case'] ?? '' ) );
		$clean['warnings']          = $this->clean_string_list( (array) $clean['warnings'] );

		if ( '' === $clean['model_name'] ) {
			$this->errors[] = __( 'Model name is required.', 'ai-content-architect' );
		}

		$clean['custom_post_types'] = $this->validate_post_types( (array) $clean['custom_post_types'], $check_conflicts );
		$post_type_keys            = wp_list_pluck( $clean['custom_post_types'], 'key' );
		$clean['taxonomies']       = $this->validate_taxonomies( (array) $clean['taxonomies'], $post_type_keys, $check_conflicts );
		$taxonomy_keys             = wp_list_pluck( $clean['taxonomies'], 'key' );
		$clean['fields']           = $this->validate_fields( (array) $clean['fields'], $post_type_keys );
		$clean['admin_columns']    = $this->validate_admin_columns( (array) $clean['admin_columns'], $post_type_keys, $taxonomy_keys, wp_list_pluck( $clean['fields'], 'key' ) );
		$clean['templates']        = $this->validate_templates( (array) $clean['templates'], $post_type_keys );
		$clean['sample_content']   = $this->validate_sample_content( (array) $clean['sample_content'], $post_type_keys, $taxonomy_keys );

		if ( empty( $clean['custom_post_types'] ) ) {
			$this->errors[] = __( 'At least one custom post type is required.', 'ai-content-architect' );
		}

		return array(
			'valid'    => empty( $this->errors ),
			'config'   => $clean,
			'errors'   => $this->errors,
			'warnings' => array_values( array_unique( array_merge( $clean['warnings'], $this->warnings ) ) ),
		);
	}

	private function validate_post_types( array $items, bool $check_conflicts ): array {
		$clean = array();
		$seen  = array();

		foreach ( $items as $item ) {
			$item = (array) $item;
			$key  = sanitize_key( (string) ( $item['key'] ?? '' ) );

			if ( ! $this->valid_slug( $key, 'post type key' ) || isset( $seen[ $key ] ) ) {
				continue;
			}

			if ( $check_conflicts && post_type_exists( $key ) ) {
				$this->errors[] = sprintf( /* translators: %s: post type key. */ __( 'Post type "%s" already exists.', 'ai-content-architect' ), $key );
			}

			$slug = sanitize_title( (string) ( $item['slug'] ?? $key ) );
			$this->valid_slug( $slug, 'post type slug' );

			$seen[ $key ] = true;
			$clean[]      = array(
				'key'            => $key,
				'singular_label' => $this->clean_text( (string) ( $item['singular_label'] ?? $key ) ),
				'plural_label'   => $this->clean_text( (string) ( $item['plural_label'] ?? $key ) ),
				'slug'           => $slug,
				'menu_icon'      => sanitize_html_class( (string) ( $item['menu_icon'] ?? 'dashicons-admin-post' ) ),
				'public'         => (bool) ( $item['public'] ?? true ),
				'has_archive'    => (bool) ( $item['has_archive'] ?? true ),
				'show_in_rest'   => (bool) ( $item['show_in_rest'] ?? true ),
				'hierarchical'   => (bool) ( $item['hierarchical'] ?? false ),
				'menu_position'  => isset( $item['menu_position'] ) && '' !== $item['menu_position'] ? absint( $item['menu_position'] ) : null,
				'supports'       => array_values( array_intersect( (array) ( $item['supports'] ?? array( 'title', 'editor' ) ), $this->allowed_supports ) ),
			);
		}

		return $clean;
	}

	private function validate_taxonomies( array $items, array $post_type_keys, bool $check_conflicts ): array {
		$clean = array();
		$seen  = array();

		foreach ( $items as $item ) {
			$item = (array) $item;
			$key  = sanitize_key( (string) ( $item['key'] ?? '' ) );

			if ( ! $this->valid_slug( $key, 'taxonomy key' ) || isset( $seen[ $key ] ) ) {
				continue;
			}

			if ( $check_conflicts && taxonomy_exists( $key ) ) {
				$this->errors[] = sprintf( /* translators: %s: taxonomy key. */ __( 'Taxonomy "%s" already exists.', 'ai-content-architect' ), $key );
			}

			$post_types = array_values( array_intersect( array_map( 'sanitize_key', (array) ( $item['post_types'] ?? array() ) ), $post_type_keys ) );
			if ( empty( $post_types ) ) {
				$this->errors[] = sprintf( /* translators: %s: taxonomy key. */ __( 'Taxonomy "%s" must be attached to at least one generated post type.', 'ai-content-architect' ), $key );
			}

			$slug = sanitize_title( (string) ( $item['slug'] ?? $key ) );
			$this->valid_slug( $slug, 'taxonomy slug' );

			$seen[ $key ] = true;
			$clean[]      = array(
				'key'            => $key,
				'singular_label' => $this->clean_text( (string) ( $item['singular_label'] ?? $key ) ),
				'plural_label'   => $this->clean_text( (string) ( $item['plural_label'] ?? $key ) ),
				'slug'           => $slug,
				'post_types'     => $post_types,
				'hierarchical'   => (bool) ( $item['hierarchical'] ?? true ),
				'public'         => (bool) ( $item['public'] ?? true ),
				'show_in_rest'   => (bool) ( $item['show_in_rest'] ?? true ),
			);
		}

		return $clean;
	}

	private function validate_fields( array $items, array $post_type_keys ): array {
		$clean = array();

		foreach ( $items as $item ) {
			$item      = (array) $item;
			$key       = sanitize_key( (string) ( $item['key'] ?? '' ) );
			$post_type = sanitize_key( (string) ( $item['post_type'] ?? '' ) );
			$type      = sanitize_key( (string) ( $item['type'] ?? 'text' ) );

			if ( ! $this->valid_slug( $key, 'field key' ) || ! in_array( $post_type, $post_type_keys, true ) ) {
				$this->errors[] = sprintf( /* translators: %s: field key. */ __( 'Field "%s" references an unknown post type.', 'ai-content-architect' ), $key );
				continue;
			}

			if ( ! in_array( $type, $this->allowed_field_types, true ) ) {
				$this->errors[] = sprintf( /* translators: %s: field key. */ __( 'Field "%s" has an unsupported field type.', 'ai-content-architect' ), $key );
				continue;
			}

			$clean[] = array(
				'key'         => $key,
				'label'       => $this->clean_text( (string) ( $item['label'] ?? $key ) ),
				'type'        => $type,
				'post_type'   => $post_type,
				'required'    => (bool) ( $item['required'] ?? false ),
				'placeholder' => $this->clean_text( (string) ( $item['placeholder'] ?? '' ) ),
				'help_text'   => $this->clean_textarea( (string) ( $item['help_text'] ?? '' ) ),
				'default'     => $this->clean_value( $item['default'] ?? '', $type, (array) ( $item['options'] ?? array() ) ),
				'options'     => $this->clean_string_list( (array) ( $item['options'] ?? array() ) ),
			);
		}

		return $clean;
	}

	private function validate_admin_columns( array $items, array $post_types, array $taxonomies, array $fields ): array {
		$clean = array();

		foreach ( $items as $group ) {
			$group     = (array) $group;
			$post_type = sanitize_key( (string) ( $group['post_type'] ?? '' ) );
			if ( ! in_array( $post_type, $post_types, true ) ) {
				continue;
			}

			$columns = array();
			foreach ( (array) ( $group['columns'] ?? array() ) as $column ) {
				$column     = (array) $column;
				$source     = sanitize_key( (string) ( $column['source'] ?? 'field' ) );
				$source_key = sanitize_key( (string) ( $column['source_key'] ?? '' ) );

				if ( 'field' === $source && ! in_array( $source_key, $fields, true ) ) {
					continue;
				}

				if ( 'taxonomy' === $source && ! in_array( $source_key, $taxonomies, true ) ) {
					continue;
				}

				$columns[] = array(
					'key'        => sanitize_key( (string) ( $column['key'] ?? $source_key ) ),
					'label'      => $this->clean_text( (string) ( $column['label'] ?? $source_key ) ),
					'source'     => in_array( $source, array( 'field', 'taxonomy', 'featured_image' ), true ) ? $source : 'field',
					'source_key' => $source_key,
					'sortable'   => (bool) ( $column['sortable'] ?? false ),
				);
			}

			$clean[] = array(
				'post_type' => $post_type,
				'columns'   => $columns,
			);
		}

		return $clean;
	}

	private function validate_templates( array $items, array $post_types ): array {
		$clean = array();

		foreach ( $items as $item ) {
			$item      = (array) $item;
			$post_type = sanitize_key( (string) ( $item['post_type'] ?? '' ) );
			if ( ! in_array( $post_type, $post_types, true ) ) {
				continue;
			}

			$clean[] = array(
				'post_type'      => $post_type,
				'single_layout'  => $this->clean_textarea( (string) ( $item['single_layout'] ?? '' ) ),
				'archive_layout' => $this->clean_textarea( (string) ( $item['archive_layout'] ?? '' ) ),
			);
		}

		return $clean;
	}

	private function validate_sample_content( array $items, array $post_types, array $taxonomies ): array {
		$clean = array();

		foreach ( $items as $item ) {
			$item      = (array) $item;
			$post_type = sanitize_key( (string) ( $item['post_type'] ?? '' ) );
			if ( ! in_array( $post_type, $post_types, true ) ) {
				continue;
			}

			$tax_values = array();
			foreach ( (array) ( $item['taxonomies'] ?? array() ) as $taxonomy => $terms ) {
				$taxonomy = sanitize_key( (string) $taxonomy );
				if ( in_array( $taxonomy, $taxonomies, true ) ) {
					$tax_values[ $taxonomy ] = $this->clean_string_list( (array) $terms );
				}
			}

			$field_values = array();
			foreach ( (array) ( $item['fields'] ?? array() ) as $key => $value ) {
				$field_values[ sanitize_key( (string) $key ) ] = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
			}

			$clean[] = array(
				'post_type'  => $post_type,
				'title'      => $this->clean_text( (string) ( $item['title'] ?? '' ) ),
				'content'    => wp_kses_post( (string) ( $item['content'] ?? '' ) ),
				'fields'     => $field_values,
				'taxonomies' => $tax_values,
			);
		}

		return $clean;
	}

	private function valid_slug( string $slug, string $label ): bool {
		if ( '' === $slug || ! preg_match( '/^[a-z0-9_\\-]+$/', $slug ) ) {
			$this->errors[] = sprintf( /* translators: 1: label, 2: slug. */ __( 'Invalid %1$s: %2$s', 'ai-content-architect' ), $label, $slug );
			return false;
		}

		if ( in_array( $slug, $this->reserved_slugs, true ) ) {
			$this->errors[] = sprintf( /* translators: %s: slug. */ __( 'Reserved slug cannot be used: %s', 'ai-content-architect' ), $slug );
			return false;
		}

		return true;
	}

	private function clean_text( string $value ): string {
		return sanitize_text_field( wp_strip_all_tags( $value ) );
	}

	private function clean_textarea( string $value ): string {
		return sanitize_textarea_field( wp_strip_all_tags( $value ) );
	}

	private function clean_string_list( array $values ): array {
		return array_values(
			array_filter(
				array_map(
					function ( $value ) {
						return $this->clean_text( (string) $value );
					},
					$values
				)
			)
		);
	}

	private function clean_value( $value, string $type, array $options ) {
		if ( 'wysiwyg' === $type ) {
			return wp_kses_post( (string) $value );
		}

		if ( 'checkbox' === $type ) {
			return (bool) $value;
		}

		if ( in_array( $type, array( 'select', 'radio' ), true ) ) {
			$options = $this->clean_string_list( $options );
			$value   = $this->clean_text( (string) $value );
			return in_array( $value, $options, true ) ? $value : '';
		}

		return $this->clean_text( is_scalar( $value ) ? (string) $value : '' );
	}
}
