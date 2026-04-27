<?php
/**
 * Fallback archive template for generated content.
 *
 * @package AIContentArchitect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$manager  = \AIContentArchitect\Plugin::instance()->templates;
$renderer = new \AIContentArchitect\Frontend_Display( $manager );
?>
<main id="primary" class="site-main aica-content aica-content-archive">
	<header class="aica-content-archive-header">
		<?php the_archive_title( '<h1 class="aica-content-archive-title">', '</h1>' ); ?>
		<?php the_archive_description( '<div class="aica-content-archive-description">', '</div>' ); ?>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="aica-content-card-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'aica-content-card' ); ?>>
					<a class="aica-content-card-media" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large' ); ?>
						<?php else : ?>
							<span class="aica-content-card-placeholder" aria-hidden="true"></span>
						<?php endif; ?>
					</a>
					<div class="aica-content-card-body">
						<h2 class="aica-content-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="aica-content-card-excerpt"><?php the_excerpt(); ?></div>
						<?php echo $renderer->archive_fields_for_post( get_the_ID(), get_post_type(), 3 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo $renderer->render_taxonomies( get_the_ID(), get_post_type(), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a class="aica-content-card-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View details', 'ai-content-architect' ); ?></a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<div class="aica-content-pagination"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<p class="aica-content-empty"><?php esc_html_e( 'No items found.', 'ai-content-architect' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
