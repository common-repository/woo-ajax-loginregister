<?php
/**
 * @wordpress-plugin
 * Plugin Name: Ajax Login/Register for WooCommerce
 * Plugin URI: https://www.zorem.com/shop/woocommerce-ajax-login-register/
 * Description: Woocommerce Ajax login/register (addon / plugin) is allows you to login and register using Ajax and add jquery validation.
 * Version: 2.0.6
 * Author: zorem
 * Author URI:  http://www.zorem.com/
 * License:     GPL-2.0+
 * License URI: http://www.zorem.com/
 * Text Domain: woocommerce-ajax-login-register
 * WC tested up to: 6.8.0
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include 'includes/settings.php';
class Zorem_AJAX_Login_Register {
	
	/**
	 * Ajax Login/Register for WooCommerce
	 *
	 * @var string
	 */
	public $version = '2.0.6';
	
	/**
     * Constructor.
 	*/
    public function __construct() {
		$this->init();
    }
	
	/*
	* init when class loaded
	*/
	public function init(){
		//login captcha
		add_action( 'woocommerce_login_form', array( $this, 'woocommerce_login_form_captcha_html') );
		
		//Registration captcha
		add_action( 'woocommerce_register_form', array( $this, 'woocommerce_register_form_captcha_html') );
		
		//Reset Password captcha
		add_action( 'woocommerce_lostpassword_form', array( $this, 'woocommerce_lostpassword_form_captcha_html'));
		
		//generate captcha
		add_action( 'wp_ajax_get_wc_captcha', array( $this, 'get_wc_captcha') );
		add_action( 'wp_ajax_nopriv_get_wc_captcha', array( $this, 'get_wc_captcha') );
		
		//ajax login function
		add_action( 'wp_ajax_nopriv_ajaxlogin', array( $this, 'ajax_login') );
		add_action( 'wp_ajax_ajaxlogin', array( $this, 'ajax_login') );
		
		//ajax register function
		add_action( 'wp_ajax_nopriv_ajaxregister', array( $this, 'ajax_register') );
		add_action( 'wp_ajax_ajaxregister', array( $this, 'ajax_register') );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script'), 200 );
		add_action('wp_footer', array( $this, 'zorem_flatsome_account_login_lightbox'), 10);
		add_action( "woocommerce_login_form_end", array( $this, 'zorem_login_message'));
		add_action( "woocommerce_lostpassword_form", array( $this, 'zorem_reset_message'));
		
		
		add_action( 'wp_ajax_nopriv_captchatest', array( $this, 'captchatest') );
		add_action( 'wp_ajax_captchatest', array( $this, 'captchatest') );		
		
		add_action( 'wp_loaded', array( $this, 'process_lost_password' ), 10 );
		
		add_filter( 'woocommerce_get_script_data', array( $this, 'zorem_change_password_strength' ), 20, 2 );
		
		add_action( "woocommerce_register_form_end", array( $this, 'zorem_register_message') );
		
		add_action( 'plugins_loaded', array( $this, 'walr_load_textdomain'));
		add_action( 'template_redirect', array( $this, 'create_session_if_not_exist'));
		
		//load javascript in admin
		add_action('admin_enqueue_scripts', array( $this, 'wc_alr_enqueue' ) );
		
	}
	
	/*** Method load Language file ***/
	function walr_load_textdomain() {
		load_plugin_textdomain( 'woocommerce-ajax-login-register', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
	}
	
	/*
	* return plugin directory URL
	*/
	public function plugin_dir_url() {
		return plugin_dir_url( __FILE__ );
	}
	
	/*
	* Add admin javascript
	*
	*/	
	public function wc_alr_enqueue() {
		
		
		// Add condition for css & js include for admin page  
		if(!isset($_GET['page'])) {
				return;
		}
		if( $_GET['page'] != 'ajax_login_register') {
			return;
		}
			
		// Add tiptip js and css file
		
		wp_enqueue_script( 'alr-material-min-js', plugin_dir_url(__FILE__) . 'assets/js/material.min.js', array(), $this->version );
		wp_enqueue_style( 'alr-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), $this->version );
		wp_enqueue_style( 'alr-material-css', plugin_dir_url(__FILE__) . 'assets/css/material.css', array(), $this->version );
			
		wp_enqueue_style('select2-alr', plugins_url('assets/css/select2.min.css', __FILE__ ));
		wp_enqueue_script('select2-alr', plugins_url('assets/js/select2.min.js', __FILE__));
		
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
	
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		wp_enqueue_script( 'jquery-tiptip' );
		
		wp_enqueue_script( 'alr-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery', 'wp-util'), $this->version, true );
		
	}

	/*
	* Method enqueue_script
	* js/css load
	*/
	function enqueue_script(){
		wp_enqueue_script( 'alr-ajax', plugin_dir_url( __FILE__ ) . 'assets/js/script.js', array( 'jquery' ), $this->version );
		wp_localize_script( 'alr-ajax', 'zorem_ajax_object', 
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 
			'required_message' => __('Please fill all required fields.','woocommerce-ajax-login-register'), 
			'valid_email' => __('Please Enter valid email.','woocommerce-ajax-login-register'),
			'loading_text' => __('Loading...','woocommerce-ajax-login-register'),
			'plugin_dir_url' => plugin_dir_url( __FILE__ ),	
		) );
		wp_enqueue_style( 'alr-ajax', plugin_dir_url( __FILE__ ).'assets/css/style.css', array(), $this->version, 'all');
	}
	
	/*
	* checkout login popup
	* copy of func : flatsome_account_login_lightbox
	*/
	function zorem_flatsome_account_login_lightbox(){
	  // Show Login Lightbox if selected
	  if (is_checkout() && !is_user_logged_in() && get_theme_mod('account_login_style') == 'lightbox') {
		$is_facebook_login = is_nextend_facebook_login();
		$is_google_login = is_nextend_google_login();
		wp_enqueue_script( 'wc-password-strength-meter' );
	
		?>
		<div id="login-form-popup" class="lightbox-content mfp-hide">
		  <?php if(get_theme_mod('social_login_pos','top') == 'top' && ($is_facebook_login || $is_google_login)) echo wc_get_template('myaccount/header.php'); ?>
		  <?php echo wc_get_template_part('myaccount/form-login'); ?>
		  <?php if(get_theme_mod('social_login_pos','top') == 'bottom' && ($is_facebook_login || $is_google_login)) echo wc_get_template('myaccount/header.php'); ?>
		</div>
	  <?php }
	}
	
	/*
	* hidden fields in login form
	*/
	function zorem_login_message(){
		global $woocommerce;
		?>
			<input type="hidden" name="action" value="ajaxlogin">		
		<?php
	}

	/*
	* Ajax login function
	*/
	function ajax_login(){
	
		// First check the nonce, if it fails the function will break
		check_ajax_referer( 'woocommerce-login', 'woocommerce-login-nonce' );
		
		$captcha_enabled = get_option( "captcha_enabled", "1" );
		if( $captcha_enabled == '1' ){
			
			session_start();
			if( strtolower($_POST['login_captcha']) != strtolower($_SESSION['login_captcha']['code']) ){
				echo json_encode(array(
					'loggedin'=>false,
					'message'=>__('Your captcha is invalid. Please try again.','woocommerce-ajax-login-register'),
					'invalid_captcha'=>true,
				) );die();
			}
			
		}
		
		if ( ! empty( $_POST['username'] ) && ! empty( $_POST['password'] ) ) {
			// Nonce is checked, get the POST data and sign user on
			$info = array();
			$info['user_login'] = wc_clean($_POST['username']);
			$info['user_password'] = $_POST['password'];
			$info['remember'] = false;
			if( isset($_POST['rememberme'])){
				$info['remember'] = true;
			}						
			
			$user_signon = wp_signon( $info, false );			
			if ( is_wp_error($user_signon) ){
				
				if ( isset( $user_signon->errors[ 'invalid_username' ] ) ) {
					$username_error = true;
				} else{
					$username_error = false;
				}
				if ( isset( $user_signon->errors[ 'incorrect_password' ] ) ) {
					$password_error = true;
				} else{
					$password_error = false;
				}				
			    $error_string = $user_signon->get_error_message();
				
				echo json_encode( array(
					'loggedin' => false, 
					'message' =>__( $error_string, 'woocommerce-ajax-login-register' ),
					'invalid_username' => $username_error,
					'incorrect_password' => $password_error,
				) );
			} else {
				// hook after successfull login
				do_action( "wcalr_after_login", $user_signon );
				$args = array(
					'loggedin'	=> true,
					'message'	=> __( 'Login successful, redirecting...', 'woocommerce-ajax-login-register' ),
					'redirect'	=> apply_filters( "wcalr_login_redirect", false)
				);	
					
				echo json_encode( $args );
			}
			die();
		} else{
			echo json_encode(array('loggedin'=>false, 'message'=>__('Please fill all required fields.','woocommerce-ajax-login-register')));
			die();
		}
	}
	
	/*
	* hidden fields in reset password form
	*/
	function zorem_reset_message(){
		global $woocommerce;
		?>
			<input type="hidden" name="action" value="ajaxresetpassword">
			<div class="login_msg success" style="display:none"></div>
			<div class="login_msg fail" style="display:none"></div>
			<div class="login_loader" style="display:none"><img src="<?php echo $woocommerce->plugin_url()?>/assets/images/wpspin.gif"><?php esc_html_e( 'Loading...', 'woocommerce-ajax-login-register' ); ?></div>
		<?php
	}
	
	/*
	* Ajax Reset Paswword function
	*/
	function process_lost_password(){
		if ( isset( $_POST['wc_reset_password'], $_POST['user_login'] ) ) {
			$nonce_value = wc_get_var( $_REQUEST['woocommerce-lost-password-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
			

			if ( ! wp_verify_nonce( $nonce_value, 'lost_password' ) ) {
				return;
			}
			
			$captcha_enabled = get_option( "captcha_enabled", "1" );
			if( $captcha_enabled == '1' ){
				
				session_start();
				if( strtolower($_POST['lostpassword_captcha']) != strtolower($_SESSION['lostpassword_captcha']['code']) ){
					echo json_encode(array('lost_password'=>false, 'message'=>__('Your captcha is invalid. Please try again.','woocommerce-ajax-login-register')));die();
				}
				
			}

			$success = WC_Shortcode_My_Account::retrieve_password();

			// If successful, redirect to my account with query arg set.
			if ( $success ) {
				$lost_password = 'Check your inbox for instructions how to set new password.';
				$lost_password = apply_filters( 'ajaxlogin_lost_password_message', $lost_password );
				echo json_encode(array('lost_password'=>true, 'message'=>__( $lost_password ,'woocommerce-ajax-login-register')));
				die();
			} else{
				echo json_encode(array('lost_password'=>false, 'message'=>__('Invalid username or email.','woocommerce-ajax-login-register')));
				die();
			}
		}
	}
	
	/*
	* hidden fields in register form
	*/
	function zorem_register_message(){
		global $woocommerce;
		?>
			<input type="hidden" name="action" value="ajaxregister">
			<div class="register_msg success" style="display:none"></div>
			<div class="register_msg fail" style="display:none"></div>
			<div class="register_loader" style="display:none"><img src="<?php echo $woocommerce->plugin_url()?>/assets/images/wpspin.gif"><?php esc_html_e( 'Loading...', 'woocommerce-ajax-login-register' ); ?></div>
		<?php
	}
	
	/*
	* Ajax register function
	*/
	public static function ajax_register(){
	
		// First check the nonce, if it fails the function will break
		check_ajax_referer( 'woocommerce-register', 'woocommerce-register-nonce' );
		
		$captcha_enabled = get_option( "captcha_enabled", "1" );
		if( $captcha_enabled == '1' ){

			session_start();
			if( strtolower($_POST['register_captcha']) != strtolower($_SESSION['register_captcha']['code']) ){
				echo json_encode( array(
					'code'=>'captcha_error',
					'message'=>__('Your captcha is invalid. Please try again.','woocommerce-ajax-login-register'),
					'invalid_captcha'=>true,
				) );die();
			}
			
		}
		
		$generate_password = get_option( 'woocommerce_registration_generate_password' );
		//echo $generate_password;exit;
		if ( ! empty( $_POST['email'] ) && ! empty( $_POST['password'] ) ) {
			$username = 'no' === get_option( 'woocommerce_registration_generate_username' ) ? $_POST['username'] : '';
			$password = 'no' === get_option( 'woocommerce_registration_generate_password' ) ? $_POST['password'] : '';
			$email    = $_POST['email'];
		
			$validation_error = new WP_Error();
			$validation_error = apply_filters( 'woocommerce_process_registration_errors', $validation_error, $username, $password, $email );
		
			if ( $validation_error->get_error_code() ) {
				//throw new Exception( $validation_error->get_error_message() );
				$error_array = array(
					'code' => $validation_error->get_error_code(),
					'message' => $validation_error->get_error_message()
				);
			} else {
				$new_customer = wc_create_new_customer( sanitize_email( $email ), wc_clean( $username ), $password );
				if ( is_wp_error( $new_customer ) ) {
					$error_array = array(
						'code' => $new_customer->get_error_code(),
						'message' => $new_customer->get_error_message()
					);
				} else {
					if ( apply_filters( 'woocommerce_registration_auth_new_customer', true, $new_customer ) ) {
						wc_set_customer_auth_cookie( $new_customer );
					}
					
					$args = array(
						'code'		=> 200,
						'message'	=>__( 'Account created successfully. redirecting...', 'woocommerce-ajax-login-register' ),
						'redirect'	=> apply_filters( "wcalr_register_redirect", false )
					);
					apply_filters( "wcalr_register_user_successful", false );
					echo json_encode( $args );die();
				}
			}
		} 
		elseif($generate_password == 'yes'){
			if ( empty( $_POST['email']) ){
				$error_array = array(
					'code' => 'error',
					'message' => __('Please fill all required fields.','woocommerce-ajax-login-register')
				);
			} else{
				$username = 'no' === get_option( 'woocommerce_registration_generate_username' ) ? $_POST['username'] : '';				
				$email    = $_POST['email'];					
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'woocommerce_process_registration_errors', $validation_error, $username, $password, $email );
		
				if ( $validation_error->get_error_code() ) {
					//throw new Exception( $validation_error->get_error_message() );
					$error_array = array(
						'code' => $validation_error->get_error_code(),
						'message' => $validation_error->get_error_message()
					);
				} else {
					$new_customer = wc_create_new_customer( sanitize_email( $email ), wc_clean( $username ));
					if ( is_wp_error( $new_customer ) ) {
						$error_array = array(
							'code' => $new_customer->get_error_code(),
							'message' => $new_customer->get_error_message()
						);
					} else {
						if ( apply_filters( 'woocommerce_registration_auth_new_customer', true, $new_customer ) ) {
							wc_set_customer_auth_cookie( $new_customer );
						}
						
						$args = array(
							'code'		=> 200,
							'message'	=> __( 'Account created successfully. redirecting...', 'woocommerce-ajax-login-register' ),
							'redirect'	=> apply_filters( "wcalr_register_redirect", false )
						);
						echo json_encode( $args );die();
					}
				}
			}
		}	
		else {
			$error_array = array(
				'code' => 'error',
				'message' => __('Please fill all required fields.','woocommerce-ajax-login-register')
			);
		}
		echo json_encode($error_array);
		die();
	}
	/**
	* Change the strength requirement on the woocommerce password
	*
	* Strength Settings
	* 4 = Strong
	* 3 = Medium (default) 
	* 2 = Also Weak but a little stronger 
	* 1 = Password should be at least Weak
	* 0 = Very Weak / Anything
	*/
	function zorem_change_password_strength($params, $handle){
		$zorem_password_strength = get_option('password_strength');
		$zorem_password_error = get_option('password_error_message');	
		$zorem_password_hint = 	get_option('password_hint');

		if(!$zorem_password_strength){
			$zorem_password_strength = 0;
		}
		if(!$zorem_password_error){
			$zorem_password_error = __( 'Do not you want to be protected? Make it stronger!', 'woocommerce-ajax-login-register' );
			$zorem_password_error = apply_filters( 'ajaxlogin_register_password_message', $zorem_password_error );
		}
		if($zorem_password_strength == '4'){
			$zorem_password_hint = __( 'Hint: The password should be at least 11 characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).', 'woocommerce-ajax-login-register' );
		} else if($zorem_password_strength == '3'){
			$zorem_password_hint = __( 'Hint: The password should be at least 9 characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).', 'woocommerce-ajax-login-register' );
		} else{
			$zorem_password_hint = __( 'Hint: The password should be at least 7 characters long, and should contain letters and numbers.', 'woocommerce-ajax-login-register' );
		}		 
		if( $handle === 'wc-password-strength-meter' ) {
			$params = array_merge( $params, array(
				'min_password_strength' => $zorem_password_strength,
				'i18n_password_error' => $zorem_password_error,
				'i18n_password_hint' => $zorem_password_hint
			) );
		}
		return $params;	
	}
	
	
	function simple_php_captcha($config = array()) {
	
		// Check for GD library
		if( !function_exists('gd_info') ) {
			throw new Exception('Required GD library is missing');
		}
	
		$bg_path = dirname(__FILE__) . '/assets/images/backgrounds/';
		$font_path = dirname(__FILE__) . '/assets/fonts/';
	
		// Default values
		$captcha_config = array(
			'code' => '',
			'min_length' => 5,
			'max_length' => 5,
			'backgrounds' => array(
				$bg_path . '45-degree-fabric.png',
				$bg_path . 'cloth-alike.png',
				$bg_path . 'grey-sandbag.png',
				$bg_path . 'kinda-jean.png',
				$bg_path . 'polyester-lite.png',
				$bg_path . 'stitched-wool.png',
				$bg_path . 'white-carbon.png',
				$bg_path . 'white-wave.png'
			),
			'fonts' => array(
				$font_path . 'times_new_yorker.ttf'
			),
			'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
			'min_font_size' => 20,
			'max_font_size' => 20,
			'color' => '#666',
			'angle_min' => 0,
			'angle_max' => 1,
			'shadow' => true,
			'shadow_color' => '#fff',
			'shadow_offset_x' => -1,
			'shadow_offset_y' => 1
		);
	
		// Overwrite defaults with custom config values
		if( is_array($config) ) {
			foreach( $config as $key => $value ) $captcha_config[$key] = $value;
		}
	
		// Restrict certain values
		if( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
		if( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
		if( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
		if( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
		if( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
		if( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];
	
		// Generate CAPTCHA code if not set by user
		if( empty($captcha_config['code']) ) {
			$captcha_config['code'] = '';
			$length = mt_rand($captcha_config['min_length'], $captcha_config['max_length']);
			while( strlen($captcha_config['code']) < $length ) {
				$captcha_config['code'] .= substr($captcha_config['characters'], mt_rand() % (strlen($captcha_config['characters'])), 1);
			}
		}
	
		// Generate HTML for image src
		if ( strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) ) {
			$image_src = substr(__FILE__, strlen( realpath($_SERVER['DOCUMENT_ROOT']) )) . '?_CAPTCHA&amp;t=' . urlencode(microtime());
			$image_src = '/' . ltrim(preg_replace('/\\\\/', '/', $image_src), '/');
		} else {
			$_SERVER['WEB_ROOT'] = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);
			$image_src = substr(__FILE__, strlen( realpath($_SERVER['WEB_ROOT']) )) . '?_CAPTCHA&amp;t=' . urlencode(microtime());
			$image_src = '/' . ltrim(preg_replace('/\\\\/', '/', $image_src), '/');
		}
	
		$_SESSION['_CAPTCHA']['config'] = serialize($captcha_config);
	
		return array(
			'code' => $captcha_config['code'],
			'config' => serialize($captcha_config)
		);
	
	}

    function hex2rgb($hex_str, $return_string = false, $separator = ',') {
        $hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $hex_str); // Gets a proper hex string
        $rgb_array = array();
        if( strlen($hex_str) == 6 ) {
            $color_val = hexdec($hex_str);
            $rgb_array['r'] = 0xFF & ($color_val >> 0x10);
            $rgb_array['g'] = 0xFF & ($color_val >> 0x8);
            $rgb_array['b'] = 0xFF & $color_val;
        } elseif( strlen($hex_str) == 3 ) {
            $rgb_array['r'] = hexdec(str_repeat(substr($hex_str, 0, 1), 2));
            $rgb_array['g'] = hexdec(str_repeat(substr($hex_str, 1, 1), 2));
            $rgb_array['b'] = hexdec(str_repeat(substr($hex_str, 2, 1), 2));
        } else {
            return false;
        }
        return $return_string ? implode($separator, $rgb_array) : $rgb_array;
	}
	
	function generate_captcha( $key ){			
		session_start();
		$_SESSION[ $key ] = $this->simple_php_captcha();

	
		$captcha_config = unserialize($_SESSION[$key]['config']);
		if( !$captcha_config ) exit();
	
		//$_SESSION['_CAPTCHA'] = $this->simple_php_captcha($key);
	
		// Pick random background, get info, and start captcha
		$background = $captcha_config['backgrounds'][mt_rand(0, count($captcha_config['backgrounds']) -1)];
		//echo '<pre>';print_r($captcha_config);echo '</pre>';exit;
		list($bg_width, $bg_height, $bg_type, $bg_attr) = getimagesize($background);
	
		$captcha = imagecreatefrompng($background);
	
		$color = $this->hex2rgb($captcha_config['color']);
		$color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);
	
		// Determine text angle
		$angle = mt_rand( $captcha_config['angle_min'], $captcha_config['angle_max'] ) * (mt_rand(0, 1) == 1 ? -1 : 1);
	
		// Select font randomly
		$font = $captcha_config['fonts'][mt_rand(0, count($captcha_config['fonts']) - 1)];
	
		// Verify font file exists
		if( !file_exists($font) ) throw new Exception('Font file not found: ' . $font);
	
		//Set the font size.
		$font_size = mt_rand($captcha_config['min_font_size'], $captcha_config['max_font_size']);
		$text_box_size = imagettfbbox($font_size, $angle, $font, $captcha_config['code']);
		
		// Determine text position
		$box_width = abs($text_box_size[6] - $text_box_size[2]);
		
		$box_height = abs($text_box_size[5] - $text_box_size[1]);
		$text_pos_x_min = 0;
		$text_pos_x_max = ($bg_width) - ($box_width);
		$text_pos_x = mt_rand($text_pos_x_min, $text_pos_x_max);
		$text_pos_y_min = $box_height;
		$text_pos_y_max = ($bg_height) - ($box_height / 2);
		if ($text_pos_y_min > $text_pos_y_max) {
			$temp_text_pos_y = $text_pos_y_min;
			$text_pos_y_min = $text_pos_y_max;
			$text_pos_y_max = $temp_text_pos_y;
		}
		$text_pos_y = mt_rand($text_pos_y_min, $text_pos_y_max);
	
		// Draw shadow
		if( $captcha_config['shadow'] ){
			$shadow_color = $this->hex2rgb($captcha_config['shadow_color']);
			$shadow_color = imagecolorallocate($captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b']);
			imagettftext($captcha, $font_size, $angle, $text_pos_x + $captcha_config['shadow_offset_x'], $text_pos_y + $captcha_config['shadow_offset_y'], $shadow_color, $font, $captcha_config['code']);
		}
	
		// Draw text
		imagettftext($captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $captcha_config['code']);
	
		// Output image
		header("Content-type: image/png");
		imagepng($captcha);
	}
	
	function get_wc_captcha(){						
		echo $this->generate_captcha( $_REQUEST['type'] . "_captcha" );		
		die();
	}
	
	function woocommerce_login_form_captcha_html(){
		$captcha_enabled = get_option( "captcha_enabled", "1" );
		if( $captcha_enabled != '1' )return;	
		
		
		?>
	        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide form-captcha-row">
				<span class="captcha_img">
					<img src="<?php echo admin_url()?>admin-ajax.php?action=get_wc_captcha&type=login" alt="">
				</span>
                <label for="login_captcha">					
                </label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" placeholder="<?php esc_html_e( 'Enter captcha code', 'woocommerce-ajax-login-register' ); ?>" type="text" name="login_captcha" id="login_captcha" />
			</p>
        <?php
		
	}
	
	function woocommerce_register_form_captcha_html(){
		$captcha_enabled = get_option( "captcha_enabled", "1" );
		if( $captcha_enabled != '1' )return;
		?>
	        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide form-captcha-row">
				<span class="captcha_img">
					<img src="<?php echo admin_url()?>admin-ajax.php?action=get_wc_captcha&type=register" alt="">
				</span>
				<label for="register_captcha">					
                </label>

				<input class="woocommerce-Input woocommerce-Input--text input-text" placeholder="<?php esc_html_e( 'Enter captcha code', 'woocommerce-ajax-login-register' ); ?>" type="text" name="register_captcha" id="register_captcha" />
			</p>
        <?php
	}
	
	function woocommerce_lostpassword_form_captcha_html(){
		$captcha_enabled = get_option( "captcha_enabled", "1" );
		if( $captcha_enabled != '1' )return;
		?>
	        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide form-captcha-row">
				<span class="captcha_img">
					<img src="<?php echo admin_url()?>admin-ajax.php?action=get_wc_captcha&type=lostpassword" alt="">
				</span>
				<label for="lostpassword_captcha">					                	
                </label>

				<input class="woocommerce-Input woocommerce-Input--text input-text" placeholder="<?php esc_html_e( 'Enter captcha code', 'woocommerce-ajax-login-register' ); ?>" type="text" name="lostpassword_captcha" id="lostpassword_captcha" />
			</p>
            <div class="clear"></div>
        <?php
	}
	
	function captchatest(){
		session_start();		
		echo '<pre>';print_r($_SESSION);echo '</pre>';
		exit;
	}
	
	public function create_session_if_not_exist(){
		if(!isset($_SESSION)) { session_start(); }// no errors produced, no page broken!
		if(!isset($_SESSION[ "login_captcha" ])){			
			$_SESSION[ "login_captcha" ] = $this->simple_php_captcha();			
		}
		if(!isset($_SESSION[ "register_captcha" ])){
			$_SESSION[ "register_captcha" ] = $this->simple_php_captcha();	
		}
		if(!isset($_SESSION[ "lostpassword_captcha" ])){
			$_SESSION[ "lostpassword_captcha" ] = $this->simple_php_captcha();	
		}
	}
}

/**
 * Returns an instance of Zorem_AJAX_Login_Register.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return Zorem_AJAX_Login_Register
*/
function Zorem_AJAX_Login_Register() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new Zorem_AJAX_Login_Register();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
$GLOBALS['Zorem_AJAX_Login_Register'] = Zorem_AJAX_Login_Register();
