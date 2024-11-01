<?php
/*
 Plugin Name: WP E-Commerce Right Now
 Plugin URI: http://www.sebs-studio.com/tag/e-commerce/
 Description: Displays the current months sales, transactions and more on the dashboard. Requires WP Shopping Cart plugin by Instinct Entertainment. http://www.instinct.co.nz/e-commerce/
 Version: 0.3
 Author: Sebs Studio (Sebastien)
 Author URI: http://www.sebs-studio.com/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

function setupSupportPageWPRN(){
	if(function_exists('add_options_page')){
		add_submenu_page('options-general.php', 'WP-E-Commerce-RightNow Support', 'Store RightNow', 10, basename(__FILE__), 'support_page');
	}
}

function support_page(){
?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br /></div>
<h2>Support WP E-Commerce Right Now</h2>
<p>If you like the plugin then why don't you show your appreciation.</p>
<ul>
<li><a href="http://shru.us/253" target="_blank">Donate to this plugin</a> or Buy me something of my <a href="http://shru.us/219" target="_blank">Amazon Wish List</a>.</li>
<li><a href="http://wordpress.org/extend/plugins/wp-e-commerce-rightnow/changelog/" target="_blank">Changelog</a></li>
<li><a href="http://wordpress.org/extend/plugins/wp-e-commerce-rightnow/faq/" target="_blank">FAQ</a></li>
<li>Check out my <a href="http://wordpress.org/extend/plugins/profile/sebd86" target="_blank">WordPress profile</a> for any other plugins I might have.</li>
<li>Follow me on <a href="http://twitter.com/sebsstudio" target="_blank">Twitter</a>.</li>
</ul>
<br />
Plugin by <a href="http://www.sebs-studio.com" target="_blank" title="Sebs Studio">Sebs Studio</a>.
</div>
<?php
}

if(is_admin()){
	$pluginDir = dirname(__FILE__);
	$pluginDir = rtrim($pluginDir, '/');
  $pluginDir = str_replace('/wp-e-commerce-rightnow','',$pluginDir);
  $pluginDir = rtrim($pluginDir, '/');

	/* This is in beta stage so it's disabled for now. Don't Touch enless you able to finish it for me. */
	/*require_once(ABSPATH.'/wp-admin/includes/plugin.php');
	if(!is_plugin_active($pluginDir . '/wp-e-commerce/wp-shopping-cart.php')){
		function ecommerce_warning(){
			echo "<div id='ecommerce-warning' class='updated fade'><p><strong>".__('WP E-Commerce Right Now has not detected the required plugin active.')."</strong> ".sprintf(__('<a href="%1$s">Click here</a> to download the Shopping Cart plugin if you haven\'t done so already,'), "http://wordpress.org/extend/plugins/wp-e-commerce/")." ".sprintf(__('or <a href="%1$s">Activate Plugin</a> if you have it installed already.'),"plugins.php?action=activate&plugin=wp-e-commerce/wp-shopping-cart.php&plugin_status=inactive&_wpnonce=1ca7c8ffb5")."</p></div>";
			}
		add_action('admin_notices', 'ecommerce_warning');
		return;
	}*/
	add_action('admin_menu', 'setupSupportPageWPRN');

	function wp_current_sales_right_now(){
		global $wpdb, $table_prefix, $nzshpcrt_imagesize_info;
		$year = date("Y");
		$month = date("m");
		$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
		$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);

		$replace_values[":productcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN ('1')");
		$product_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN ('1')");
		$replace_values[":productcount:"] .= " ".(($replace_values[":productcount:"] == 1) ? TXT_WPSC_PRODUCTCOUNT_SINGULAR : TXT_WPSC_PRODUCTCOUNT_PLURAL);
		$product_unit = (($replace_values[":productcount:"] == 1) ? TXT_WPSC_PRODUCTCOUNT_SINGULAR : TXT_WPSC_PRODUCTCOUNT_PLURAL);

		$replace_values[":groupcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active` IN ('1')");
		$group_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active` IN ('1')");
		$replace_values[":groupcount:"] .= " ".(($replace_values[":groupcount:"] == 1) ? TXT_WPSC_GROUPCOUNT_SINGULAR : TXT_WPSC_GROUPCOUNT_PLURAL);
		$group_unit = (($replace_values[":groupcount:"] == 1) ? TXT_WPSC_GROUPCOUNT_SINGULAR : TXT_WPSC_GROUPCOUNT_PLURAL);

		$replace_values[":salecount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$start_timestamp."' AND '".$end_timestamp."'");
		$sales_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$start_timestamp."' AND '".$end_timestamp."'");
		$replace_values[":salecount:"] .= " ".(($replace_values[":salecount:"] == 1) ? TXT_WPSC_SALECOUNT_SINGULAR : TXT_WPSC_SALECOUNT_PLURAL);
		$sales_unit = (($replace_values[":salecount:"] == 1) ? TXT_WPSC_SALECOUNT_SINGULAR : TXT_WPSC_SALECOUNT_PLURAL);

		$replace_values[":monthtotal:"] = nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
		$replace_values[":overaltotal:"] = nzshpcrt_currency_display(admin_display_total_price(),1);

		$variation_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."`");
		$variation_unit = (($variation_count == 1) ? TXT_WPSC_VARIATION_SINGULAR : TXT_WPSC_VARIATION_PLURAL);

		$replace_values[":pendingcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('1')");
		$pending_sales = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('1')");
		$replace_values[":pendingcount:"] .= " " . (($replace_values[":pendingcount:"] == 1) ? TXT_WPSC_PENDINGCOUNT_SINGULAR : TXT_WPSC_PENDINGCOUNT_PLURAL);
		$pending_sales_unit = (($replace_values[":pendingcount:"] == 1) ? TXT_WPSC_PENDINGCOUNT_SINGULAR : TXT_WPSC_PENDINGCOUNT_PLURAL);

		$accept_sales = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('2' ,'3', '4')");
		$accept_sales_unit = (($accept_sales == 1) ? TXT_WPSC_PENDINGCOUNT_SINGULAR : TXT_WPSC_PENDINGCOUNT_PLURAL);

		$replace_values[":theme:"] = get_option('wpsc_selected_theme');
		$replace_values[":versionnumber:"] = WPSC_PRESENTABLE_VERSION;

		if(function_exists('add_object_page')){
			$output = "";	
			$output .= "<div id='dashboard_right_now'>";
			//$output .= '<p class="sub" style="margin-top:6px; margin-bottom:6px; border-bottom:1px solid #ECECEC;">'.TXT_WPSC_AT_A_GLANCE.'</p>';

			$currentsalesthismonth = "You have <a href=\"admin.php?page=wpsc-edit-products\">$product_count</a> products, contained within <a href=\"admin.php?page=wpsc-edit-groups\">$group_count</a> groups. This month you made <a href=\"admin.php?page=wpsc-sales-logs\">$sales_count</a>. You have <a href=\"admin.php?page=wpsc-sales-logs\">$pending_sales</a> pending transactions awaiting your approval.";

			$output .= "<div class='table'>";
			$output .= "<table>";

			$output .= "<tr class='first'>";
			$output .= "<td class='first b'>";
			$output .= "<a href='admin.php?page=wpsc-edit-products'>".$product_count."</a>";
			$output .= "</td>";
			$output .= "<td class='t'>";
			$output .= ucfirst($product_unit);
			$output .= "</td>";
			$output .= "<td class='b'>";
			$output .= "<a href='admin.php?page=wpsc-sales-logs'>".$sales_count."</a>";
			$output .= "</td>";
			$output .= "<td class='last'>";
			$output .= ucfirst($sales_unit);
			$output .= "</td>";
			$output .= "</tr>";

			$output .= "<tr>";
			$output .= "<td class='first b'>";
			$output .= "<a href='admin.php?page=wpsc-edit-groups'>".$group_count."</a>";
			$output .= "</td>";
			$output .= "<td class='t'>";
			$output .= ucfirst($group_unit);
			$output .= "</td>";
			$output .= "<td class='b'>";
			$output .= "<a href='admin.php?page=wpsc-sales-logs'>".$pending_sales."</a>";
			$output .= "</td>";
			$output .= "<td class='last t waiting'>".TXT_WPSC_PENDING." ";
			$output .= ucfirst($pending_sales_unit);
			$output .= "</td>";
			$output .= "</tr>";

			$output .= "<tr>";
			$output .= "<td class='first b'>";
			$output .= "<a href='admin.php?page=wpsc-edit-variations'>".$variation_count."</a>";
			$output .= "</td>";
			$output .= "<td class='t'>";
			$output .= ucfirst($variation_unit);
			$output .= "</td>";
			$output .= "<td class='b'>";
			$output .= "<a href='admin.php?page=wpsc-sales-logs'>".$accept_sales."</a>";
			$output .= "</td>";
			$output .= "<td class='last t approved'>".TXT_WPSC_CLOSED." ";
			$output .= ucfirst($accept_sales_unit);
			$output .= "</td>";
			$output .= "</tr>";

			$output .= "</table>";
			$output .= "</div>";
			$output .= "<div class='versions' style='clear:both;'>";
			$output .= "<p>".TXT_WPSC_HERE_YOU_CAN_ADD."</p>";
			$output .= "<p>";
			$output .= "<a class='button rbutton' style='float:left; margin-right:4px;' href='admin.php?page=wpsc-edit-products'>".TXT_WPSC_ADD_NEW_PRODUCT."</a>";
			$output .= "<a class='button rbutton' style='float:left; margin-right:4px;' href='admin.php?page=wpsc_display_coupons_page'>".TXT_WPSC_MARKETING."</a>";
			$output .= "<a class='button rbutton' style='float:left; margin-right:4px;' href='admin.php?page=wpsc-settings'>".TXT_WPSC_OPTIONS."</a>";
			$output .= "</p><br />";
			$output .= "<p class='youhave'>$currentsalesthismonth</p>";
			$output .= "</div>";
			$output .= "</div>";
		}
		echo $output;
	}

	/* Detects if the logged in user is a subscriber. */
	function check_subscriber(){
		global $current_user;
		return $current_user->caps['subscriber'];
	}

	/* Only display the dashboard widget if the logged in user is not a subscriber. */
	function wp_current_sales_add_dashboard_widgets(){
		if(!check_subscriber()){ wp_add_dashboard_widget('wp_current_sales_right_now', 'Current Sales this Month', 'wp_current_sales_right_now'); }
	}
}

add_action('wp_dashboard_setup', 'wp_current_sales_add_dashboard_widgets');
?>