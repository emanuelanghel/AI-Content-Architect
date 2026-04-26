<?php
/**
 * Settings screen.
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
				<span class="aica-brand-mark">AI</span>
				<div>
					<strong><?php esc_html_e( 'Content Architect', 'ai-content-architect' ); ?></strong>
					<span><?php esc_html_e( 'Model builder', 'ai-content-architect' ); ?></span>
				</div>
			</div>
			<nav class="aica-side-nav">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ai-content-architect' ) ); ?>"><span class="dashicons dashicons-edit-page"></span><?php esc_html_e( 'Architect', 'ai-content-architect' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aica-models' ) ); ?>"><span class="dashicons dashicons-database"></span><?php esc_html_e( 'Content Models', 'ai-content-architect' ); ?></a>
				<a class="is-active" href="<?php echo esc_url( admin_url( 'admin.php?page=aica-settings' ) ); ?>"><span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'Settings', 'ai-content-architect' ); ?></a>
			</nav>
			<div class="aica-sidebar-note">
				<strong><?php esc_html_e( 'Provider control', 'ai-content-architect' ); ?></strong>
				<span><?php esc_html_e( 'Use mock mode locally, then switch providers when an API key is available.', 'ai-content-architect' ); ?></span>
			</div>
		</aside>
		<main class="aica-main-panel">
			<div class="aica-page-header">
				<div>
					<p class="aica-kicker"><?php esc_html_e( 'Provider and safety', 'ai-content-architect' ); ?></p>
					<h1><?php esc_html_e( 'Settings', 'ai-content-architect' ); ?></h1>
					<p class="description"><?php esc_html_e( 'Configure generation, validation, and frontend display behavior.', 'ai-content-architect' ); ?></p>
				</div>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="aica-panel">
				<?php wp_nonce_field( 'aica_admin' ); ?>
				<input type="hidden" name="action" value="aica_save_settings">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="aica_provider"><?php esc_html_e( 'AI Provider', 'ai-content-architect' ); ?></label></th>
						<td>
							<select id="aica_provider" name="provider">
								<option value="mock" <?php selected( $settings['provider'], 'mock' ); ?>><?php esc_html_e( 'Mock provider (development)', 'ai-content-architect' ); ?></option>
								<option value="openai" <?php selected( $settings['provider'], 'openai' ); ?>><?php esc_html_e( 'OpenAI-compatible', 'ai-content-architect' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Use the mock provider to test the full workflow without an API key. OpenAI-compatible mode calls the configured HTTP endpoint.', 'ai-content-architect' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aica_api_key"><?php esc_html_e( 'API Key', 'ai-content-architect' ); ?></label></th>
						<td>
							<input id="aica_api_key" type="password" name="api_key" value="" class="regular-text" autocomplete="new-password">
							<?php if ( ! empty( $settings['api_key'] ) ) : ?>
								<p class="description"><?php esc_html_e( 'An API key is saved. It is not displayed here.', 'ai-content-architect' ); ?></p>
								<label><input type="checkbox" name="clear_api_key" value="1"> <?php esc_html_e( 'Clear saved API key', 'ai-content-architect' ); ?></label>
							<?php endif; ?>
							<p class="description"><?php esc_html_e( 'Saved keys are never printed back into this screen.', 'ai-content-architect' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aica_base_url"><?php esc_html_e( 'Provider Base URL', 'ai-content-architect' ); ?></label></th>
						<td>
							<input id="aica_base_url" type="url" name="base_url" value="<?php echo esc_attr( $settings['base_url'] ); ?>" class="regular-text">
							<p class="description"><?php esc_html_e( 'Default is the OpenAI chat completions endpoint. Change this for compatible gateways.', 'ai-content-architect' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aica_model"><?php esc_html_e( 'Model', 'ai-content-architect' ); ?></label></th>
						<td>
							<input id="aica_model" type="text" name="model" value="<?php echo esc_attr( $settings['model'] ); ?>" class="regular-text" placeholder="gpt-4.1-mini">
							<p class="description"><?php esc_html_e( 'The plugin does not lock you to one model name.', 'ai-content-architect' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Safety Settings', 'ai-content-architect' ); ?></th>
						<td>
							<label><input type="checkbox" name="strict_validation" value="1" <?php checked( ! empty( $settings['strict_validation'] ) ); ?>> <?php esc_html_e( 'Enable strict validation', 'ai-content-architect' ); ?></label><br>
							<label><input type="checkbox" name="prevent_slug_conflicts" value="1" <?php checked( ! empty( $settings['prevent_slug_conflicts'] ) ); ?>> <?php esc_html_e( 'Prevent slug conflicts', 'ai-content-architect' ); ?></label><br>
							<label><input type="checkbox" checked disabled> <?php esc_html_e( 'Require review before apply', 'ai-content-architect' ); ?></label><br>
							<label><input type="checkbox" name="show_frontend_fields" value="1" <?php checked( ! empty( $settings['show_frontend_fields'] ) ); ?>> <?php esc_html_e( 'Append generated fields to single content', 'ai-content-architect' ); ?></label><br>
							<label><input type="checkbox" name="enable_templates" value="1" <?php checked( ! empty( $settings['enable_templates'] ) ); ?>> <?php esc_html_e( 'Enable simple fallback frontend templates', 'ai-content-architect' ); ?></label>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'ai-content-architect' ) ); ?>
			</form>
		</main>
	</div>
</div>
