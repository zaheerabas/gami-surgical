<?php
/**
 * Instrument card used in grids.
 *
 * @package Gami_Surgical
 */

$gami_cat_no = get_post_meta( get_the_ID(), '_gami_catalogue_no', true );
$gami_sizes  = get_post_meta( get_the_ID(), '_gami_sizes', true );
?>
<li class="instrument-card">
	<a class="instrument-card__link" href="<?php the_permalink(); ?>">
		<div class="instrument-card__media">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'gami-card', array( 'loading' => 'lazy' ) );
			} else {
				echo gami_placeholder_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
		<div class="instrument-card__body">
			<?php if ( $gami_cat_no ) : ?>
				<p class="instrument-card__catno"><?php echo esc_html( $gami_cat_no ); ?></p>
			<?php endif; ?>
			<h3 class="instrument-card__title"><?php the_title(); ?></h3>
			<?php if ( $gami_sizes ) : ?>
				<p class="instrument-card__sizes"><?php echo esc_html( $gami_sizes ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</li>
