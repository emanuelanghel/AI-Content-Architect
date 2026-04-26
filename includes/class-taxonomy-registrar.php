<?php
/**
 * Registers generated taxonomies.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Taxonomy_Registrar {
	private Model_Store $store;

	public function __construct( Model_Store $store ) {
		$this->store = $store;
	}

	public function register(): void {
		foreach ( $this->store->applied() as $model ) {
			foreach ( (array) ( $model['config']['taxonomies'] ?? array() ) as $taxonomy ) {
				$key = sanitize_key( (string) ( $taxonomy['key'] ?? '' ) );
				if ( '' === $key || taxonomy_exists( $key ) ) {
					continue;
				}

				register_taxonomy(
					$key,
					array_map( 'sanitize_key', (array) $taxonomy['post_types'] ),
					apply_filters(
						'aica_register_taxonomy_args',
						array(
							'labels'            => array(
								'name'          => $taxonomy['plural_label'],
								'singular_name' => $taxonomy['singular_label'],
								'menu_name'     => $taxonomy['plural_label'],
								'search_items'  => sprintf( __( 'Search %s', 'ai-content-architect' ), $taxonomy['plural_label'] ),
								'all_items'     => sprintf( __( 'All %s', 'ai-content-architect' ), $taxonomy['plural_label'] ),
								'edit_item'     => sprintf( __( 'Edit %s', 'ai-content-architect' ), $taxonomy['singular_label'] ),
								'add_new_item'  => sprintf( __( 'Add New %s', 'ai-content-architect' ), $taxonomy['singular_label'] ),
							),
							'public'            => (bool) $taxonomy['public'],
							'show_ui'           => true,
							'show_admin_column' => true,
							'show_in_rest'      => (bool) $taxonomy['show_in_rest'],
							'hierarchical'      => (bool) $taxonomy['hierarchical'],
							'rewrite'           => array( 'slug' => sanitize_title( $taxonomy['slug'] ) ),
						),
						$taxonomy,
						$model
					)
				);
			}
		}
	}
}
