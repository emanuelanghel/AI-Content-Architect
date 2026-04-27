<?php
/**
 * Fallback single template for generated content.
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
<main id="primary" class="site-main aica-content aica-content-single">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'aica-content-entry' ); ?>>
			<header class="aica-content-hero">
				<div class="aica-content-hero-text">
					<?php the_title( '<h1 class="aica-content-title">', '</h1>' ); ?>
				</div>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="aica-content-featured-image"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>
			</header>
			<?php
			$GLOBALS['aica_rendering_fallback_template'] = true;
			?>
			<div class="aica-content-body"><?php the_content(); ?></div>
			<?php
			unset( $GLOBALS['aica_rendering_fallback_template'] );

			$fields = $manager->fields_for_post_type( get_post_type() );
			if ( ! empty( $fields ) ) {
				echo $renderer->render_fields( get_the_ID(), $fields ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo $renderer->render_taxonomies( get_the_ID(), get_post_type() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
