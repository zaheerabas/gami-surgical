<?php
/**
 * Blog: the Blog page, post listings, article extras and blog styles.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GAMI_BLOG_TEMPLATE', 'page-templates/template-blog.php' );

/**
 * Create or take over the "Blog" page once per theme version, so it is set
 * up right after the theme is installed or updated.
 */
function gami_maybe_setup_blog_page() {
	if ( get_option( 'gami_blog_setup_version' ) === GAMI_VERSION ) {
		return;
	}
	gami_setup_blog_page();
	update_option( 'gami_blog_setup_version', GAMI_VERSION );
}
add_action( 'init', 'gami_maybe_setup_blog_page', 20 );

function gami_reset_blog_setup() {
	delete_option( 'gami_blog_setup_version' );
}
add_action( 'after_switch_theme', 'gami_reset_blog_setup' );

/**
 * Use the existing page with the slug "blog" (or the current posts page), else
 * create one. It gets the Blog template, and becomes the posts page when the
 * site uses a static front page.
 *
 * @return int Blog page ID, or 0.
 */
function gami_setup_blog_page() {
	$page = get_page_by_path( 'blog' );
	if ( ! $page && get_option( 'page_for_posts' ) ) {
		$page = get_post( get_option( 'page_for_posts' ) );
	}

	if ( $page && 'page' === $page->post_type ) {
		$page_id = $page->ID;
		if ( 'publish' !== $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page_id,
					'post_status' => 'publish',
				)
			);
		}
	} else {
		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => __( 'Blog', 'gami-surgical' ),
				'post_name'   => 'blog',
				'post_status' => 'publish',
			)
		);
		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return 0;
		}
	}

	update_post_meta( $page_id, '_wp_page_template', GAMI_BLOG_TEMPLATE );

	if ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) !== (int) $page_id ) {
		update_option( 'page_for_posts', $page_id );
	}

	return (int) $page_id;
}

/**
 * Is this request part of the blog (listing, post, post archive or search)?
 */
function gami_is_blog() {
	return is_home()
		|| is_singular( 'post' )
		|| is_category()
		|| is_tag()
		|| is_author()
		|| is_date()
		|| is_page_template( GAMI_BLOG_TEMPLATE );
}

function gami_blog_styles() {
	if ( gami_is_blog() ) {
		wp_enqueue_style( 'gami-blog', GAMI_URI . '/assets/css/blog.css', array( 'gami-main' ), GAMI_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'gami_blog_styles' );

/**
 * Minutes to read a post, at 200 words a minute.
 */
function gami_reading_time( $post_id = null ) {
	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ? $post_id : get_the_ID() ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

function gami_reading_time_label( $post_id = null ) {
	$minutes = gami_reading_time( $post_id );
	/* translators: %d: minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'gami-surgical' ), $minutes );
}

/**
 * First category of a post, skipping "Uncategorized".
 *
 * @return WP_Term|null
 */
function gami_primary_category( $post_id = null ) {
	foreach ( (array) get_the_category( $post_id ? $post_id : get_the_ID() ) as $cat ) {
		if ( 'uncategorized' !== $cat->slug ) {
			return $cat;
		}
	}
	return null;
}

/**
 * URL of the blog listing.
 */
function gami_blog_url() {
	$page_id = (int) get_option( 'page_for_posts' );
	if ( ! $page_id ) {
		$page = get_page_by_path( 'blog' );
		$page_id = $page ? $page->ID : 0;
	}
	return $page_id ? get_permalink( $page_id ) : home_url( '/' );
}

/**
 * Banner at the top of blog listings.
 */
function gami_blog_hero( $title, $text = '' ) {
	?>
	<header class="blog-hero">
		<div class="container">
			<?php gami_breadcrumbs(); ?>
			<p class="blog-hero__eyebrow"><?php esc_html_e( 'Gami Surgical Journal', 'gami-surgical' ); ?></p>
			<h1 class="blog-hero__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $text ) : ?>
				<div class="blog-hero__text"><?php echo wp_kses_post( $text ); ?></div>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Category links above the listing.
 */
function gami_blog_category_nav() {
	$cats = get_categories(
		array(
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_category' ) ),
		)
	);
	$cats = array_filter(
		$cats,
		function ( $cat ) {
			return 'uncategorized' !== $cat->slug;
		}
	);
	if ( ! $cats ) {
		return;
	}
	$current = is_category() ? get_queried_object_id() : 0;
	echo '<nav class="blog-cats" aria-label="' . esc_attr__( 'Blog categories', 'gami-surgical' ) . '"><ul>';
	printf(
		'<li><a href="%1$s"%2$s>%3$s</a></li>',
		esc_url( gami_blog_url() ),
		$current ? '' : ' aria-current="page"',
		esc_html__( 'All articles', 'gami-surgical' )
	);
	foreach ( $cats as $cat ) {
		printf(
			'<li><a href="%1$s"%2$s>%3$s</a></li>',
			esc_url( get_category_link( $cat ) ),
			$current === $cat->term_id ? ' aria-current="page"' : '',
			esc_html( $cat->name )
		);
	}
	echo '</ul></nav>';
}

/**
 * Card grid of posts with pagination. The first post on page one is shown large.
 *
 * @param WP_Query|null $query Posts to list; defaults to the main query.
 */
function gami_blog_listing( $query = null ) {
	global $wp_query;
	$query = $query ? $query : $wp_query;
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	if ( ! $query->have_posts() ) {
		echo '<section class="no-results blog-empty"><h2>' . esc_html__( 'No articles yet', 'gami-surgical' ) . '</h2>';
		if ( current_user_can( 'edit_posts' ) ) {
			printf(
				'<p><a class="button button--primary" href="%1$s">%2$s</a> <a class="button button--ghost" href="%3$s">%4$s</a></p>',
				esc_url( admin_url( 'edit.php?page=gami-upload-articles' ) ),
				esc_html__( 'Upload articles', 'gami-surgical' ),
				esc_url( admin_url( 'post-new.php' ) ),
				esc_html__( 'Write a post', 'gami-surgical' )
			);
		} else {
			echo '<p>' . esc_html__( 'New guides are on the way. Check back soon.', 'gami-surgical' ) . '</p>';
		}
		echo '</section>';
		return;
	}

	echo '<div class="blog-grid">';
	$i = 0;
	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template-parts/content', 'card', array( 'featured' => 0 === $i && 1 === $paged ) );
		++$i;
	}
	echo '</div>';
	wp_reset_postdata();

	$links = paginate_links(
		array(
			'total'     => (int) $query->max_num_pages,
			'current'   => $paged,
			'mid_size'  => 1,
			'prev_text' => __( 'Previous', 'gami-surgical' ),
			'next_text' => __( 'Next', 'gami-surgical' ),
		)
	);
	if ( $links ) {
		echo '<nav class="navigation pagination" aria-label="' . esc_attr__( 'Posts', 'gami-surgical' ) . '"><div class="nav-links">' . $links . '</div></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Up to three posts from the same category.
 */
function gami_related_posts( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$cat     = gami_primary_category( $post_id );
	$args    = array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( $cat ) {
		$args['cat'] = $cat->term_id;
	}
	$related = new WP_Query( $args );
	if ( ! $related->have_posts() ) {
		return;
	}
	?>
	<section class="related-posts" aria-labelledby="related-title">
		<h2 id="related-title" class="related-posts__title"><?php esc_html_e( 'Keep reading', 'gami-surgical' ); ?></h2>
		<div class="blog-grid blog-grid--related">
			<?php
			while ( $related->have_posts() ) {
				$related->the_post();
				get_template_part( 'template-parts/content', 'card' );
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
}

/**
 * Quote request box shown under each article.
 */
function gami_article_cta() {
	?>
	<aside class="article-cta">
		<div>
			<p class="article-cta__title"><?php esc_html_e( 'Need instruments made to this standard?', 'gami-surgical' ); ?></p>
			<p class="article-cta__text"><?php esc_html_e( 'Gami Surgical manufactures surgical, dental and beauty instruments in Sialkot and ships worldwide. Send us your list for a quote.', 'gami-surgical' ); ?></p>
		</div>
		<a class="button button--primary" href="<?php echo esc_url( gami_quote_url() ); ?>"><?php esc_html_e( 'Request a quote', 'gami-surgical' ); ?></a>
	</aside>
	<?php
}

/**
 * Share links (no third-party scripts).
 */
function gami_share_links() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$links = array(
		'LinkedIn' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url,
		'Facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
		'X'        => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
		'WhatsApp' => 'https://wa.me/?text=' . $title . '%20' . $url,
		'Email'    => 'mailto:?subject=' . $title . '&body=' . $url,
	);
	echo '<div class="share-links"><span class="share-links__label">' . esc_html__( 'Share', 'gami-surgical' ) . '</span><ul>';
	foreach ( $links as $name => $href ) {
		printf(
			'<li><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></li>',
			esc_url( $href ),
			esc_html( $name )
		);
	}
	echo '</ul></div>';
}

/**
 * Plain "…" instead of "[…]" at the end of excerpts.
 */
function gami_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'gami_excerpt_more' );

/**
 * Archive titles without the "Category:" / "Tag:" prefix.
 */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
