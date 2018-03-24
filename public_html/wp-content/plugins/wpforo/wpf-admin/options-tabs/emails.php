<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;
	if( !current_user_can('administrator') ) exit;
?>

<form action="" method="POST" class="validate">
    <?php wp_nonce_field( 'wpforo-settings-emails' ); ?>
    <table class="wpforo_settings_table">
        <tbody>
            <tr>
                <th style="width:40%"><label for="from_name"><?php _e('FROM Name', 'wpforo'); ?>:</label></th>
                <td><input id="from_name" name="wpforo_subscribe_options[from_name]" type="text" value="<?php wpfo(WPF()->sbscrb->options['from_name']); ?>" required></td>
            </tr>
            <tr>
                <th><label for="from_email"><?php _e('FROM Email Address', 'wpforo'); ?>:</label></th>
                <td><input id="from_email" name="wpforo_subscribe_options[from_email]" type="text" value="<?php wpfo(WPF()->sbscrb->options['from_email']); ?>" required /></td>
            </tr>
            <tr>
                <th>
                    <label for="admin_emails"><?php _e('Forum Admins email addresses', 'wpforo'); ?>:</label>
                    <p class="wpf-info"><?php _e('Comma separated email addresses of forum administrators to get forum notifications. For example post report messages.', 'wpforo') ?></p>
                </th>
                <td><input id="admin_emails" name="wpforo_subscribe_options[admin_emails]" type="text" value="<?php wpfo(WPF()->sbscrb->options['admin_emails']); ?>" required /></td>
            </tr>
            <tr>
                <th>
                    <label><?php _e('Notify Admins via email on new Topic', 'wpforo'); ?>:</label>
                    <p class="wpf-info"><?php _e('Send Notification emails to all email addresses (comma separated ) of forum administrators when a new Topic is created.', 'wpforo') ?></p>
                </th>
                <td>
                    <div class="wpf-switch-field">
                        <input type="radio" value="1" name="wpforo_subscribe_options[new_topic_notify]" id="wpf_new_topic_notify_1" <?php wpfo_check(WPF()->sbscrb->options['new_topic_notify'], 1); ?>><label for="wpf_new_topic_notify_1"><?php _e('Yes', 'wpforo'); ?></label> &nbsp;
                        <input type="radio" value="0" name="wpforo_subscribe_options[new_topic_notify]" id="wpf_new_topic_notify_0" <?php wpfo_check(WPF()->sbscrb->options['new_topic_notify'], 0); ?>><label for="wpf_new_topic_notify_0"><?php _e('No', 'wpforo'); ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e('Notify Admins via email on new Post', 'wpforo'); ?>:</label>
                    <p class="wpf-info"><?php _e('Send Notification emails to all email addresses (comma separated ) of forum administrators when a new Reply is created.', 'wpforo') ?></p>
                </th>
                <td>
                    <div class="wpf-switch-field">
                        <input type="radio" value="1" name="wpforo_subscribe_options[new_reply_notify]" id="wpf_new_reply_notify_1" <?php wpfo_check(WPF()->sbscrb->options['new_reply_notify'], 1); ?>><label for="wpf_new_reply_notify_1"><?php _e('Yes', 'wpforo'); ?></label> &nbsp;
                        <input type="radio" value="0" name="wpforo_subscribe_options[new_reply_notify]" id="wpf_new_reply_notify_0" <?php wpfo_check(WPF()->sbscrb->options['new_reply_notify'], 0); ?>><label for="wpf_new_reply_notify_0"><?php _e('No', 'wpforo'); ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom:2px solid #ddd;">
                <h3 style="font-weight:400; padding:10px 0 0 0; margin:0;"><?php _e('Subscription Emails', 'wpforo'); ?></h3>
                </td>
            </tr>
            <tr>
                <th><label for="confirmation_email_subject"><?php _e('Subscribe confirmation email subject', 'wpforo'); ?>:</label></th>
                <td><input id="confirmation_email_subject" name="wpforo_subscribe_options[confirmation_email_subject]" type="text"  value="<?php wpfo(WPF()->sbscrb->options['confirmation_email_subject']); ?>" required></td>
            </tr>
            <tr>
                <th><label for="confirmation_email_message"><?php _e('Subscribe confirmation email message', 'wpforo'); ?>:</label></th>
                <td><textarea id="confirmation_email_message" style="height:190px;" name="wpforo_subscribe_options[confirmation_email_message]" required><?php wpfo(WPF()->sbscrb->options['confirmation_email_message'], true, 'esc_textarea'); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="new_topic_notification_email_subject"><?php _e('New topic notification email subject', 'wpforo'); ?>:</label></th>
                <td><input id="new_topic_notification_email_subject" name="wpforo_subscribe_options[new_topic_notification_email_subject]" type="text"  value="<?php wpfo(WPF()->sbscrb->options['new_topic_notification_email_subject']); ?>" required></td>
            </tr>
            <tr>
                <th><label for="new_topic_notification_email_message"><?php _e('New topic notification email message', 'wpforo'); ?>:</label></th>
                <td><textarea id="new_topic_notification_email_message" style="height:190px;" name="wpforo_subscribe_options[new_topic_notification_email_message]" required><?php wpfo(WPF()->sbscrb->options['new_topic_notification_email_message'], true, 'esc_textarea'); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="new_post_notification_email_subject"><?php _e('New reply notification email subject', 'wpforo'); ?>:</label></th>
                <td><input id="new_post_notification_email_subject" name="wpforo_subscribe_options[new_post_notification_email_subject]" type="text"  value="<?php wpfo(WPF()->sbscrb->options['new_post_notification_email_subject']); ?>" required></td>
            </tr>
            <tr>
                <th><label for="new_post_notification_email_message"><?php _e('New reply notification email message', 'wpforo'); ?>:</label></th>
                <td><textarea id="new_post_notification_email_message" style="height:190px;" name="wpforo_subscribe_options[new_post_notification_email_message]" required><?php wpfo(WPF()->sbscrb->options['new_post_notification_email_message'], true, 'esc_textarea'); ?></textarea></td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom:2px solid #ddd;">
                <h3 style="font-weight:400; padding:10px 0 0 0; margin:0;"><?php _e('Post Reporting Emails', 'wpforo'); ?></h3>
                <p class="wpf-info"><?php _e('This message comes from post reporting pop-up form.', 'wpforo') ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="report_email_subject"><?php _e('Report message subject', 'wpforo'); ?>:</label>
                </th>
                <td><input id="report_email_subject" name="wpforo_subscribe_options[report_email_subject]" type="text"  value="<?php wpfo(WPF()->sbscrb->options['report_email_subject']); ?>" required></td>
            </tr>
            <tr>
                <th><label for="report_email_message"><?php _e('Report message body', 'wpforo'); ?>:</label></th>
                <td><textarea id="report_email_message" style="height:190px;" name="wpforo_subscribe_options[report_email_message]" required><?php wpfo(WPF()->sbscrb->options['report_email_message'], true, 'esc_textarea'); ?></textarea></td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom:2px solid #ddd;">
                    <h3 style="font-weight:400; padding:10px 0 0 0; margin:0;"><?php _e('Reset Password Emails', 'wpforo'); ?></h3>
                    <p class="wpf-info"><?php _e('This message comes from Reset Password form.', 'wpforo') ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="reset_password_email_message"><?php _e('Reset Password message body', 'wpforo'); ?>:</label></th>
                <td><textarea id="reset_password_email_message" style="height:190px;" name="wpforo_subscribe_options[reset_password_email_message]" required><?php wpfo(WPF()->sbscrb->options['reset_password_email_message'], true, 'esc_textarea'); ?></textarea></td>
            </tr>
            <tr>
                <td colspan="2" style="border-bottom:2px solid #ddd;">
                    <h3 style="font-weight:400; padding:10px 0 0 0; margin:0;"><?php _e('User Mentioning Email', 'wpforo'); ?></h3>
                </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e('Enable Email Notification', 'wpforo'); ?>:</label>
                </th>
                <td>
                    <div class="wpf-switch-field">
                        <input type="radio" value="1" name="wpforo_subscribe_options[user_mention_notify]" id="user_mention_notify_1" <?php wpfo_check(WPF()->sbscrb->options['user_mention_notify'], 1); ?>><label for="user_mention_notify_1"><?php _e('Yes', 'wpforo'); ?></label> &nbsp;
                        <input type="radio" value="0" name="wpforo_subscribe_options[user_mention_notify]" id="user_mention_notify_0" <?php wpfo_check(WPF()->sbscrb->options['user_mention_notify'], 0); ?>><label for="user_mention_notify_0"><?php _e('No', 'wpforo'); ?></label>
                    </div>
                </td>
            </tr>
            <tr>
                <th><label for="user_mention_email_subject"><?php _e('User Mention message subject', 'wpforo'); ?>:</label></th>
                <td><input id="user_mention_email_subject" name="wpforo_subscribe_options[user_mention_email_subject]" type="text"  value="<?php wpfo(WPF()->sbscrb->options['user_mention_email_subject']); ?>" required></td>
            </tr>
            <tr>
                <th><label for="user_mention_email_message"><?php _e('User Mention message body', 'wpforo'); ?>:</label></th>
                <td><textarea id="user_mention_email_message" style="height:190px;" name="wpforo_subscribe_options[user_mention_email_message]" required><?php wpfo(WPF()->sbscrb->options['user_mention_email_message'], true, 'esc_textarea'); ?></textarea></td>
            </tr>
        </tbody>
    </table>
    <div class="wpforo_settings_foot">
        <input type="submit" class="button button-primary" value="<?php _e('Update Options', 'wpforo'); ?>" />
    </div>
</form>
