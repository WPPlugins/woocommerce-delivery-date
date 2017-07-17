<?php
/**
 * Plugin Name: Woocommerce Delivery Date
 * Plugin URI: www.dreamfoxmedia.com 
 * Version: 1.0.7
 * Author URI: www.dreamfoxmedia.com
 * Author: Dreamfox Media
 * Description: Extend Woocommerce plugin to add delivery date on checkout
 * Requires at least: 3.7
 * Tested up to: 4.6.1
 * License: GPLv3 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: woocommerce-delivery-date
 * Domain Path: /lang/
 * @Developer : Marco van Loghum SLaterus / Anand Rathi ( Dreamfoxmedia )
 */
/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && !function_exists('softsdev_delivery_date')  ) {
	/*-----------------------------------------------------*/
	// load text domain
	add_action( 'plugins_loaded', 'softsdev_dd_load_textdomain' );
	// Submenu on woocommerce section
	add_action('admin_menu', 'softsdev_delivery_submenu_page');
	// delivery date selection on checkout page
	add_action( 'woocommerce_after_order_notes' , 'softsdev_dd_checkout_field' );
	//error message on submit
	add_action( 'woocommerce_checkout_process', 'softsdev_dd_checkout_field_process ');
	// update delivery date
	add_action( 'woocommerce_checkout_update_order_meta', 'softsdev_dd_checkout_field_update_order_meta' );
	// delivery date on order view & thank you page
	add_action( 'woocommerce_email_after_order_table', 'softsdev_dd_email_with_delivery_date', 15, 2 );
	// delivery date on order view & thank you page
	add_action( 'woocommerce_order_details_after_order_table', 'softsdev_dd_order_view',20 );
	add_action( 'woocommerce_thankyou', 'softsdev_dd_order_view',20 );
	/*-----------------------------------------------------*/
	/*-----------------------------------------------------*/
	/*-----------------------------------------------------*/
	function softsdev_dd_load_textdomain() {
		load_plugin_textdomain( 'softsdev', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
	}
	/*-----------------------------------------------------*/

	// woocommerce delivery date menu	
	function softsdev_delivery_submenu_page() {
		add_submenu_page( 'woocommerce', __( 'Delivery Date', 'softsdev' ), __( 'Delivery Date', 'softsdev' ), 'manage_options', 'delivery-date', 'softsdev_delivery_date' ); 
	}
	/*-----------------------------------------------------*/
	
	function softsdev_delivery_date() {
		echo '<div class="wrap "><div id="icon-tools" class="icon32"></div>';
			echo '<h2>'. __( 'Delivery Date', 'softsdev' ) .'</h2>';
			
			$args = array( 
						'hide_empty' => 0,
					    'orderby'    => 'slug',
					    'order'      => 'ASC',											
					);
			$product_categories = get_terms('product_cat', $args);
			
			// fwt and set settings
			if( isset( $_POST['delivery_date'] ) ){
				$dd_setting = $_POST['delivery_date'];

				update_option( 'delivery_date_setting', $dd_setting );
			}else{
				$dd_setting = get_option('delivery_date_setting');			
			}
			$no_of_days_to_deliver = ( $dd_setting ) && array_key_exists( 'no_of_days_to_deliver', $dd_setting ) ? $dd_setting['no_of_days_to_deliver'] : '';
			$applicable_categories = ( $dd_setting ) && array_key_exists ( 'categories', $dd_setting ) ? $dd_setting['categories'] : array();
						
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'].'?page=delivery-date' ?>" method="post">
				<div class="postbox " style="padding: 10px; margin: 10px 0px;">
					<h3 class="hndle"><?php echo __( 'No of day\'s to Delivery', 'softsdev' ); ?></h3>				
					<input type="text" value="<?php echo $no_of_days_to_deliver ?>" name="delivery_date[no_of_days_to_deliver]" id="no_of_days_to_deliver" /><br />
					<small><?php echo __( 'How many days user can select delivery date', 'softsdev' ); ?></small>
				</div>
				<div class="postbox" style="padding: 10px; margin: 10px 0px;">				
					<h3 class="hndle"><?php echo __( 'Applicable Categories', 'softsdev' ); ?></h3>
					<small><?php echo __( 'Select categories for to choose delivery date on checkout', 'softsdev' ); ?></small>					
					<div>
						<ul id="applicable_category">
							<?php
							foreach( $product_categories as $category ){
								if( in_array(  $category->term_id,  $applicable_categories ) ){
									$checked = 'checked="checked"';
									$class = 'checked';
								}else{
									$class = '';
									$checked = '';
								}
								echo '<li class="'.$class.'"><input '.$checked .' id="pro_cat_'.$category->term_id.'" name="delivery_date[categories][]" type="checkbox" value="'.$category->term_id.'" /><label for="pro_cat_'.$category->term_id.'"> '. ucwords($category->name) .'</label></li>';
							}
							?>
						</ul>
						<small><?php echo __( 'You can only able select two category for this. You can purchase full version at www.dreamfoxmedia.nl!', 'softsdev' ); ?></small>
						
					</div>
				</div>
				<input class="button-large button-primary" type="submit" value="save" />
			</form>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery("#applicable_category input:checkbox").change(function(){
						var length = jQuery("#applicable_category input:checkbox:checked").length;
						var is_checked = jQuery(this).is(':checked')
						if( length > 1 ) {
							jQuery('#applicable_category input[type=checkbox]').attr('disabled', 'disabled');
							jQuery('#applicable_category input[type=checkbox]:checked').removeAttr('disabled');
						}else{
							jQuery('#applicable_category input[type=checkbox]').removeAttr('disabled');
						}
						if( is_checked )
							jQuery(this).attr('checked', 'checked').parents('li').addClass('checked');
						else
							jQuery(this).parents('li').removeClass('checked');							
					});
					var length = jQuery("#applicable_category input:checkbox:checked").length;
					if( length > 1 ) {
							jQuery('#applicable_category input[type=checkbox]').attr('disabled', 'disabled');
							jQuery('#applicable_category input[type=checkbox]:checked').removeAttr('disabled');						
					}
				});
			</script>
			<style type="text/css">
				#applicable_category{
					border: 1px solid #D0D0D0;
					height: 273px;
					overflow-y: auto;
					padding: 7px;
					width: 40%;				
				}
				#applicable_category li.checked{
					background:#222222;
					color:#cccccc;
				}
				#applicable_category li {
					border: 1px solid #D0D0D0;
				}
				#applicable_category input {
					float: left;
					left: 5px;
					position: relative;
					top: 10px;
				}
				#applicable_category label {
				  display: block;
				  padding: 7px 10px 7px 25px;
				}			
			</style>			
			<?php
		echo '</div>';
	
	}
	/*-----------------------------------------------------*/
	
	// Our hooked in function - $fields is passed via the filter!
	function softsdev_dd_checkout_field( $checkout ) {
	
		$show_delivery_datepicker = false;
		$dd_setting = get_option('delivery_date_setting');			
		$applicable_categories = isset( $dd_setting ) && array_key_exists ( 'categories', $dd_setting ) ? $dd_setting['categories'] : array();
				
		global $woocommerce;
		$cart_products = $woocommerce->cart->get_cart();
		foreach( $cart_products as  $_product ){
			$category_list = wp_get_post_terms( $_product['product_id'], 'product_cat', array('fields'=>'ids') );
			$is_common = array_intersect( $category_list, $applicable_categories );
			if( count( $is_common ) ){
				$show_delivery_datepicker = true;
				break;
			}
		}
		
		if( $show_delivery_datepicker === false ){
			return '';
		}else{
			$dates_to_deliver = isset( $dd_setting['no_of_days_to_deliver'] ) && is_numeric( $dd_setting['no_of_days_to_deliver'] ) ? $dd_setting['no_of_days_to_deliver'] : 0;
			
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui', "http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" , '', '', false);
			$date_format 	= get_option( 'date_format' );
			$js_date_format = softsdev_date_format_php_to_js( get_option( 'date_format' ) );
			$min_delivery_date 	= date( $date_format, strtotime( '+'.( $dates_to_deliver ).' day', current_time( 'timestamp', 0 ) ) );
			echo '<script language="javascript">jQuery(document).ready(function(){
					jQuery("#delivery_date").width("150px");
					jQuery("#delivery_date").datepicker({dateFormat: "'.$js_date_format.'", minDate:'.$dates_to_deliver.',changeMonth: true, changeYear: true, yearRange: "'.date('Y').':'.(date('Y')+4).'"});
					jQuery("#delivery_date").after("<div><small style=font-size:10px;>'. __('We will try our best to deliver your order on the specified date', 'softsdev' ) .'</small></div>");
				});</script>';
			echo '<style type="text/css">
					.ui-datepicker-calendar .ui-datepicker-unselectable span { background: none repeat scroll 0 0 red !important; color:#fff !important; }
					.ui-datepicker-calendar td a { background: none repeat scroll 0 0 #008000 !important; color:#fff !important; }
					.ui-datepicker-calendar .ui-state-disabled{ opacity:0.85 !important; cursor:no-drop !important;	}
				  </style>';


			echo '<div id="dd__checkout_field"><h2>'.__('Delivery Date', 'softsdev').'</h2>';
				woocommerce_form_field( 'delivery_date', array(
					'type'          => 'text',
					'class'         => array('delivery-date form-row-wide'),
					'label'         => __('Select delivery date', 'softsdev' ),
					'default'		=> $min_delivery_date,
				), $checkout->get_value( 'delivery_date' ));
			
			echo '</div>';
		}	
	}
	/*-----------------------------------------------------*/
	
	/**
	* Process the checkout
	**/
	function softsdev_dd_checkout_field_process() {
		global $woocommerce;
		// Check if set, if its not set add an error.
		if (!$_POST['delivery_date'])
			$woocommerce->add_error( __('Please enter something into this new shiny field.', 'softsdev') );
	}
	/*-----------------------------------------------------*/
	
	/**
	* Update the order meta with field value
	**/
	function softsdev_dd_checkout_field_update_order_meta( $order_id ) {
		
		if ( isset( $_POST['delivery_date'] ) && !empty(  $_POST['delivery_date'] ) )
			update_post_meta( $order_id, 'Delivery Date', esc_attr($_POST['delivery_date']));			
	
	}
	/*-----------------------------------------------------*/

	function softsdev_dd_email_with_delivery_date( $order, $is_admin_email ) {
		$delivery_date =  @get_post_meta($order->id, 'Delivery Date', true);		
		if( !empty( $delivery_date ) ){
			$date_format 	= get_option( 'date_format' );		
			$delivery_date = date( $date_format, strtotime( $delivery_date ) );		
			echo '<p><strong>' . __( 'Delivery Date', 'softsdev' ) . ':</strong> ' . $delivery_date . '</p>';
		}
	}
	/*-----------------------------------------------------*/

	function softsdev_dd_order_view( $order ) {
		$delivery_date =  @get_post_meta($order->id, 'Delivery Date', true);
		if( !empty( $delivery_date ) ){
			$date_format 	= get_option( 'date_format' );		
			$delivery_date = date( $date_format, strtotime( $delivery_date ) );		
			echo '<div>';
			echo '<header class="title"><h3>' . __( 'Delivery Date', 'softsdev' ) . '</h3></header>';
			echo '<dl>'.$delivery_date.'</dl>';
			echo '</div>';

		}

	}
	/*-----------------------------------------------------*/
        function softsdev_date_format_php_to_js( $php_format ) {
            $SYMBOLS_MATCHING = array(
                // Day
                'd' => 'dd',
                'D' => 'D',
                'j' => 'd',
                'l' => 'DD',
                'N' => '',
                'S' => '',
                'w' => '',
                'z' => 'o',
                // Week
                'W' => '',
                // Month
                'F' => 'MM',
                'm' => 'mm',
                'M' => 'M',
                'n' => 'm',
                't' => '',
                // Year
                'L' => '',
                'o' => '',
                'Y' => 'yy',
                'y' => 'y',
                // Time
                'a' => '',
                'A' => '',
                'B' => '',
                'g' => '',
                'G' => '',
                'h' => '',
                'H' => '',
                'i' => '',
                's' => '',
                'u' => ''
            );
            $jqueryui_format = "";
            $escaping = false;
            for ($i = 0; $i < strlen($php_format); $i++) {
                $char = $php_format[$i];
                if ($char === '\\') { // PHP date format escaping character
                    $i++;
                    if ($escaping)
                        $jqueryui_format .= $php_format[$i];
                    else
                        $jqueryui_format .= '\'' . $php_format[$i];
                    $escaping = true;
                }
                else {
                    if ($escaping) {
                        $jqueryui_format .= "'";
                        $escaping = false;
                    }
                    if (isset($SYMBOLS_MATCHING[$char]))
                        $jqueryui_format .= $SYMBOLS_MATCHING[$char];
                    else
                        $jqueryui_format .= $char;
                }
            }
            return $jqueryui_format;
        }        
	
	/*-----------------------------------------------------*/
	/*******************************************************/
	/*-----------------------------------------------------*/

}
?>