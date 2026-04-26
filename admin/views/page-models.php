<?php
/**
 * Models list screen.
 *
 * @package AIContentArchitect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap aica-wrap">
	<div class="aica-app-shell">
		<aside class="aica-sidebar" aria-label="<?php echo esc_attr__( 'AI Content Architect navigation', 'ai-content-architect' ); ?>">
			<div class="aica-brand">
				<span class="aica-brand-mark"><img src="<?php echo esc_url( AICA_URL . 'admin/images/ai-logo.png' ); ?>" alt="<?php echo esc_attr__( 'AI Content Architect', 'ai-content-architect' ); ?>"></span>
				<div>
					<strong><?php esc_html_e( 'Content Architect', 'ai-content-architect' ); ?></strong>
					<span><?php esc_html_e( 'Model builder', 'ai-content-architect' ); ?></span>
				</div>
			</div>
			<nav class="aica-side-nav">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ai-content-architect' ) ); ?>"><span class="dashicons dashicons-edit-page"></span><?php esc_html_e( 'Architect', 'ai-content-architect' ); ?></a>
				<a class="is-active" href="<?php echo esc_url( admin_url( 'admin.php?page=aica-models' ) ); ?>"><span class="dashicons dashicons-database"></span><?php esc_html_e( 'Content Models', 'ai-content-architect' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aica-settings' ) ); ?>"><span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'Settings', 'ai-content-architect' ); ?></a>
			</nav>
			<div class="aica-sidebar-note">
				<strong><?php esc_html_e( 'Reversible models', 'ai-content-architect' ); ?></strong>
				<span><?php esc_html_e( 'Disable registration without deleting existing posts.', 'ai-content-architect' ); ?></span>
			</div>
		</aside>
		<main class="aica-main-panel">
			<div class="aica-page-header">
				<div>
					<p class="aica-kicker"><?php esc_html_e( 'Saved architectures', 'ai-content-architect' ); ?></p>
					<h1><?php esc_html_e( 'Content Models', 'ai-content-architect' ); ?></h1>
					<p class="description"><?php esc_html_e( 'Manage draft, applied, and disabled content models. Disabling stops registration but keeps existing content.', 'ai-content-architect' ); ?></p>
				</div>
				<div class="aica-header-actions">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ai-content-architect' ) ); ?>"><?php esc_html_e( 'Create New Model', 'ai-content-architect' ); ?></a>
				</div>
			</div>

			<div id="aica-notice-area"></div>

			<?php if ( empty( $models ) ) : ?>
				<div class="aica-empty-state">
					<h2><?php esc_html_e( 'No content models yet', 'ai-content-architect' ); ?></h2>
					<p><?php esc_html_e( 'Start with a plain-English prompt and generate your first editable architecture.', 'ai-content-architect' ); ?></p>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ai-content-architect' ) ); ?>"><?php esc_html_e( 'Create your first model', 'ai-content-architect' ); ?></a>
				</div>
			<?php else : ?>
				<div class="aica-table-scroll">
					<table class="widefat striped aica-models-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Model name', 'ai-content-architect' ); ?></th>
								<th><?php esc_html_e( 'Status', 'ai-content-architect' ); ?></th>
								<th><?php esc_html_e( 'CPTs', 'ai-content-architect' ); ?></th>
								<th><?php esc_html_e( 'Taxonomies', 'ai-content-architect' ); ?></th>
								<th><?php esc_html_e( 'Fields', 'ai-content-architect' ); ?></th>
								<th><?php esc_html_e( 'Modified', 'ai-content-architect' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'ai-content-architect' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $models as $model ) : ?>
								<?php $status = sanitize_html_class( (string) $model['status'] ); ?>
								<tr data-model-id="<?php echo esc_attr( $model['id'] ); ?>">
									<td>
										<strong><?php echo esc_html( $model['name'] ); ?></strong>
										<span class="aica-muted"><?php echo esc_html( $model['created_at'] ); ?></span>
									</td>
									<td><span class="aica-status-badge aica-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $model['status'] ); ?></span></td>
									<td><?php echo esc_html( count( (array) ( $model['config']['custom_post_types'] ?? array() ) ) ); ?></td>
									<td><?php echo esc_html( count( (array) ( $model['config']['taxonomies'] ?? array() ) ) ); ?></td>
									<td><?php echo esc_html( count( (array) ( $model['config']['fields'] ?? array() ) ) ); ?></td>
									<td><?php echo esc_html( $model['updated_at'] ); ?></td>
									<td class="aica-row-actions">
										<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'ai-content-architect', 'model_id' => $model['id'] ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Edit', 'ai-content-architect' ); ?></a>
										<button class="button button-small button-primary aica-row-apply"><?php esc_html_e( 'Apply', 'ai-content-architect' ); ?></button>
										<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'aica_export_model', 'model_id' => $model['id'] ), admin_url( 'admin-post.php' ) ), 'aica_admin' ) ); ?>"><?php esc_html_e( 'Export', 'ai-content-architect' ); ?></a>
										<button class="button button-small aica-row-disable"><?php esc_html_e( 'Disable', 'ai-content-architect' ); ?></button>
										<button class="button button-small button-link-delete aica-row-delete"><?php esc_html_e( 'Delete', 'ai-content-architect' ); ?></button>
										<textarea class="aica-row-config"><?php echo esc_textarea( wp_json_encode( $model['config'] ) ); ?></textarea>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<div class="aica-panel">
				<h2><?php esc_html_e( 'Import Model JSON', 'ai-content-architect' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'aica_admin' ); ?>
					<input type="hidden" name="action" value="aica_import_model">
					<textarea name="import_json" rows="8" class="large-text code"></textarea>
					<p><button type="submit" class="button"><?php esc_html_e( 'Import as Draft', 'ai-content-architect' ); ?></button></p>
				</form>
			</div>
		</main>
	</div>
</div>
