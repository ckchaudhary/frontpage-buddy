<?php
/**
 * Template for manage widgets screen - for buddypress groups.
 *
 * @version 1.0.0
 * @package frontpage-buddy
 */

! defined( 'ABSPATH' ) ? exit : '';

do_action( 'frontpage_buddy_manage_frontpage__before', 'bp_groups' );
?>
<?php /* The css class 'fpbuddy_manage_widgets' is required by javascript */ ?>
<div class="fpbuddy_manage_widgets fpbuddy_wrapper">
	<div class="fpbuddy_container">
		<div class="fpbuddy_title">
			<h3><?php esc_html_e( 'Customize group\'s front page', 'frontpage-buddy' ); ?></h3>
		</div>

		<div class="fpbuddy_content">
			<p>
				<?php esc_html_e( 'Customize this group\'s front page by adding text, images, embedding your social media feed, etc.', 'frontpage-buddy' ); ?>
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

<?php do_action( 'frontpage_buddy_manage_frontpage__after', 'bp_groups' ); ?>
