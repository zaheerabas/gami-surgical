<?php
/**
 * Post card for blog listings. Pass array( 'featured' => true ) for the large card.
 *
 * @package Gami_Surgical
 */

$gami_featured = ! empty( $args['featured'] );
$gami_cat      = gami_primary_category();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' . ( $gami_featured ? ' post-card--featured' : '' ) ); ?>>
	<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( $gami_featured ? 'gami-wide' : 'gami-card', array( 'loading' => $gami_featured ? 'eager' : 'lazy' ) ); ?>
		<?php else : ?>
			<span class="post-card__placeholder"><span><?php echo esc_html( $gami_cat ? $gami_cat->name : get_bloginfo( 'name' ) ); ?></span></span>
		<?php endif; ?>
	</a>
	<div class="post-card__body">
		<?php if ( $gami_cat ) : ?>
			<a class="post-card__cat" href="<?php echo esc_url( get_category_link( $gami_cat ) ); ?>"><?php echo esc_html( $gami_cat->name ); ?></a>
		<?php endif; ?>
		<?php
		$gami_tag = $gami_featured ? 'h2' : 'h3';
		the_title( '<' . $gami_tag . ' class="post-card__title"><a href="' . esc_url( get_permalink() ) . '">', '</a></' . $gami_tag . '>' );
		?>
		<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $gami_featured ? 40 : 22 ) ); ?></p>
		<p class="post-card__meta">
			<?php gami_posted_on(); ?>
			<span><?php echo esc_html( gami_reading_time_label() ); ?></span>
		</p>
	</div>
</article>
