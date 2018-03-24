<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;
	if( !current_user_can('administrator') ) exit;
?>

	<form action="" method="POST" class="validate">
        	<?php wp_nonce_field( 'wpforo-tools-misc' ); ?>
			<div class="wpf-tool-box" style="width:60%;">
            	<h3><?php _e('SEO Tools', 'wpforo'); ?>
                <p class="wpf-info"></p>
                </h3>
                <div style="margin-top:10px; clear:both;">
                <table style="width:100%;">
                    <tbody style="padding:10px;">
                        <tr>
                            <th colspan="2">
                            	<label style="padding-bottom:5px; display:block;"><?php _e('Allowed dofollow domains', 'wpforo'); ?>:</label>
                            	<p class="wpf-info"><?php _e('wpForo adds nofollow to all links with external URLs. If you want to keep some domains as internal please insert domains one per line in the textarea bellow.', 'wpforo'); ?></p>    
                            	<br>
                                <textarea name="wpforo_tools_misc[dofollow]" style="font-size: 13px; display:block; width:100%; height:120px;" placeholder="example.com" /><?php wpfo(WPF()->tools_misc['dofollow']) ?></textarea></td>
                        	</th>
                        </tr>
                        <tr>
                            <th colspan="2">
                            	<label style="padding-bottom:5px; display:block;"><?php _e('Noindex forum page URLs', 'wpforo'); ?>:</label>
                            	<p class="wpf-info"><?php _e('The noIndex code tells Google and other search engines to NOT index the page, so that it cannot be found in search results. Please insert page URLs you do not want to be indexed one per line in the textarea bellow.', 'wpforo'); ?></p>    
                            	<br>
                                <textarea name="wpforo_tools_misc[noindex]" style="font-size: 13px; display:block; width:100%; height:120px;" placeholder="https://myforum.com/community/main-forum/my-topic/" /><?php wpfo(WPF()->tools_misc['noindex']) ?></textarea></td>
                       		</th>
                        </tr>
                        </tbody>
                </table>
                </div>
            </div>
            <div class="wpforo_settings_foot" style="clear:both; margin-top:20px;">
                <input type="submit" class="button button-primary" value="<?php _e('Update Options', 'wpforo'); ?>" />
            </div>
		</form>