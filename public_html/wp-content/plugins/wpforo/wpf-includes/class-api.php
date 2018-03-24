<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;

class wpForoAPI{
	private $default;
	public $options;
	public $locale = 'en_US';
	public $locales = array( 'af_ZA', 'ar_AR', 'az_AZ', 'be_BY', 'bg_BG', 'bn_IN', 'bs_BA', 'ca_ES', 'cs_CZ', 'cy_GB', 'da_DK', 'de_DE', 'el_GR', 'en_US', 
							 'en_GB', 'eo_EO', 'es_ES', 'es_LA', 'et_EE', 'eu_ES', 'fa_IR', 'fb_LT', 'fi_FI', 'fo_FO', 'fr_FR', 'fr_CA', 'fy_NL', 'ga_IE', 
							 'gl_ES', 'he_IL', 'hi_IN', 'hr_HR', 'hu_HU', 'hy_AM', 'id_ID', 'is_IS', 'it_IT', 'ja_JP', 'ka_GE', 'km_KH', 'ko_KR', 'ku_TR', 
							 'la_VA', 'lt_LT', 'lv_LV', 'mk_MK', 'ml_IN', 'ms_MY', 'nb_NO', 'ne_NP', 'nl_NL', 'nn_NO', 'pa_IN', 'pl_PL', 'ps_AF', 'pt_PT', 
							 'pt_BR', 'ro_RO', 'ru_RU', 'sk_SK', 'sl_SI', 'sq_AL', 'sr_RS', 'sv_SE', 'sw_KE', 'ta_IN', 'te_IN', 'th_TH', 'tl_PH', 'tr_TR', 
							 'uk_UA', 'vi_VN', 'zh_CN', 'zh_HK', 'zh_TW' );

	
	public function __construct(){
		$this->init_defaults();
		$this->init_options();
	}
	
	private function init_defaults(){
	    $this->default = new stdClass;
        $this->default->options = array(
            'fb_api_id' => '',
            'fb_api_secret' => '',
            'fb_login' => 0,
			'fb_load_sdk' => 1,
            'fb_lb_on_lp' => 1,
            'fb_lb_on_rp' => 1,
			'fb_redirect' => 'profile',
			'fb_redirect_url' => ''
        );
    }
	
	private function init_options(){
		$this->options = get_wpf_option('wpforo_api_options', $this->default->options);
    }
	
	public function hooks(){
		
		$template = WPF()->current_object['template'];
		
		###############################################################################
		############### Facebook API ##################################################
		###############################################################################
		
		if(!is_user_logged_in()){
			if( $this->options['fb_login'] ){
				if( $template == 'login' || $template == 'register' ){
					add_action('wp_enqueue_scripts', array($this, 'fb_enqueue'));
					if( $this->options['fb_load_sdk'] ){
						add_action('wpforo_bottom_hook', array($this, 'fb_login_scripts'));
					}
				}
				if( $this->options['fb_lb_on_lp'] ){
					add_action('wpforo_login_form_end', array($this, 'fb_login_button'));
				}
				if( $this->options['fb_lb_on_rp'] ){
					add_action('wpforo_register_form_end', array($this, 'fb_login_button'));
				}
				add_action('wp_ajax_wpforo_facebook_auth', array($this, 'fb_auth'));
				add_action('wp_ajax_nopriv_wpforo_facebook_auth', array($this, 'fb_auth'));
			}
		}
		
		###############################################################################
		############### reCAPTCHA API #################################################
		###############################################################################
		
		$site_key = WPF()->tools_antispam['rc_site_key'];
		$secret_key = WPF()->tools_antispam['rc_secret_key'];
		
		if( !is_user_logged_in() && $site_key && $secret_key ){
			
			$rc_reg_form = WPF()->tools_antispam['rc_reg_form'];
			$rc_login_form = WPF()->tools_antispam['rc_login_form'];
			$rc_lostpass_form = WPF()->tools_antispam['rc_lostpass_form'];
			$rc_wpf_reg_form = WPF()->tools_antispam['rc_wpf_reg_form'];
			$rc_wpf_login_form = WPF()->tools_antispam['rc_wpf_login_form'];
			$rc_wpf_lostpass_form = WPF()->tools_antispam['rc_wpf_lostpass_form'];
			$rc_post_editor = WPF()->tools_antispam['rc_post_editor'];
			$rc_topic_editor = WPF()->tools_antispam['rc_topic_editor'];

			add_filter('script_loader_tag', array(&$this,'rc_enqueue_async'), 10, 3);
			
			//Verification Hooks: Login / Register / Reset Pass 
			if( $rc_login_form || $rc_wpf_login_form ) add_filter('wp_authenticate_user', array($this, 'rc_verify_wp_login'), 15, 2);
			if( $rc_reg_form || $rc_wpf_reg_form ) add_filter('registration_errors', array($this, 'rc_verify_wp_register'), 10, 3);
			if( $rc_lostpass_form || $rc_wpf_lostpass_form ) add_action('lostpassword_post', array($this, 'rc_verify_wp_lostpassword'), 10);
			
			//Load reCAPTCHA API and Widget on wp-login.php
			if( $rc_reg_form || $rc_login_form || $rc_lostpass_form ){
				add_action('login_enqueue_scripts', array($this, 'rc_enqueue'));
				add_action('login_enqueue_scripts', array($this, 'rc_enqueue_css'));
				if( $rc_login_form && $template != 'login' ) add_action('login_form', array($this, 'rc_widget'));
				if( $rc_reg_form && $template != 'register') add_action('register_form', array($this, 'rc_widget'));
				if( $rc_lostpass_form && $template != 'lostpassword' )add_action('lostpassword_form', array( $this, 'rc_widget'));
			}
			
			//Load reCAPTCHA API on wpForo pages: Login / Register / Reset Pass 
			if( $template == 'login' || $template == 'register' || $template == 'lostpassword'){
				if( $rc_wpf_reg_form || $rc_wpf_login_form || $rc_wpf_lostpass_form ){
					add_action('wp_enqueue_scripts', array($this, 'rc_enqueue'));
				}
			} 
			
			//Load reCAPTCHA Widget wpForo forms: Login / Register / Reset Pass 
			if( $rc_wpf_login_form && $template == 'login' ) add_action('login_form', array($this, 'rc_widget'));
			if( $rc_wpf_reg_form && $template == 'register') add_action('register_form', array($this, 'rc_widget'));
			if( $rc_wpf_lostpass_form && $template == 'lostpassword' ) add_action('lostpassword_form', array( $this, 'rc_widget'));
			
			//Load reCAPTCHA API and Widget for Topic and Post Editor
			if( $template == 'forum' || $template == 'topic' || $template == 'post' ){
				add_action('wp_enqueue_scripts', array($this, 'rc_enqueue'));
				add_action('wpforo_verify_form_end', array($this, 'rc_verify'));
				add_action('wpforo_topic_form_extra_fields_after', array($this, 'rc_widget'));
				add_action('wpforo_reply_form_extra_fields_after', array($this, 'rc_widget'));
			}
		}
		
		###############################################################################
	}
	
	public function fb_local(){
		$wplocal = get_locale();
		if( in_array($wplocal, $this->locales) ){
			return $wplocal;
		}
		else{
			return $this->locale;
		}
	}
	
	public function fb_enqueue() {
		$app_id = $this->options['fb_api_id'];
		wp_register_script('wpforo-snfb', WPFORO_URL . '/wpf-assets/js/snfb.js', array('jquery'), WPFORO_VERSION, false );
		wp_enqueue_script('wpforo-snfb');
		wp_localize_script('wpforo-snfb', 'wpforo_fb', 
			array( 	'ajaxurl'  => admin_url('admin-ajax.php'), 'site_url' => home_url(), 'scopes' => 'email,public_profile', 'appId' => $app_id, 'l18n' => array( 'chrome_ios_alert' => __( 'Please login into facebook and then click connect button again', 'wpforo' )))
		);
	}
	
	public function fb_auth(){
		
		$app_version = '2.10';
		$app_secret = $this->options['fb_api_secret'];
		check_ajax_referer( 'wpforo-fb-nonce', 'security' );
		$fb_token = isset( $_POST['fb_response']['authResponse']['accessToken'] ) ? $_POST['fb_response']['authResponse']['accessToken'] : '';
		$fb_url = add_query_arg( array( 'fields'  =>  'id,first_name,last_name,email,link,about,locale,birthday', 'access_token' =>  $fb_token ), 'https://graph.facebook.com/v' . $app_version . '/' . $_POST['fb_response']['authResponse']['userID'] );
		
		###################################################################################################################
		// Verifying Graph API Calls with appsecret_proof
		// Graph API calls can be made from clients or from your server on behalf of clients. 
		// Calls from a server can be better secured by adding a parameter called appsecret_proof.
		// https://developers.facebook.com/docs/graph-api/securing-requests/
		if( !$app_secret ) {
			$appsecret_proof = hash_hmac('sha256', $fb_token, trim($app_secret) );
			$fb_url = add_query_arg( array( 'appsecret_proof' => $appsecret_proof ), $fb_url );
		}
		###################################################################################################################
		
		$fb_response = wp_remote_get( esc_url_raw( $fb_url ), array( 'timeout' => 30 ) );
		if( is_wp_error( $fb_response ) ) wpforo_ajax_response( array( 'error' => $fb_response->get_error_message() ) );
		$fb_user = json_decode( wp_remote_retrieve_body( $fb_response ), true );
		if( isset( $fb_user['error'] ) ) wpforo_ajax_response( array( 'error' => 'Error code: '. $fb_user['error']['code'] . ' - ' . $fb_user['error']['message'] ) );
		if( empty( $fb_user['email'] ) ) wpforo_ajax_response( array( 'error' => __('Your email is required to be able authorize you here. Please try loging again. ', 'wpforo' ), 'fb' => $fb_user ) );
		$fb_user['link'] = ( isset($fb_user['link']) ) ? $fb_user['link'] : '';
		$fb_user['about'] = ( isset($fb_user['about']) ) ? $fb_user['about'] : '';
		$fb_user['locale'] = ( isset($fb_user['locale']) ) ? $fb_user['locale'] : '';
		$user = array( 'fb_user_id' => $fb_user['id'], 'first_name' => $fb_user['first_name'], 'last_name' => $fb_user['last_name'], 'user_email' => $fb_user['email'], 'user_url' => $fb_user['link'], 'user_pass' => wp_generate_password(), 'description' => $fb_user['about'], 'locale' => $fb_user['locale'], 'rich_editing' => 'true' );
		$message = array( 'error' => __( 'Invalid User', 'wpforo' ) );
		if ( empty( $user['fb_user_id'] ) ) wpforo_ajax_response( $message );
		$member = wpforo_get_fb_user( $user );
		$meta_updated = false;
		
		if ( $member ){
			$user_id = $member->ID;
			$message = array( 'success' => $user_id, 'method' => 'login');
			if( empty( $member->user_email ) ) wp_update_user( array( 'ID' => $user_id, 'user_email' => $user['user_email'] ) );
		} else {
			if( !wpforo_feature('user-register') ) wpforo_ajax_response( array( 'error' => __( 'User registration is disabled', 'wpforo' ) ) );
			$username = wpforo_unique_username( $user['user_email'] );
			$user['user_login'] = str_replace('.', '', $username);
			$user['user_nicename'] = sanitize_title($username);
			$user['display_name'] = ( $user['first_name'] || $user['last_name'] ) ? trim($user['first_name'] . ' ' .  $user['last_name']) : ucfirst(str_replace('-', ' ', $user['user_nicename']));
			$user_id = wp_insert_user( $user );
			if( !is_wp_error( $user_id ) ) {
				wp_new_user_notification( $user_id, NULL, 'admin' );
				wp_new_user_notification( $user_id, '', 'user' );
				update_user_meta( $user_id, '_fb_user_id', $user['fb_user_id'] );
				if( isset($fb_user['birthday']) && $fb_user['birthday'] ){
					update_user_meta( $user_id, '_fb_user_birthday', $fb_user['birthday'] );
				}
				$meta_updated = true;
				$message = array( 'success' => $user_id, 'method' => 'registration' );
			}
		}
		if( is_numeric( $user_id ) ) {
			wp_set_auth_cookie( $user_id, true );
			if( !$meta_updated ) update_user_meta( $user_id, '_fb_user_id', $user['fb_user_id'] );
		}
		wpforo_ajax_response( $message );
	}
	
	public function fb_redirect(){
		if( $this->options['fb_redirect'] == 'custom' && $this->options['fb_redirect_url'] != '' ){
			return esc_url($this->options['fb_redirect_url']);
		}
		elseif( $this->options['fb_redirect'] == 'profile' ){
			$current_user_id = WPF()->current_userid;
			return wpforo_home_url("account/");
		}
		else{
			return wpforo_home_url();
		}
	}
	
	public function fb_login_scripts(){
		?>
        <!-- wpForo FB Login start -->
        	<script type='text/javascript' > function statusChangeCallback(response){ if (response.status === 'connected') {} else if (response.status === 'not_authorized') {} else {}} function checkLoginState() { FB.getLoginStatus(function(response) { statusChangeCallback(response); }); } window.fbAsyncInit = function(){ FB.init({ appId	: '<?php echo trim($this->options['fb_api_id']) ?>', cookie	: true, xfbml	: true, status	: false, version : 'v2.10' }); FB.getLoginStatus(function(response) {statusChangeCallback(response);}); }; (function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/<?php echo $this->fb_local() ?>/sdk.js"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk')); </script>
		<!-- wpForo FB Login end -->
		<?php
	}
	
	public function fb_login_button(){
		?>
        <div class="wpforo_fb-button wpforo-fb-login-wrap" data-redirect="<?php echo $this->fb_redirect() ?>" data-fb_nonce="<?php echo wp_create_nonce( 'wpforo-fb-nonce' ) ?>">
			<div class="fb-login-button" data-max-rows="1" onlogin="wpforo_fb_check_auth" data-size="medium" data-button-type="login_with" data-show-faces="false" data-auth-type="rerequest" data-auto-logout-link="false" data-use-continue-as="true" data-scope="email,public_profile"></div> 
            <img data-no-lazy="1" src="<?php echo WPFORO_URL . '/wpf-assets/images/loading.gif'; ?>" class="wpforo_fb-spinner" style="display:none"/> 
		</div>
        <?php
	}
	
	public function rc_enqueue() {
		$theme = WPF()->tools_antispam['rc_theme'];
		$site_key = WPF()->tools_antispam['rc_site_key'];
		wp_register_script( 'wpforo_recaptcha', 'https://www.google.com/recaptcha/api.js?onload=wpForoReCallback&render=explicit' );
		wp_enqueue_script( 'wpforo_recaptcha' );
		wp_localize_script('wpforo_recaptcha', 'wpForoRC', 
			array( 	'wpforo_rc_site_key' => $site_key, 'wpforo_rc_theme' => $theme )
		);
	}
	
	public function rc_enqueue_async( $tag, $handle, $src ) {
		if ( $handle == 'wpforo_recaptcha' ) return str_replace( '<script', '<script async defer', $tag );
		return $tag;
	} 
	
	public function rc_enqueue_css() {
		wp_register_style( 'wpforo-rc-style', false );
		wp_enqueue_style( 'wpforo-rc-style' );
        $custom_css = "#wpforo_recaptcha_widget{ -webkit-transform:scale(0.9); transform:scale(0.9); -webkit-transform-origin:left 0; transform-origin:left 0; }";
        wp_add_inline_style( 'wpforo-rc-style', $custom_css );
	}
	
	public function rc_widget() {
		$site_key = WPF()->tools_antispam['rc_site_key'];
		if( $site_key ){
			echo '<div id="wpforo_recaptcha_widget"></div><div class="wpf-cl"></div>';
			echo "\r\n<script>var wpForoReCallback = function () { grecaptcha.render('wpforo_recaptcha_widget', { 'sitekey': wpForoRC.wpforo_rc_site_key, 'theme': wpForoRC.wpforo_rc_theme, }); };</script>";
		}
	}
	
	public function rc_check() {
        if ( isset( $_POST['g-recaptcha-response'] ) ) {
            $secret_key = WPF()->tools_antispam['rc_secret_key'];
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $_POST['g-recaptcha-response'];
			$response = wp_remote_get( $url );
			if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
                $error = wpforo_phrase("ERROR: Can't connect to Google reCAPTCHA API", false);
                if( WP_DEBUG === true ) $error .=  ' ( '. $response->get_error_message() .' )';
			    return $error;
		    }
			$response = json_decode( $response['body'], true );
			if ( $response['success'] == true ) {
				return 'success';
			} else {
				return wpforo_phrase('Google reCAPTCHA verification failed', false);
			}
		}
		else{
			return wpforo_phrase('Google reCAPTCHA data are not submitted', false);
		}
	}
	
	public function rc_verify() {
		$result = $this->rc_check();
		if ( $result == 'success' ) {
			return true;
		} else {
			WPF()->notice->add( $result , 'error');
			wp_redirect( wpforo_get_request_uri() );
			exit();
		}
	}
	
	public function rc_verify_wp_login( $user ) {
		if ( !isset($_POST['log']) && !isset($_POST['pwd'])) return $user;
		$errors = is_wp_error($user) ? $user : new WP_Error();
		$result = $this->rc_check();
        if( $result != 'success' ) {
            $errors->add('wpforo-recaptcha-error', $result);
			$user = is_wp_error($user) ? $user : $errors;
			remove_filter('authenticate', 'wp_authenticate_username_password', 10);
			remove_filter('authenticate', 'wp_authenticate_cookie', 10);
		}
		return $user;
	} 
	
	public function rc_verify_wp_register( $errors = '' ){
		if ( !is_wp_error($errors) ) $errors = new WP_Error();
		$result = $this->rc_check();
	    if( $result != 'success' ) {
		   $errors->add('wpforo-recaptcha-error', $result);
	    }
	    return $errors;
	}
	
	public function rc_verify_wp_lostpassword( $errors = '' ){
		if ( !is_wp_error($errors) ) $errors = new WP_Error();
		$result = $this->rc_check();
		if( $result != 'success' ) {
			if ( isset($_POST['wc_reset_password']) && isset($_POST['_wp_http_referer']) ) {
				 //$errors->add('wpforo-recaptcha-error', $result);
				 //return $errors;
				return;
			 } else {
				 wp_die( $result, 'reCAPTCHA ERROR', array( 'back_link' => true ) ); 
			 }
		 }
		 return;
	}
	
	public function rc_exists(){
		
	}
	
}