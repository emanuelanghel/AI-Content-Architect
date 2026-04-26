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
?>
<main id="primary" class="site-main aica-template">
	<header class="page-header">
		<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="aica-archive-list">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class(); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a>
					<?php endif; ?>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No items found.', 'ai-content-architect' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
