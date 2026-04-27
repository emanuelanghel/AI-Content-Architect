<?php
/**
 * Optional AI provider model discovery contract.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface AI_Model_Provider_Interface {
	/**
	 * List available models for the configured provider account.
	 *
	 * @return array
	 */
	public function list_models(): array;

	/**
	 * Test provider connectivity.
	 *
	 * @return array
	 */
	public function test_connection(): array;
}
