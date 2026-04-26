<?php
/**
 * Registers generated custom post types.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPT_Registrar {
	private Model_Store $store;

	public function __construct( Model_Store $store ) {
		$this->store = $store;
	}

	public function register(): void {
		foreach ( $this->store->applied() as $model ) {
			foreach ( (array) ( $model['config']['custom_post_types'] ?? array() ) as $post_type ) {
				$key = sanitize_key( (string) ( $post_type['key'] ?? '' ) );
				if ( '' === $key || post_type_exists( $key ) ) {
					continue;
				}

				register_post_type(
					$key,
					apply_filters(
						'aica_register_post_type_args',
						array(
							'labels'        => array(
								'name'               => $post_type['plural_label'],
								'singular_name'      => $post_type['singular_label'],
								'menu_name'          => $post_type['plural_label'],
								'add_new_item'       => sprintf( __( 'Add New %s', 'ai-content-architect' ), $post_type['singular_label'] ),
								'edit_item'          => sprintf( __( 'Edit %s', 'ai-content-architect' ), $post_type['singular_label'] ),
								'view_item'          => sprintf( __( 'View %s', 'ai-content-architect' ), $post_type['singular_label'] ),
								'all_items'          => sprintf( __( 'All %s', 'ai-content-architect' ), $post_type['plural_label'] ),
								'search_items'       => sprintf( __( 'Search %s', 'ai-content-architect' ), $post_type['plural_label'] ),
								'not_found'          => __( 'No items found.', 'ai-content-architect' ),
								'not_found_in_trash' => __( 'No items found in Trash.', 'ai-content-architect' ),
							),
							'public'        => (bool) $post_type['public'],
							'show_ui'       => true,
							'show_in_menu'  => true,
							'show_in_rest'  => (bool) $post_type['show_in_rest'],
							'has_archive'   => (bool) $post_type['has_archive'],
							'hierarchical'  => (bool) $post_type['hierarchical'],
							'menu_icon'     => $post_type['menu_icon'],
							'menu_position' => $post_type['menu_position'],
							'supports'      => (array) $post_type['supports'],
							'rewrite'       => array( 'slug' => sanitize_title( $post_type['slug'] ) ),
						),
						$post_type,
						$model
					)
				);
			}
		}
	}
}
