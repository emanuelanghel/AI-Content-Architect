<?php
/**
 * Content model normalizer.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Content_Model {
	public static function defaults( array $config ): array {
		$config = wp_parse_args(
			$config,
			array(
				'model_name'        => '',
				'description'       => '',
				'intended_use_case' => '',
				'warnings'          => array(),
				'custom_post_types' => array(),
				'taxonomies'        => array(),
				'fields'            => array(),
				'admin_columns'     => array(),
				'templates'         => array(),
				'sample_content'    => array(),
			)
		);

		foreach ( $config['custom_post_types'] as $index => $post_type ) {
			$config['custom_post_types'][ $index ] = wp_parse_args(
				(array) $post_type,
				array(
					'key'            => '',
					'singular_label' => '',
					'plural_label'   => '',
					'slug'           => '',
					'menu_icon'      => 'dashicons-admin-post',
					'public'         => true,
					'has_archive'    => true,
					'show_in_rest'   => true,
					'hierarchical'   => false,
					'menu_position'  => null,
					'supports'       => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				)
			);
		}

		return $config;
	}
}
