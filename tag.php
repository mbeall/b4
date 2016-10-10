<?php
/**
 * Tag Archive Template.
 *
 * @package B4
 */
?>
<?php get_header(); ?>
<?php $tag_obj = $wp_query->get_queried_object(); ?>

<div class="sleeve_main">

	<div id="main">
		<h2><?php printf( __( 'Tagged: %s', 'b4' ), single_tag_title( '', false) ); ?>
			<span class="controls">
				<a href="#" id="togglecomments"> <?php _e( 'Toggle Comment Threads', 'b4' ); ?></a> | <a href="#directions" id="directions-keyboard"><?php _e( 'Keyboard Shortcuts', 'b4' ); ?></a>
			</span>
		</h2>

		<?php if ( have_posts() ) : ?>

			<ul id="postlist">
			<?php while ( have_posts() ) : the_post(); ?>

				<?php b4_load_entry(); ?>

			<?php endwhile; ?>
			</ul>

		<?php else : ?>

			<div class="no-posts">
			    <h3><?php _e( 'No posts found!', 'b4' ); ?></h3>
			</div>

		<?php endif ?>

		<div class="navigation">
			<p class="nav-older"><?php next_posts_link( __( '&larr; Older posts', 'b4' ) ); ?></p>
			<p class="nav-newer"><?php previous_posts_link( __( 'Newer posts &rarr;', 'b4' ) ); ?></p>
		</div>

	</div> <!-- main -->

</div> <!-- sleeve -->

<?php get_footer(); ?>