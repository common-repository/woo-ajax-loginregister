<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Settings_Tab_WALR {
	
	/*
	* __construct function
	*/
	function __construct() {		
		$this->init();
    }
	
	/*
	* init function
	*/
    function init() {
		
		add_action('admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );
		
		//ajax save admin api settings
		add_action( 'wp_ajax_alr_setting_form_update', array( $this, 'alr_setting_form_update_callback') );
		    
    }
	
	
	/*
	* Admin Menu add function
	* WC sub menu 
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Ajax Login/Register', 'Ajax Login/Register', 'manage_options', 'ajax_login_register', array( $this, 'woocommerce_product_ajax_login_register_page_callback' ) ); //woocommerce_product_ajax_login_register_page_callback
	}
	
	
	/*
	* settings form save for Setting tab
	*/
	function alr_setting_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'alr_setting_form_action', 'alr_setting_form_nonce_field' ) ) {

			update_option( 'password_strength', $_POST[ 'password_strength' ] );
			update_option( 'captcha_enabled', $_POST[ 'captcha_enabled' ] );

			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	
	/*
	* callback for Ajax Login/Register page
	*/
	public function woocommerce_product_ajax_login_register_page_callback(){
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : ''; ?>
		<?php require_once( 'views/header.phtml' ); ?>
		<div class="woocommerce alr_admin_layout">
            <div class="alr_admin_content">
				<div class="zorem_alr_tab_name">
					<input id="tab1" type="radio" name="tabs" class="alr_tab_input" data-label="<?php _e('Settings', 'woocommerce'); ?>" data-name="alr_content1" data-tab="settings" checked>
					<label for="tab1" class="alr_tab_label first_label"><?php _e('Settings', 'woocommerce'); ?></label>
					
					<input id="tab2" type="radio" name="tabs" class="alr_tab_input" data-label="<?php _e('Add-ons', 'woocommerce'); ?>" data-name="alr_content2" data-tab="add-ons" <?php if(isset($_GET['tab']) && ($_GET['tab'] == 'add-ons')){ echo 'checked'; } ?>>
					<label for="tab2" class="alr_tab_label"><?php _e('Add-ons', 'woocommerce'); ?></label>
				</div>
				<div class="zorem_alr_tab_wraper">
					<?php require_once( 'views/alr_setting_tab.php' ); ?>
					<?php require_once( 'views/alr_addons_tab.phtml' ); ?>
				</div>
            </div>
        </div>
    <?php }
	
    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
	function get_settings() {
		
        $settings = array(
			'password_strength' => array(
				'title'		=> __( 'Required registration password strength', 'woocommerce-ajax-login-register' ),
				'desc_tip'      => '<span class="desc_hint">'.__( 'Hint: The password should be at least 7 characters long, and should contain letters and numbers.', 'woocommerce-ajax-login-register' ).'</span>',
				'type'		=> 'dropdown',
				'default'	=> '0',
				'show'		=> true,
				'id'		=> 'password_strength',				
				'options'	=> array(
					'0' => __( 'Very Weak / Anything', 'woocommerce-ajax-login-register' ),
					'1' => __( 'Password should be at least Weak', 'woocommerce-ajax-login-register' ),
					'2' => __( 'Also Weak but a little stronger', 'woocommerce-ajax-login-register' ),
					'3' => __( 'Medium (default)', 'woocommerce-ajax-login-register' ),
					'4' => __( 'Strong', 'woocommerce-ajax-login-register' ),
				),
				'class'		=> 'password_strength',
			),
			'captcha_enabled' => array(
				'title'		=> __( 'Captcha Enable/Disable', 'woocommerce-ajax-login-register' ),
				'type'		=> 'checkbox',
				'default'	=> 'yes',
				'show'		=> true,
				'id'		=> 'captcha_enabled',
				'class'		=> 'captcha_enabled',
			),
        );
        return $settings;
    }
	
	/*
	* get html of fields
	*/
	public function get_html( $arrays ){
		
		$checked = '';
		?>
		<table class="form-table">
			<tbody>
            	<?php foreach( (array)$arrays as $id => $array ){
				
					if($array['show']){	
					?>
                	<?php if($array['type'] == 'title'){ ?>
                		<tr valign="top titlerow">
                        	<th colspan="2"><h3><?php echo $array['title']?></h3></th>
                        </tr>    	
                    <?php continue;} ?>
				<tr valign="top" class="<?php //echo $array['class'];?>">
					<?php if($array['type'] != 'desc'){ ?>										
					<th scope="row" class="titledesc"  >
						<label for=""><?php echo $array['title']?><?php if(isset($array['title_link'])){ echo $array['title_link']; } ?>
							<?php if( isset($array['tooltip']) ){?>
                            	<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
                            <?php } ?>
                        </label>
					</th>
					<?php } ?>
					<td class="forminp"  <?php if($array['type'] == 'desc'){ ?> colspan=2 <?php } ?>>
                    	<?php if( $array['type'] == 'checkbox' ){								
																						
								if(get_option($id)){
									$checked = 'checked';
								} else{
									$checked = '';
								} 
							
							if(isset($array['disabled']) && $array['disabled'] == true){
								$disabled = 'disabled';
								$checked = '';
							} else{
								$disabled = '';
							}							
							?>
						<span class="">
                        	<input type="hidden" name="<?php echo $id?>" value="0"/>
							<input type="checkbox" id="<?php echo $id?>" name="<?php echo $id?>" class="tgl tgl-flat <?php echo $array['class']?>" <?php echo $checked ?> value="1" <?php echo $disabled; ?>/>
							<label class="tgl-btn <?php echo $array['class']?>" for="<?php echo $id?>"></label>
                            <p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
						</span>
						<?php } elseif( $array['type'] == 'textarea' ){ ?>
                                        <fieldset>
                                        <textarea rows="3" cols="20" class="input-text regular-input <?php echo $array['class']?>" type="textarea" name="<?php echo $id?>" id="<?php echo $id?>" style="" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>"><?php if(!empty(get_option($id))){echo get_option($id); }?></textarea>
                                        </fieldset>
                        <?php }  elseif( isset( $array['type'] ) && $array['type'] == 'dropdown' ){?>
                        	<?php
								if( isset($array['multiple']) ){
									$multiple = 'multiple';
									$field_id = $array['multiple'];
								} else {
									$multiple = '';
									$field_id = $id;
								}
							?>
                        	<fieldset>
								<select class="select select2 <?php echo $array['class']?>" id="<?php echo $field_id?>" name="<?php echo $id?>" <?php echo $multiple;?>>    <?php foreach((array)$array['options'] as $key => $val ){?>
                                    	<?php
											$selected = '';
											if( isset($array['multiple']) ){
												if (in_array($key, (array)$this->data->$field_id ))$selected = 'selected';
											} else {
												if( get_option($id) == (string)$key )$selected = 'selected';
											}
                                        
										?>
										<option value="<?php echo $key?>" <?php echo $selected?> ><?php echo $val?></option>
                                    <?php } ?><p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
								</select><p class="description"><?php echo (isset($array['desc_tip']))? $array['desc_tip']: ''?></p>
							</fieldset>
                        <?php } elseif( $array['type'] == 'title' ){?>
						<?php }
						elseif( $array['type'] == 'label' ){ ?>
							<fieldset>
                               <label><?php echo $array['value']; ?></label>
                            </fieldset>
						<?php }
						elseif( $array['type'] == 'button' ){ ?>
							<fieldset>
								<button class="button-primary btn_green2 <?php echo $array['button_class'];?>" <?php if($array['disable']  == 1){ echo 'disabled'; }?>><?php echo $array['label'];?></button>
							</fieldset>
						<?php }
						elseif( $array['type'] == 'radio' ){ ?>
							<fieldset>
                            	<ul>
									<?php foreach((array)$array['options'] as $key => $val ){?>
									<li><label><input name="product_visibility" value="<?php echo $key; ?>" type="radio" style="" class="product_visibility" <?php if( get_option($id) == $key ) { echo 'checked'; } ?> /><?php echo $val;?><br></label></li>
                                    <?php } ?>
                                 </ul>
							</fieldset>
						<?php }
						else { ?>
                                                    
                        	<fieldset>
                                <input class="input-text regular-input <?php echo $array['class']?>" type="text" name="<?php echo $id?>" id="<?php echo $id?>" style="" value="<?php echo get_option($id)?>" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>">
                            </fieldset>
                        <?php } ?>
                        
					</td>
				</tr>
	<?php } } ?>
			</tbody>
		</table>
	<?php 
	}

}
new WC_Settings_Tab_WALR();