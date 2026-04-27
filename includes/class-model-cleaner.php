<?php
/**
 * Deletes content created under a generated model.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Model_Cleaner {
	public function delete_model_content( array $model ): array {
		$config = (array) ( $model['config'] ?? array() );
		$counts = array(
			'posts' => 0,
			'terms' => 0,
		);

		$this->ensure_structures_registered( $config );

		foreach ( (array) ( $config['custom_post_types'] ?? array() ) as $post_type ) {
			$key = sanitize_key( (string) ( $post_type['key'] ?? '' ) );
			if ( '' === $key ) {
				continue;
			}

			$counts['posts'] += $this->delete_posts_for_type( $key );
		}

		foreach ( (array) ( $config['taxonomies'] ?? array() ) as $taxonomy ) {
			$key = sanitize_key( (string) ( $taxonomy['key'] ?? '' ) );
			if ( '' === $key || ! taxonomy_exists( $key ) ) {
				continue;
			}

			$counts['terms'] += $this->delete_terms_for_taxonomy( $key );
		}

		return $counts;
	}

	private function ensure_structures_registered( array $config ): void {
		foreach ( (array) ( $config['custom_post_types'] ?? array() ) as $post_type ) {
			$key = sanitize_key( (string) ( $post_type['key'] ?? '' ) );
			if ( '' === $key || post_type_exists( $key ) ) {
				continue;
			}

			register_post_type(
				$key,
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => false,
				)
			);
		}

		foreach ( (array) ( $config['taxonomies'] ?? array() ) as $taxonomy ) {
			$key = sanitize_key( (string) ( $taxonomy['key'] ?? '' ) );
			if ( '' === $key || taxonomy_exists( $key ) ) {
				continue;
			}

			$post_types = array_filter( array_map( 'sanitize_key', (array) ( $taxonomy['post_types'] ?? array() ) ) );
			register_taxonomy(
				$key,
				$post_types,
				array(
					'public'       => false,
					'show_ui'      => false,
					'show_in_rest' => false,
				)
			);
		}
	}

	private function delete_posts_for_type( string $post_type ): int {
		$post_ids = get_posts(
			array(
				'post_type'        => $post_type,
				'post_status'      => get_post_stati( array(), 'names' ),
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'no_found_rows'    => true,
				'suppress_filters' => true,
			)
		);

		$deleted = 0;
		foreach ( $post_ids as $post_id ) {
			if ( wp_delete_post( (int) $post_id, true ) ) {
				$deleted++;
			}
		}

		return $deleted;
	}

	private function delete_terms_for_taxonomy( string $taxonomy ): int {
		$term_ids = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);

		if ( is_wp_error( $term_ids ) ) {
			return 0;
		}

		$deleted = 0;
		foreach ( $term_ids as $term_id ) {
			$result = wp_delete_term( (int) $term_id, $taxonomy );
			if ( $result && ! is_wp_error( $result ) ) {
				$deleted++;
			}
		}

		return $deleted;
	}
}
