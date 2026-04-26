<?php
/**
 * Optional sample content creation.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sample_Content_Generator {
	public function generate( array $model, int $limit = 3 ): int {
		$count = 0;
		foreach ( array_slice( (array) ( $model['config']['sample_content'] ?? array() ), 0, max( 1, $limit ) ) as $sample ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => sanitize_key( (string) $sample['post_type'] ),
					'post_status'  => 'draft',
					'post_title'   => sanitize_text_field( (string) $sample['title'] ),
					'post_content' => wp_kses_post( (string) $sample['content'] ),
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			foreach ( (array) ( $sample['fields'] ?? array() ) as $key => $value ) {
				update_post_meta( $post_id, aica_meta_key( sanitize_key( (string) $key ) ), sanitize_text_field( (string) $value ) );
			}

			foreach ( (array) ( $sample['taxonomies'] ?? array() ) as $taxonomy => $terms ) {
				wp_set_object_terms( $post_id, array_map( 'sanitize_text_field', (array) $terms ), sanitize_key( (string) $taxonomy ) );
			}

			++$count;
		}

		return $count;
	}
}
