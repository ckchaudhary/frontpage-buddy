<?php
/**
 * Template for manage widgets screen - for buddypress user profiles.
 *
 * @since 1.0.0
 * @package frontpage-buddy
 */

do_action( 'frontpage_buddy_manage_frontpage__before', 'bp_members' );
?>

<div class="fpbuddy_manage_widgets fpbuddy_wrapper">
	<div class="fpbuddy_container">
		<div class="fpbuddy_content">
			<p>
				<?php esc_html_e( 'Customize your front page by adding text, images, embedding your social media feed, etc.', 'frontpage-buddy' ); ?>
			</p>
			
			<div class="fpbuddy_added_widgets fpbuddy_wrapper">
				<div class="fpbuddy_container">
					<div class="fpbuddy_content">
						<div id="fpbuddy_fp_layout_outer">
							<img src="<?php echo esc_attr( FPBUDDY_PLUGIN_URL ); ?>assets/images/spinner.gif" class="img_loading" >
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'frontpage_buddy_manage_frontpage__after', 'bp_members' ); ?>

<script>
	jQuery( ($) => {
		let fpbuddy_manager = new FPBuddyWidgetsManager( {
			'el_outer' : '.fpbuddy_manage_widgets',
			'el_content' : '#fpbuddy_fp_layout_outer',
		} )
	});
</script>