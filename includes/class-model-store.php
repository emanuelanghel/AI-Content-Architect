<?php
/**
 * Option-backed model storage.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Model_Store {
	public function all(): array {
		$models = get_option( AICA_OPTION_MODELS, array() );
		return is_array( $models ) ? $models : array();
	}

	public function applied(): array {
		return array_values(
			array_filter(
				$this->all(),
				static function ( $model ) {
					return is_array( $model ) && 'applied' === ( $model['status'] ?? '' );
				}
			)
		);
	}

	public function get( string $id ): ?array {
		foreach ( $this->all() as $model ) {
			if ( is_array( $model ) && $id === ( $model['id'] ?? '' ) ) {
				return $model;
			}
		}

		return null;
	}

	public function save( array $config, string $status = 'draft', ?string $id = null ): array {
		$models = $this->all();
		$now    = current_time( 'mysql' );
		$id     = $id ? sanitize_key( $id ) : wp_generate_uuid4();
		$record = array(
			'id'         => $id,
			'name'       => sanitize_text_field( (string) ( $config['model_name'] ?? __( 'Untitled Model', 'ai-content-architect' ) ) ),
			'status'     => in_array( $status, array( 'draft', 'applied', 'disabled' ), true ) ? $status : 'draft',
			'config'     => $config,
			'created_at' => $now,
			'updated_at' => $now,
			'version'    => AICA_VERSION,
		);

		$found = false;
		foreach ( $models as $index => $model ) {
			if ( is_array( $model ) && $id === ( $model['id'] ?? '' ) ) {
				$record['created_at'] = $model['created_at'] ?? $now;
				$models[ $index ]    = $record;
				$found               = true;
				break;
			}
		}

		if ( ! $found ) {
			$models[] = $record;
		}

		update_option( AICA_OPTION_MODELS, array_values( $models ), false );
		return $record;
	}

	public function set_status( string $id, string $status ): bool {
		if ( ! in_array( $status, array( 'draft', 'applied', 'disabled' ), true ) ) {
			return false;
		}

		$models = $this->all();
		foreach ( $models as $index => $model ) {
			if ( is_array( $model ) && $id === ( $model['id'] ?? '' ) ) {
				$models[ $index ]['status']     = $status;
				$models[ $index ]['updated_at'] = current_time( 'mysql' );
				update_option( AICA_OPTION_MODELS, array_values( $models ), false );
				return true;
			}
		}

		return false;
	}

	public function delete( string $id ): bool {
		$models = array_values(
			array_filter(
				$this->all(),
				static function ( $model ) use ( $id ) {
					return ! is_array( $model ) || $id !== ( $model['id'] ?? '' );
				}
			)
		);

		update_option( AICA_OPTION_MODELS, $models, false );
		return true;
	}
}
