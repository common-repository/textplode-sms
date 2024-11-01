<?php

if (!defined('ABSPATH')){ exit; }

/**
 * Plugin Name: Textplode
 * Plugin URI: http://woothemes.com/products/textplode/
 * Description: Textplode integration for WooCommerce
 * Version: 1.0.0
 * Author: Textplode
 * Author URI: https://www.textplode.com/
 * Developer: Textplode
 * Developer URI: https://www.textplode.com/
 * Text Domain: textplode
 *
 * Copyright: © 2009-2016 Textplode.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Check if WooCommerce is active
 **/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){

	register_activation_hook(__FILE__, 'textplode_activate');
	register_deactivation_hook(__FILE__, 'textplode_deactivate');

	$textplode = null;

	function textplode_activate(){

	}

	function textplode_deactivate(){

	}


	function textplode_create_template_post_type() {

		$data = array(
			'labels' => array(
				'name' => __('SMS Templates', 'textplode'),
				'singular_name' => __('Template', 'textplode'),
				'add_new_item' => __('Add New Template', 'textplode'),
				'edit_item' => __('Edit Template', 'textplode'),
				'new_item' => __('New Template', 'textplode'),
				'view_item' => __('View Template', 'textplode'),
				'search_items' => __('Search Templates', 'textplode'),
				'not_found' => __('No Templates Found', 'textplode'),
				'not_found_in_trash' => __('No Templates Found in Trash', 'textplode'),
				'all_items' => __('SMS Templates', 'textplode'),
				),
			'public' => false,
			'has_archive' => false,
			'hierarchical' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => '',
			// 'menu_postition' => 99,
		);

		register_post_type('textplode_template', $data);
	}

	function textplode_create_template_menu_item(){
		if(stristr($_SERVER['SCRIPT_NAME'], 'post-new.php') && 'textplode_template' == $_GET['post_type']){
			add_submenu_page('woocommerce', __('Textplode', 'textplode'),  __('Textplode', 'textplode'), 'manage_options', 'post-new.php?post_type=textplode_template');
		}else{
			add_submenu_page('woocommerce', __('Textplode', 'textplode'),  __('Textplode', 'textplode'), 'manage_options', 'edit.php?post_type=textplode_template');
		}
	}

	function textplode_get_instance($force = false){
		global $textplode; 

		if(!$textplode || $force){
			require_once(dirname(__FILE__) . '/models/textplode.class.php');
			$textplode = new Textplode(get_option('textplode_api_key'));
		}

		return $textplode;
	}

	function textplode_get_instance_force(){
		return textplode_get_instance(true);
	}

	function textplode_get_service_status(){
		global $textplode;

		$status = $textplode->get_service_status();
		return $status;
	}

	function textplode_get_service_messages(){
		global $textplode;

		$messages = $textplode->get_service_messages();

		if($messages){
			foreach($messages as $message){
				echo '<div class="notice notice-warning is-dismissible"><p>Textplode: ' . $message . '</p></div>';	
			}
		}

	}

	function textplode_get_credits(){
		global $textplode;

		$credits = $textplode->account->get_credits();

		return $credits;
	}

	function textplode_get_groups_array(){
		global $textplode;

		$_groups = $textplode->groups->get_all();
		$groups = array();

		if($_groups){
			foreach($_groups as $group){
				$groups[$group['id']] = $group['name'] . ' (' . $group['count'] . ')';
			}
		}
		return $groups;
	}

	function textplode_get_templates(){
		$query = new WP_Query(array('post_type' => 'textplode_template', 'post_status' => 'publish'));

		$templates = array();

		while($query->have_posts()){
		    $query->the_post();
		    $templates[get_the_ID()] = get_the_title();
		}

		wp_reset_query();
		return array(0 => __('--- Please Select ---', 'textplode')) + $templates;
	}

	function textplode_get_template_by_id($id){
		if($id){

			$query = new WP_Query(array('post_type' => 'textplode_template', 'post_status' => 'publish', 'p' => $id));

			if($query->post){
				return $query->post->post_content;
			}

		}

		return null;
	}

	function textplode_admin_field_select_with_button($value){
		$option_value = get_option( $value['id'], $value['default'] );

		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo wc_help_tip($value['desc_tip']); ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<select
					name="<?php echo esc_attr($value['id'] ); ?>" id="<?php echo esc_attr($value['id']); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" class="<?php echo esc_attr($value['class']); ?>" <?php echo implode(' ', $custom_attributes); ?>>
					<?php
						foreach($value['options'] as $key => $val) {
							?>
							<option value="<?php echo esc_attr($key); ?>" <?php

								if(is_array($option_value)){
									selected(in_array($key, $option_value), true);
								}else{
									selected( $option_value, $key );
								}

							?>><?php echo $val ?></option>
							<?php
						}
					?>
				</select>
				<input type="button" name="<?php echo esc_attr($value['id'] ); ?>_button" value="<?php echo $value['action']; ?>" class="button-primary"> 
				<?php echo $description; ?>

				<script type="text/javascript">
					jQuery(document).on('ready', function($){

						jQuery('input[name="<?php echo esc_attr($value['id'] ); ?>_button"]').on('click', function(){

							if(confirm("This will refresh the current page. Continue?")){

								var data = {
									'action': 'merge_customers',
									'group': jQuery('select[name="<?php echo esc_attr($value['id'] ); ?>"] option:selected').val(),
								};

								jQuery.post(ajaxurl, data, function(response) {
									window.location.reload();
								});

							}

						})
					});
				</script>

			</td>
		</tr><?php
	}

	function textplode_merge_customers_callback(){
		global $textplode;

		$orders = get_posts(array(
		    'post_type'   => 'shop_order',
		    'post_status' => array_keys(wc_get_order_statuses()),
		));

		foreach($orders as $order){
			$meta = get_post_meta($order->ID);

			$first_name = $meta['_billing_first_name'][0];
			$last_name = $meta['_billing_last_name'][0];
			$phone_number = $meta['_billing_phone'][0];

			$textplode->contacts->add($first_name, $last_name, $phone_number, $_POST['group']);
		}

		wp_die();
	}

	function textplode_admin_field_read_only($value){
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
			</th>
			<td class="forminp forminp-text">
				<span class="description" style="display:inline-block; width: 160px;<?php echo $value['css']; ?>">
					<?php echo $value['default']; ?>
				</span>
				<span class="description">
					<?php echo $value['desc']; ?>
				</span>
			</td>
		</tr>
		<?php
	}

	add_action('woocommerce_admin_field_readonly', 'textplode_admin_field_read_only', 10, 1);
	add_action('woocommerce_admin_field_select_with_button', 'textplode_admin_field_select_with_button', 10, 1);

	function textplode_get_settings($current_section = '') {

		$service_status = textplode_get_service_status();
		$templates = textplode_get_templates();
		$assign_templates = array();

		foreach(wc_get_order_statuses() as $status_slug => $status_name){

			$assign_templates[] = array(
				'name'	=> __($status_name . ' Active', 'textplode'),
				'type'	=> 'checkbox',
				'desc'	=> '',
				'id'	=> 'textplode_' . $status_slug . '_active',
			);

			$assign_templates[] = array(
				'name'	=> __($status_name . ' Template', 'textplode'),
				'type'	=> 'select',
				'options' => $templates,
				'desc'	=> '',
				'id'	=> 'textplode_' . $status_slug . '_template',
			);

		}

	    $settings = array(

	        array(
	            'name'     => __('Service Status', 'textplode'),
	            'type'     => 'title',
	            'desc'	   => 'Shows the current status of the various aspects of the Textplode service.<br/>If one does not show the work "OK", then there may be issues preventing<br/>the plugin from communicating with our servers',
	            'id'       => 'textplode_section_status'
	        ),     

	        array(
	            'name' => __('Textplode', 'textplode'),
	            'type' => 'readonly',
				'css' => '',
				'default' => $service_status['Textplode'], 
	            'id'   => 'textplode_status_textplode'
	        ),

	        array(
	            'name' => __('Website', 'textplode'),
	            'type' => 'readonly',
				'css' => '',
				'default' => $service_status['Website'], 
	            'id'   => 'textplode_status_website'
	        ),

	        array(
	            'name' => __('API', 'textplode'),
	            'type' => 'readonly',
				'css' => '',
				'default' => $service_status['API'], 
	            'id'   => 'textplode_status_api'
	        ),

			array(
				'type' 	=> 'sectionend',
				'id' 	=> 'textplode_section_status',
			),

	        array(
	            'name'     => __('Account Settings', 'textplode'),
	            'type'     => 'title',
	            'desc'	   => 'These settings are directly related to your account. If you changed your<br/>API key from your Textplode account, also remember to change it here<br/>to avoid any disruption to your service',
	            'id'       => 'textplode_section_account'
	        ),

	        array(
	            'name' => __('Credits', 'textplode'),
	            'type' => 'readonly',
				'css' => 'font-size: 2em;',
				'default' => textplode_get_credits(), 
				'desc' 	=> 'Not all messages cost 1 credit. If the message is longer than 160 characters, or 70 if it includes Emoji or Unicode symbols, then you will be charged accordingly.',
	            'id'   => 'textplode_credits',
				'desc' => __('<a href="https://app.textplode.com/send#buy-credits" target="_blank">Buy Credits</a>', 'textplode'),
	        ),

	        array(
	            'name' => __('API Key', 'textplode'),
	            'type' => 'text',
	            'desc' => __('<a href="https://app.textplode.com/settings/developer" target="_blank">Need an API key?</a>', 'textplode'),
	            'desc_tip' => __('You must generate an API key for your Textplode account and enter it here before using this extension', 'textplode'),
	            'id'   => 'textplode_api_key'
	        ),

			array(
				'type' 	=> 'sectionend',
				'id' 	=> 'textplode_section_account',
			),

	        array(
	            'name'     => __('General Settings', 'textplode'),
	            'type'     => 'title',
	            'desc'	   => 'These settings are related to how Textplode integrates with your<br/>WooCommerce store.',
	            'id'       => 'textplode_section_general'
	        ),

	        array(
	            'name' => __('From Name', 'textplode'),
	            'type' => 'text',
	            'default' => 'Textplode',
	            'desc_tip' => __('The name the recipient will receive the message from', 'textplode'),
	            'id'   => 'textplode_from_name'
	        ),

	        array(
	            'name' => __('Admin Number', 'textplode'),
	            'type' => 'text',
	            'desc_tip' => __('This will not be visible. Setting this will allow you to receive notifications', 'textplode'),
	            'id'   => 'textplode_admin_phone'
	        ),

	        array(
	            'name' => __('Merge To Groups', 'textplode'),
	            'type' => 'select_with_button',
	            'options' => textplode_get_groups_array(),
	            'desc_tip' => __('Merges your store\'s customers to the selected Textplode group', 'textplode'),
	            'id'   => 'textplode_admin_number',
	            'action' => __('Merge', 'textplode'),
	        ),

	        array(
	             'type' => 'sectionend',
	             'id' => 'textplode_section_general'
	        ),

	        array(
	            'name'     => __('Admin Notifications', 'textplode'),
	            'type'     => 'title',
	            'desc'	   => 'Enable or Disable admin notifications and set which message it will send',
	            'id'       => 'textplode_section_notifications_admin'
	        ),

	        array(
	            'name' => __('New Order Active', 'textplode'),
	            'type' => 'checkbox',
	            'id'   => 'textplode_admin-new-order_active'
	        ),

			array(
				'name'	=> __('New Order Template', 'textplode'),
				'type'	=> 'select',
				'options' => $templates,
				'desc'	=> '',
				'id'	=> 'textplode_admin-new-order_template',
			),

	        array(
	            'name' => __('New Customer Active', 'textplode'),
	            'type' => 'checkbox',
	            'id'   => 'textplode_admin-new-customer_active'
	        ),

			array(
				'name'	=> __('New Customer Template', 'textplode'),
				'type'	=> 'select',
				'options' => $templates,
				'desc'	=> '',
				'id'	=> 'textplode_admin-new-customer_template',
			),

	        array(
	             'type' => 'sectionend',
	             'id' => 'textplode_section_notifications_admin'
	        ),

	        array(
	            'name'     => __('Customer Notifications', 'textplode'),
	            'type'     => 'title',
	            'desc'	   => 'Enable or Disable customer notifications and set which message it will send',
	            'id'       => 'textplode_section_notifications_customer'
	        ),

	    );

	    $settings = array_merge($settings, $assign_templates);

	    $settings[] = 
	        array(
	             'type' => 'sectionend',
	             'id' => 'textplode_section_notifications_customer'
	        );

	    $settings = apply_filters('textplode', $settings);

	    return apply_filters('woocommerce_get_settings_textplode', $settings, $current_section);
	}

	function textplode_add_settings_tab($settings_tabs){
		$settings_tabs['textplode'] = __('Textplode', 'textplode_tab_title');
		return $settings_tabs;
	}

	function textplode_settings_tab(){ 
		textplode_get_sections();
		woocommerce_admin_fields(textplode_get_settings());
	}

	function textplode_update_settings() {
        woocommerce_update_options(textplode_get_settings());
    }

    function textplode_parse_merge_tags($id, $message, $admin = false, $customer = false, $posted = false){
    	if($id){

    		if($customer){
    			$user = get_user_by('email', $posted['billing_email']);
				$message = str_ireplace('#customer_id#', $user->ID, $message);
			    $message = str_ireplace('#fname#', $posted['billing_first_name'], $message);
			    $message = str_ireplace('#lname#', $posted['billing_last_name'], $message);
			    $message = str_ireplace('#email#', $posted['billing_email'], $message);
			    $message = str_ireplace('#phone#', $posted['billing_phone'], $message);
			    $to = get_option('textplode_admin_phone');
    		}else{
		    	$message = str_ireplace('#order_id#', $id, $message);
			    $message = str_ireplace('#fname#', $posted['billing_first_name'], $message);
			    $message = str_ireplace('#lname#', $posted['billing_last_name'], $message);
			    $message = str_ireplace('#email#', $posted['billing_email'], $message);
			    $message = str_ireplace('#phone#', $posted['billing_phone'], $message);
			    $to = $admin ? get_option('textplode_admin_phone') : $posted['billing_phone'];
		    }
		    return array('to' => $to, 'message' => $message);
		}
    }

	function textplode_get_sections() {
		global $sections;

		// $sections = array(
		// 	''          	=> __( 'General', 'woocommerce' ),
		// 	'display'       => __( 'Display', 'woocommerce' ),
		// 	'inventory' 	=> __( 'Inventory', 'woocommerce' ),
		// 	'downloadable' 	=> __( 'Downloadable Products', 'woocommerce' ),
		// );
		// print_r($sections);

		return apply_filters('woocommerce_get_sections_textplode', $sections);
	}

    function textplode_send_message($id, $old, $new, $posted = false){
    	global $wpdb;
    	global $textplode;

    	$template_id = null;
    	$customer = false;
    	$admin = false;

    	if('yes' == get_option('textplode_wc-' . $new . '_active')){ /* Only for WC Statuses */
			$template_id = get_option('textplode_wc-' . $new . '_template');
		}else if('yes' == get_option('textplode_' . $new . '_active') && get_option('textplode_admin_phone')){ /* Only for Admin Statuses if we have an admin number set */
			$template_id = get_option('textplode_' . $new . '_template');
			$customer = ('admin-new-customer' == $new);
			$admin = true;
		}

    	if($template_id){

    		$template = textplode_get_template_by_id($template_id);
    		$message = textplode_parse_merge_tags($id, $template, (bool)$admin, (bool)$customer, $posted);

	    	$textplode->messages->add_recipient($message['to'], array());
	    	$textplode->messages->set_message($message['message']);
	    	$textplode->messages->set_from(get_option('textplode_from_name'));
	    	$textplode->messages->send();

    	}

    }

    function textplode_new_order($order_id, $posted){
    	textplode_send_message($order_id, '', 'admin-new-order', $posted);
    }

    function textplode_new_customer($order_id, $posted){
    	if(!empty($posted['createaccount'])){
    		textplode_send_message($order_id, '', 'admin-new-customer', $posted);
    	}
    }


	add_action('init', 'textplode_create_template_post_type');
	add_action('init', 'textplode_get_instance');
	add_action('admin_notices', 'textplode_get_service_status');
	add_action('admin_notices', 'textplode_get_service_messages');
	add_action('admin_menu', 'textplode_create_template_menu_item', 99);
	add_action('woocommerce_settings_tabs_textplode', 'textplode_settings_tab');
	add_action('woocommerce_update_options_textplode', 'textplode_update_settings');
	add_action('update_option_textplode_api_key', 'textplode_get_instance_force');
	add_action('woocommerce_order_status_changed', 'textplode_send_message', 10, 3);
	add_filter('woocommerce_settings_tabs_array', 'textplode_add_settings_tab', 99);
	add_action('woocommerce_checkout_update_order_meta', 'textplode_new_order', 10, 2); // woocommerce_new_order fires before we have all data
	add_action('woocommerce_checkout_order_processed', 'textplode_new_customer', 10, 2);  // woocommerce_created_customer fires before meta is added 
	add_action('wp_ajax_merge_customers', 'textplode_merge_customers_callback');
}
?>
