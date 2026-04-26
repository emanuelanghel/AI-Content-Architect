<?php
/**
 * AI provider contract.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface AI_Provider_Interface {
	/**
	 * Generate a content model configuration.
	 *
	 * @param string $user_prompt Natural language prompt.
	 * @return array
	 */
	public function generate_content_model( string $user_prompt ): array;
}
