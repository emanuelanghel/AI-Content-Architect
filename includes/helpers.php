<?php
/**
 * Shared helper functions.
 *
 * @package AIContentArchitect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aica_array_get' ) ) {
	/**
	 * Safely read an array key.
	 *
	 * @param array  $array   Source array.
	 * @param string $key     Key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	function aica_array_get( array $array, string $key, $default = null ) {
		return array_key_exists( $key, $array ) ? $array[ $key ] : $default;
	}
}

if ( ! function_exists( 'aica_meta_key' ) ) {
	/**
	 * Prefix a generated field key for storage.
	 *
	 * @param string $field_key Field key.
	 * @return string
	 */
	function aica_meta_key( string $field_key ): string {
		return '_aica_' . sanitize_key( $field_key );
	}
}

if ( ! function_exists( 'aica_json_response_error' ) ) {
	/**
	 * Return an AJAX JSON error consistently.
	 *
	 * @param string $message Error message.
	 * @param array  $data    Extra data.
	 * @return void
	 */
	function aica_json_response_error( string $message, array $data = array() ): void {
		wp_send_json_error(
			array_merge(
				array(
					'message' => $message,
				),
				$data
			)
		);
	}
}
