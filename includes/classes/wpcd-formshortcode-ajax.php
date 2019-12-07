<?php

class WPCD_Formshortcode_Ajax extends WPCD_Ajax_Base {


	/**
	 * @inheritDoc
	 */
	public function logic() {
		// TODO [task-001][erdembircan] implement full post fields

		$response = [];
		if ( filter_var( $this->_c()->check_ajax_referer( 'wpcd_shortcode_form', 'nonce', false ),
			FILTER_VALIDATE_BOOLEAN ) ) {
			$data = $_POST;

			$original_fields = WPCD_Meta_Boxes_Fields_Pro__Premium_Only::getFields();

			// adding 'coupon-title' since it is not included in original fields
			$original_fields[] = [ 'id' => 'coupon-title' ];

			$rules = [];
			foreach ( $original_fields as $field ) {
				$key  = $field['id'];
				$rule = 'sanitize_text_field';
				if ( strpos( $key, 'link' ) !== false ) {
					$rule = 'esc_url';
				}
				$rules[ $key ] = $rule;
			}
			$sanitized_fields = WPCD_Sanitizer::sanitize( $data, $rules );

			$meta_input = $this->batchMetaInput( 'coupon_details_', $sanitized_fields );

			$terms = get_terms( WPCD_Plugin::CUSTOM_TAXONOMY, [ 'hide_empty' => false ] );

			$tax_input = isset( $_POST['terms'] ) ? $_POST['terms'] : [];


			// inserting new post based on submitted form fields
			$operation_result = $this->_c()->wp_insert_post( [
				'post_title'  => $sanitized_fields['coupon-title'],
				'post_type'   => WPCD_Plugin::CUSTOM_POST_TYPE,
				'post_status' => 'publish',
				'meta_input'  => $meta_input,
			] );


			if ( $this->_c()->is_wp_error( $operation_result ) ) {
				$this->add_error_to_response( $response, $operation_result->get_error_message() );
			} else {
				// taxonomy input
				$tax_input = isset( $_POST['terms'] ) ? $_POST['terms'] : [];
				foreach ( $tax_input as $tax_name => $term ) {
					$this->_c()->wp_set_object_terms( $operation_result, $term, $tax_name );
				}

				$response['data'] = [ 'id' => $operation_result ];

				$coupon_image_field = 'coupon-image-input';
				if ( isset( $_FILES[ $coupon_image_field ] ) ) {

					$response['data'] = [ 'id' => $operation_result ];
					// file upload process
					$upload_result = $this->_c()->wp_handle_upload( $_FILES['coupon-image-input'],
						[ 'test_form' => false ] );
					if ( $upload_result['error'] ) {
						$this->add_error_to_response( $response, $upload_result['error'] );
						$this->_c()->wp_delete_post( $operation_result );
					} else {

						// attachment process for uploaded file
						$parent_id      = $operation_result;
						$file_path      = $upload_result['file'];
						$upload_dir     = $this->_c()->wp_upload_dir();
						$file_base_name = basename( $file_path );

						$file_type          = $this->_c()->wp_check_filetype( $file_base_name );
						$attachment_options = [
							'guid'           => $upload_dir . '/' . $file_base_name,
							'post_mime_type' => $file_type['type'],
							'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_base_name ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						];

						$attachment_id = $this->_c()->wp_insert_attachment( $attachment_options, $file_path,
							$parent_id );

						require_once( ABSPATH . 'wp-admin/includes/file.php' );

						$attachment_meta_data = $this->_c()->wp_generate_attachment_metadata( $attachment_id,
							$file_path );
						$this->_c()->wp_update_attachment_metadata( $attachment_id, $attachment_meta_data );

						$this->_c()->update_post_meta( $operation_result, 'coupon_details_coupon-image-input',
							$attachment_id );
					}
				}
			}
		} else {
			// nonce error response
			$this->add_error_to_response( $response,
				__( 'You are not authorized to submit forms, refresh page and try again',
					WPCD_Plugin::TEXT_DOMAIN ) );
		}

		echo json_encode( $response );

		die();
	}

	private function add_error_to_response( &$response, $message ) {
		$response['error'] = $message;
	}

	private function batchMetaInput( $suffix, $meta_array ) {
		$temp_array = [];

		foreach ( $meta_array as $key => $value ) {
			$final_key = "$suffix$key";
			if ( $key === 'wpcd_description' ) {
				$final_key = $suffix . "description";
			}
			$temp_array[ $final_key ] = $value;
		}

		return $temp_array;
	}
}