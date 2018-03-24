<?php
	
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

$args = array(); $items_count = 0;
if(!empty($_GET['wpfpaged'])) $paged = intval($_GET['wpfpaged']);
if(!empty($_GET['wpff'])) $args['forumid'] = intval($_GET['wpff']);

$type = WPF()->post->options['recent_posts_type'];
if(!is_user_logged_in() || !WPF()->perm->usergroup_can('em')){ $args['private'] = 0; $args['status'] = 0; }

if( $type == 'topics' ){
	$end_date = time() - (14 * 24 * 60 * 60);
	$args['where'] = "`modified` > '" . date( 'Y-m-d H:i:s', $end_date ) . "'"; 
	$args['orderby'] = (!empty($_GET['wpfob'])) ? sanitize_text_field($_GET['wpfob']) : 'modified';
	$args['order'] = (!empty($_GET['wpfo'])) ? sanitize_text_field($_GET['wpfo']) : 'DESC';
	$args['offset'] = ($paged - 1) * WPF()->post->options['topics_per_page'];
	$args['row_count'] = WPF()->post->options['topics_per_page'];
	$topics = WPF()->topic->get_topics($args, $items_count);
	if(empty($topics)){ $end_date = time() - (90 * 24 * 60 * 60); $args['where'] = "`modified` > '" . date( 'Y-m-d H:i:s', $end_date ) . "'"; $topics = WPF()->topic->get_topics($args, $items_count);}
}
else{
	$end_date = time() - (14 * 24 * 60 * 60);
	$args['where'] = "`created` > '" . date( 'Y-m-d H:i:s', $end_date ) . "'"; 
	$args['orderby'] = (!empty($_GET['wpfob'])) ? sanitize_text_field($_GET['wpfob']) : 'created';
	$args['order'] = (!empty($_GET['wpfo'])) ? sanitize_text_field($_GET['wpfo']) : 'DESC';
	$args['offset'] = ($paged - 1) * WPF()->post->options['posts_per_page'];
	$args['row_count'] = WPF()->post->options['posts_per_page'];
	$posts = WPF()->post->get_posts($args, $items_count);
	if(empty($posts)){ $end_date = time() - (90 * 24 * 60 * 60); $args['where'] = "`created` > '" . date( 'Y-m-d H:i:s', $end_date ) . "'"; $posts = WPF()->post->get_posts($args, $items_count); }
}
?>
<div class="wpforo-recent-wrap">
  <div class="wpf-head-bar">
	 <h1 id="wpforo-title" style="padding-bottom:0px; margin-bottom:0px;">
	 	<?php wpforo_phrase('Recent Posts') ?>
     	<?php if( wpforo_feature('rss-feed') ): ?>
            <div class="wpforo-feed"> 
                <span class="wpf-feed-forums">
                    <a href="<?php WPF()->feed->rss2_url( true, 'forum' ); ?>"  title="<?php wpforo_phrase('Forums RSS Feed') ?>" target="_blank">
                        <span><?php wpforo_phrase('Forums') ?></span> <i class="fa fa-rss wpfsx"></i>
                    </a>
                </span><sep> | </sep>
                <span class="wpf-feed-topics">
                    <a href="<?php WPF()->feed->rss2_url( true, 'topic' ); ?>"  title="<?php wpforo_phrase('Topics RSS Feed') ?>" target="_blank">
                        <span><?php wpforo_phrase('Topics') ?></span> <i class="fa fa-rss wpfsx"></i>
                    </a>
                </span>
             </div>
         <?php endif; ?>
     </h1>
  	 <div class="wpf-snavi">
        <?php WPF()->tpl->pagenavi($paged, $items_count, FALSE); ?>
     </div>
  </div>
  <?php if( $type == 'topics' ): ?>
     <div class="wpforo-recent-content wpfr-topics">
        <?php if( !empty($topics) ): ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr class="wpf-htr">
            <td class="wpf-shead-avatar">&nbsp;</td>
            <td class="wpf-shead-title"><?php wpforo_phrase('Topic Title') ?></td>
            <td class="wpf-shead-forum"><?php wpforo_phrase('Forum') ?></td>
          </tr>
              <?php foreach($topics as $topic) : extract($topic, EXTR_OVERWRITE); ?>
                  <?php 
                    $member = wpforo_member($topic); 
                    $forum = wpforo_forum($topic['forumid']);
					if(isset($topic['last_post']) && $topic['last_post'] != 0){ 
						$last_post = wpforo_post($topic['last_post']);
						$last_poster = wpforo_member($last_post);
					}
					if(isset($topic['first_postid']) && $topic['first_postid'] != 0){
						$first_post = wpforo_post($topic['first_postid']);
						$intro_posts = WPF()->post->options['layout_extended_intro_posts_count']; if( $intro_posts < 1 ){ $intro_posts = NULL; } else { $intro_posts = ($intro_posts > 1) ? ($intro_posts - 1) : $intro_posts = 0; }
						$first_poster = wpforo_member($first_post); 
						$posts = WPF()->post->get_posts( array('topicid' => $topic['topicid'], 'order' => 'DESC', 'orderby' => 'postid', 'row_count' => $intro_posts) );
					}
					$topic_url = wpforo_topic($topic['topicid'], 'url');
					$post_toglle = WPF()->post->options['layout_extended_intro_posts_toggle'];
					$classes = WPF()->tpl->icon('topic', $topic, false);
					$class = explode( ' ',  $classes); $class = ( isset($class[0]) ) ? 'wpf-' . str_replace('fa-', '', $class[0]) : '';
				  ?>
                  <tr class="wpf-ttr">
                    <td class="wpf-spost-avatar"> 
                        <?php if( WPF()->perm->usergroup_can('va') && wpforo_feature('avatars') ): ?>
                            <?php echo WPF()->member->avatar($member, 'alt="'.esc_attr($member['display_name']).'"', 30) ?>
                        <?php endif; ?>
                    </td>
                    <td class="wpf-spost-title">
                        <a href="<?php echo esc_url(WPF()->topic->get_topic_url($topicid)) ?>" class="wpf-spost-title-link <?php wpforo_unread_topic($topic['topicid']); ?>" title="<?php wpforo_phrase('View entire topic') ?>"><?php echo esc_html($title) ?> &nbsp;<i class="fa fa-chevron-right" style="font-weight:100; font-size:11px;"></i></a>
                        <p style="font-size:12px"><?php wpforo_member_link($member, 'by'); ?> <?php wpforo_date($topic['created']); ?> &nbsp;|&nbsp; <span style="text-transform: lowercase;"><?php wpforo_phrase('Last post') ?>:</span> <?php wpforo_date($last_post['created']); ?></p>
                    </td>
                    <td class="wpf-spost-forum"><a href="<?php echo $forum['url'] ?>"><?php echo esc_html($forum['title']); ?></a></td>
                  </tr>
                  <tr class="wpf-ptr">
                    <td class="wpf-spost-icon">&nbsp;</td>
                    <td colspan="2" class="wpf-stext">
                    <ul>
                        <?php if(!empty($posts) && is_array($posts)) : ?>
                            <?php foreach($posts as $post) : ?>
                                <?php $poster = wpforo_member($post); ?>
                                <li>
                                    <i class="fa fa-reply fa-rotate-180 wpfsx wpfcl-0"></i> &nbsp; <a href="<?php echo esc_url( wpforo_post($post['postid'], 'url') ); ?>" title="<?php wpforo_phrase('REPLY:') ?> <?php echo esc_html( wpforo_text($post['body'], 100, false)) ?>"><?php echo (( $post_body = esc_html(wpforo_text($post['body'], WPF()->post->options['layout_extended_intro_posts_length'], FALSE)) ) ? $post_body : esc_html($post['title'])) ?></a>
                                    <div class="wpf-spost-topic-recent-posts"><?php wpforo_date($post['created']); ?>&nbsp; <?php wpforo_member_link($poster, 'by %s', 5); ?> </div> 
                                	<br class="wpf-clear">
                                </li>
                            <?php endforeach ?>
                            <?php if(intval($topic['posts']) > ($intro_posts+1)): ?>
                                <li style="text-align:right;"><a href="<?php echo esc_url($topic_url) ?>"><?php wpforo_phrase('view all posts', true, 'lower');  ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
                            <?php endif ?>
                        <?php endif ?>
                        </ul>
                    </td>
                  </tr>
              <?php endforeach ?>
        </table>
        <?php else: ?>
            <p class="wpf-p-error"><?php wpforo_phrase('No topics were found here') ?>  </p>
        <?php endif; ?>
      </div>
      <div class="wpf-snavi">
        <?php WPF()->tpl->pagenavi($paged, $items_count, FALSE); ?>
      </div>
  <?php else: ?>
      <div class="wpforo-recent-content wpfr-posts">
        <?php if( !empty($posts) ): ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr class="wpf-htr">
            <td class="wpf-shead-avatar">&nbsp;</td>
            <td class="wpf-shead-title"><?php wpforo_phrase('Post Title') ?></td>
            <td class="wpf-shead-forum"><?php wpforo_phrase('Forum') ?></td>
          </tr>
              <?php foreach($posts as $post) : extract($post, EXTR_OVERWRITE); ?>
                  <?php 
                    $member = wpforo_member($post); 
                    $forum = wpforo_forum($post['forumid']);
                  ?>
                  <tr class="wpf-ttr">
                    <td class="wpf-spost-avatar"> 
                        <?php if( WPF()->perm->usergroup_can('va') && wpforo_feature('avatars') ): ?>
                            <?php echo WPF()->member->avatar($member, 'alt="'.esc_attr($member['display_name']).'"', 40) ?>
                        <?php endif; ?>
                    </td>
                    <td class="wpf-spost-title">
                        <a href="<?php echo esc_url(WPF()->post->get_post_url($postid)) ?>" class="wpf-spost-title-link <?php wpforo_unread_topic($post['topicid']); ?>" title="<?php wpforo_phrase('View entire post') ?>"><?php echo esc_html($title) ?> &nbsp;<i class="fa fa-chevron-right" style="font-weight:100; font-size:11px;"></i></a>
                        <p style="font-size:12px"><?php wpforo_member_link($member, 'by'); ?>, <?php wpforo_date($post['created']); ?></p>
                    </td>
                    <td class="wpf-spost-forum"><a href="<?php echo $forum['url'] ?>"><?php echo esc_html($forum['title']); ?></a></td>
                  </tr>
                  <tr class="wpf-ptr">
                    <td class="wpf-spost-icon">&nbsp;</td>
                    <td colspan="2" class="wpf-stext">
                    <?php
                        $body = wpforo_content_filter( $body );
                        $body = preg_replace('#\[attach\][^\[\]]*\[\/attach\]#is', '', strip_tags($body));
                        wpforo_text($body, 200);
                    ?>
                    </td>
                  </tr>
              <?php endforeach ?>
        </table>
        <?php else: ?>
            <p class="wpf-p-error"><?php wpforo_phrase('No posts were found here') ?>  </p>
        <?php endif; ?>
      </div>
      <div class="wpf-snavi">
        <?php WPF()->tpl->pagenavi($paged, $items_count, FALSE); ?>
      </div>
  <?php endif; ?>
  
  
</div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
