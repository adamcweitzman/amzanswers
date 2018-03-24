<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;
	if( !current_user_can('administrator') ) exit;
?>

<form action="" method="POST" class="validate">
	<?php wp_nonce_field( 'wpforo-settings-api' ); ?>
	<table class="wpforo_settings_table">
		<tbody>
        	<?php do_action('wpforo_settings_api_top'); ?>
            <tr>
                <td colspan="2" style="border-bottom:3px solid #395598;">
                	<h3 style="font-weight:600; padding:0px 0px 0px 0px; margin:0px; text-align:right; color:#666666;">
                    	<div style="float:left; height:25px; line-height:25px;"><img src="<?php echo WPFORO_URL . '/wpf-assets/images/sn/fb-m.jpg' ?>" align="middle" /></div>
                    	Facebook API &nbsp;
                    </h3>
                </td>
            </tr>
            <tr>
				<th style="padding-top:15px;">
                	<label><?php _e('Facebook API Configuration', 'wpforo'); ?></label>
                	<p class="wpf-info"><?php _e('In order to get an App ID and Secret Key from Facebook, you’ll need to register a new application. Don’t worry – its very easy, and your application doesn\'t need to do anything. We only need the keys.', 'wpforo'); ?> <a href="https://wpforo.com/community/faq/how-to-get-facebook-app-id-and-secret-key/" target="_blank"><?php _e('Please follow to this instruction', 'wpforo'); ?> &raquo;</a></p>
                </th>
				<td style="padding-top:15px;">
					<input name="wpforo_api_options[fb_api_id]" placeholder="<?php _e('App ID', 'wpforo'); ?>" type="text" value="<?php echo trim(WPF()->api->options['fb_api_id']); ?>"/>&nbsp; <?php _e('App ID', 'wpforo'); ?><br />
                    <input name="wpforo_api_options[fb_api_secret]" placeholder="<?php _e('App Secret', 'wpforo'); ?>" type="text" value="<?php echo trim(WPF()->api->options['fb_api_secret']); ?>"/>&nbsp; <?php _e('App Secret', 'wpforo'); ?>
				</td>
			</tr>
            <tr>
                <th>
                	<label><?php _e('Facebook Login', 'wpforo'); ?></label>
                	<p class="wpf-info"><?php _e('Adds Facebook Login button on Registration and Login pages.') ?></p>
                </th>
                <td>
                    <div class="wpf-switch-field">
                        <input type="radio" value="1" name="wpforo_api_options[fb_login]" id="fb_login_1" <?php wpfo_check(WPF()->api->options['fb_login'], 1); ?>><label for="fb_login_1"><?php _e('Enable', 'wpforo'); ?></label> &nbsp;
                        <input type="radio" value="0" name="wpforo_api_options[fb_login]" id="fb_login_0" <?php wpfo_check(WPF()->api->options['fb_login'], 0); ?>><label for="fb_login_0"><?php _e('Disable', 'wpforo'); ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                	<label><?php _e('Facebook SDK for JavaScript', 'wpforo'); ?></label>
                	<p class="wpf-info"><?php _e('Facebook API connection script (sharing, login, cross-posting...)') ?></p>
                </th>
                <td>
                    <div class="wpf-switch-field">
                        <input type="radio" value="1" name="wpforo_api_options[fb_load_sdk]" id="fb_load_sdk_1" <?php wpfo_check(WPF()->api->options['fb_load_sdk'], 1); ?>><label for="fb_load_sdk_1"><?php _e('Enable', 'wpforo'); ?></label> &nbsp;
                        <input type="radio" value="0" name="wpforo_api_options[fb_load_sdk]" id="fb_load_sdk_0" <?php wpfo_check(WPF()->api->options['fb_load_sdk'], 0); ?>><label for="fb_load_sdk_0"><?php _e('Disable', 'wpforo'); ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                	<label><?php _e('Facebook Login button on User Login page', 'wpforo'); ?></label>
                </th>
                <td>
                    <div class="wpf-switch-field">
                        <input type="radio" value="1" name="wpforo_api_options[fb_lb_on_lp]" id="fb_lb_on_lp_1" <?php wpfo_check(WPF()->api->options['fb_lb_on_lp'], 1); ?>><label for="fb_lb_on_lp_1"><?php _e('Enable', 'wpforo'); ?></label> &nbsp;
                        <input type="radio" value="0" name="wpforo_api_options[fb_lb_on_lp]" id="fb_lb_on_lp_0" <?php wpfo_check(WPF()->api->options['fb_lb_on_lp'], 0); ?>><label for="fb_lb_on_lp_0"><?php _e('Disable', 'wpforo'); ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                	<label><?php _e('Facebook Login button on User Registration page', 'wpforo'); ?></label>
                </th>
                <td>
                    <div class="wpf-switch-field">
                        <input type="radio" value="1" name="wpforo_api_options[fb_lb_on_rp]" id="fb_lb_on_rp_1" <?php wpfo_check(WPF()->api->options['fb_lb_on_rp'], 1); ?>><label for="fb_lb_on_rp_1"><?php _e('Enable', 'wpforo'); ?></label> &nbsp;
                        <input type="radio" value="0" name="wpforo_api_options[fb_lb_on_rp]" id="fb_lb_on_rp_0" <?php wpfo_check(WPF()->api->options['fb_lb_on_rp'], 0); ?>><label for="fb_lb_on_rp_0"><?php _e('Disable', 'wpforo'); ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                	<label><?php _e('Redirect to this page after success login', 'wpforo'); ?></label>
                </th>
                <td>
                    <div class="wpf-switch-field">
                           <input type="radio" value="profile" name="wpforo_api_options[fb_redirect]" id="fb_redirect_2" <?php wpfo_check(WPF()->api->options['fb_redirect'], 'profile'); ?>><label for="fb_redirect_2">&nbsp;<?php _e('Profile', 'wpforo'); ?>&nbsp;</label>
                    	   <input type="radio" value="home" name="wpforo_api_options[fb_redirect]" id="fb_redirect_1" <?php wpfo_check(WPF()->api->options['fb_redirect'], 'home'); ?>><label for="fb_redirect_1">&nbsp;<?php _e('Forums', 'wpforo'); ?>&nbsp;</label> &nbsp;
                    	   <input type="radio" value="custom" name="wpforo_api_options[fb_redirect]" id="fb_redirect_3" <?php wpfo_check(WPF()->api->options['fb_redirect'], 'custom'); ?>><label for="fb_redirect_3">&nbsp;<?php _e('Custom', 'wpforo'); ?>&nbsp;</label> &nbsp;
                    </div>
                    <input style="margin-top:10px; padding:3px 5px; font-size:13px; width:48%;" name="wpforo_api_options[fb_redirect_url]" placeholder="<?php _e('Custom URL, e.g.: http://example.com/my-page/', 'wpforo'); ?>" type="text" value="<?php echo trim(WPF()->api->options['fb_redirect_url']); ?>"/>&nbsp; <?php _e('Custom URL', 'wpforo'); ?>
                </td>
            </tr>
            <?php do_action('wpforo_settings_api_bottom'); ?>
		</tbody>
	</table>
    <div class="wpforo_settings_foot">
        <input type="submit" class="button button-primary" value="<?php _e('Update Options', 'wpforo'); ?>" />
    </div>
</form>