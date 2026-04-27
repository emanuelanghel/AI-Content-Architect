<?php
/**
 * Theme-compatible frontend field output.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend_Display {
	private Template_Manager $templates;

	public function __construct( Template_Manager $templates ) {
		$this->templates = $templates;
	}

	public function append_fields_to_content( string $content ): string {
		if ( is_admin() || is_feed() || ! is_singular() || ! in_the_loop() || ! is_main_query() || ! empty( $GLOBALS['aica_rendering_fallback_template'] ) ) {
			return $content;
		}

		$settings = wp_parse_args(
			get_option( AICA_OPTION_SETTINGS, array() ),
			array(
				'show_frontend_fields' => true,
			)
		);
		if ( empty( $settings['show_frontend_fields'] ) ) {
			return $content;
		}

		$post_type = get_post_type();
		$fields    = $this->templates->fields_for_post_type( (string) $post_type );

		if ( empty( $fields ) ) {
			return $content;
		}

		$output = $this->render_fields( get_the_ID(), $fields );
		$output .= $this->render_taxonomies( get_the_ID(), (string) $post_type );
		return '' === trim( $output ) ? $content : $content . '<div class="aica-content aica-content-injected">' . $output . '</div>';
	}

	public function render_fields( int $post_id, array $fields, int $limit = 0 ): string {
		$rows = '';
		$count = 0;

		foreach ( $fields as $field ) {
			if ( $limit > 0 && $count >= $limit ) {
				break;
			}

			$value = get_post_meta( $post_id, aica_meta_key( $field['key'] ), true );

			if ( ! $this->has_display_value( $value, $field ) ) {
				continue;
			}

			$formatted_value = $this->format_value( $value, $field );
			if ( '' === trim( wp_strip_all_tags( $formatted_value ) ) && false === strpos( $formatted_value, '<img' ) ) {
				continue;
			}

			$type = sanitize_html_class( (string) ( $field['type'] ?? 'text' ) );
			$span = in_array( $field['type'] ?? 'text', array( 'textarea', 'wysiwyg', 'gallery' ), true ) ? ' is-wide' : '';
			$rows .= '<div class="aica-content-field aica-content-field-' . esc_attr( $field['key'] ) . ' aica-content-field-type-' . esc_attr( $type ) . esc_attr( $span ) . '">';
			$rows .= '<div class="aica-content-field-label">' . esc_html( $this->field_label( $field ) ) . '</div>';
			$rows .= '<div class="aica-content-field-value">' . $formatted_value . '</div>';
			$rows .= '</div>';
			$count++;
		}

		if ( '' === $rows ) {
			return '';
		}

		return '<section class="aica-content-fields" aria-label="' . esc_attr__( 'Generated content details', 'ai-content-architect' ) . '"><div class="aica-content-section-heading"><h2>' . esc_html__( 'Details', 'ai-content-architect' ) . '</h2></div><div class="aica-content-fields-grid">' . $rows . '</div></section>';
	}

	public function render_taxonomies( int $post_id, string $post_type, bool $compact = false ): string {
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		$groups = '';

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! $taxonomy->public ) {
				continue;
			}

			$terms = get_the_terms( $post_id, $taxonomy->name );
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}

			$term_links = '';
			foreach ( $terms as $term ) {
				$link = get_term_link( $term );
				if ( is_wp_error( $link ) ) {
					$term_links .= '<span class="aica-content-term">' . esc_html( $term->name ) . '</span>';
				} else {
					$term_links .= '<a class="aica-content-term" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
				}
			}

			$groups .= '<div class="aica-content-taxonomy">';
			$groups .= '<div class="aica-content-taxonomy-label">' . esc_html( $taxonomy->labels->singular_name ?: $taxonomy->label ) . '</div>';
			$groups .= '<div class="aica-content-taxonomy-terms">' . $term_links . '</div>';
			$groups .= '</div>';
		}

		if ( '' === $groups ) {
			return '';
		}

		$heading = $compact ? '' : '<div class="aica-content-section-heading"><h2>' . esc_html__( 'Categories', 'ai-content-architect' ) . '</h2></div>';
		$class = $compact ? ' is-compact' : '';
		return '<section class="aica-content-taxonomies' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Generated content taxonomies', 'ai-content-architect' ) . '">' . $heading . $groups . '</section>';
	}

	public function render_template_suggestions( string $post_type ): string {
		$models = \AIContentArchitect\Plugin::instance()->store->applied();
		foreach ( $models as $model ) {
			foreach ( (array) ( $model['config']['templates'] ?? array() ) as $template ) {
				if ( $post_type !== (string) ( $template['post_type'] ?? '' ) ) {
					continue;
				}

				$layout = trim( (string) ( $template['single_layout'] ?? '' ) );
				if ( '' === $layout ) {
					return '';
				}

				return '<aside class="aica-content-template-note"><h2>' . esc_html__( 'Suggested Layout', 'ai-content-architect' ) . '</h2><p>' . esc_html( $layout ) . '</p></aside>';
			}
		}

		return '';
	}

	public function archive_fields_for_post( int $post_id, string $post_type, int $limit = 3 ): string {
		$fields = $this->templates->fields_for_post_type( $post_type );
		if ( empty( $fields ) ) {
			return '';
		}

		$items = '';
		$count = 0;
		foreach ( $fields as $field ) {
			if ( $count >= $limit ) {
				break;
			}

			$value = get_post_meta( $post_id, aica_meta_key( $field['key'] ), true );
			if ( ! $this->has_display_value( $value, $field ) ) {
				continue;
			}

			$display_value = trim( wp_strip_all_tags( $this->format_value( $value, $field ) ) );
			if ( '' === $display_value ) {
				continue;
			}

			$items .= '<div class="aica-content-card-field"><span class="aica-content-card-field-label">' . esc_html( $this->field_label( $field ) ) . '</span><span class="aica-content-card-field-value">' . esc_html( $display_value ) . '</span></div>';
			$count++;
		}

		return '' === $items ? '' : '<div class="aica-content-card-fields">' . $items . '</div>';
	}

	private function format_value( $value, array $field ): string {
		$type = $field['type'] ?? 'text';

		if ( 'checkbox' === $type ) {
			$label = $value ? __( 'Yes', 'ai-content-architect' ) : __( 'No', 'ai-content-architect' );
			$class = $value ? ' is-yes' : ' is-no';
			return '<span class="aica-content-badge' . esc_attr( $class ) . '">' . esc_html( $label ) . '</span>';
		}

		if ( 'url' === $type ) {
			$url = esc_url( (string) $value );
			return '' === $url ? '' : '<a href="' . $url . '" rel="nofollow">' . esc_html( $this->display_url( $url ) ) . '</a>';
		}

		if ( 'email' === $type ) {
			$email = sanitize_email( (string) $value );
			return '' === $email ? '' : '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
		}

		if ( 'date' === $type ) {
			$timestamp = strtotime( (string) $value );
			return $timestamp ? esc_html( date_i18n( get_option( 'date_format' ), $timestamp ) ) : esc_html( (string) $value );
		}

		if ( 'number' === $type ) {
			return is_numeric( $value ) ? esc_html( number_format_i18n( (float) $value ) ) : esc_html( (string) $value );
		}

		if ( 'image' === $type ) {
			return $this->format_image( absint( $value ) );
		}

		if ( 'gallery' === $type ) {
			return $this->format_gallery( (string) $value );
		}

		if ( 'wysiwyg' === $type ) {
			return wp_kses_post( (string) $value );
		}

		return esc_html( (string) $value );
	}

	private function has_display_value( $value, array $field ): bool {
		if ( 'checkbox' === ( $field['type'] ?? 'text' ) ) {
			return '' !== (string) $value;
		}

		return '' !== trim( (string) $value );
	}

	private function field_label( array $field ): string {
		$label = trim( (string) ( $field['label'] ?? '' ) );
		if ( '' !== $label ) {
			return $label;
		}

		return ucwords( str_replace( array( '-', '_' ), ' ', (string) ( $field['key'] ?? '' ) ) );
	}

	private function display_url( string $url ): string {
		$parts = wp_parse_url( $url );
		if ( empty( $parts['host'] ) ) {
			return $url;
		}

		$path = empty( $parts['path'] ) || '/' === $parts['path'] ? '' : untrailingslashit( $parts['path'] );
		return $parts['host'] . $path;
	}

	private function format_image( int $attachment_id ): string {
		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			return '';
		}

		$image = wp_get_attachment_image( $attachment_id, 'large', false, array( 'class' => 'aica-content-image' ) );
		return $image ? '<figure class="aica-content-image-wrap">' . $image . '</figure>' : '';
	}

	private function format_gallery( string $value ): string {
		$ids = array_filter( array_map( 'absint', explode( ',', $value ) ) );
		if ( empty( $ids ) ) {
			return '';
		}

		$images = '';
		foreach ( $ids as $attachment_id ) {
			if ( 'attachment' !== get_post_type( $attachment_id ) ) {
				continue;
			}

			$image = wp_get_attachment_image( $attachment_id, 'medium_large', false, array( 'class' => 'aica-content-gallery-image' ) );
			if ( $image ) {
				$images .= '<figure>' . $image . '</figure>';
			}
		}

		return '' === $images ? '' : '<div class="aica-content-gallery">' . $images . '</div>';
	}
}
