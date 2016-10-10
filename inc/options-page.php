<?php
/**
 * Theme options page.
 *
 * @package B4
 */

add_action( 'admin_menu', array( 'B4_Options', 'init' ) );

class B4_Options {

	static function init() {
		global $plugin_page;

		add_theme_page( __( 'Theme Options', 'b4' ), __( 'Theme Options', 'b4' ), 'edit_theme_options', 'b4-options-page', array( 'B4_Options', 'page' ) );

		if ( 'b4-options-page' == $plugin_page ) {
			wp_enqueue_script( 'farbtastic' );
			wp_enqueue_style( 'farbtastic' );
			wp_enqueue_script( 'colorpicker' );
			wp_enqueue_style( 'colorpicker' );
		}
	}

	static function page() {
		register_setting( 'b4ops', 'prologue_show_titles' );
		register_setting( 'b4ops', 'b4_allow_users_publish' );
		register_setting( 'b4ops', 'b4_prompt_text' );
		register_setting( 'b4ops', 'b4_hide_sidebar' );
		register_setting( 'b4ops', 'b4_background_color' );
		register_setting( 'b4ops', 'b4_background_image' );
		register_setting( 'b4ops', 'b4_hide_threads' );

		$prologue_show_titles_val    = get_option( 'prologue_show_titles' );
		$b4_allow_users_publish_val  = get_option( 'b4_allow_users_publish' );
		$b4_prompt_text_val          = get_option( 'b4_prompt_text' );
		$b4_hide_sidebar             = get_option( 'b4_hide_sidebar' );
		$b4_background_color         = get_option( 'b4_background_color' );
		$b4_background_image         = get_option( 'b4_background_image' );
		$b4_hide_threads             = get_option( 'b4_hide_threads' );

		if ( isset( $_POST[ 'action' ] ) && esc_attr( $_POST[ 'action' ] ) == 'update' ) {
			check_admin_referer( 'b4ops-options' );

			if ( isset( $_POST[ 'prologue_show_titles' ] ) )
				$prologue_show_titles_val = intval( $_POST[ 'prologue_show_titles' ] );
			else
				$prologue_show_titles_val = 0;

			if ( isset( $_POST[ 'b4_allow_users_publish' ] ) )
				$b4_allow_users_publish_val = intval( $_POST[ 'b4_allow_users_publish' ] );
			else
				$b4_allow_users_publish_val = 0;

			if ( isset( $_POST[ 'b4_background_color_hex' ] ) ) {
				// color value must be 3 or 6 hexadecimal characters
				if ( preg_match( '/^#?([a-f0-9]{3}){1,2}$/i', $_POST['b4_background_color_hex'] ) ) {
					$b4_background_color = $_POST['b4_background_color_hex'];
					// if color value doesn't have a preceding hash, add it
					if ( false === strpos( $b4_background_color, '#' ) )
						$b4_background_color = '#' . $b4_background_color;
				} else {
					$b4_background_color = '';
				}
			}

			if ( esc_attr( $_POST[ 'b4_prompt_text' ] ) != __( "Whatcha' up to?", 'b4') )
				$b4_prompt_text_val = esc_attr( $_POST[ 'b4_prompt_text' ] );

			if ( isset( $_POST[ 'b4_hide_sidebar' ] ) )
				$b4_hide_sidebar = intval( $_POST[ 'b4_hide_sidebar' ] );
			else
				$b4_hide_sidebar = false;

			if ( isset( $_POST[ 'b4_hide_threads' ] ) )
				$b4_hide_threads = $_POST[ 'b4_hide_threads' ];
			else
				$b4_hide_threads = false;

			if ( isset( $_POST['b4_background_image'] ) )
				$b4_background_image = $_POST[ 'b4_background_image' ];
			else
				$b4_background_image = 'none';

			update_option( 'prologue_show_titles', $prologue_show_titles_val );
			update_option( 'b4_allow_users_publish', $b4_allow_users_publish_val );
			update_option( 'b4_prompt_text', $b4_prompt_text_val );
			update_option( 'b4_hide_sidebar', $b4_hide_sidebar );
			update_option( 'b4_background_color', $b4_background_color );
			update_option( 'b4_background_image', $b4_background_image );
			update_option( 'b4_hide_threads', $b4_hide_threads );

		?>
			<div class="updated"><p><strong><?php _e( 'Options saved.', 'b4' ); ?></strong></p></div>
		<?php

			} ?>

		<div class="wrap">
	    <?php echo "<h2>" . __( 'B4 Options', 'b4' ) . "</h2>"; ?>

		<form enctype="multipart/form-data" name="form1" method="post" action="<?php echo esc_attr( str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ) ); ?>">

			<h3 style="font-family: georgia, times, serif; font-weight: normal; border-bottom: 1px solid #ddd; padding-bottom: 5px">
				<?php _e( 'Functionality Options', 'b4' ); ?>
			</h3>

			<?php settings_fields( 'b4ops' ); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php _e( 'Posting Access:', 'b4' ); ?></th>
						<td>

						<input id="b4_allow_users_publish" type="checkbox" name="b4_allow_users_publish" <?php if ( $b4_allow_users_publish_val == 1 ) echo 'checked="checked"'; ?> value="1" />

						<?php if ( defined( 'IS_WPCOM' ) && IS_WPCOM )
								$msg = __( 'Allow any WordPress.com member to post', 'b4' );
							  else
							  	$msg = __( 'Allow any registered member to post', 'b4' );
						 ?>

						<label for="b4_allow_users_publish"><?php echo $msg; ?></label>

						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Hide Threads:', 'b4' ); ?></th>
						<td>

						<input id="b4_hide_threads" type="checkbox" name="b4_hide_threads" <?php if ( $b4_hide_threads == 1 ) echo 'checked="checked"'; ?> value="1" />
						<label for="b4_hide_threads"><?php _e( 'Hide comment threads by default on all non-single views', 'b4' ); ?></label>

						</td>
					</tr>
				</tbody>
			</table>

			<p>&nbsp;</p>

			<h3 style="font-family: georgia, times, serif; font-weight: normal; border-bottom: 1px solid #ddd; padding-bottom: 5px">
				<?php _e( 'Design Options', 'b4' ); ?>
			</h3>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php _e( 'Custom Background Color:', 'b4' ); ?></th>
						<td>
							<input id="pickcolor" type="button" class="button" name="pickcolor" value="<?php esc_attr_e( 'Pick a Color', 'b4' ); ?> "/>
							<input name="b4_background_color_hex" id="b4_background_color_hex" type="text" value="<?php echo esc_attr( $b4_background_color ); ?>" />
							<div id="colorPickerDiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"> </div>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Background Image:', 'b4' ); ?></th>
						<td>
							<input type="radio" id="bi_none" name="b4_background_image" value="none"<?php if ( 'none' == $b4_background_image ) : ?> checked="checked" <?php endif; ?>/> <label for="bi_none"><?php _e( 'None', 'b4' ); ?></label><br />
							<input type="radio" id="bi_bubbles" name="b4_background_image" value="bubbles"<?php if ( 'bubbles' == $b4_background_image ) : ?> checked="checked" <?php endif; ?>/> <label for="bi_bubbles"><?php _e( 'Bubbles', 'b4' ); ?></label><br />
							<input type="radio" id="bi_polka" name="b4_background_image" value="dots"<?php if ( 'dots' == $b4_background_image ) : ?> checked="checked" <?php endif; ?>/> <label for="bi_polka"><?php _e( 'Polka Dots', 'b4' ); ?></label><br />
							<input type="radio" id="bi_squares" name="b4_background_image" value="squares"<?php if ( 'squares' == $b4_background_image ) : ?> checked="checked" <?php endif; ?>/> <label for="bi_squares"><?php _e( 'Squares', 'b4' ); ?></label><br />
							<input type="radio" id="bi_plaid" name="b4_background_image" value="plaid"<?php if ( 'plaid' == $b4_background_image ) : ?> checked="checked" <?php endif; ?>/> <label for="bi_plaid"><?php _e( 'Plaid', 'b4' ); ?></label><br />
							<input type="radio" id="bi_stripes" name="b4_background_image" value="stripes"<?php if ( 'stripes' == $b4_background_image ) : ?> checked="checked" <?php endif; ?>/> <label for="bi_stripes"><?php _e( 'Stripes', 'b4' ); ?></label><br />
							<input type="radio" id="bi_santa" name="b4_background_image" value="santa"<?php if ( 'santa' == $b4_background_image ) : ?> checked="checked" <?php endif; ?>/> <label for="bi_santa"><?php _e( 'Santa', 'b4' ); ?></label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Sidebar display:', 'b4' ); ?></th>
						<td>
							<input id="b4_hide_sidebar" type="checkbox" name="b4_hide_sidebar" <?php if ( $b4_hide_sidebar ) echo 'checked="checked"'; ?> value="1" />
							<label for="b4_hide_sidebar"><?php _e( 'Hide the Sidebar', 'b4' ); ?></label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Post prompt:', 'b4' ); ?></th>
						<td>
							<input id="b4_prompt_text" type="input" name="b4_prompt_text" value="<?php echo ($b4_prompt_text_val == __( "Whatcha' up to?", 'b4') ) ? __("Whatcha' up to?", 'b4') : stripslashes( $b4_prompt_text_val ); ?>" />
				 			(<?php _e( 'if empty, defaults to <strong>Whatcha up to?</strong>', 'b4' ); ?>)
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Post Titles:', 'b4' )?></th>
						<td>
							<input id="prologue_show_titles" type="checkbox" name="prologue_show_titles" <?php if ( $prologue_show_titles_val != 0 ) echo 'checked="checked"'; ?> value="1" />
							<label for="prologue_show_titles"><?php _e( 'Display titles', 'b4' ); ?></label>
						</td>
					</tr>
				</tbody>
			</table>

			<p>
			</p>

			<p class="submit">
				<input type="submit" name="Submit" value="<?php esc_attr_e( 'Update Options', 'b4' ); ?>" />
			</p>

		</form>
		</div>

<script type="text/javascript">
/* <![CDATA[ */
	var farbtastic;

	function pickColor(color) {
		jQuery('#b4_background_color_hex').val(color);
		farbtastic.setColor(color);
	}

	jQuery(document).ready(function() {
		jQuery('#pickcolor').click(function() {
			jQuery('#colorPickerDiv').show();
		});

		jQuery('#hidetext' ).click(function() {
			toggle_text();
		});

		farbtastic = jQuery.farbtastic( '#colorPickerDiv', function(color) { pickColor(color); });
	});

	jQuery(document).mousedown(function(){
		// Make the picker disappear, since we're using it in an independant div
		hide_picker();
	});

	function colorDefault() {
		pickColor( '#<?php echo HEADER_TEXTCOLOR; ?>' );
	}

	function hide_picker(what) {
		var update = false;
		jQuery('#colorPickerDiv').each(function(){
			var id = jQuery(this).attr( 'id' );
			if (id == what) {
				return;
			}
			var display = jQuery(this).css( 'display' );
			if (display == 'block' ) {
				jQuery(this).fadeOut(2);
			}
		});
	}

	function toggle_text(force) {
		if (jQuery('#textcolor').val() == 'blank' ) {
			//Show text
			jQuery(buttons.toString()).show();
			jQuery('#textcolor').val( '<?php echo HEADER_TEXTCOLOR; ?>' );
			jQuery('#hidetext').val( '<?php _e( 'Hide Text', 'b4' ); ?>' );
		}
		else {
			//Hide text
			jQuery(buttons.toString()).hide();
			jQuery('#textcolor').val( 'blank' );
			jQuery('#hidetext').val( '<?php _e( 'Show Text', 'b4' ); ?>' );
		}
	}
/* ]]> */
</script>

<?php
	}
}
