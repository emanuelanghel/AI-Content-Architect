<?php
/**
 * Review partial.
 *
 * @package AIContentArchitect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$supports = array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'revisions' );
$cpt_count = count( (array) ( $config['custom_post_types'] ?? array() ) );
$tax_count = count( (array) ( $config['taxonomies'] ?? array() ) );
$field_count = count( (array) ( $config['fields'] ?? array() ) );
$warning_count = count( (array) ( $config['warnings'] ?? array() ) );
?>
<section class="aica-review aica-panel">
	<div class="aica-section-heading">
		<span class="aica-step">2</span>
		<div>
			<h2><?php esc_html_e( 'Review the generated model', 'ai-content-architect' ); ?></h2>
			<p><?php esc_html_e( 'Check structure, edit anything that feels off, then save a draft or apply it to WordPress.', 'ai-content-architect' ); ?></p>
		</div>
	</div>
	<textarea id="aica-config-json" class="aica-hidden-json"><?php echo esc_textarea( wp_json_encode( $config ) ); ?></textarea>

	<div class="aica-review-summary">
		<div>
			<span><?php esc_html_e( 'Custom Post Types', 'ai-content-architect' ); ?></span>
			<strong><?php echo esc_html( $cpt_count ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Taxonomies', 'ai-content-architect' ); ?></span>
			<strong><?php echo esc_html( $tax_count ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Fields', 'ai-content-architect' ); ?></span>
			<strong><?php echo esc_html( $field_count ); ?></strong>
		</div>
		<div class="<?php echo esc_attr( $warning_count ? 'has-warning' : '' ); ?>">
			<span><?php esc_html_e( 'Warnings', 'ai-content-architect' ); ?></span>
			<strong><?php echo esc_html( $warning_count ); ?></strong>
		</div>
	</div>

	<nav class="aica-review-nav" aria-label="<?php echo esc_attr__( 'Review sections', 'ai-content-architect' ); ?>">
		<a href="#aica-overview"><?php esc_html_e( 'Overview', 'ai-content-architect' ); ?></a>
		<a href="#aica-cpts"><?php esc_html_e( 'CPTs', 'ai-content-architect' ); ?></a>
		<a href="#aica-taxonomies"><?php esc_html_e( 'Taxonomies', 'ai-content-architect' ); ?></a>
		<a href="#aica-fields"><?php esc_html_e( 'Fields', 'ai-content-architect' ); ?></a>
		<a href="#aica-display"><?php esc_html_e( 'Display', 'ai-content-architect' ); ?></a>
		<a href="#aica-sample"><?php esc_html_e( 'Sample', 'ai-content-architect' ); ?></a>
	</nav>

	<div class="aica-card" id="aica-overview">
		<h3><?php esc_html_e( 'Overview', 'ai-content-architect' ); ?></h3>
		<label><?php esc_html_e( 'Model name', 'ai-content-architect' ); ?><input type="text" class="regular-text aica-config-input" data-path="model_name" value="<?php echo esc_attr( $config['model_name'] ?? '' ); ?>"></label>
		<label><?php esc_html_e( 'Description', 'ai-content-architect' ); ?><textarea class="large-text aica-config-input" data-path="description"><?php echo esc_textarea( $config['description'] ?? '' ); ?></textarea></label>
		<label><?php esc_html_e( 'Intended use case', 'ai-content-architect' ); ?><textarea class="large-text aica-config-input" data-path="intended_use_case"><?php echo esc_textarea( $config['intended_use_case'] ?? '' ); ?></textarea></label>
		<?php if ( ! empty( $config['warnings'] ) ) : ?>
			<ul class="aica-warnings">
				<?php foreach ( (array) $config['warnings'] as $warning ) : ?>
					<li><?php echo esc_html( $warning ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<div class="aica-grid">
		<div class="aica-card" id="aica-cpts">
			<h3><?php esc_html_e( 'Custom Post Types', 'ai-content-architect' ); ?></h3>
			<?php foreach ( (array) ( $config['custom_post_types'] ?? array() ) as $i => $post_type ) : ?>
				<div class="aica-subcard">
					<label><?php esc_html_e( 'Singular label', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="custom_post_types.<?php echo esc_attr( $i ); ?>.singular_label" value="<?php echo esc_attr( $post_type['singular_label'] ?? '' ); ?>"></label>
					<label><?php esc_html_e( 'Plural label', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="custom_post_types.<?php echo esc_attr( $i ); ?>.plural_label" value="<?php echo esc_attr( $post_type['plural_label'] ?? '' ); ?>"></label>
					<label><?php esc_html_e( 'Key', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="custom_post_types.<?php echo esc_attr( $i ); ?>.key" value="<?php echo esc_attr( $post_type['key'] ?? '' ); ?>"></label>
					<label><?php esc_html_e( 'Slug', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="custom_post_types.<?php echo esc_attr( $i ); ?>.slug" value="<?php echo esc_attr( $post_type['slug'] ?? '' ); ?>"></label>
					<label><?php esc_html_e( 'Menu icon', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="custom_post_types.<?php echo esc_attr( $i ); ?>.menu_icon" value="<?php echo esc_attr( $post_type['menu_icon'] ?? '' ); ?>"></label>
					<div class="aica-checks">
						<?php foreach ( array( 'public', 'has_archive', 'show_in_rest', 'hierarchical' ) as $flag ) : ?>
							<label><input type="checkbox" class="aica-config-input" data-type="boolean" data-path="custom_post_types.<?php echo esc_attr( $i ); ?>.<?php echo esc_attr( $flag ); ?>" <?php checked( ! empty( $post_type[ $flag ] ) ); ?>> <?php echo esc_html( str_replace( '_', ' ', $flag ) ); ?></label>
						<?php endforeach; ?>
					</div>
					<div class="aica-checks">
						<strong><?php esc_html_e( 'Supports', 'ai-content-architect' ); ?></strong>
						<?php foreach ( $supports as $support ) : ?>
							<label><input type="checkbox" class="aica-config-array" data-path="custom_post_types.<?php echo esc_attr( $i ); ?>.supports" value="<?php echo esc_attr( $support ); ?>" <?php checked( in_array( $support, (array) ( $post_type['supports'] ?? array() ), true ) ); ?>> <?php echo esc_html( $support ); ?></label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="aica-card" id="aica-taxonomies">
			<h3><?php esc_html_e( 'Taxonomies', 'ai-content-architect' ); ?></h3>
			<?php foreach ( (array) ( $config['taxonomies'] ?? array() ) as $i => $taxonomy ) : ?>
				<div class="aica-subcard">
					<label><?php esc_html_e( 'Singular label', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="taxonomies.<?php echo esc_attr( $i ); ?>.singular_label" value="<?php echo esc_attr( $taxonomy['singular_label'] ?? '' ); ?>"></label>
					<label><?php esc_html_e( 'Plural label', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="taxonomies.<?php echo esc_attr( $i ); ?>.plural_label" value="<?php echo esc_attr( $taxonomy['plural_label'] ?? '' ); ?>"></label>
					<label><?php esc_html_e( 'Key', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="taxonomies.<?php echo esc_attr( $i ); ?>.key" value="<?php echo esc_attr( $taxonomy['key'] ?? '' ); ?>"></label>
					<label><?php esc_html_e( 'Slug', 'ai-content-architect' ); ?><input class="aica-config-input" data-path="taxonomies.<?php echo esc_attr( $i ); ?>.slug" value="<?php echo esc_attr( $taxonomy['slug'] ?? '' ); ?>"></label>
					<label><?php esc_html_e( 'Attached post types', 'ai-content-architect' ); ?><input class="aica-config-csv" data-path="taxonomies.<?php echo esc_attr( $i ); ?>.post_types" value="<?php echo esc_attr( implode( ',', (array) ( $taxonomy['post_types'] ?? array() ) ) ); ?>"></label>
					<div class="aica-checks">
						<label><input type="checkbox" class="aica-config-input" data-type="boolean" data-path="taxonomies.<?php echo esc_attr( $i ); ?>.hierarchical" <?php checked( ! empty( $taxonomy['hierarchical'] ) ); ?>> <?php esc_html_e( 'Hierarchical', 'ai-content-architect' ); ?></label>
						<label><input type="checkbox" class="aica-config-input" data-type="boolean" data-path="taxonomies.<?php echo esc_attr( $i ); ?>.public" <?php checked( ! empty( $taxonomy['public'] ) ); ?>> <?php esc_html_e( 'Public', 'ai-content-architect' ); ?></label>
						<label><input type="checkbox" class="aica-config-input" data-type="boolean" data-path="taxonomies.<?php echo esc_attr( $i ); ?>.show_in_rest" <?php checked( ! empty( $taxonomy['show_in_rest'] ) ); ?>> <?php esc_html_e( 'Show in REST', 'ai-content-architect' ); ?></label>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="aica-card" id="aica-fields">
		<h3><?php esc_html_e( 'Custom Fields', 'ai-content-architect' ); ?></h3>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Label', 'ai-content-architect' ); ?></th><th><?php esc_html_e( 'Key', 'ai-content-architect' ); ?></th><th><?php esc_html_e( 'Type', 'ai-content-architect' ); ?></th><th><?php esc_html_e( 'Post Type', 'ai-content-architect' ); ?></th><th><?php esc_html_e( 'Required', 'ai-content-architect' ); ?></th><th><?php esc_html_e( 'Options', 'ai-content-architect' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( (array) ( $config['fields'] ?? array() ) as $i => $field ) : ?>
					<tr>
						<td><input class="aica-config-input" data-path="fields.<?php echo esc_attr( $i ); ?>.label" value="<?php echo esc_attr( $field['label'] ?? '' ); ?>"></td>
						<td><input class="aica-config-input" data-path="fields.<?php echo esc_attr( $i ); ?>.key" value="<?php echo esc_attr( $field['key'] ?? '' ); ?>"></td>
						<td><select class="aica-config-input" data-path="fields.<?php echo esc_attr( $i ); ?>.type"><?php foreach ( array( 'text', 'textarea', 'number', 'email', 'url', 'date', 'checkbox', 'select', 'radio', 'image', 'gallery', 'wysiwyg' ) as $type ) : ?><option value="<?php echo esc_attr( $type ); ?>" <?php selected( $field['type'] ?? '', $type ); ?>><?php echo esc_html( $type ); ?></option><?php endforeach; ?></select></td>
						<td><input class="aica-config-input" data-path="fields.<?php echo esc_attr( $i ); ?>.post_type" value="<?php echo esc_attr( $field['post_type'] ?? '' ); ?>"></td>
						<td><input type="checkbox" class="aica-config-input" data-type="boolean" data-path="fields.<?php echo esc_attr( $i ); ?>.required" <?php checked( ! empty( $field['required'] ) ); ?>></td>
						<td><input class="aica-config-csv" data-path="fields.<?php echo esc_attr( $i ); ?>.options" value="<?php echo esc_attr( implode( ',', (array) ( $field['options'] ?? array() ) ) ); ?>"></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="aica-grid">
		<div class="aica-card" id="aica-display">
			<h3><?php esc_html_e( 'Admin Columns', 'ai-content-architect' ); ?></h3>
			<?php foreach ( (array) ( $config['admin_columns'] ?? array() ) as $group ) : ?>
				<p><strong><?php echo esc_html( $group['post_type'] ?? '' ); ?></strong>: <?php echo esc_html( implode( ', ', wp_list_pluck( (array) ( $group['columns'] ?? array() ), 'label' ) ) ); ?></p>
			<?php endforeach; ?>
		</div>
		<div class="aica-card">
			<h3><?php esc_html_e( 'Template Suggestions', 'ai-content-architect' ); ?></h3>
			<?php foreach ( (array) ( $config['templates'] ?? array() ) as $template ) : ?>
				<p><strong><?php echo esc_html( $template['post_type'] ?? '' ); ?></strong></p>
				<p><?php echo esc_html( $template['single_layout'] ?? '' ); ?></p>
				<p><?php echo esc_html( $template['archive_layout'] ?? '' ); ?></p>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="aica-card" id="aica-sample">
		<h3><?php esc_html_e( 'Sample Content', 'ai-content-architect' ); ?></h3>
		<label><input type="checkbox" id="aica-generate-sample"> <?php esc_html_e( 'Generate sample content when applying', 'ai-content-architect' ); ?></label>
		<label><?php esc_html_e( 'Sample post limit', 'ai-content-architect' ); ?><input type="number" id="aica-sample-count" value="3" min="1" max="20"></label>
	</div>

	<div class="aica-sticky-actions">
		<div>
			<strong><?php esc_html_e( 'Ready to continue?', 'ai-content-architect' ); ?></strong>
			<span><?php esc_html_e( 'Save a draft for later or apply this model to register the generated WordPress structures.', 'ai-content-architect' ); ?></span>
		</div>
		<p class="aica-actions">
			<button type="button" class="button button-secondary" id="aica-save-draft"><?php esc_html_e( 'Save Draft Model', 'ai-content-architect' ); ?></button>
			<button type="button" class="button button-primary" id="aica-apply-model"><?php esc_html_e( 'Apply Content Model', 'ai-content-architect' ); ?></button>
			<button type="button" class="button" id="aica-regenerate"><?php esc_html_e( 'Regenerate', 'ai-content-architect' ); ?></button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aica-models' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'ai-content-architect' ); ?></a>
		</p>
	</div>
</section>
