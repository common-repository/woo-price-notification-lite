jQuery(document).ready(function() {	
	function pricenoti_all_value(){
		var values = [];
		var email = jQuery("#pn-new-email").val();
		var productprice = jQuery(".summary.entry-summary .price .amount").last().text();
		var newprice = productprice.replace(".00", "");
		var price = newprice.replace(/[^0-9\.]/g, "");
		jQuery('.pn-old-input-wrap').each(function(){
			var oldemail = jQuery(this).find("#pn-email").val();
			var oldprice = jQuery(this).find("#pn-price").val();
			values.push({
				"email" : oldemail,
		  		"productprice" : oldprice
			});
		});
		values.push({
			"email" : email,
			"productprice" : price
		});
	    jQuery('#pn-all-data').val(JSON.stringify(values));
	}

	jQuery('#pn-submit').on('click', function(){
		var email = jQuery("#pn-new-email").val();
		if(email == ''){
			alert('Please Enter Email');
		}else{
			pricenoti_all_value();
			var product_id = jQuery('#pn-new-product-id').val();
			var alldata = jQuery('#pn-all-data').val();
			var data = {
				'action' : 'new_email',
				'email' : email,
				'productid' : product_id,
			    'alldata' : alldata
			};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('.responce').remove();
				var obj = jQuery.parseJSON( response );
				jQuery('.pn-old-all-data .pn-old-input-wrap').remove();
				jQuery('#pn-all-data').remove();
				jQuery('#pn-new-email').val('');
				jQuery('.pn-old-all-data').append(obj.all_data);
				jQuery('.pn-email-input').append(obj.json_data);
				jQuery('.pn-email-input').append('<span class="responce">'+obj.resultmsg+'</span>');
				jQuery('.responce').fadeIn('slow', function () {
				    jQuery(this).delay(5000).fadeOut('slow');
				});
			});
		}    
	});

	

});
