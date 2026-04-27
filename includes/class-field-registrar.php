<?php
/**
 * Registers and renders generated post meta fields.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Field_Registrar {
	private Model_Store $store;

	public function __construct( Model_Store $store ) {
		$this->store = $store;
	}

	public function register_meta(): void {
		foreach ( $this->fields_by_post_type() as $post_type => $fields ) {
			foreach ( $fields as $field ) {
				register_post_meta(
					$post_type,
					aica_meta_key( $field['key'] ),
					array(
						'type'              => $this->meta_type( $field['type'] ),
						'single'            => true,
						'show_in_rest'      => true,
						'sanitize_callback' => function ( $value ) use ( $field ) {
							return $this->sanitize_field_value( $value, $field );
						},
						'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
							return current_user_can( 'edit_post', (int) $post_id );
						},
					)
				);
			}
		}
	}

	public function register_meta_box_hooks(): void {
		foreach ( array_keys( $this->fields_by_post_type() ) as $post_type ) {
			add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_meta_box_for_post_type' ) );
		}

		add_filter( 'default_hidden_meta_boxes', array( $this, 'keep_meta_boxes_visible' ), 10, 2 );
	}

	public function add_meta_boxes(): void {
		foreach ( $this->fields_by_post_type() as $post_type => $fields ) {
			$this->add_meta_box( $post_type, $fields );
		}
	}

	public function add_meta_box_for_post_type( \WP_Post $post ): void {
		$post_type = $post->post_type;
		$fields    = $this->fields_by_post_type()[ $post_type ] ?? array();

		if ( ! empty( $fields ) ) {
			$this->add_meta_box( $post_type, $fields );
		}
	}

	public function keep_meta_boxes_visible( array $hidden, \WP_Screen $screen ): array {
		if ( isset( $this->fields_by_post_type()[ $screen->post_type ?? '' ] ) ) {
			foreach ( array_keys( $this->fields_by_post_type() ) as $post_type ) {
				$hidden = array_diff( $hidden, array( 'aica_fields_' . $post_type ) );
			}
		}

		return array_values( $hidden );
	}

	public function render_meta_box( \WP_Post $post, array $box ): void {
		$fields = (array) ( $box['args']['fields'] ?? array() );
		wp_nonce_field( 'aica_save_fields_' . $post->post_type, 'aica_fields_nonce' );

		echo '<div class="aica-meta-fields">';
		foreach ( $fields as $field ) {
			$key       = aica_meta_key( $field['key'] );
			$value     = get_post_meta( $post->ID, $key, true );
			$field_id  = 'aica_field_' . esc_attr( $field['key'] );
			$type      = $field['type'];
			$options   = (array) ( $field['options'] ?? array() );
			$help_text = (string) ( $field['help_text'] ?? '' );

			echo '<p class="aica-field">';
			echo '<label for="' . esc_attr( $field_id ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label>';

			if ( 'textarea' === $type ) {
				echo '<textarea id="' . esc_attr( $field_id ) . '" name="aica_fields[' . esc_attr( $field['key'] ) . ']" rows="4" class="large-text" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . esc_textarea( $value ) . '</textarea>';
			} elseif ( 'wysiwyg' === $type ) {
				wp_editor( wp_kses_post( $value ), $field_id, array( 'textarea_name' => 'aica_fields[' . esc_attr( $field['key'] ) . ']', 'textarea_rows' => 6 ) );
			} elseif ( 'select' === $type ) {
				echo '<select id="' . esc_attr( $field_id ) . '" name="aica_fields[' . esc_attr( $field['key'] ) . ']">';
				echo '<option value="">' . esc_html__( 'Select...', 'ai-content-architect' ) . '</option>';
				foreach ( $options as $option ) {
					echo '<option value="' . esc_attr( $option ) . '"' . selected( $value, $option, false ) . '>' . esc_html( $option ) . '</option>';
				}
				echo '</select>';
			} elseif ( 'radio' === $type ) {
				foreach ( $options as $option ) {
					echo '<label class="aica-choice"><input type="radio" name="aica_fields[' . esc_attr( $field['key'] ) . ']" value="' . esc_attr( $option ) . '"' . checked( $value, $option, false ) . '> ' . esc_html( $option ) . '</label>';
				}
			} elseif ( 'checkbox' === $type ) {
				echo '<label><input type="checkbox" id="' . esc_attr( $field_id ) . '" name="aica_fields[' . esc_attr( $field['key'] ) . ']" value="1"' . checked( (bool) $value, true, false ) . '> ' . esc_html__( 'Enabled', 'ai-content-architect' ) . '</label>';
			} else {
				$input_type = in_array( $type, array( 'number', 'email', 'url', 'date' ), true ) ? $type : 'text';
				echo '<input id="' . esc_attr( $field_id ) . '" type="' . esc_attr( $input_type ) . '" name="aica_fields[' . esc_attr( $field['key'] ) . ']" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="' . esc_attr( $field['placeholder'] ) . '">';
				if ( in_array( $type, array( 'image', 'gallery' ), true ) ) {
					echo '<span class="description"> ' . esc_html__( 'Enter attachment ID(s). Gallery accepts comma-separated IDs.', 'ai-content-architect' ) . '</span>';
				}
			}

			if ( '' !== $help_text ) {
				echo '<span class="description">' . esc_html( $help_text ) . '</span>';
			}
			echo '</p>';
		}
		echo '</div>';
	}

	public function save_post( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['aica_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aica_fields_nonce'] ) ), 'aica_save_fields_' . $post->post_type ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = $this->fields_by_post_type()[ $post->post_type ] ?? array();
		$input  = isset( $_POST['aica_fields'] ) && is_array( $_POST['aica_fields'] ) ? wp_unslash( $_POST['aica_fields'] ) : array();

		foreach ( $fields as $field ) {
			$value = $input[ $field['key'] ] ?? ( 'checkbox' === $field['type'] ? false : '' );
			update_post_meta( $post_id, aica_meta_key( $field['key'] ), $this->sanitize_field_value( $value, $field ) );
		}
	}

	public function fields_by_post_type(): array {
		$grouped = array();
		foreach ( $this->store->applied() as $model ) {
			foreach ( (array) ( $model['config']['fields'] ?? array() ) as $field ) {
				$post_type             = sanitize_key( (string) $field['post_type'] );
				$grouped[ $post_type ] = $grouped[ $post_type ] ?? array();
				$grouped[ $post_type ][] = $field;
			}
		}
		return $grouped;
	}

	public function sanitize_field_value( $value, array $field ) {
		$type = $field['type'] ?? 'text';
		if ( 'checkbox' === $type ) {
			return $value ? 1 : 0;
		}
		if ( 'number' === $type ) {
			return is_numeric( $value ) ? $value + 0 : '';
		}
		if ( 'email' === $type ) {
			return sanitize_email( (string) $value );
		}
		if ( 'url' === $type ) {
			return esc_url_raw( (string) $value );
		}
		if ( 'textarea' === $type ) {
			return sanitize_textarea_field( (string) $value );
		}
		if ( 'wysiwyg' === $type ) {
			return wp_kses_post( (string) $value );
		}
		if ( in_array( $type, array( 'select', 'radio' ), true ) ) {
			$value   = sanitize_text_field( (string) $value );
			$options = (array) ( $field['options'] ?? array() );
			return in_array( $value, $options, true ) ? $value : '';
		}
		if ( 'gallery' === $type ) {
			$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
			return implode( ',', $ids );
		}
		if ( 'image' === $type ) {
			return absint( $value );
		}
		return sanitize_text_field( (string) $value );
	}

	private function meta_type( string $type ): string {
		if ( 'checkbox' === $type ) {
			return 'boolean';
		}
		if ( 'number' === $type ) {
			return 'number';
		}
		return 'string';
	}

	private function add_meta_box( string $post_type, array $fields ): void {
		add_meta_box(
			'aica_fields_' . $post_type,
			__( 'AI Content Architect Fields', 'ai-content-architect' ),
			array( $this, 'render_meta_box' ),
			$post_type,
			'normal',
			'high',
			array(
				'fields'                          => $fields,
				'__block_editor_compatible_meta_box' => true,
			)
		);
	}
}
