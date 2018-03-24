<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;
?>

<div class="wpfl-2">

    <div class="wpforo-post-head"> 
        <div class="wpf-left">&nbsp;<a href="<?php echo esc_url( wpforo_post($topic['last_post'], 'url') ); ?>" class="wpfcl-2"><i class="fa fa-caret-square-o-down wpfsx wpfcl-3"></i> &nbsp; <span class="wpfcl-3"><?php wpforo_phrase('Last Post'); ?></span></a></div>
        <div class="wpf-right">&nbsp;<?php if( wpforo_feature('rss-feed') ): ?><a href="<?php WPF()->feed->rss2_url(); ?>" class="wpfcl-2" title="<?php wpforo_phrase('Topic RSS Feed') ?>"><span class="wpfcl-3"><?php wpforo_phrase('RSS') ?></span> <i class="fa fa-rss wpfsx wpfcl-3"></i></a><?php endif; ?></div>
        <div class="wpf-clear"></div>
    </div>
  
	<?php foreach($posts as $key => $post) : ?>
		
		<?php $member = wpforo_member($post); ?>
		<div id="post-<?php echo intval($post['postid']) ?>" class="post-wrap">
              <div class="wpforo-post wpfcl-1">
                <div class="wpf-left">
                	<?php if( WPF()->perm->usergroup_can('va') && wpforo_feature('avatars') ): ?>
                		<div class="author-avatar"><?php echo WPF()->member->avatar($member, 'alt="'.esc_attr($member['display_name']).'"', 110) ?></div>
                    <?php endif; ?>
                    <div class="author-data">
                        <div class="author-name"><span><?php WPF()->member->show_online_indicator($member['userid']) ?></span>&nbsp;<?php wpforo_member_link($member); ?></div>
                        <?php wpforo_member_nicename($member, '@'); ?>
                        <div class="wpf-member-profile-buttons">
                            <?php WPF()->tpl->member_buttons($member) ?>
                        </div>
                        <div class="author-title">
                            <?php wpforo_member_title($member) ?>
                        </div>
                        <?php wpforo_member_badge($member) ?>
                	</div>
                    <div class="wpf-clear"></div>
                </div><!-- left -->
                <div class="wpf-right">
                	<div class="wpforo-post-content-top">
                    	<div class="wpf-post-actions">
							<?php if( $post['is_first_post'] ){
                                $buttons = array( 'solved', 'sticky', 'private', 'close', 'report', 'move', 'delete' );
                                WPF()->tpl->buttons( $buttons, $forum, $topic, $post, TRUE );
                            }else{
                                $buttons = array( 'report', 'delete' );
                                WPF()->tpl->buttons( $buttons, $forum, $topic, $post );
                            } ?>
                        </div>
                        <a href="<?php echo esc_url( wpforo_post($post['postid'], 'url') ); ?>"><i class="fa fa-link wpfsx"></i></a>
                    </div>
                    <div class="wpforo-post-content">
                        <?php echo wpforo_content_filter( wpforo_kses($post['body'], 'post') ) ?>
                        <?php wpforo_post_edited($post); ?>
                        <?php do_action( 'wpforo_tpl_post_loop_after_content', $post, $member ) ?>
                        <?php if( wpforo_feature('signature') ): ?>
                        	<?php if($member['signature']): ?><div class="wpforo-post-signature"><?php wpforo_signature( $member ) ?></div><?php endif; ?>
                        <?php endif; ?>
                        <div class="wpf-post-button-actions">
                        <?php if( $post['is_first_post'] ){
							$buttons = array( 'reply', 'quote', 'approved', 'edit',	'like' );
							WPF()->tpl->buttons( $buttons, $forum, $topic, $post, TRUE );
						}else{
							$buttons = array( 'reply', 'quote', 'approved', 'edit', 'like' );
							WPF()->tpl->buttons( $buttons, $forum, $topic, $post );
						} ?>
                        <?php if($post['status']): ?><span class="wpf-mod-message"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <?php wpforo_phrase('Awaiting moderation') ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="wpforo-post-content-bottom">
                    	<div class="cbleft wpfcl-0"><?php wpforo_phrase('Posted') ?> : <?php wpforo_date($post['created'], 'd/m/Y g:i a') ?> 
							<span class="bleft"><?php echo WPF()->tpl->likers($post['postid']); ?></span>
						</div>
                    	<div class="wpf-clear"></div>
                    </div>
                </div><!-- right -->
                <div class="wpf-clear"></div>
              </div><!-- wpforo-post -->
          </div><!-- post-wrap -->
       
        <?php do_action( 'wpforo_loop_hook', $key ) ?>
       
    <?php endforeach; ?>
</div><!-- wpfl-2 -->