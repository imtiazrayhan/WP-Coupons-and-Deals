<?php

/**
*
* This exits from the script if it's accessed
* directly from somewhere else.
*
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* This is the default Shortcode template.
*
* @since 1.2
*/
global $coupon_id;
$title                     = get_the_title();
$description               = get_post_meta( $coupon_id, 'coupon_details_description', true );
$discount_text             = get_post_meta( $coupon_id, 'coupon_details_discount-text', true );
$coupon_type               = get_post_meta( $coupon_id, 'coupon_details_coupon-type', true );
$link                      = get_post_meta( $coupon_id, 'coupon_details_link', true );
$coupon_code               = get_post_meta( $coupon_id, 'coupon_details_coupon-code-text', true );
$deal_text                 = get_post_meta( $coupon_id, 'coupon_details_deal-button-text', true );
$coupon_hover_text         = get_option( 'wpcd_coupon-hover-text' );
$deal_hover_text           = get_option( 'wpcd_deal-hover-text' );
$button_class              = 'wpcd-btn-' . $coupon_id;
$no_expiry                 = get_option( 'wpcd_no-expiry-message' );
$expire_text               = get_option( 'wpcd_expire-text' );
$expired_text              = get_option( 'wpcd_expired-text' );
$hide_coupon_text          = get_option( 'wpcd_hidden-coupon-text' );
$hidden_coupon_hover_text  = get_option( 'wpcd_hidden-coupon-hover-text' );
$copy_button_text          = get_option( 'wpcd_copy-button-text' );
$coupon_title_tag          = get_option( 'wpcd_coupon-title-tag', 'h1' );
$coupon_share              = get_option( 'wpcd_coupon-social-share' );
$show_expiration           = get_post_meta( $coupon_id, 'coupon_details_show-expiration', true );
$today                     = date( 'd-m-Y' );
$expire_date               = get_post_meta( $coupon_id, 'coupon_details_expire-date', true );
$hide_coupon               = get_post_meta( $coupon_id, 'coupon_details_hide-coupon', true );
$dt_coupon_type_name       = get_option( 'wpcd_dt-coupon-type-text' );
$dt_deal_type_name         = get_option( 'wpcd_dt-deal-type-text' );
$disable_coupon_title_link = get_option( 'wpcd_disable-coupon-title-link' );
$wpcd_text_to_show         = get_option( 'wpcd_text-to-show' );
$wpcd_custom_text          = get_option( 'wpcd_custom-text' );
$wpcd_eight_btn_text       = get_option( 'wpcd_eight-button-text' );

$dt_coupon_type_name = ( !empty( $dt_coupon_type_name ) ) ? $dt_coupon_type_name : __( 'Coupon', 'wpcd-coupon' );
$dt_deal_type_name = ( !empty( $dt_deal_type_name ) ) ? $dt_deal_type_name : __( 'Deal', 'wpcd-coupon' );
$expire_text = ( !empty( $expire_text ) ) ? $expire_text : __( 'Expires On: ', 'wpcd-coupon' );
$expired_text = ( !empty( $expired_text ) ) ? $expired_text : __( 'Expired On: ', 'wpcd-coupon' );
$no_expiry = ( !empty( $no_expiry ) ) ? $no_expiry : __( "Doesn't expire", 'wpcd-coupon' );
$coupon_hover_text = ( ! empty( $coupon_hover_text ) ) ? $coupon_hover_text : __( 'Click To Copy Coupon', 'wpcd-coupon' );
$wpcd_eight_btn_text = ( !empty( $wpcd_eight_btn_text ) ) ? $wpcd_eight_btn_text : __( 'GET THE DEAL', 'wpcd-coupon' );

if ( $wpcd_text_to_show == 'description' ) {
	$wpcd_custom_text = $description;
} else {
	if ( empty( $wpcd_custom_text ) ) {
		$wpcd_custom_text = __( "Click on 'Copy' to Copy the Coupon Code.", 'wpcd-coupon' );
	}
}

wp_enqueue_script( 'wpcd-clipboardjs' );
$template = new WPCD_Template_Loader();

?>

<div class="wpcd-new-grid-container">
	<div class="wpcd-new-grid-one">
		<div class="wpcd-new-discount-text">
		   <?php echo $discount_text; ?>
		</div>

		<?php if ( $coupon_type == 'Coupon' ) { ?>
			<div class="wpcd-new-coupon-type">
				<?php echo $dt_coupon_type_name; ?>
			</div>
		<?php } elseif ( $coupon_type == 'Deal' ) { ?>
			<div class="wpcd-new-deal-type">
				<?php echo $dt_deal_type_name; ?>
			</div>
		<?php }
		if ( $show_expiration == 'Show' ) {
			if ( ! empty( $expire_date ) ) {
				if ( strtotime( $expire_date ) >= strtotime( $today ) ) { ?>
					<p class="wpcd-new-expire-text">
						<?php echo $expire_text . ' ' . $expire_date; ?>
					</p> <?php
				} elseif ( strtotime( $expire_date ) < strtotime( $today ) ) { ?>
					<p class="wpcd-new-expired-text">
						<?php echo $expired_text . ' ' . $expire_date; ?>
					</p> <?php
				}
			} else { ?>
				<p class="wpcd-new-expire-text">
					<?php echo $no_expiry; ?>
				</p> <?php
			}
		} else {
			echo '';
		} ?>
   </div> <!-- End of grid-one -->

   <div class="wpcd-new-grid-two">
	   <?php
		if ( 'on' === $disable_coupon_title_link ) { ?>
			<<?php echo esc_html( $coupon_title_tag ); ?> class="wpcd-new-title">
				<?php echo $title; ?>
			</<?php echo esc_html( $coupon_title_tag ); ?>> <?php
		} else { ?>
			<<?php echo esc_html( $coupon_title_tag ); ?> class="wpcd-new-title">
				<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="nofollow"><?php echo $title; ?></a>
			</<?php echo esc_html( $coupon_title_tag ); ?>> <?php
		}
	   ?>
		<div class="wpcd-coupon-description">
			<span class="wpcd-full-description"><?php echo $description; ?></span>
			<span class="wpcd-short-description"></span>
			<a href="#" class="wpcd-more-description"><?php echo __( 'More', 'wpcd-coupon' ); ?></a>
			<a href="#" class="wpcd-less-description"><?php echo __( 'Less', 'wpcd-coupon' ); ?></a>
		</div>
	</div> <!-- End of grid-two -->
	<div class="wpcd-new-grid-three">
		<a class="wpcd-new-coupon-code <?php echo 'wpcd-btn-' . $coupon_id; ?> masterTooltip" rel="nofollow" href="<?php echo esc_url( $link ); ?>" target="_blank" data-clipboard-text="<?php echo $coupon_code; ?>" title="<?php echo $coupon_hover_text; ?>">
		   <?php echo $coupon_code; ?>
		</a>
		<a class="wpcd-new-goto-button" rel="nofollow" href="<?php echo esc_url( $link ); ?>" target="_blank">
		   <?php echo $wpcd_eight_btn_text; ?>
		</a>
	</div><!-- End of grid-three -->
	<script type="text/javascript">
		var clip = new Clipboard('.<?php echo $button_class; ?>');
	</script>
</div>