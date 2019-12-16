<?php

class WPCD_Formshortcode_Coupons_Ajax extends WPCD_Ajax_Base {

	/**
	 * @inheritDoc
	 */
	public function logic() {
		if ( filter_var( $this->_c()->check_ajax_referer( 'wpcd_shortcode_form', 'nonce', false ),
			FILTER_VALIDATE_BOOLEAN ) ) {
			if ( isset( $_GET['coupons'] ) ) {
				$coupons_query = sanitize_text_field( $_GET['coupons'] );
				$user_id       = $this->_c()->wp_get_current_user()->ID;

				switch ( $coupons_query ) {
					case 'single':
						if ( isset( $_GET['coupon_id'] ) ) {
							$coupon_id = sanitize_text_field( $_GET['coupon_id'] );
							$this->get_a_coupon( $coupon_id, $user_id );
						} else {
							$this->setError( __( 'Bad request, check request and try again' ) );
						}
						break;
					default:
						$this->get_all_user_coupons( $user_id );
						break;
				}
			}
		} else {
			$this->setError( __( 'You are not authorized to fetch coupons, refresh page and try again',
				WPCD_Plugin::TEXT_DOMAIN ) );

		}

		$this->getResponseJSON( true );
		die();
	}

	/**
	 * fetch a single coupon
	 *
	 * @param int $coupon_id coupon post id
	 * @param int $user_id user id
	 */
	private function get_a_coupon( $coupon_id, $user_id ) {
		$coupon        = [];
		$coupon['ID']  = $coupon_id;
		$coupon_author = $this->_c()->get_post( $coupon_id )->post_author;


		if ( (int) $coupon_author === $user_id ) {
			$post_meta = $this->_c()->get_post_meta( $coupon_id );
			$coupon    = array_merge( $coupon, $post_meta );

			$post_meta_prefix = 'coupon_details_';
			foreach ( array_keys( $coupon ) as $key ) {
				if ( strpos( $key, $post_meta_prefix ) === 0 ) {
					$new_key            = str_replace( $post_meta_prefix, '', $key );
					$coupon[ $new_key ] = $coupon[ $key ][0];
					unset( $coupon[ $key ] );
				}
			}

			$custom_taxonomy = wp_get_post_terms( $coupon_id, WPCD_Plugin::CUSTOM_TAXONOMY );
			$vendor_taxonomy = wp_get_post_terms( $coupon_id, WPCD_Plugin::VENDOR_TAXONOMY );

			$coupon['terms'][WPCD_Plugin::CUSTOM_TAXONOMY] = $this->filter_tax( $custom_taxonomy, 'name' );
			$coupon['terms'][WPCD_Plugin::VENDOR_TAXONOMY] = $this->filter_tax( $vendor_taxonomy, 'name' );


			$this->setData( 'data', $coupon );
		} else {
			$this->setError( __( 'You are not authorized to fetch this coupon', WPCD_Plugin::TEXT_DOMAIN ) );
		}
	}

	private function filter_tax( $tax_array, $key ) {
		$temp_array = [];
		foreach ( $tax_array as $tax ) {

			$temp_array[] = $tax->$key;
		}

		return $temp_array;
	}

	/**
	 * ajax logic for getting all current users' coupons
	 *
	 * @param int $user_id current user id
	 */
	private function get_all_user_coupons( $user_id ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT ID, post_status, post_title, 
       MAX(CASE WHEN (meta.meta_key= 'coupon_details_coupon-type') THEN meta.meta_value ELSE NULL END) as coupon_type ,
       MAX(CASE WHEN (meta.meta_key= 'coupon_details_coupon-title') THEN meta.meta_value ELSE NULL END) as coupon_title 
from $wpdb->posts as posts inner JOIN $wpdb->postmeta as meta  on posts.ID = meta.post_id where posts.post_type = %s and posts.post_status in ('publish', 'draft', 'pending') and meta.meta_key in ('coupon_details_coupon-type', 'coupon_details_coupon-title') and posts.post_author=%d group by posts.ID",
			WPCD_Plugin::CUSTOM_POST_TYPE, $user_id );

		$results = $wpdb->get_results( $query );

		$this->setData( 'data', $results );
	}
}