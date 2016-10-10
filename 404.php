<?php
/**
 * 404 Post not found template.
 *
 * @package B4
 */
?>
<?php get_header(); ?>

<div class="sleeve_main">

	<div id="main">

		<h2><?php _e( 'Not Found', 'b4' ); ?></h2>
		<p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', 'b4' ); ?></p>
		<?php get_search_form(); ?>

	</div> <!-- main -->

</div> <!-- sleeve -->

<?php get_footer(); ?>