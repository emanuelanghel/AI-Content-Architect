<?php
/**
 * Capability helpers.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capabilities {
	public const MANAGE = 'manage_options';

	public static function current_user_can_manage(): bool {
		return current_user_can( self::MANAGE );
	}
}
