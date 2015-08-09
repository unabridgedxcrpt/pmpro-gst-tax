<?php
/*
Plugin Name: Paid Memberships Pro - GST Tax
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-gst-tax/
Description: Calculate CANADIAN GST tax at checkout and allow customers with a GST excemption Number to avoid the tax.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

//add tax info to cost text.
function pmprogst_pmpro_tax($tax, $values, $order)
{  	
	$tax = round((float)$values[price] * 0.05, 2);		
	return $tax;
}
 
function pmprogst_pmpro_level_cost_text($cost, $level)
{
	//only applicable for levels > 1
	$cost .= " Members in Canada will be charged a GST tax.";
	
	return $cost;
}
add_filter("pmpro_level_cost_text", "pmprogst_pmpro_level_cost_text", 10, 2);
 
//add BC checkbox to the checkout page
function pmprogst_pmpro_checkout_boxes()
{
?>
<table id="pmpro_pricing_fields" class="pmpro_checkout" width="100%" cellpadding="0" cellspacing="0" border="0">
<thead>
	<tr>
		<th>
			Canadian Company
		</th>						
	</tr>
</thead>
<tbody>                
	<tr>	
		<td>
			<div>				
				
			</div>				
		</td>
	</tr>
</tbody>
</table>
<?php
}
add_action("pmpro_checkout_boxes", "pmprogst_pmpro_checkout_boxes");
 
//update tax calculation if buyer is CANADA
function pmprogst_region_tax_check()
{
	//check request and session
	if(isset($_REQUEST['taxregion']))
	{
		//update the session var
		$_SESSION['taxregion'] = $_REQUEST['taxregion'];	
		
		//not empty? setup the tax function
		if(!empty($_REQUEST['taxregion']))
			add_filter("pmpro_tax", "pmprogst_pmpro_tax", 10, 3);
	}
	elseif(!empty($_SESSION['taxregion']))
	{
		//add the filter
		add_filter("pmpro_tax", "pmprogst_pmpro_tax", 10, 3);
	}
	else
	{
		//check prov and country - use this or add more to define taxes for other provinces.
		if(!empty($_REQUEST['bstate']) && !empty($_REQUEST['bcountry']))
		{
			$bstate = trim(strtolower($_REQUEST['bstate']));
			$bcountry = trim(strtolower($_REQUEST['bcountry']));
			if(($bstate == "sk" || $bstate == "saskatchewan" || $bstate == "sask") && $bcountry = "ca")
			{
				//billing address is in SK
				add_filter("pmpro_tax", "pmprogst_pmpro_tax", 10, 3);
			}
		}
	}
}
add_action("init", "pmprogst_region_tax_check");
 
//remove the taxregion session var on checkout
function pmprogst_pmpro_after_checkout()
{
	if(isset($_SESSION['taxregion']))
		unset($_SESSION['taxregion']);
}
add_action("pmpro_after_checkout", "pmprogst_pmpro_after_checkout");

/*
Function to add links to the plugin row meta
*/
function pmprogst_plugin_row_meta($links, $file) {
	if(strpos($file, 'pmpro-gst-tax.php') !== false)
	{
		$new_links = array(
			'<a href="' . esc_url('http://simsa.ca/contact') . '" title="' . esc_attr( __( 'Contact us for support', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter('plugin_row_meta', 'pmprogst_plugin_row_meta', 10, 2);
