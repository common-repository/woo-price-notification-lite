<?php
/*
	Plugin Name: Woo Price Notification Lite
	Plugin URI: http://www.wpfruits.com
	Description: Woo Price Notification Plugin is a powerful and must have plugin for every woocommerce site, online store, art gallery or product site. This plugin efficiently weaves into any wordpress site and automatically sends notification when the price of the product falls down. This price notification plugin comes with multiple actions apart from simply sending e-mail notification to the subscribers. By installing the plugin. one can set email form in on/off mode, set mail subject and mail content along with a great thank you message. This Plugin integrates well with the site to result into the best woocommerce experience.
	Version: 1.0.0
	Author: WPFruits
*/

/********************************************************
 TEXT DOMAIN
*********************************************************/

function price_noti_load_textdomain() {
  load_plugin_textdomain( 'price-noti', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'price_noti_load_textdomain' );

/********************************************************
 AJAX URL
*********************************************************/

function price_noti_ajaxurl() {
?>
	<script type="text/javascript">
		var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	</script>
<?php
}
add_action('wp_head','price_noti_ajaxurl');

/********************************************************
 PLUGIN CLASS
*********************************************************/

class PriceNotificationLite
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	protected $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action( 'woocommerce_product_meta_start', 'pricenotiadd_custom_field', 0 );
		add_action('wp_footer', 'pricenoti_front_enqueue_scripts', 0 );
		add_action('admin_enqueue_scripts','pricenoti_admin_enqueue_scripts');
		add_action( 'admin_menu', array( $this, 'pricenoti_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'pricenoti_page_init' ) );
	}

	/**
	 * Add plugin page
	 */
	public function pricenoti_plugin_page()
	{
		add_submenu_page(
	        'woocommerce',
	        __('Price Notification', 'price-noti'),
	        __('Price Notification', 'price-noti'),
	        'manage_options',
	        'pricenoti-setting-submenu',
	        array( $this, 'pricenoti_admin_page' )
	    );
	}

	/**
	 * Plugin page callback
	 */
	public function pricenoti_admin_page()
	{
		// Set class property
		$this->options = get_option( 'pricenoti_options' );
?>
		<div class="wrap">
			<h2><?php _e('Price Notifications Settings', 'price-noti'); ?></h2>
			<form method="post" action="options.php">
			<hr>
			<?php
				settings_fields( 'pricenoti_option_group' );   
				do_settings_sections( 'pricenoti-setting-admin' );
				?>
			<hr/>
				<?php
				submit_button();
			?>
			</form>
		</div>
<?php
	}

	/**
	 * Register and add settings
	 */
	public function pricenoti_page_init()
	{
		register_setting(
			'pricenoti_option_group', // Option group
			'pricenoti_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'wpn_setting_section', // ID
			'', // Title
			array( $this, 'pricenoti_print_section_info' ), // Callback
			'pricenoti-setting-admin' // Page
		);

		add_settings_field(
			'on-off', // ID
			__('Show Email Form','price-noti'), // Title
			array( $this, 'pricenoti_onoff_callback' ), // Callback 
			'pricenoti-setting-admin', // Page
			'wpn_setting_section' // Section
		);

		add_settings_field(
			'mail-subject', // ID
			__('Mail Subject','price-noti'), // Title
			array( $this, 'pricenoti_mailsubject_callback' ), // Callback 
			'pricenoti-setting-admin', // Page
			'wpn_setting_section' // Section
		);

		add_settings_field(
			'mail-content', // ID
			__('Mail Content','price-noti'), // Title
			array( $this, 'pricenoti_mailcontent_callback' ), // Callback 
			'pricenoti-setting-admin', // Page
			'wpn_setting_section' // Section
		);

		add_settings_field(
			'thanks-msg', // ID
			__('Thank You Message','price-noti'), // Title
			array( $this, 'pricenoti_thankyou_callback' ), // Callback 
			'pricenoti-setting-admin', // Page
			'wpn_setting_section' // Section
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input )
	{
		$new_input = array();

		if( isset( $input['wpn-onoff-checkbox'] ) )
			$new_input['wpn-onoff-checkbox'] = sanitize_text_field( $input['wpn-onoff-checkbox'] );

		if( isset( $input['wpn-mail-subject'] ) )
			$new_input['wpn-mail-subject'] = sanitize_text_field( $input['wpn-mail-subject'] );

		if( isset( $input['wpn-mail-content'] ) )
            $new_input['wpn-mail-content'] = force_balance_tags($input['wpn-mail-content']);

        if( isset( $input['wpn-thankyou'] ) )
            $new_input['wpn-thankyou'] = force_balance_tags($input['wpn-thankyou']);

		return $new_input;
	}

	/** 
	 * Print the Section text
	 */
	public function pricenoti_print_section_info()
	{
	
	}

	/** 
	 * NOTIFICATION ON OFF
	 */
	public function pricenoti_onoff_callback()
	{	
		if($this->options == ''){
			$checkedonoff = 'checked';	
		}else{
			$checkedonoff = (isset($this->options["wpn-onoff-checkbox"])) ? 'checked' : '';
		}
		echo '<span><p class="wpn-onoff-checkbox"><input type="checkbox" name="pricenoti_options[wpn-onoff-checkbox]" id="wpn-onoff-checkbox" value="0"'. $checkedonoff .'/><label for="wpn-onoff-checkbox"></label></p></span>';
	}

	/** 
     * MAIL SUBJECT
     */
    public function pricenoti_mailsubject_callback()
    {
        printf(
            '<input type="text" id="wpn-mail-subject" class="wpn-mail-subject" name="pricenoti_options[wpn-mail-subject]" value="%s" />',
            isset( $this->options['wpn-mail-subject'] ) ? esc_attr( $this->options['wpn-mail-subject']) : 'Price Notification'
        );
    }

    /** 
     * MAIL CONTENT
     */
    public function pricenoti_mailcontent_callback()
    {
        printf(
        	'<textarea id="wpn-mail-content" name="pricenoti_options[wpn-mail-content]" rows="10" cols="55" value="">%s</textarea>',
        	isset( $this->options["wpn-mail-content"] ) ? esc_attr( $this->options["wpn-mail-content"] ) : '<h3>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries</h3>'
        );
    }

    /** 
     * THANK YOU
     */
    public function pricenoti_thankyou_callback()
    {
        printf(
            '<input type="text" id="wpn-thankyou" class="wpn-thankyou" name="pricenoti_options[wpn-thankyou]" value="%s" />',
            isset( $this->options['wpn-thankyou'] ) ? esc_attr( $this->options['wpn-thankyou']) : 'Thanks For Subscribe'
        );
    }

}

$PriceNotificationLite = new PriceNotificationLite();

/********************************************************
 CALL AJAX FUNCTION
*********************************************************/

include_once(plugin_dir_path( __FILE__ ) . "ajax-fn/wpn-ajax-functions.php");

/********************************************************
 ENQUEUE SCRIPTS
*********************************************************/

function pricenotiadd_custom_field() { 

	global $product;
	$options = get_option( 'pricenoti_options' );
	if(isset($options['wpn-onoff-checkbox']) || $options == ''){
		$meta = '_price_notification_lite';
		$productid = $product->get_id(); 
		$allvalues = get_post_meta( $productid, $meta );
	    $old_values = json_encode($allvalues);
		if( $product->is_type( 'simple' ) ){
		  	?>
	 
			<div class='pn-email' style="position:relative; width:100%; display:inline-block;">
					<span class='pn-email-input'>
					<span class='pn-label'><?php _e("Subscribe for price notification","price-noti") ?></span>
					<span class='pn-new-input-wrap'>
						<input type="hidden" id="pn-new-product-id" name="pn-product-id" value="<?php echo $productid; ?>" />
						<input type="text" id="pn-new-email" name="pn-email" placeholder="E-mail"/>
					</span>
					<span class='pn-old-all-data'>
						<?php 
						if($allvalues != ''){
							foreach ($allvalues as $newvalue) {
								$jsonvalue = json_decode($newvalue);
								foreach ($jsonvalue as $value) {
									echo '<span class="pn-old-input-wrap"><input type="hidden" id="pn-email" value="'.$value->email.'" />';
									echo '<input type="hidden" id="pn-price" value="'.$value->productprice.'" /></span>';

								}
							} 
						}
						?>
					</span>
						<input type="hidden" id="pn-all-data" name="pn-all-data" value='<?php echo stripcslashes($old_values); ?>' />
					<span id="pn-submit-wrap"><button id="pn-submit"><?php _e("Subscribe", "price-noti") ?></button></span>
					</span>
			</div>

		<?php
		} elseif( $product->is_type( 'variable' ) ){
			$available_variations = $product->get_available_variations();
			$variation_id=$available_variations[0]['variation_id'];
			$variable_product1= new WC_Product_Variation( $variation_id );
			$regular_price = $variable_product1 ->regular_price;
			?>
	 
			<div class='pn-email' style="position:relative; width:100%; display:inline-block;">
					<span class='pn-email-input'>
					<span class='pn-label'><?php _e("Subscribe for price notification","price-noti") ?></span>
					<span class='pn-new-input-wrap'>
						<input type="hidden" id="pn-new-product-id" name="pn-product-id" value="<?php echo $productid; ?>" />
						<input type="text" id="pn-new-email" name="pn-email" placeholder="E-mail"/>
						<!-- <input type="hidden" id="pn-new-price" value="<?php #echo $regular_price; ?>" /> -->
					</span>
					<span class='pn-old-all-data'>
						<?php 
						if($allvalues != ''){
							foreach ($allvalues as $newvalue) {
								$jsonvalue = json_decode($newvalue);
								foreach ($jsonvalue as $value) {
									echo '<span class="pn-old-input-wrap"><input type="hidden" id="pn-email" value="'.$value->email.'" />';
									echo '<input type="hidden" id="pn-price" value="'.$value->productprice.'" /></span>';

								}
							} 
						}
						?>
					</span>
						<input type="hidden" id="pn-all-data" name="pn-all-data" value='<?php echo stripcslashes($old_values); ?>' />
					<span id="pn-submit-wrap"><button id="pn-submit"><?php _e("Subscribe", "price-noti") ?></button></span>
					</span>
			</div>
			
		<?php
		}
		 
	}
}

/********************************************************
 UPDATE PRODUCT
*********************************************************/
function post_noti_save_post()
{	
	$wpn_options = get_option( 'pricenoti_options' );

	$product = new WC_Product(get_the_ID());
	$productid = get_the_ID(); 
	$product_image = $product->get_image();
	$product_title = '<a href="'.get_permalink($productid).'">'.get_the_title($productid).'</a>';
	$product_price = $product->price;
	$currency_symbol = get_woocommerce_currency_symbol();
	$product_url = get_permalink();

	$wpn_content = (isset($wpn_options['wpn-mail-content'])) ? $wpn_options['wpn-mail-content'] : '<h3>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries</h3>';
	$footer_link = '<a href="wpfruits.com">WPFruits</a>';
	$message = '<!DOCTYPE HTML>'. 
				'<html xmlns="http://www.w3.org/1999/xhtml">'.
				'<head>'. 
				'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'. 
				'<title>Price notification</title>'. 
				'</head>'. 
				'<body>'. 
				'<div id="outer" style="width: 100%;margin-top: 10px;">'.  
					'<div id="inner" style="width: 100%; background-color: #fff;font-family: Open Sans,Arial,sans-serif;">'.
						'<div id="inner" style=" padding:10px;width: 100%;background-color: #fff;font-family: Open Sans,Arial,sans-serif;font-size: 13px;font-weight: normal;line-height: 1.4em;color: #444;margin-top: 10px;">'. 
							$wpn_content.
						'</div>'.
						'<div style="width: 30%;padding: 10px;float: left;display: block;">'.
							$product_image.
						'</div>'.
						'<div style="width: 40%; padding: 20px 10px 20px 10px;display: block;float: left;">'.
							'<h2>'.$product_title.'</h2>'.
							'<h3><span>'.$currency_symbol.'</span><span>'.$product_price.'</span</h3>'.
							'<br>'.
							'<h3><a href="'.$product_url.'">'.__("Get Product", "price-noti").'</a></h3>'.
						'</div><div style="clear:both;"></div>'.
					'</div>'.   
				'</div>'. 
				'<div id="footer" style="width: 100%;height: 40px;margin: 0 auto;text-align: center;padding: 10px;font-family: Verdena;background-color: #E2E2E2;">'. 
				   'All rights reserved '.$footer_link.
				'</div>'. 
				'</body>'.
				'</html>';
				 //Prepare headers for HTML    
				// $headers  = 'MIME-Version: 1.0' . "\r\n";    
				// $headers .= 'Content-type: text/html; charset=iso-8859-1' . '\r\n';     
	/*EMAIL TEMPLATE ENDS*/ 
	$subject = (isset($wpn_options['wpn-mail-subject'])) ? $wpn_options['wpn-mail-subject'] : 'Price Notification';  //change subject of email 
	$meta = '_price_notification_lite';
	$allvalues = get_post_meta( $productid, $meta );
	if($allvalues != ''){
		$unset_queue = array();
		foreach ( $allvalues as $i => $item ){
			$newitem = json_decode($item);
			foreach ($newitem as $key => $value) {
			    if ($value->productprice > $product->price){
			    	wp_mail( $value->email, $subject, $message, 'Content-type: text/html; charset=iso-8859-1' . '\r\n' );
			        $unset_queue[] = $key;
			    }
			}
		}
		foreach ( $unset_queue as $index ){	
		    unset($newitem[$index]);
		}
		// rebase the array
		$newitem = array_values($newitem);
		$new_json_data = json_encode($newitem);
		if($new_json_data == 'null'){
			update_post_meta( $productid, $meta, '[]' );
		}else{
			update_post_meta( $productid, $meta, $new_json_data );
		}
	}
}
add_action( 'save_post', 'post_noti_save_post' );


function pricenoti_admin_enqueue_scripts(){
	if(isset($_REQUEST['page']) && $_REQUEST['page']=="pricenoti-setting-submenu") {
		wp_enqueue_script('pricenoti-front-script', plugins_url('js/wpn-backend-custom.js',__FILE__),'','', true);
		wp_enqueue_style('wpn-admin-style', plugins_url('css/wpn-backend.css',__FILE__));
	}
}

function pricenoti_front_enqueue_scripts(){
	wp_enqueue_script('pricenoti-front-script', plugins_url('js/wpn-front-custom.js',__FILE__),'','', true);
	include_once(plugin_dir_path( __FILE__ ) . "css/wpn-front-customcss.php");
}