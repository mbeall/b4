<?php
/**
 * Template part for displaying comments.
 *
 * @package B4
 */
?>

<?php if ( post_password_required() )
	return;
?>

<ul id="comments-<?php the_id(); ?>" class="commentlist inlinecomments">
<?php if ( get_comments_number() > 0 ) : ?>
	<?php wp_list_comments( array( 'callback' => 'b4_comments' ) ); ?>
<?php endif; ?>
</ul>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
<div class="navigation">
	<p class="nav-older"><?php previous_comments_link( __( '&larr; Older Comments', 'b4' ) ); ?></p>
	<p class="nav-newer"><?php next_comments_link( __( 'Newer Comments &rarr;', 'b4' ) ); ?></p>
</div>
<?php endif; ?>