<?php
/**
 * Twitter feed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed twitter feed.
 */
class TwitterFeed extends Widget {
	/**
	 * Constructor.
	 *
	 * @param string $type A unique identifier.
	 * @param mixed  $args Initial data for the widget. e.g: id, options etc.
	 */
	public function __construct( $type, $args = '' ) {
		$this->type           = $type;
		$this->name           = 'X Feed';
		$this->description    = 'Display your X/Twitter feed.';
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-x-feed.png';

		$this->setup( $args );
	}

	public function get_output() {
		$tw_handle = $this->options['username'];

		if ( ! $tw_handle ) {
			return;
		}

		// remove the @ if found.
		if ( strrpos( $tw_handle, '@', -strlen( $tw_handle ) ) !== false ) {
			$tw_handle = substr( $tw_handle, 1 );
		}

		$this->output_start();
		$this->output_title();
		?>
		<div class="bpfp_w_content">
			<a class="twitter-timeline" 
				<?php
				if ( ! empty( $this->options['theme'] ) ) {
					echo "data-theme='" . esc_attr( $this->options['theme'] ) . "'";
				}

				if ( ! empty( $this->options['linkcolor'] ) ) {
					echo "data-link-color='" . esc_attr( $this->options['linkcolor'] ) . "'";
				}

				if ( ! empty( $this->options['theight'] ) ) {
					echo "data-height='" . esc_attr( $this->options['theight'] ) . "'";
				}
				?>
				href="https://twitter.com/<?php echo esc_url( $tw_handle ); ?>">
				<?php esc_html_e( 'Tweets by', 'frontpage-buddy' ) . ' ' . $tw_handle; ?>
			</a> 
			<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
		</div>
		<?php
		$this->output_end();
	}

	public function get_fields() {
		return array(
			'username'  => array(
				'type'       => 'text',
				'label'      => __( 'X/Twitter Handle', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'username' ) ) ? $this->edit_field_value( 'username' ) : '',
				'attributes' => array( 'placeholder' => __( 'e.g: @johndoe', 'frontpage-buddy' ) ),
			),
			'theight'   => array(
				'type'       => 'number',
				'label'      => __( 'Height', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'theight' ) ) ? $this->edit_field_value( 'theight' ) : '',
				'attributes' => array( 'placeholder' => __( 'Height in pixels (optional)', 'frontpage-buddy' ) ),
			),
			'twidth'    => array(
				'type'       => 'number',
				'label'      => __( 'Width', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'twidth' ) ) ? $this->edit_field_value( 'twidth' ) : '',
				'attributes' => array( 'placeholder' => __( 'Width in pixels (optional)', 'frontpage-buddy' ) ),
			),
			'theme'     => array(
				'type'    => 'select',
				'label'   => __( 'Theme', 'frontpage-buddy' ),
				'value'   => ! empty( $this->edit_field_value( 'theme' ) ) ? $this->edit_field_value( 'theme' ) : 'light',
				'options' => array(
					'light' => __( 'Light', 'frontpage-buddy' ),
					'dark'  => __( 'Dark', 'frontpage-buddy' ),
				),
			),
			'linkcolor' => array(
				'type'    => 'select',
				'label'   => __( 'Default link color', 'frontpage-buddy' ),
				'value'   => ! empty( $this->edit_field_value( 'linkcolor' ) ) ? $this->edit_field_value( 'linkcolor' ) : '#2B7BB9',
				'options' => array(
					'#981CEB' => __( 'Purple', 'frontpage-buddy' ),
					'#19CF86' => __( 'Green', 'frontpage-buddy' ),
					'#FAB81E' => __( 'Yellow', 'frontpage-buddy' ),
					'#E95F28' => __( 'Orange', 'frontpage-buddy' ),
					'#E81C4F' => __( 'Red', 'frontpage-buddy' ),
					'#2B7BB9' => __( 'Blue', 'frontpage-buddy' ),
				),
			),
		);
	}
}
/*

------- Timeline of tweets by @XDevelopers ------- 
<a class="twitter-timeline" data-dnt="true" href="https://twitter.com/XDevelopers?ref_src=twsrc%5Etfw">
Tweets by XDevelopers</a>
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

<a class="twitter-timeline" data-dnt="true" href="https://twitter.com/XDevelopers?ref_src=twsrc%5Etfw">
Tweets by XDevelopers</a> 
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

<a class="twitter-hashtag-button" data-dnt="true" href="https://twitter.com/intent/tweet?button_hashtag=india&ref_src=twsrc%5Etfw" 
	data-show-count="false">Tweet #india</a>
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
*/
