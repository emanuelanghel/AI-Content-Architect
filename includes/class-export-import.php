<?php
/**
 * Export/import helpers.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Export_Import {
	private Model_Store $store;
	private Schema_Validator $validator;

	public function __construct( Model_Store $store, Schema_Validator $validator ) {
		$this->store     = $store;
		$this->validator = $validator;
	}

	public function export_model( string $id ): ?array {
		$model = $this->store->get( $id );
		return $model['config'] ?? null;
	}

	public function import_model( string $json ): array {
		$config = json_decode( $json, true );
		if ( ! is_array( $config ) ) {
			return array( 'valid' => false, 'errors' => array( __( 'Invalid JSON.', 'ai-content-architect' ) ) );
		}

		$result = $this->validator->validate( $config, false );
		if ( ! $result['valid'] ) {
			return $result;
		}

		$model = $this->store->save( $result['config'], 'draft' );
		return array( 'valid' => true, 'model' => $model, 'warnings' => $result['warnings'] );
	}
}
