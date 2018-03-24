<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;
?>

<div class="wpfl-1">
	<div class="wpforo-post-head"> 
	      <div class="wpf-left">&nbsp; <a href="<?php echo esc_url( wpforo_post($topic['last_post'],'url') ); ?>" class="wpfcl-2"><i class="fa fa-caret-square-o-down wpfsx wpfcl-3"></i> &nbsp; <span class="wpfcl-3"><?php wpforo_phrase('Last Post'); ?></span></a> </div>
	      <div class="wpf-right">&nbsp; <?php if( wpforo_feature('rss-feed') ): ?><a href="<?php WPF()->feed->rss2_url(); ?>" class="wpfcl-2" title="<?php wpforo_phrase('Topic RSS Feed') ?>" target="_blank"><span class="wpfcl-3">RSS</span> <i class="fa fa-rss wpfsx wpfcl-3"></i></a><?php endif; ?></div>
	      <br class="wpf-clear" />
	</div>
<?php 
	
	foreach($posts as $key => $post) : ?>
		
		<?php $member = wpforo_member($post); ?>
	  	<div id="post-<?php echo intval($post['postid']) ?>" class="post-wrap">
	      <div class="wpforo-post wpfcl-1">
	        <div class="wpf-left">
            	<?php if( WPF()->perm->usergroup_can('va') && wpforo_feature('avatars') ): ?>
	            	<div class="author-avatar"><?php echo WPF()->member->avatar( $member, 'alt="'.esc_attr($member['display_name']).'"', 80 ) ?></div>
	            <?php endif ?>
                <div class="author-data">
                    <div class="author-name"><span><?php WPF()->member->show_online_indicator($member['userid']) ?></span>&nbsp;<?php wpforo_member_link($member); ?></div>
                    <?php wpforo_member_nicename($member, '@'); ?>
                    <div class="author-title"><?php wpforo_member_title($member) ?></div>
                    <?php wpforo_member_badge($member) ?>
                    <div class="author-joined"><?php wpforo_phrase('Joined') ?>:<?php wpforo_date($member['user_registered']); ?></div>
                    <div class="author-posts"><?php wpforo_phrase('Posts') ?>: <?php echo intval($member['posts']) ?></div>
	            </div>
                <div class="wpf-clear"></div>
                <div id="wpforo-memberinfo-toggle-<?php echo intval($post['postid']) ?>" class="wpforo-membertoggle" title="<?php wpforo_phrase('More') ?>">
                	<i class="fa fa-caret-down" aria-hidden="true"></i>
                </div>
            	<div id="wpforo-memberinfo-<?php echo intval($post['postid']) ?>" class="wpforo-memberinfo">
                	<div class="wpf-member-profile-buttons">
                        <?php WPF()->tpl->member_buttons($member) ?>
                        <?php WPF()->tpl->member_social_buttons($member) ?>
                    </div>
                </div>
	        </div><!-- left -->
	        <div class="wpf-right">
	            <div class="wpforo-post-content-top">
	                <?php if($post['status']): ?><span class="wpf-mod-message"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <?php wpforo_phrase('Awaiting moderation') ?></span><?php endif; ?> <span><?php wpforo_date($post['created'], 'd/m/Y g:i a'); ?></span> &nbsp; <a href="<?php echo esc_url( wpforo_post($post['postid'],'url') );  ?>"><i class="fa fa-link wpfsx"></i></a>
	            </div><!-- wpforo-post-content-top -->
	            <div class="wpforo-post-content">
					<?php echo wpforo_content_filter( wpforo_kses($post['body'], 'post') ) ?>
	            </div>
	            <?php wpforo_post_edited($post); ?>
                <?php do_action( 'wpforo_tpl_post_loop_after_content', $post, $member ) ?>
                <?php if( wpforo_feature('signature') ): ?>
	            	<?php if($member['signature']): ?><div class="wpforo-post-signature"><?php wpforo_signature( $member ) ?></div><?php endif; ?>
                <?php endif; ?>
	        </div><!-- right -->
	        <br class="wpf-clear" />
	        <div class="bottom">
				<div class="bleft">
					<?php echo WPF()->tpl->likers($post['postid']); ?>
				</div>
	            <div class="bright">
	            	<?php if( $post['is_first_post'] ){
	            		$buttons = array( 'reply', 'quote',	'like', 'solved', 'sticky', 'private', 'close', 'report', 'move', 'approved', 'edit', 'delete' );
						WPF()->tpl->buttons( $buttons, $forum, $topic, $post, TRUE );
					}else{
						$buttons = array( 'reply', 'quote', 'like', 'report', 'approved', 'edit', 'delete' );
						WPF()->tpl->buttons( $buttons, $forum, $topic, $post );
					} ?>
				</div>
	            <div class="wpf-clear"></div>
	        </div><!-- bottom -->
	      </div><!-- wpforo-post -->
	 	</div><!-- post-wrap -->
	 	
	 	<?php do_action( 'wpforo_loop_hook', $key ) ?>
	 	
	<?php endforeach; ?>
	
</div><!-- wpfl-1 -->