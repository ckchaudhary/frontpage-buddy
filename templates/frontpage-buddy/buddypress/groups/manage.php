<?php
/**
 * Template for manage widgets screen - for buddypress groups.
 *
 * @since 1.0.0
 * @package frontpage-buddy
 */

do_action( 'frontpage_buddy_manage_frontpage__before', 'bp_groups' );
?>

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

<?php
$inline_script  = 'jQuery( ($) => {';
$inline_script .= 'let fpbuddy_manager = new FPBuddyWidgetsManager({';
$inline_script .= '"el_outer" : ".fpbuddy_manage_widgets",';
$inline_script .= '"el_content" : "#fpbuddy_fp_layout_outer",';
$inline_script .= '})';
$inline_script .= '});';
wp_add_inline_script(
	'frontpage-buddy-editor',
	$inline_script
);
