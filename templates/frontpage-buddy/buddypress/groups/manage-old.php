<?php
/**
 * Template for manage widgets screen - for buddypress groups.
 *
 * @since 1.0.0
 * @package frontpage-buddy
 */

do_action( 'frontpage_buddy_manage_frontpage__before', 'groups' );

$is_enabled = frontpage_buddy()->bp_groups()->has_custom_front_page();
?>
<div class="fpbuddy_enable_fp fpbuddy_wrapper">
	<div class="fpbuddy_container">
		<div class="fpbuddy_content">
			<div class="fpbuddy_fpstatus <?php echo $is_enabled ? 'fpbuddy_hidden' : ''; ?> hide_if_fp_enabled">
				<p class="alert alert-success">
					<?php _e( 'A custom front page for this group is not enabled yet. Before enabling it, make sure you have added some content for the front page.', 'frontpage-buddy' ); ?>
				</p>
			</div>

			<span><strong><?php esc_html_e( 'Enable custom front page?', 'frontpage-buddy' ); ?></strong></span>
			<label class="fpbuddy-switch">
				<input type="checkbox" name="has_custom_frontpage" value="yes" <?php echo $is_enabled ? 'checked' : ''; ?> >
				<span class="switch-mask"></span>
				<span class="switch-labels">
					<span class="label-on">Yes</span>
					<span class="label-off">No</span>
				</span>
			</label>

			<div class="fpbuddy_fpstatus <?php echo $is_enabled ? '' : 'fpbuddy_hidden'; ?> show_if_fp_enabled">
				<p class="alert alert-success">
					<?php _e( 'This group now has a custom front page!', 'frontpage-buddy' ); ?>
					&nbsp;
					<?php printf( "<a href='%s'>%s</a>", bp_get_group_url( bp_get_current_group_id() ), __( 'View', 'frontpage-buddy' ) ); ?>
				</p>
			</div>
		</div>
	</div>
</div>

<div class="fpbuddy_manage_widgets fpbuddy_wrapper">
	<div class="fpbuddy_container">
		<div class="fpbuddy_title">
			<h3><?php esc_html_e( 'Customize your front page', 'frontpage-buddy' ); ?></h3>
		</div>

		<div class="fpbuddy_content">
			<p>
				<?php _e( 'Customize your front page by adding text, images, embedding your social media feed, etc.', 'frontpage-buddy' ); ?>
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

<?php do_action( 'frontpage_buddy_manage_frontpage__after', 'groups' ); ?>

<script>
	jQuery( ($) => {
		let fpbuddy_manager = new FPBuddyWidgetsManager( {
			'el_outer' : '.fpbuddy_manage_widgets',
			'el_content' : '#fpbuddy_fp_layout_outer',
		} )
	});
</script>