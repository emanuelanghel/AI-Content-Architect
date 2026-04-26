<?php
/**
 * Uninstall cleanup.
 *
 * @package AIContentArchitect
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'aica_version' );
delete_option( 'aica_settings' );
delete_option( 'aica_content_models' );
