<?php 
/********************************************************
 PHP AJAX CALL BACK FUNCTION
*********************************************************/

add_action( 'wp_ajax_new_email', 'pricenoti_email_callback' );
add_action( 'wp_ajax_nopriv_new_email', 'pricenoti_email_callback' );

function pricenoti_email_callback() {
    global $wpdb;
    $wpn_options = get_option( 'pricenoti_options' );
  
  	$val = '';
  	$msg = '';
  	$all_data = '';
    $meta = '_price_notification_lite';
    $newemail = sanitize_email( $_POST['email'] );
    $product_id = intval( $_POST['productid'] );

	$alldata = sanitize_text_field( $_POST['alldata'] );
	$thnx = (isset($wpn_options['wpn-thankyou'])) ? $wpn_options['wpn-thankyou'] : 'Thanks For Subscribe';

	if (filter_var($newemail, FILTER_VALIDATE_EMAIL) === false) {
	  	$msg = __( "Please Enter Valid Email Address", "price-noti");
	  	$updatedallvalues = get_post_meta( $product_id, $meta );
		$old_values = json_encode($updatedallvalues);
		foreach ($updatedallvalues as $value) {
			$allnewvalues = json_decode($value);
			foreach ($allnewvalues as $key) {
				$all_data .= '<span class="pn-old-input-wrap"><input type="hidden" id="pn-email" value="'.$key->email.'" />'.
							'<input type="hidden" id="pn-price" value="'.$key->productprice.'" /></span>';
			}
		}
		$json_data = '<input type="hidden" id="pn-all-data" name="pn-all-data" value=\''.stripcslashes($old_values).'\' />';
	} else {
		$allvalues = get_post_meta( $product_id, $meta );
		if(!empty($allvalues)){
			if($allvalues[0] == '[]'){
				delete_post_meta($product_id, $meta);
				add_post_meta( $product_id, $meta, $alldata );
				$msg = $thnx;
				$updatedallvalues = get_post_meta( $product_id, $meta );
				$old_values = json_encode($updatedallvalues);
				foreach ($updatedallvalues as $value) {
					$allnewvalues = json_decode($value);
					foreach ($allnewvalues as $key) {
						$all_data .= '<span class="pn-old-input-wrap"><input type="hidden" id="pn-email" value="'.$key->email.'" />'.
									'<input type="hidden" id="pn-price" value="'.$key->productprice.'" /></span>';
					}
				}
				$json_data = '<input type="hidden" id="pn-all-data" name="pn-all-data" value="'.stripcslashes($old_values).'" />';
			}else{
				foreach ($allvalues as $value) {
					$allnewvalues = json_decode($value);
					foreach ($allnewvalues as $key) {
						if($key->email == $newemail){
							$msg = __("Already Subscribed","price-noti");
							$val = '0';
							
							$updatedallvalues = get_post_meta( $product_id, $meta );
							$old_values = json_encode($updatedallvalues);
							foreach ($updatedallvalues as $value) {
								$allnewvalues = json_decode($value);
								foreach ($allnewvalues as $key) {
									$all_data .= '<span class="pn-old-input-wrap"><input type="hidden" id="pn-email" value="'.$key->email.'" />'.
												'<input type="hidden" id="pn-price" value="'.$key->productprice.'" /></span>';
								}
							}
							$json_data = '<input type="hidden" id="pn-all-data" name="pn-all-data" value="'.stripcslashes($old_values).'" />';
						}
					}
					if($val != '0'){
						update_post_meta( $product_id, $meta, $alldata );
						$msg = $thnx;
						$updatedallvalues = get_post_meta( $product_id, $meta );
						$old_values = json_encode($updatedallvalues);
						foreach ($updatedallvalues as $value) {
							$allnewvalues = json_decode($value);
							foreach ($allnewvalues as $key) {
								$all_data .= '<span class="pn-old-input-wrap"><input type="hidden" id="pn-email" value="'.$key->email.'" />'.
											'<input type="hidden" id="pn-price" value="'.$key->productprice.'" /></span>';
							}
						}
						$json_data = '<input type="hidden" id="pn-all-data" name="pn-all-data" value="'.stripcslashes($old_values).'" />';
					}
					
				}
			}
		}else{
	    	add_post_meta( $product_id, $meta, $alldata );
			$msg = $thnx;
			$updatedallvalues = get_post_meta( $product_id, $meta );
			$old_values = json_encode($updatedallvalues);
			foreach ($updatedallvalues as $value) {
				$allnewvalues = json_decode($value);
				foreach ($allnewvalues as $key) {
					$all_data .= '<span class="pn-old-input-wrap"><input type="hidden" id="pn-email" value="'.$key->email.'" />'.
								'<input type="hidden" id="pn-price" value="'.$key->productprice.'" /></span>';
				}
			}
			$json_data = '<input type="hidden" id="pn-all-data" name="pn-all-data" value="'.stripcslashes($old_values).'" />';
		}
	}
	$responce = array(
				'resultmsg' => $msg,
				'all_data' => $all_data,
				'json_data'	=> $json_data,
				);
	$json_responce = json_encode($responce);
	echo $json_responce;
    die();
}