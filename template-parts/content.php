<?php
/**
 * Post summary in lists.
 *
 * @package Gami_Surgical
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-summary' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="post-summary__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'gami-card', array( 'loading' => 'lazy' ) ); ?></a>
	<?php endif; ?>
	<div class="post-summary__body">
		<p class="entry-meta"><?php gami_posted_on(); ?></p>
		<?php the_title( '<h2 class="post-summary__title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
	</div>
</article>
