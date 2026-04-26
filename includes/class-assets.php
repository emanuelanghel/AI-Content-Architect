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
	public function enqueue( string $hook_suffix ): void {
		if ( false === strpos( $hook_suffix, 'ai-content-architect' ) ) {
			return;
		}

		wp_enqueue_style( 'aica-admin', AICA_URL . 'admin/css/admin.css', array(), AICA_VERSION );
		wp_enqueue_script( 'aica-admin', AICA_URL . 'admin/js/admin.js', array( 'jquery' ), AICA_VERSION, true );
		wp_localize_script(
			'aica-admin',
			'aicaAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'aica_admin' ),
				'messages' => array(
					'confirmApply'  => __( 'Apply this content model? This registers the generated structure but does not create content unless sample content is checked.', 'ai-content-architect' ),
					'confirmDelete' => __( 'Delete this model? Existing content will not be deleted.', 'ai-content-architect' ),
					'working'       => __( 'Working...', 'ai-content-architect' ),
				),
			)
		);
	}
}
