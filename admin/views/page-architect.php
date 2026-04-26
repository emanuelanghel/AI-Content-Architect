<?php
/**
 * Architect screen.
 *
 * @package AIContentArchitect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$existing_config = $edit_model['config'] ?? null;
$model_id        = $edit_model['id'] ?? '';
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
				<a class="is-active" href="<?php echo esc_url( admin_url( 'admin.php?page=ai-content-architect' ) ); ?>"><span class="dashicons dashicons-edit-page"></span><?php esc_html_e( 'Architect', 'ai-content-architect' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aica-models' ) ); ?>"><span class="dashicons dashicons-database"></span><?php esc_html_e( 'Content Models', 'ai-content-architect' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=aica-settings' ) ); ?>"><span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'Settings', 'ai-content-architect' ); ?></a>
			</nav>
			<div class="aica-sidebar-note">
				<strong><?php esc_html_e( 'Safe by design', 'ai-content-architect' ); ?></strong>
				<span><?php esc_html_e( 'JSON configuration only. No generated PHP is executed.', 'ai-content-architect' ); ?></span>
			</div>
		</aside>
		<main class="aica-main-panel">
			<div class="aica-page-header">
				<div>
					<p class="aica-kicker"><?php esc_html_e( 'Structured content builder', 'ai-content-architect' ); ?></p>
					<h1><?php esc_html_e( 'Architect', 'ai-content-architect' ); ?></h1>
					<p class="description"><?php esc_html_e( 'Describe a content section, review the generated architecture, then apply it only when it looks right.', 'ai-content-architect' ); ?></p>
				</div>
				<div class="aica-header-actions">
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=aica-models' ) ); ?>"><?php esc_html_e( 'View Models', 'ai-content-architect' ); ?></a>
				</div>
			</div>

			<div id="aica-notice-area"></div>

			<section class="aica-builder-panel">
				<div class="aica-prompt-panel">
					<div class="aica-section-heading">
						<span class="aica-step">1</span>
						<div>
							<h2><?php esc_html_e( 'Describe what you want to build', 'ai-content-architect' ); ?></h2>
							<p><?php esc_html_e( 'Include the main content type, stored details, browsing structure, admin columns, and sample content expectations.', 'ai-content-architect' ); ?></p>
						</div>
					</div>
					<textarea id="aica-prompt" rows="8" class="large-text aica-prompt" placeholder="<?php echo esc_attr__( 'Example: I want to create a job board with jobs, companies, locations, salary ranges, employment types, remote/hybrid options, application links, and featured jobs.', 'ai-content-architect' ); ?>"></textarea>
					<div class="aica-generate-row">
						<button type="button" class="button button-primary button-hero" id="aica-generate-model"><?php esc_html_e( 'Generate Content Model', 'ai-content-architect' ); ?></button>
						<span class="spinner" id="aica-spinner"></span>
						<span class="aica-muted"><?php esc_html_e( 'Nothing is created until you review and apply the model.', 'ai-content-architect' ); ?></span>
					</div>
					<div class="aica-examples" aria-label="<?php echo esc_attr__( 'Example prompts', 'ai-content-architect' ); ?>">
						<button type="button" class="aica-example-chip" data-prompt="<?php echo esc_attr__( 'Create a real estate directory with properties, locations, prices, bedrooms, bathrooms, amenities, gallery images, agents, and search filters.', 'ai-content-architect' ); ?>"><?php esc_html_e( 'Real estate', 'ai-content-architect' ); ?></button>
						<button type="button" class="aica-example-chip" data-prompt="<?php echo esc_attr__( 'Create a job board with jobs, companies, locations, salary range, employment type, remote option, application deadline, and application URL.', 'ai-content-architect' ); ?>"><?php esc_html_e( 'Job board', 'ai-content-architect' ); ?></button>
						<button type="button" class="aica-example-chip" data-prompt="<?php echo esc_attr__( 'Create an events calendar with events, venues, speakers, dates, ticket links, event types, and featured events.', 'ai-content-architect' ); ?>"><?php esc_html_e( 'Events', 'ai-content-architect' ); ?></button>
						<button type="button" class="aica-example-chip" data-prompt="<?php echo esc_attr__( 'Create a course library with courses, instructors, topics, difficulty levels, duration, enrollment URL, lessons, and featured courses.', 'ai-content-architect' ); ?>"><?php esc_html_e( 'Courses', 'ai-content-architect' ); ?></button>
						<button type="button" class="aica-example-chip" data-prompt="<?php echo esc_attr__( 'Create a documentation hub with articles, product areas, resource types, difficulty, estimated reading time, and featured resources.', 'ai-content-architect' ); ?>"><?php esc_html_e( 'Documentation', 'ai-content-architect' ); ?></button>
						<button type="button" class="aica-example-chip" data-prompt="<?php echo esc_attr__( 'Create a restaurant menu with dishes, categories, dietary tags, prices, ingredients, spice level, images, and availability.', 'ai-content-architect' ); ?>"><?php esc_html_e( 'Menu', 'ai-content-architect' ); ?></button>
					</div>
				</div>
				<div class="aica-flow-panel">
					<h2><?php esc_html_e( 'Review flow', 'ai-content-architect' ); ?></h2>
					<div class="aica-mini-kpis">
						<div><strong><?php esc_html_e( 'JSON', 'ai-content-architect' ); ?></strong><span><?php esc_html_e( 'Config only', 'ai-content-architect' ); ?></span></div>
						<div><strong><?php esc_html_e( 'Safe', 'ai-content-architect' ); ?></strong><span><?php esc_html_e( 'Validated', 'ai-content-architect' ); ?></span></div>
					</div>
					<ol>
						<li><?php esc_html_e( 'Generate a structured proposal.', 'ai-content-architect' ); ?></li>
						<li><?php esc_html_e( 'Edit labels, slugs, fields, and warnings.', 'ai-content-architect' ); ?></li>
						<li><?php esc_html_e( 'Save as draft or apply when ready.', 'ai-content-architect' ); ?></li>
					</ol>
				</div>
			</section>

			<div id="aica-review">
				<?php
				if ( is_array( $existing_config ) ) {
					$config = $existing_config;
					require AICA_PATH . 'admin/views/partial-model-review.php';
				}
				?>
			</div>

			<input type="hidden" id="aica-model-id" value="<?php echo esc_attr( $model_id ); ?>">
		</main>
	</div>
</div>
