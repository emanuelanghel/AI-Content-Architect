<?php
/**
 * Generated admin columns.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Columns {
	private Model_Store $store;

	public function __construct( Model_Store $store ) {
		$this->store = $store;
	}

	public function register_hooks(): void {
		foreach ( $this->columns_by_post_type() as $post_type => $columns ) {
			add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_columns' ) );
			add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
			add_filter( 'manage_edit-' . $post_type . '_sortable_columns', array( $this, 'sortable_columns' ) );
		}
		add_action( 'pre_get_posts', array( $this, 'handle_sorting' ) );
	}

	public function add_columns( array $columns ): array {
		$post_type = get_current_screen()->post_type ?? '';
		foreach ( $this->columns_by_post_type()[ $post_type ] ?? array() as $column ) {
			$columns[ 'aica_' . $column['key'] ] = $column['label'];
		}
		return $columns;
	}

	public function render_column( string $column_name, int $post_id ): void {
		if ( 0 !== strpos( $column_name, 'aica_' ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		foreach ( $this->columns_by_post_type()[ $post_type ] ?? array() as $column ) {
			if ( 'aica_' . $column['key'] !== $column_name ) {
				continue;
			}

			if ( 'taxonomy' === $column['source'] ) {
				$terms = get_the_terms( $post_id, $column['source_key'] );
				echo esc_html( $terms && ! is_wp_error( $terms ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '' );
				return;
			}

			if ( 'featured_image' === $column['source'] ) {
				echo get_the_post_thumbnail( $post_id, array( 48, 48 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				return;
			}

			echo esc_html( (string) get_post_meta( $post_id, aica_meta_key( $column['source_key'] ), true ) );
			return;
		}
	}

	public function sortable_columns( array $columns ): array {
		$post_type = get_current_screen()->post_type ?? '';
		foreach ( $this->columns_by_post_type()[ $post_type ] ?? array() as $column ) {
			if ( ! empty( $column['sortable'] ) && 'field' === $column['source'] ) {
				$columns[ 'aica_' . $column['key'] ] = 'aica_' . $column['source_key'];
			}
		}
		return $columns;
	}

	public function handle_sorting( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$orderby = sanitize_key( (string) $query->get( 'orderby' ) );
		if ( 0 === strpos( $orderby, 'aica_' ) ) {
			$query->set( 'meta_key', aica_meta_key( substr( $orderby, 5 ) ) );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	private function columns_by_post_type(): array {
		$grouped = array();
		foreach ( $this->store->applied() as $model ) {
			foreach ( (array) ( $model['config']['admin_columns'] ?? array() ) as $group ) {
				$post_type             = sanitize_key( (string) ( $group['post_type'] ?? '' ) );
				$grouped[ $post_type ] = array_merge( $grouped[ $post_type ] ?? array(), (array) ( $group['columns'] ?? array() ) );
			}
		}
		return $grouped;
	}
}
