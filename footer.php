<?php
/**
 * Footer template.
 *
 * @package B4
 */
?>
	<div class="clear"></div>

</div> <!-- // wrapper -->

<div id="footer">
	<p>
		<?php echo prologue_poweredby_link(); ?>
		<?php
				printf(
					__( 'Theme: %1$s by %2$s.', 'b4' ),
					'<a href="https://wordpress.com/themes/b4">B4</a>',
					'<a href="https://wordpress.com/themes/" rel="designer">WordPress.com</a>'
				);
			?>
	</p>
</div>

<div id="notify"></div>

<div id="help">
	<dl class="directions">
		<dt>c</dt><dd><?php _e( 'Compose new post', 'b4' ); ?></dd>
		<dt>j</dt><dd><?php _e( 'Next post/Next comment', 'b4' ); ?></dd>
		<dt>k</dt> <dd><?php _e( 'Previous post/Previous comment', 'b4' ); ?></dd>
		<dt>r</dt> <dd><?php _e( 'Reply', 'b4' ); ?></dd>
		<dt>e</dt> <dd><?php _e( 'Edit', 'b4' ); ?></dd>
		<dt>o</dt> <dd><?php _e( 'Show/Hide comments', 'b4' ); ?></dd>
		<dt>t</dt> <dd><?php _e( 'Go to top', 'b4' ); ?></dd>
		<dt>l</dt> <dd><?php _e( 'Go to login', 'b4' ); ?></dd>
		<dt>h</dt> <dd><?php _e( 'Show/Hide help', 'b4' ); ?></dd>
		<dt><?php _e( 'shift + esc', 'b4' ); ?></dt> <dd><?php _e( 'Cancel', 'b4' ); ?></dd>
	</dl>
</div>

<?php wp_footer(); ?>

</body>
</html>