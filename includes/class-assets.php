<?php
/**
 * Admin assets.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	private ?Model_Store $store = null;

	public function __construct( ?Model_Store $store = null ) {
		$this->store = $store;
	}

	public function enqueue( string $hook_suffix ): void {
		if ( false === strpos( $hook_suffix, 'ai-content-architect' ) ) {
			return;
		}

		$admin_css = AICA_PATH . 'admin/css/admin.css';
		$admin_js  = AICA_PATH . 'admin/js/admin.js';

		wp_enqueue_style( 'aica-admin', AICA_URL . 'admin/css/admin.css', array(), $this->asset_version( $admin_css ) );
		wp_enqueue_script( 'aica-admin', AICA_URL . 'admin/js/admin.js', array( 'jquery' ), $this->asset_version( $admin_js ), true );
		wp_localize_script(
			'aica-admin',
			'aicaAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'aica_admin' ),
				'messages' => array(
					'confirmApply'             => __( 'Apply this content model? This registers the generated structure but does not create content unless sample content is checked.', 'ai-content-architect' ),
					'confirmDelete'            => __( 'Delete this model?', 'ai-content-architect' ),
					'deleteTitle'              => __( 'Delete model?', 'ai-content-architect' ),
					'deleteDescription'        => __( 'Choose whether to keep existing generated content or remove it with the model.', 'ai-content-architect' ),
					'deleteWarning'            => __( 'Deleting generated content removes posts and taxonomy terms for this model. Media files are kept.', 'ai-content-architect' ),
					'deleteModelOnly'          => __( 'Delete model only', 'ai-content-architect' ),
					'deleteModelWithContent'   => __( 'Delete model + content', 'ai-content-architect' ),
					'cancel'                   => __( 'Cancel', 'ai-content-architect' ),
					'working'                  => __( 'Working...', 'ai-content-architect' ),
				),
			)
		);
	}

	public function enqueue_frontend(): void {
		if ( ! $this->is_generated_frontend_view() ) {
			return;
		}

		$frontend_css = AICA_PATH . 'frontend/css/frontend.css';
		wp_enqueue_style( 'aica-frontend', AICA_URL . 'frontend/css/frontend.css', array(), $this->asset_version( $frontend_css ) );
	}

	private function asset_version( string $path ): string {
		return file_exists( $path ) ? AICA_VERSION . '-' . (string) filemtime( $path ) : AICA_VERSION;
	}

	private function is_generated_frontend_view(): bool {
		if ( is_admin() || ! $this->store ) {
			return false;
		}

		$post_type = '';
		if ( is_singular() ) {
			$post_type = (string) get_post_type();
		} elseif ( is_post_type_archive() ) {
			$query_post_type = get_query_var( 'post_type' );
			$post_type       = is_array( $query_post_type ) ? (string) reset( $query_post_type ) : (string) $query_post_type;
		}

		if ( '' === $post_type ) {
			return false;
		}

		foreach ( $this->store->applied() as $model ) {
			foreach ( (array) ( $model['config']['custom_post_types'] ?? array() ) as $item ) {
				if ( $post_type === (string) ( $item['key'] ?? '' ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
