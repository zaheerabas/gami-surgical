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

function gami_blog_assets() {
	if ( ! gami_is_blog() ) {
		return;
	}
	wp_enqueue_style( 'gami-blog', GAMI_URI . '/assets/css/blog.css', array( 'gami-main' ), GAMI_VERSION );
	if ( is_singular( 'post' ) ) {
		wp_enqueue_script(
			'gami-article',
			GAMI_URI . '/assets/js/article.js',
			array(),
			GAMI_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'gami_blog_assets' );

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
 * Current listing page number (works on the posts page and on a page template).
 */
function gami_blog_paged() {
	return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
}

/**
 * Categories that have posts, biggest first, without "Uncategorized".
 *
 * @return WP_Term[]
 */
function gami_blog_categories() {
	$cats = get_categories(
		array(
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);
	return array_values(
		array_filter(
			$cats,
			function ( $cat ) {
				return 'uncategorized' !== $cat->slug;
			}
		)
	);
}

/**
 * Section heading with an optional "View all" link.
 */
function gami_blog_section_head( $title, $link = '', $link_text = '', $id = '' ) {
	echo '<header class="blog-section__head">';
	printf( '<h2 class="blog-section__title"%2$s>%1$s</h2>', esc_html( $title ), $id ? ' id="' . esc_attr( $id ) . '"' : '' );
	if ( $link ) {
		printf(
			'<a class="blog-section__more" href="%1$s">%2$s <span aria-hidden="true">→</span></a>',
			esc_url( $link ),
			esc_html( $link_text ? $link_text : __( 'View all', 'gami-surgical' ) )
		);
	}
	echo '</header>';
}

/**
 * Banner at the top of blog listings.
 *
 * @param string $title Heading.
 * @param string $text  Introduction (HTML allowed).
 * @param bool   $full  Add the search box and counts (blog home only).
 */
function gami_blog_hero( $title, $text = '', $full = false ) {
	$posts = (int) wp_count_posts()->publish;
	$cats  = count( gami_blog_categories() );
	?>
	<header class="blog-hero<?php echo $full ? ' blog-hero--home' : ''; ?>">
		<div class="container blog-hero__inner">
			<div class="blog-hero__main">
				<?php gami_breadcrumbs(); ?>
				<?php if ( gami_option( 'blog_eyebrow' ) ) : ?>
					<p class="blog-hero__eyebrow"><?php echo esc_html( gami_option( 'blog_eyebrow' ) ); ?></p>
				<?php endif; ?>
				<h1 class="blog-hero__title"><?php echo esc_html( $title ); ?></h1>
				<?php if ( $text ) : ?>
					<div class="blog-hero__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
				<?php endif; ?>
			</div>
			<?php if ( $full && ( gami_option( 'blog_search' ) || $posts ) ) : ?>
				<div class="blog-hero__side">
					<?php if ( gami_option( 'blog_search' ) ) : ?>
						<form class="blog-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<label for="blog-search-field"><?php esc_html_e( 'Search articles', 'gami-surgical' ); ?></label>
							<div class="blog-search__row">
								<input type="search" id="blog-search-field" name="s" placeholder="<?php esc_attr_e( 'Sterilization, scissors, steel…', 'gami-surgical' ); ?>">
								<input type="hidden" name="post_type" value="post">
								<button type="submit"><?php esc_html_e( 'Search', 'gami-surgical' ); ?></button>
							</div>
						</form>
					<?php endif; ?>
					<?php if ( $posts ) : ?>
						<dl class="blog-stats">
							<div><dt><?php esc_html_e( 'Articles', 'gami-surgical' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $posts ) ); ?></dd></div>
							<?php if ( $cats ) : ?>
								<div><dt><?php echo esc_html( _n( 'Topic', 'Topics', $cats, 'gami-surgical' ) ); ?></dt><dd><?php echo esc_html( number_format_i18n( $cats ) ); ?></dd></div>
							<?php endif; ?>
						</dl>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Category links above archive listings.
 */
function gami_blog_category_nav() {
	$cats = gami_blog_categories();
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
			'<li><a href="%1$s"%2$s>%3$s <span>%4$s</span></a></li>',
			esc_url( get_category_link( $cat ) ),
			$current === $cat->term_id ? ' aria-current="page"' : '',
			esc_html( $cat->name ),
			esc_html( number_format_i18n( $cat->count ) )
		);
	}
	echo '</ul></nav>';
}

/**
 * Loop a query through the card template.
 */
function gami_blog_cards( $query, $variant = 'card', $first_variant = '' ) {
	$i = 0;
	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template-parts/content', 'card', array( 'variant' => ( 0 === $i && $first_variant ) ? $first_variant : $variant ) );
		++$i;
	}
	wp_reset_postdata();
}

function gami_blog_pagination( $query, $paged ) {
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

function gami_blog_empty() {
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
}

/**
 * Card grid with pagination, for category, tag, author and date archives.
 *
 * @param WP_Query|null $query Posts to list; defaults to the main query.
 */
function gami_blog_listing( $query = null ) {
	global $wp_query;
	$query = $query ? $query : $wp_query;
	$paged = gami_blog_paged();

	if ( ! $query->have_posts() ) {
		gami_blog_empty();
		return;
	}

	echo '<div class="blog-grid">';
	gami_blog_cards( $query, 'card', 1 === $paged ? 'feature' : '' );
	echo '</div>';
	gami_blog_pagination( $query, $paged );
}

/* ---------- Blog home sections ---------- */

/**
 * Latest: one lead story beside a list of the next three.
 *
 * @return int[] IDs shown.
 */
function gami_blog_section_latest() {
	$q = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 4,
			'ignore_sticky_posts' => false,
			'no_found_rows'       => true,
		)
	);
	if ( ! $q->have_posts() ) {
		return array();
	}
	$ids = wp_list_pluck( $q->posts, 'ID' );
	?>
	<section class="blog-section blog-latest" aria-labelledby="blog-latest-title">
		<?php gami_blog_section_head( __( 'Latest articles', 'gami-surgical' ), '', '', 'blog-latest-title' ); ?>
		<div class="blog-latest__grid<?php echo 1 === count( $ids ) ? ' is-single' : ''; ?>">
			<?php
			$q->the_post();
			get_template_part( 'template-parts/content', 'card', array( 'variant' => 'lead' ) );
			if ( $q->have_posts() ) {
				echo '<div class="blog-latest__list">';
				while ( $q->have_posts() ) {
					$q->the_post();
					get_template_part( 'template-parts/content', 'card', array( 'variant' => 'compact' ) );
				}
				echo '</div>';
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
	return $ids;
}

/**
 * Browse by topic: one tile per category.
 */
function gami_blog_section_topics() {
	$cats = gami_blog_categories();
	if ( ! $cats ) {
		return;
	}
	?>
	<section class="blog-section blog-topics" aria-labelledby="blog-topics-title">
		<?php gami_blog_section_head( __( 'Browse by topic', 'gami-surgical' ), '', '', 'blog-topics-title' ); ?>
		<ul class="topic-tiles">
			<?php foreach ( $cats as $i => $cat ) : ?>
				<li class="topic-tile<?php echo 0 === $i ? ' topic-tile--dark' : ''; ?>">
					<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>">
						<span class="topic-tile__count">
							<?php
							/* translators: %s: number of articles */
							echo esc_html( sprintf( _n( '%s article', '%s articles', $cat->count, 'gami-surgical' ), number_format_i18n( $cat->count ) ) );
							?>
						</span>
						<span class="topic-tile__name"><?php echo esc_html( $cat->name ); ?></span>
						<?php if ( $cat->description ) : ?>
							<span class="topic-tile__text"><?php echo esc_html( wp_trim_words( $cat->description, 18 ) ); ?></span>
						<?php endif; ?>
						<span class="topic-tile__arrow" aria-hidden="true">→</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/**
 * One row per category (up to four), alternating card and list layouts.
 * Only when there are two or more categories; with one, it would repeat Latest.
 */
function gami_blog_section_category_rows() {
	$cats = array_slice( gami_blog_categories(), 0, 4 );
	if ( count( $cats ) < 2 ) {
		return;
	}
	foreach ( $cats as $i => $cat ) {
		$list = 1 === $i % 2;
		$q    = new WP_Query(
			array(
				'post_type'           => 'post',
				'cat'                 => $cat->term_id,
				'posts_per_page'      => $list ? 4 : 3,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( ! $q->have_posts() ) {
			continue;
		}
		$id = 'blog-cat-' . $cat->term_id;
		echo '<section class="blog-section blog-catrow' . ( $list ? ' blog-catrow--list' : '' ) . '" aria-labelledby="' . esc_attr( $id ) . '">';
		gami_blog_section_head( $cat->name, get_category_link( $cat ), __( 'All in this topic', 'gami-surgical' ), $id );
		echo '<div class="' . ( $list ? 'blog-rowlist' : 'blog-grid blog-grid--3' ) . '">';
		gami_blog_cards( $q, $list ? 'row' : 'card' );
		echo '</div></section>';
	}
}

/**
 * Question banner between the sections.
 */
function gami_blog_section_cta() {
	$wa = gami_whatsapp_url( __( 'Hello, I have a question about an instrument.', 'gami-surgical' ) );
	?>
	<section class="blog-ask">
		<div class="blog-ask__text">
			<h2 class="blog-ask__title"><?php echo esc_html( gami_option( 'blog_cta_title' ) ); ?></h2>
			<p><?php echo esc_html( gami_option( 'blog_cta_text' ) ); ?></p>
		</div>
		<div class="blog-ask__actions">
			<a class="button button--primary" href="<?php echo esc_url( gami_inquiry_page_url_safe() ); ?>"><?php esc_html_e( 'Ask our team', 'gami-surgical' ); ?></a>
			<?php if ( $wa ) : ?>
				<a class="button button--light" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat on WhatsApp', 'gami-surgical' ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * All articles: compact dated list with pagination.
 */
function gami_blog_section_all( $query, $paged ) {
	if ( ! $query->have_posts() ) {
		return;
	}
	?>
	<section class="blog-section blog-all" id="all-articles" aria-labelledby="blog-all-title">
		<?php
		gami_blog_section_head(
			/* translators: %s: number of articles */
			sprintf( __( 'All articles (%s)', 'gami-surgical' ), number_format_i18n( $query->found_posts ) ),
			'',
			'',
			'blog-all-title'
		);
		?>
		<div class="blog-index">
			<?php gami_blog_cards( $query, 'index' ); ?>
		</div>
		<?php gami_blog_pagination( $query, $paged ); ?>
	</section>
	<?php
}

/**
 * The whole Blog page.
 *
 * @param string   $title Page heading.
 * @param string   $intro Introduction.
 * @param WP_Query $query Paginated posts for "All articles".
 */
function gami_render_blog_home( $title, $intro, $query ) {
	$paged = gami_blog_paged();
	gami_blog_hero( $title, $intro, 1 === $paged );
	echo '<main id="primary" class="site-main site-main--blog container">';

	if ( ! $query->found_posts ) {
		gami_blog_empty();
	} elseif ( $paged > 1 ) {
		gami_blog_section_all( $query, $paged );
	} else {
		$shown = gami_option( 'blog_latest' ) ? gami_blog_section_latest() : array();
		if ( gami_option( 'blog_topics' ) ) {
			gami_blog_section_topics();
		}
		if ( gami_option( 'blog_category_rows' ) ) {
			gami_blog_section_category_rows();
		}
		if ( gami_option( 'blog_cta' ) ) {
			gami_blog_section_cta();
		}
		// Skip the full list when Latest already shows every article.
		if ( $query->found_posts > count( $shown ) ) {
			gami_blog_section_all( $query, $paged );
		}
	}

	echo '</main>';
}

/**
 * Blog page introduction: the page excerpt, else the Customizer text.
 */
function gami_blog_intro( $page_id ) {
	return $page_id && has_excerpt( $page_id ) ? get_the_excerpt( $page_id ) : gami_option( 'blog_intro' );
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
