<?php
/**
 * Template for manage widgets screen - for bbpress members.
 *
 * @version 1.0.0
 * @package frontpage-buddy
 */

defined( 'ABSPATH' ) || exit;

do_action( 'frontpage_buddy_manage_frontpage__before', 'bbp_profiles' );
?>

<?php /* The css class 'fpbuddy_manage_widgets' is required by javascript */ ?>
<div class="fpbuddy_manage_widgets fpbuddy_wrapper">
	<div class="fpbuddy_container">
		<div class="fpbuddy_title">
			<h3><?php esc_html_e( 'Personalize your profile', 'frontpage-buddy' ); ?></h3>
		</div>

		<div class="fpbuddy_content">
			<p>
				<?php esc_html_e( 'Personalize your profile by adding text, videos, embedding your social media feed, etc.', 'frontpage-buddy' ); ?>
			</p>
			
			<div class="fpbuddy_added_widgets fpbuddy_wrapper">
				<div class="fpbuddy_container">
					<div class="fpbuddy_content">
						<?php /* The id must be 'fpbuddy_fp_layout_outer' as it is required by javascript */ ?>
						<div id="fpbuddy_fp_layout_outer">
							<img src="<?php echo esc_attr( FRONTPAGE_BUDDY_PLUGIN_URL ); ?>assets/images/spinner.gif" class="img_loading" >
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'frontpage_buddy_manage_frontpage__after', 'bbp_profiles' ); ?>
