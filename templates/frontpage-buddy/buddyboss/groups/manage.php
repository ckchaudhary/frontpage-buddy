<?php
/**
 * Template for manage widgets screen - for buddypress groups.
 *
 * @version 1.0.0
 * @package frontpage-buddy
 */

do_action( 'frontpage_buddy_manage_frontpage__before', 'bp_groups' );

$integration = frontpage_buddy()->get_integration( 'buddyboss_groups' );
$group_url   = '';
if ( function_exists( '\bp_get_group_url' ) ) {
	$group_url = bp_get_group_url( groups_get_current_group(), array( $integration->get_option( 'frontpage_nav_slug' ) ) );
} else {
	$group_url = bp_get_group_permalink( groups_get_current_group() ) . $integration->get_option( 'frontpage_nav_slug' );
}

$is_enabled = $integration->has_custom_front_page( bp_get_current_group_id() );
?>

<div class="fpbuddy_manage_widgets fpbuddy_wrapper">
	<div class="fpbuddy_enable_fp fpbuddy_wrapper">
		<div class="fpbuddy_container">
			<div class="fpbuddy_content">
				<div class="fpbuddy_fpstatus <?php echo $is_enabled ? 'fpbuddy_hidden' : ''; ?> hide_if_fp_enabled">
					<p class="alert alert-warning">
						<?php esc_html_e( 'A custom front page for this group is not enabled yet. Before enabling it, make sure you have added some content for the front page.', 'frontpage-buddy' ); ?>
					</p>
				</div>

				<p>
					<span><strong><?php esc_html_e( 'Enable custom front page?', 'frontpage-buddy' ); ?></strong></span>
					<label class="fpbuddy-switch">
						<input type="checkbox" name="has_custom_frontpage" value="yes" <?php echo $is_enabled ? 'checked' : ''; ?> >
						<span class="switch-mask"></span>
						<span class="switch-labels">
							<span class="label-on">Yes</span>
							<span class="label-off">No</span>
						</span>
					</label>
				</p>

				<div class="fpbuddy_fpstatus <?php echo $is_enabled ? '' : 'fpbuddy_hidden'; ?> show_if_fp_enabled">
					<p class="alert alert-success">
						<?php esc_html_e( 'This group has a custom front page.', 'frontpage-buddy' ); ?>
						&nbsp;
						<?php printf( "<a href='%s'>%s</a>", esc_attr( $group_url ), esc_attr__( 'View', 'frontpage-buddy' ) ); ?>
					</p>
				</div>
			</div>
		</div>
	</div>


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
