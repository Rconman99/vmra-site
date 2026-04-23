<?php
/**
 * Main index template — fallback when a more specific template is not available.
 *
 * WordPress template hierarchy docs:
 *   https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * For the VMRA theme, specific pages use their own templates:
 *   front-page.php      Homepage
 *   page-schedule.php   /schedule/
 *   page-standings.php  /standings/
 *   single-driver.php   A single driver profile
 *   single-race.php     A single race recap
 *   archive-news.php    News archive
 *
 * This file catches anything that falls through.
 */

get_header(); ?>

<section class="hero">
	<div class="hero-inner">
		<span class="eyebrow">§ <?php echo esc_html( wp_get_document_title() ); ?></span>
		<h1><?php single_post_title(); ?></h1>
	</div>
</section>

<main class="fallback-main" style="max-width:1080px;margin:0 auto;padding:60px 5vw;">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry-content">
					<?php the_excerpt(); ?>
				</div>
			</article>
		<?php endwhile; ?>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p>No posts found.</p>
	<?php endif; ?>
</main>

<?php get_footer();
