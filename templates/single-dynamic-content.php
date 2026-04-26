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
?>
<main id="primary" class="site-main aica-template">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="post-thumbnail"><?php the_post_thumbnail( 'large' ); ?></div>
				<?php endif; ?>
			</header>
			<div class="entry-content"><?php the_content(); ?></div>
			<?php
			$manager = \AIContentArchitect\Plugin::instance()->templates;
			$fields  = $manager->fields_for_post_type( get_post_type() );
			if ( ! empty( $fields ) ) :
				?>
				<section class="aica-generated-fields">
					<h2><?php esc_html_e( 'Details', 'ai-content-architect' ); ?></h2>
					<dl>
						<?php foreach ( $fields as $field ) : ?>
							<?php $value = get_post_meta( get_the_ID(), aica_meta_key( $field['key'] ), true ); ?>
							<?php if ( '' !== (string) $value ) : ?>
								<dt><?php echo esc_html( $field['label'] ); ?></dt>
								<dd><?php echo 'wysiwyg' === $field['type'] ? wp_kses_post( $value ) : esc_html( (string) $value ); ?></dd>
							<?php endif; ?>
						<?php endforeach; ?>
					</dl>
				</section>
			<?php endif; ?>
			<footer class="entry-footer"><?php the_taxonomies(); ?></footer>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
