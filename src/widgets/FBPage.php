<?php
/**
 * Richcontent widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed facebook page widget.
 */
class FBPage extends Widget {
	public function __construct( $args = '' ) {
		$this->type           = 'fbpage';
		$this->name           = 'Facebook Page';
		$this->description    = 'The Page plugin lets you easily embed and promote any Facebook Page. Just like on Facebook, your visitors can like and share the Page without leaving your site.';
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-fb-pages.png';

		$this->setup( $args );
	}

	public function output() {
		$fb_url = $this->options['url'];

		if ( ! $fb_url ) {
			return;
		}

		$this->output_start();
		$this->output_title();
		?>
		<div class="bpfp_w_content">
			<div id="fb-root"></div>
			<script>(function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id))
						return;
					js = d.createElement(s);
					js.id = id;
					js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.7&appId=551595108254181";
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>

			<div class="fb-page" 
				data-href="<?php echo esc_url( $fb_url ); ?>" 
				data-tabs="<?php echo ! empty( $this->options['tabs'] ) ? esc_attr( $this->options['tabs'] ) : 'timeline'; ?>" 
				<?php
				if ( ! empty( $this->options['theight'] ) ) {
					echo "data-height='" . esc_attr( $this->options['theight'] ) . "'";
				}
				
				if ( ! empty( $this->options['twidth'] ) ) {
					echo "data-width='" . esc_attr( $this->options['twidth'] ) . "'";
				}
				?>
				data-small-header="<?php echo ! empty( $this->options['smallheader'] ) ? 'true' : 'false'; ?>" 
				data-adapt-container-width="<?php echo ! empty( $this->options['adaptwidth'] ) ? 'true' : 'false'; ?>" 
				data-hide-cover="<?php echo ! empty( $this->options['hidecover'] ) ? 'true' : 'false'; ?>" 
				data-show-facepile="<?php echo ! empty( $this->options['facepile'] ) ? 'true' : 'false'; ?>" 
				>
				<blockquote cite="<?php echo esc_url( $fb_url ); ?>" class="fb-xfbml-parse-ignore">
					<a href="<?php echo esc_url( $fb_url ); ?>"></a>
				</blockquote>
			</div>
		</div>
		<?php
		$this->output_end();
	}

	public function get_fields() {
		return array(
			'url'         => array(
				'type'       => 'url',
				'label'      => __( 'Facebook Page URL', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'url' ) ) ? $this->edit_field_value( 'url' ) : '',
				'attributes' => array( 'placeholder' => __( 'The url of the facebook page', 'frontpage-buddy' ) ),
			),
			'tabs'        => array(
				'type'       => 'text',
				'label'      => __( 'Tabs', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'tabs' ) ) ? $this->edit_field_value( 'tabs' ) : '',
				'attributes' => array( 'placeholder' => __( 'e.g: timeline, messages, events', 'frontpage-buddy' ) ),
			),
			'theight'     => array(
				'type'       => 'number',
				'label'      => __( 'Height', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'tabs' ) ) ? $this->edit_field_value( 'tabs' ) : '',
				'attributes' => array( 'placeholder' => __( 'Height in pixels (optional)', 'frontpage-buddy' ) ),
			),
			'twidth'      => array(
				'type'       => 'number',
				'label'      => __( 'Width', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'tabs' ) ) ? $this->edit_field_value( 'tabs' ) : '',
				'attributes' => array( 'placeholder' => __( 'Width in pixels (optional)', 'frontpage-buddy' ) ),
			),
			'smallheader' => array(
				'type'    => 'checkbox',
				'label'   => '',
				'value'   => ! empty( $this->edit_field_value( 'smallheader' ) ) ? $this->edit_field_value( 'smallheader' ) : '',
				'options' => array( 'yes' => __( 'Use Small Header', 'frontpage-buddy' ) ),
			),
			'hidecover'   => array(
				'type'    => 'checkbox',
				'label'   => '',
				'value'   => ! empty( $this->edit_field_value( 'hidecover' ) ) ? $this->edit_field_value( 'hidecover' ) : '',
				'options' => array( 'yes' => __( 'Hide Cover Photo', 'frontpage-buddy' ) ),
			),
			'adaptwidth'  => array(
				'type'    => 'checkbox',
				'label'   => '',
				'value'   => ! empty( $this->edit_field_value( 'adaptwidth' ) ) ? $this->edit_field_value( 'adaptwidth' ) : '',
				'options' => array( 'yes' => __( 'Adapt to plugin container width', 'frontpage-buddy' ) ),
			),
			'facepile'    => array(
				'type'    => 'checkbox',
				'label'   => '',
				'value'   => ! empty( $this->edit_field_value( 'facepile' ) ) ? $this->edit_field_value( 'facepile' ) : '',
				'options' => array( 'yes' => __( 'Show Friend\'s Faces', 'frontpage-buddy' ) ),
			),
		);
	}
}
