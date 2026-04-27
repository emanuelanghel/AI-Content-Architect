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
				<span class="aica-brand-mark"><img src="<?php echo esc_url( AICA_URL . 'admin/images/ai-logo.png' ); ?>" alt="<?php echo esc_attr__( 'AI Content Architect', 'ai-content-architect' ); ?>"></span>
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

			<div id="aica-notice-area">
				<?php if ( isset( $_GET['aica_notice'] ) && 'settings_saved' === sanitize_key( wp_unslash( $_GET['aica_notice'] ) ) ) : ?>
					<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'ai-content-architect' ); ?></p></div>
				<?php endif; ?>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="aica-panel">
				<?php wp_nonce_field( 'aica_admin' ); ?>
				<input type="hidden" name="action" value="aica_save_settings">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="aica_provider"><?php esc_html_e( 'AI Provider', 'ai-content-architect' ); ?></label></th>
						<td>
							<select id="aica_provider" name="provider">
								<?php foreach ( $providers as $provider_key => $provider ) : ?>
									<option value="<?php echo esc_attr( $provider_key ); ?>" data-default-base-url="<?php echo esc_attr( $provider['default_base_url'] ?? '' ); ?>" data-requires-key="<?php echo ! empty( $provider['requires_key'] ) ? '1' : '0'; ?>" <?php selected( $settings['provider'], $provider_key ); ?>>
										<?php echo esc_html( $provider['label'] ?? $provider_key ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<div class="aica-provider-help">
								<?php foreach ( $providers as $provider_key => $provider ) : ?>
									<p class="description aica-provider-description" data-provider="<?php echo esc_attr( $provider_key ); ?>"><?php echo esc_html( $provider['description'] ?? '' ); ?></p>
								<?php endforeach; ?>
							</div>
							<p class="aica-provider-badge" data-provider-badge="mock"><?php esc_html_e( 'Mock mode is ideal for local testing. No API key or paid provider is required.', 'ai-content-architect' ); ?></p>
						</td>
					</tr>
					<tr class="aica-api-key-row">
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
					<tr class="aica-base-url-row">
						<th scope="row"><label for="aica_base_url"><?php esc_html_e( 'Provider Base URL', 'ai-content-architect' ); ?></label></th>
						<td>
							<input id="aica_base_url" type="url" name="base_url" value="<?php echo esc_attr( $settings['base_url'] ); ?>" class="regular-text">
							<p class="description"><?php esc_html_e( 'Use the API base URL, such as https://api.openai.com/v1. The plugin adds /models and /chat/completions automatically.', 'ai-content-architect' ); ?></p>
						</td>
					</tr>
					<tr class="aica-model-row">
						<th scope="row"><label for="aica_model"><?php esc_html_e( 'Model', 'ai-content-architect' ); ?></label></th>
						<td>
							<div class="aica-model-picker">
								<select id="aica_model" name="model" <?php disabled( ! empty( $settings['use_custom_model'] ) ); ?>>
									<?php foreach ( (array) $model_choices['models'] as $model ) : ?>
										<?php $model_id = (string) ( $model['id'] ?? '' ); ?>
										<option value="<?php echo esc_attr( $model_id ); ?>" <?php selected( $settings['model'], $model_id ); ?>>
											<?php
											$label = (string) ( $model['label'] ?? $model_id );
											$badge = (string) ( $model['badge'] ?? '' );
											echo esc_html( '' === $badge ? $label : sprintf( '%1$s - %2$s', $label, $badge ) );
											?>
										</option>
									<?php endforeach; ?>
									<?php if ( ! empty( $settings['model'] ) && ! in_array( $settings['model'], wp_list_pluck( (array) $model_choices['models'], 'id' ), true ) ) : ?>
										<option value="<?php echo esc_attr( $settings['model'] ); ?>" selected><?php echo esc_html( $settings['model'] ); ?></option>
									<?php endif; ?>
								</select>
								<button type="button" class="button" id="aica-refresh-models"><?php esc_html_e( 'Refresh models', 'ai-content-architect' ); ?></button>
								<button type="button" class="button" id="aica-test-provider"><?php esc_html_e( 'Test connection', 'ai-content-architect' ); ?></button>
							</div>
							<p class="description" id="aica-model-source">
								<?php if ( ! empty( $model_choices['refreshed_at'] ) ) : ?>
									<?php
									printf(
										/* translators: %s: refresh timestamp. */
										esc_html__( 'Model list last refreshed: %s.', 'ai-content-architect' ),
										esc_html( $model_choices['refreshed_at'] )
									);
									?>
								<?php elseif ( 'fallback' === ( $model_choices['source'] ?? '' ) ) : ?>
									<?php esc_html_e( 'Showing built-in OpenAI suggestions until you refresh models with an API key.', 'ai-content-architect' ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Model selection is ignored in mock mode.', 'ai-content-architect' ); ?>
								<?php endif; ?>
							</p>
							<label class="aica-inline-check">
								<input type="checkbox" id="aica_use_custom_model" name="use_custom_model" value="1" <?php checked( ! empty( $settings['use_custom_model'] ) ); ?>>
								<?php esc_html_e( 'Use custom model ID', 'ai-content-architect' ); ?>
							</label>
							<input id="aica_custom_model" type="text" name="custom_model" value="<?php echo esc_attr( $settings['custom_model'] ); ?>" class="regular-text" placeholder="provider-model-id">
							<p class="description"><?php esc_html_e( 'Use a custom model only when it is not returned by the provider model list.', 'ai-content-architect' ); ?></p>
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
