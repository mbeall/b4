<?php
/**
 * @package B4
 */

require_once( get_template_directory() . '/inc/utils.php' );

b4_maybe_define( 'B4_INC_PATH', get_template_directory()     . '/inc' );
b4_maybe_define( 'B4_INC_URL',  get_template_directory_uri() . '/inc' );
b4_maybe_define( 'B4_JS_PATH',  get_template_directory()     . '/js'  );
b4_maybe_define( 'B4_JS_URL',   get_template_directory_uri() . '/js'  );

class B4 {
	/**
	 * DB version.
	 *
	 * @var int
	 */
	var $db_version = 3;

	/**
	 * Options.
	 *
	 * @var array
	 */
	var $options = array();

	/**
	 * Option name in DB.
	 *
	 * @var string
	 */
	var $option_name = 'b4_manager';

	/**
	 * Components.
	 *
	 * @var array
	 */
	var $components = array();

	/**
	 * Includes and instantiates the various B4 components.
	 */
	function B4() {
		// Fetch options
		$this->options = get_option( $this->option_name );
		if ( false === $this->options )
			$this->options = array();

		// Include the B4 components
		$includes = array( 'compat', 'terms-in-comments', 'js-locale',
			'mentions', 'search', 'js', 'options-page', 'widgets/recent-tags', 'widgets/recent-comments',
			'list-creator' );

		require_once( B4_INC_PATH . "/template-tags.php" );

		// Logged-out/unprivileged users use the add_feed() + ::ajax_read() API rather than the /admin-ajax.php API
		// current_user_can( 'read' ) should be equivalent to is_user_member_of_blog()
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ( b4_user_can_post() || current_user_can( 'read' ) ) )
			$includes[] = 'ajax';

		foreach ( $includes as $name ) {
			require_once( B4_INC_PATH . "/$name.php" );
		}

		// Add the default B4 components
		$this->add( 'mentions',             'B4_Mentions'             );
		$this->add( 'search',               'B4_Search'               );
		$this->add( 'post-list-creator',    'B4_Post_List_Creator'    );
		$this->add( 'comment-list-creator', 'B4_Comment_List_Creator' );

		// Bind actions
		add_action( 'init',       array( &$this, 'init'             ) );
		add_action( 'admin_init', array( &$this, 'maybe_upgrade_db' ), 5 );
	}

	function init() {
		// Load language pack
		load_theme_textdomain( 'b4', get_template_directory() . '/languages' );

		// Set up the AJAX read handler
		add_feed( 'b4.ajax', array( $this, 'ajax_read' ) );
	}

	function ajax_read() {
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		require_once( B4_INC_PATH . '/ajax-read.php' );

		B4Ajax_Read::dispatch();
	}

	/**
	 * Will upgrade the database if necessary.
	 *
	 * When upgrading, triggers actions:
	 *    'b4_upgrade_db_version'
	 *    'b4_upgrade_db_version_$number'
	 *
	 * Flushes rewrite rules automatically on upgrade.
	 */
	function maybe_upgrade_db() {
		if ( ! isset( $this->options['db_version'] ) || $this->options['db_version'] < $this->db_version ) {
			$current_db_version = isset( $this->options['db_version'] ) ? $this->options['db_version'] : 0;

			do_action( 'b4_upgrade_db_version', $current_db_version );
			for ( ; $current_db_version <= $this->db_version; $current_db_version++ ) {
				do_action( "b4_upgrade_db_version_$current_db_version" );
			}

			// Flush rewrite rules once, so callbacks don't have to.
			flush_rewrite_rules();

			$this->set_option( 'db_version', $this->db_version );
			$this->save_options();
		}
	}

	/**
	 * COMPONENTS API
	 */
	function add( $component, $class ) {
		$class = apply_filters( "b4_add_component_$component", $class );
		if ( class_exists( $class ) )
			$this->components[ $component ] = new $class();
	}
	function get( $component ) {
		return $this->components[ $component ];
	}
	function remove( $component ) {
		unset( $this->components[ $component ] );
	}

	/**
	 * OPTIONS API
	 */
	function get_option( $key ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : null;
	}
	function set_option( $key, $value ) {
		return $this->options[ $key ] = $value;
	}
	function save_options() {
		update_option( $this->option_name, $this->options );
	}
}

$GLOBALS['b4'] = new B4;

function b4_get( $component = '' ) {
	global $b4;
	return empty( $component ) ? $b4 : $b4->get( $component );
}
function b4_get_option( $key ) {
	return $GLOBALS['b4']->get_option( $key );
}
function b4_set_option( $key, $value ) {
	return $GLOBALS['b4']->set_option( $key, $value );
}
function b4_save_options() {
	return $GLOBALS['b4']->save_options();
}




/**
 * ----------------------------------------------------------------------------
 * NOTE: Ideally, the rest of this file should be moved elsewhere.
 * ----------------------------------------------------------------------------
 */

if ( ! isset( $content_width ) )
	$content_width = 632;

$themecolors = array(
	'bg'     => 'ffffff',
	'text'   => '555555',
	'link'   => '3478e3',
	'border' => 'f1f1f1',
	'url'    => 'd54e21',
);

/**
 * Setup B4 Theme.
 *
 * Hooks into the after_setup_theme action.
 *
 * @uses b4_get_supported_post_formats()
 */
function b4_setup() {
	require_once( get_template_directory() . '/inc/custom-header.php' );
	b4_setup_custom_header();

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-formats', b4_get_supported_post_formats( 'post-format' ) );

	add_theme_support( 'custom-background', apply_filters( 'b4_custom_background_args', array( 'default-color' => 'f1f1f1' ) ) );

	add_filter( 'the_content', 'make_clickable', 12 ); // Run later to avoid shortcode conflicts

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'b4' ),
	) );

	if ( is_admin() && false === get_option( 'prologue_show_titles' ) )
		add_option( 'prologue_show_titles', 1 );
}
add_filter( 'after_setup_theme', 'b4_setup' );

function b4_register_sidebar() {
	register_sidebar( array(
		'name' => __( 'Sidebar', 'b4' ),
		'id'   => 'sidebar-1',
	) );
}
add_action( 'widgets_init', 'b4_register_sidebar' );

function b4_background_color() {
	$background_color = get_option( 'b4_background_color' );

	if ( '' != $background_color ) :
	?>
	<style type="text/css">
		body {
			background-color: <?php echo esc_attr( $background_color ); ?>;
		}
	</style>
	<?php endif;
}
add_action( 'wp_head', 'b4_background_color' );

function b4_background_image() {
	$b4_background_image = get_option( 'b4_background_image' );

	if ( 'none' == $b4_background_image || '' == $b4_background_image )
		return false;

?>
	<style type="text/css">
		body {
			background-image: url( <?php echo get_template_directory_uri() . '/i/backgrounds/pattern-' . sanitize_key( $b4_background_image ) . '.png' ?> );
		}
	</style>
<?php
}
add_action( 'wp_head', 'b4_background_image' );

/**
 * Add a custom class to the body tag for the background image theme option.
 *
 * This dynamic class is used to style the bundled background
 * images for retina screens. Note: The background images that
 * ship with B4 have been deprecated as of B4 1.5. For backwards
 * compatibility, B4 will still recognize them if the option was
 * set before upgrading.
 *
 * @since P2 1.5
 */
function b4_body_class_background_image( $classes ) {
	$image = get_option( 'b4_background_image' );

	if ( empty( $image ) || 'none' == $image )
		return $classes;

	$classes[] = esc_attr( 'b4-background-image-' . $image );

	return $classes;
}
add_action( 'body_class', 'b4_body_class_background_image' );

// Content Filters
function b4_title( $before = '<h2>', $after = '</h2>', $echo = true ) {
	if ( is_page() )
		return;

	if ( is_single() && false === b4_the_title( '', '', false ) ) { ?>
		<h2 class="transparent-title"><?php the_title(); ?></h2><?php
		return true;
	} else {
		b4_the_title( $before, $after, $echo );
	}
}

/**
 * Generate a nicely formatted post title
 *
 * Ignore empty titles, titles that are auto-generated from the
 * first part of the post_content
 *
 * @package WordPress
 * @subpackage B4
 * @since P2 1.0.5
 *
 * @param    string    $before    content to prepend to title
 * @param    string    $after     content to append to title
 * @param    string    $echo      echo or return
 * @return   string    $out       nicely formatted title, will be boolean(false) if no title
 */
function b4_the_title( $before = '<h2>', $after = '</h2>', $echo = true ) {
	global $post;

	$temp = $post;
	$t = apply_filters( 'the_title', $temp->post_title, $temp->ID );
	$title = $temp->post_title;
	$content = $temp->post_content;
	$pos = 0;
	$out = '';

	// Don't show post title if turned off in options or title is default text
	if ( 1 != (int) get_option( 'prologue_show_titles' ) || 'Post Title' == $title )
		return false;

	$content = trim( $content );
	$title = trim( $title );
	$title = preg_replace( '/\.\.\.$/', '', $title );
	$title = str_replace( "\n", ' ', $title );
	$title = str_replace( '  ', ' ', $title);
	$content = str_replace( "\n", ' ', strip_tags( $content) );
	$content = str_replace( '  ', ' ', $content );
	$content = trim( $content );
	$title = trim( $title );

	// Clean up links in the title
	if ( false !== strpos( $title, 'http' ) )  {
		$split = @str_split( $content, strpos( $content, 'http' ) );
		$content = $split[0];
		$split2 = @str_split( $title, strpos( $title, 'http' ) );
		$title = $split2[0];
	}

	// Avoid processing an empty title
	if ( '' == $title )
		return false;

	// Avoid processing the title if it's the very first part of the post content,
	// which is the case with most "status" posts
	$pos = strpos( $content, $title );
	if ( '' == get_post_format() || false === $pos || 0 < $pos ) {
		if ( is_single() )
			$out = $before . $t . $after;
		else
			$out = $before . '<a href="' . get_permalink( $temp->ID ) . '">' . $t . '&nbsp;</a>' . $after;

		if ( $echo )
			echo $out;
		else
			return $out;
	}

	return false;
}

function b4_comments( $comment, $args ) {
	$GLOBALS['comment'] = $comment;

	if ( !is_single() && get_comment_type() != 'comment' )
		return;

	$depth          = prologue_get_comment_depth( get_comment_ID() );
	$can_edit_post  = current_user_can( 'edit_post', $comment->comment_post_ID );

	$reply_link     = prologue_get_comment_reply_link(
		array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'before' => ' | ', 'reply_text' => __( 'Reply', 'b4' ) ),
		$comment->comment_ID, $comment->comment_post_ID );

	$content_class  = 'commentcontent';
	if ( $can_edit_post )
		$content_class .= ' comment-edit';

	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
		<?php do_action( 'b4_comment' ); ?>

		<?php echo get_avatar( $comment, 32 ); ?>
		<h4>
			<?php echo get_comment_author_link(); ?>
			<span class="meta">
				<?php echo b4_date_time_with_microformat( 'comment' ); ?>
				<span class="actions">
					<a class="thepermalink" href="<?php echo esc_url( get_comment_link() ); ?>" title="<?php esc_attr_e( 'Permalink', 'b4' ); ?>"><?php _e( 'Permalink', 'b4' ); ?></a>
					<?php
					echo $reply_link;

					if ( $can_edit_post )
						edit_comment_link( __( 'Edit', 'b4' ), ' | ' );

					?>
				</span>
			</span>
		</h4>
		<div id="commentcontent-<?php comment_ID(); ?>" class="<?php echo esc_attr( $content_class ); ?>"><?php
				echo apply_filters( 'comment_text', $comment->comment_content, $comment );

				if ( $comment->comment_approved == '0' ): ?>
					<p><em><?php esc_html_e( 'Your comment is awaiting moderation.', 'b4' ); ?></em></p>
				<?php endif; ?>
		</div>
	<?php
}

function get_tags_with_count( $post, $format = 'list', $before = '', $sep = '', $after = '' ) {
	$posttags = get_the_tags($post->ID, 'post_tag' );

	if ( !$posttags )
		return '';

	foreach ( $posttags as $tag ) {
		if ( $tag->count > 1 && !is_tag($tag->slug) ) {
			$tag_link = '<a href="' . get_tag_link( $tag ) . '" rel="tag">' . $tag->name . ' ( ' . number_format_i18n( $tag->count ) . ' )</a>';
		} else {
			$tag_link = $tag->name;
		}

		if ( $format == 'list' )
			$tag_link = '<li>' . $tag_link . '</li>';

		$tag_links[] = $tag_link;
	}

	return apply_filters( 'tags_with_count', $before . join( $sep, $tag_links ) . $after, $post );
}

function tags_with_count( $format = 'list', $before = '', $sep = '', $after = '' ) {
	global $post;
	echo get_tags_with_count( $post, $format, $before, $sep, $after );
}

function b4_title_from_content( $content ) {
	$title = b4_excerpted_title( $content, 8 ); // limit title to 8 full words

	// Try to detect image or video only posts, and set post title accordingly
	if ( empty( $title ) ) {
		if ( preg_match("/<object|<embed/", $content ) )
			$title = __( 'Video Post', 'b4' );
		elseif ( preg_match( "/<img/", $content ) )
			$title = __( 'Image Post', 'b4' );
	}

	return $title;
}

function b4_excerpted_title( $content, $word_count ) {
	$content = strip_tags( $content );
	$words = preg_split( '/([\s_;?!\/\(\)\[\]{}<>\r\n\t"]|\.$|(?<=\D)[:,.\-]|[:,.\-](?=\D))/', $content, $word_count + 1, PREG_SPLIT_NO_EMPTY );

	if ( count( $words ) > $word_count ) {
		array_pop( $words ); // remove remainder of words
		$content = implode( ' ', $words );
		$content = $content . '...';
	} else {
		$content = implode( ' ', $words );
	}

	$content = trim( strip_tags( $content ) );

	return $content;
}

function b4_add_reply_title_attribute( $link ) {
	return str_replace( "rel='nofollow'", "rel='nofollow' title='" . __( 'Reply', 'b4' ) . "'", $link );
}
add_filter( 'post_comments_link', 'b4_add_reply_title_attribute' );

function b4_fix_empty_titles( $data, $postarr ) {
	if ( 'post' != $data['post_type'] )
		return $data;

	if ( ! empty( $postarr['post_title'] ) )
		return $data;

	$data['post_title'] = b4_title_from_content( $data['post_content'] );

	return $data;
}
add_filter( 'wp_insert_post_data', 'b4_fix_empty_titles', 10, 2 );

function b4_add_head_content() {
	if ( is_home() && is_user_logged_in() ) {
		include_once( ABSPATH . '/wp-admin/includes/media.php' );
	}
}
add_action( 'wp_head', 'b4_add_head_content' );

function b4_new_post_noajax() {
	if ( empty( $_POST['action'] ) || $_POST['action'] != 'post' )
	    return;

	if ( !is_user_logged_in() )
		auth_redirect();

	if ( !current_user_can( 'publish_posts' ) ) {
		wp_redirect( home_url( '/' ) );
		exit;
	}

	$current_user = wp_get_current_user();

	check_admin_referer( 'new-post' );

	$user_id        = $current_user->ID;
	$post_content   = $_POST['posttext'];
	$tags           = $_POST['tags'];

	$post_title = b4_title_from_content( $post_content );

	$post_id = wp_insert_post( array(
		'post_author'   => $user_id,
		'post_title'    => $post_title,
		'post_content'  => $post_content,
		'tags_input'    => $tags,
		'post_status'   => 'publish'
	) );

	$post_format = 'status';
	if ( in_array( $_POST['post_format'], b4_get_supported_post_formats() ) )
		$post_format = $_POST['post_format'];

	set_post_format( $post_id, $post_format );

	wp_redirect( home_url( '/' ) );

	exit;
}
add_filter( 'template_redirect', 'b4_new_post_noajax' );

/**
 * iPhone Stylesheet.
 *
 * Hooks into the wp_enqueue_scripts action late.
 *
 * @uses b4_is_iphone()
 * @since P2 1.4
 */
function b4_iphone_style() {
	if ( b4_is_iphone() ) {
		wp_enqueue_style(
			'b4-iphone-style',
			get_template_directory_uri() . '/style-iphone.css',
			array(),
			'20120402'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'b4_iphone_style', 1000 );

/**
 * Print Stylesheet.
 *
 * Hooks into the wp_enqueue_scripts action.
 *
 * @since P2 1.5
 */
function b4_print_style() {
	wp_enqueue_style( 'b4', get_stylesheet_uri() );
	wp_enqueue_style( 'b4-print-style', get_template_directory_uri() . '/style-print.css', array( 'b4' ), '20120807', 'print' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'b4_print_style' );

/*
	Modified to replace query string with blog url in output string
*/
function prologue_get_comment_reply_link( $args = array(), $comment = null, $post = null ) {
	global $user_ID;

	if ( post_password_required() )
		return;

	$defaults = array( 'add_below' => 'commentcontent', 'respond_id' => 'respond', 'reply_text' => __( 'Reply', 'b4' ),
		'login_text' => __( 'Log in to Reply', 'b4' ), 'depth' => 0, 'before' => '', 'after' => '' );

	$args = wp_parse_args($args, $defaults);
	if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] )
		return;

	extract($args, EXTR_SKIP);

	$comment = get_comment($comment);
	$post = get_post($post);

	if ( 'open' != $post->comment_status )
		return false;

	$link = '';

	$reply_text = esc_html( $reply_text );

	if ( get_option( 'comment_registration' ) && !$user_ID )
		$link = '<a rel="nofollow" href="' . site_url( 'wp-login.php?redirect_to=' . urlencode( get_permalink() ) ) . '">' . esc_html( $login_text ) . '</a>';
	else
		$link = "<a rel='nofollow' class='comment-reply-link' href='". get_permalink($post). "#" . urlencode( $respond_id ) . "' title='". __( 'Reply', 'b4' )."' onclick='return addComment.moveForm(\"" . esc_js( "$add_below-$comment->comment_ID" ) . "\", \"$comment->comment_ID\", \"" . esc_js( $respond_id ) . "\", \"$post->ID\")'>$reply_text</a>";
	return apply_filters( 'comment_reply_link', $before . $link . $after, $args, $comment, $post);
}

function prologue_comment_depth_loop( $comment_id, $depth )  {
	$comment = get_comment( $comment_id );

	if ( isset( $comment->comment_parent ) && 0 != $comment->comment_parent ) {
		return prologue_comment_depth_loop( $comment->comment_parent, $depth + 1 );
	}
	return $depth;
}

function prologue_get_comment_depth( $comment_id ) {
	return prologue_comment_depth_loop( $comment_id, 1 );
}

function prologue_comment_depth( $comment_id ) {
	echo prologue_get_comment_depth( $comment_id );
}

function prologue_poweredby_link() {
	return apply_filters( 'prologue_poweredby_link', sprintf( '<a href="%1$s" rel="generator">%2$s</a>', esc_url( __('http://wordpress.org/', 'b4') ), sprintf( __('Proudly powered by %s.', 'b4'), 'WordPress' ) ) );
}

function b4_hidden_sidebar_css() {
	$hide_sidebar = get_option( 'b4_hide_sidebar' );
		$sleeve_margin = ( is_rtl() ) ? 'margin-left: 0;' : 'margin-right: 0;';
	if ( '' != $hide_sidebar ) :
	?>
	<style type="text/css">
		.sleeve_main { <?php echo $sleeve_margin;?> }
		#wrapper { background: transparent; }
		#header, #footer, #wrapper { width: 760px; }
	</style>
	<?php endif;
}
add_action( 'wp_head', 'b4_hidden_sidebar_css' );

// Network signup form
function b4_before_signup_form() {
	echo '<div class="sleeve_main"><div id="main">';
}
add_action( 'before_signup_form', 'b4_before_signup_form' );

function b4_after_signup_form() {
	echo '</div></div>';
}
add_action( 'after_signup_form', 'b4_after_signup_form' );

/**
 * Returns accepted post formats.
 *
 * The value should be a valid post format registered for B4, or one of the back compat categories.
 * post formats: link, quote, standard, status
 * categories: link, post, quote, status
 *
 * @since P2 1.3.4
 *
 * @param string type Which data to return (all|category|post-format)
 * @return array
 */
function b4_get_supported_post_formats( $type = 'all' ) {
	$post_formats = array( 'link', 'quote', 'status' );

	switch ( $type ) {
		case 'post-format':
			break;
		case 'category':
			$post_formats[] = 'post';
			break;
		case 'all':
		default:
			array_push( $post_formats, 'post', 'standard' );
			break;
	}

	return apply_filters( 'b4_get_supported_post_formats', $post_formats );
}

/**
 * Is site being viewed on an iPhone or iPod Touch?
 *
 * For testing you can modify the output with a filter:
 * add_filter( 'b4_is_iphone', '__return_true' );
 *
 * @return bool
 * @since P2 1.4
 */
function b4_is_iphone() {
	$output = false;

	if ( ( isset( $_SERVER['HTTP_USER_AGENT'] ) && strstr( $_SERVER['HTTP_USER_AGENT'], 'iPhone' ) && ! strstr( $_SERVER['HTTP_USER_AGENT'], 'iPad' ) ) || isset( $_GET['iphone'] ) && $_GET['iphone'] )
		$output = true;

	$output = (bool) apply_filters( 'b4_is_iphone', $output );

	return $output;
}

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @since P2 1.5
 */
function b4_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() )
		return $title;

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $sep $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= " $sep " . sprintf( __( 'Page %s', 'b4' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'b4_wp_title', 10, 2 );
