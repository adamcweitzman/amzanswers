<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;

define('WPFORO_THEME_DIR', WPFORO_DIR . '/wpf-themes' );
define('WPFORO_THEME_URL', WPFORO_URL . '/wpf-themes' );

class wpForoTemplate{
	public $default;
	public $options;
	public $style;
	public $theme;
	
	function __construct(){
		$this->init_defaults();
		$this->init_options();
	}

	public function init(){
        $this->init_hooks();
        $this->init_member_templates();
        $this->init_nav_menu();
    }

	private function init_hooks(){
        if( is_wpforo_page() ){
            add_filter("mce_external_plugins", array(&$this, 'add_tinymce_buttons'), 15);
            add_filter("tiny_mce_plugins", array(&$this, 'filter_tinymce_plugins'), 15);
            add_filter("wp_mce_translation", array(&$this, 'add_tinymce_translations'));
        }
    }

	private function init_defaults(){
        $this->default = new stdClass;

        $this->default->style = array(
            'font_size_forum' => 17,
            'font_size_topic' => 16,
            'font_size_post_content' => 14,
            'custom_css' => "#wpforo-wrap {\r\n   font-size: 13px; width: 100%; padding:10px 20px; margin:0px;\r\n}\r\n"
        );

        $theme = $this->find_theme( 'classic' );
        if( $current_theme = get_option('wpforo_theme_options') ) $theme = wpforo_deep_merge($theme, $current_theme);
        $this->default->options = $theme;
    }

    private function init_options(){
        $this->style = get_wpf_option('wpforo_style_options', $this->default->style);

        $this->options = get_wpf_option('wpforo_theme_options', $this->default->options);
        $this->theme = $this->options['folder'];

        $this->init_defines();
    }

    private function init_defines(){
        define('WPFORO_THEME', $this->theme );
        define('WPFORO_TEMPLATE_DIR', WPFORO_THEME_DIR . '/' . $this->theme );
        define('WPFORO_TEMPLATE_URL', WPFORO_THEME_URL . '/' . $this->theme );
    }

	function add_tinymce_buttons($plugin_array) {
	  $plugin_array = array();
	  $plugin_array['wpforo_pre_button'] = WPFORO_URL . '/wpf-assets/js/tinymce-pre.js';
	  $plugin_array['wpforo_link_button'] = WPFORO_URL . '/wpf-assets/js/tinymce-link.js';
	  $plugin_array['wpforo_source_code_button'] = WPFORO_URL . '/wpf-assets/js/tinymce-code.js';
	  $plugin_array['emoticons'] = WPFORO_URL . '/wpf-assets/js/tinymce-emoji.js';
	  return $plugin_array;
	}
	
	function filter_tinymce_plugins($plugins){
		return array('hr','lists','textcolor','paste');
	}
	
	function add_tinymce_translations($mce_translation){
		$mce_translation['Insert link'] = __( 'Insert link' );
		$mce_translation['Link Text'] = __( 'Link Text' );
		$mce_translation['Open link in a new tab'] = __( 'Open link in a new tab' );
		return $mce_translation;
	}
	
	function topic_form($forumid){
		if(!isset(WPF()->post->options['max_upload_size']) || !WPF()->post->options['max_upload_size']){ $server_mus = wpforo_human_size_to_bytes(ini_get('upload_max_filesize')); if( !$server_mus || $server_mus > 10485760 ) $server_mus = 10485760; WPF()->post->options['max_upload_size'] = $server_mus;}
		?>
		<div id="wpf-topic-create" class="wpf-topic-create">
			<form name="topic" action="" enctype="multipart/form-data" method="POST">
				<?php wp_nonce_field( 'wpforo_verify_form', 'wpforo_form' ); ?>
                <input type="hidden" name="topic[action]" value="add"/>
				<input type="hidden" id="parent" name="topic[forumid]" value="<?php echo intval($forumid) ?>" />
				
                <?php if(!is_user_logged_in()): ?>
                	<?php $guest = WPF()->member->get_guest_cookies(); ?>
                    <div class="wpf-topic-guest-fields">
                        <div class="wpf-topic-guest-name">
                            <label style="padding-left:8px;"> <?php wpforo_phrase('Author Name') ?> * </label>
                            <input id="wpf_user_name" type="text" placeholder="<?php esc_attr( wpforo_phrase('Your name') ) ?>" name="topic[name]" value="<?php echo esc_attr($guest['name']) ?>" />
                        </div>
                        <div class="wpf-topic-guest-email">
                            <label style="padding-left:8px;"> <?php wpforo_phrase('Author Email') ?> * </label>
                            <input id="wpf_user_email" type="text" placeholder="<?php esc_attr( wpforo_phrase('Your email') ) ?>" name="topic[email]" value="<?php echo esc_attr($guest['email']) ?>" />
                        </div>
                        <div class="wpf-clear"></div>
                    </div>
                <?php endif; ?>
                
				<label style="padding-left:8px;"> <?php wpforo_phrase('Topic Title') ?> * </label>
				<input required="true" autofocus type="text" name="topic[title]" class="wpf-subject" value="" id="title" autocomplete="off" placeholder="<?php esc_attr( wpforo_phrase('Enter title here') ) ?>">
				<?php
				$content   = '';
				$editor_id = 'postbody';
				$settings  = array(
					'wpautop'      => true,// use wpautop?
					'media_buttons'=> FALSE,// show insert / upload button(s)
					'textarea_name'=> $editor_id,// set the textarea name to something different, square brackets [] can be used here
					'textarea_rows'=> get_option('default_post_edit_rows', 20),// rows = "..."
					'tabindex'=> '',
					'editor_height' => '180',
					'editor_css'   => '',	// intended for extra styles for both visual and HTML editors buttons, needs to include the < style > tags, can use "scoped".
					'editor_class'=> '',	// add extra class(es) to the editor textarea
					'teeny'=> FALSE,		// output the minimal editor config used in Press This
					'dfw'=> false,			// replace the default fullscreen with DFW (supported on the front - end in WordPress 3.4)
					'tinymce'=> array(
						'toolbar1' => 'fontsizeselect,bold,italic,underline,strikethrough,forecolor,bullist,numlist,hr,alignleft,aligncenter,alignright,alignjustify,link,unlink,blockquote,pre,undo,redo,pastetext,source_code,emoticons',
						'toolbar2' => '', 
						'toolbar3' => '', 
						'toolbar4' => '',
						'content_style' => 'blockquote{border: #cccccc 1px dotted; background: #F7F7F7; padding:10px;font-size:12px; font-style:italic; margin: 20px 10px;}',
                        'object_resizing' => false
					),		// load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'=> true, 		// load Quicktags, can be used to pass settings directly to Quicktags using an array()
					'default_editor' => 'tinymce'
				);
				wp_editor( $content, $editor_id, $settings );
				?>
				<div class="wpf-extra-fields">
				   <?php do_action('wpforo_topic_form_extra_fields_before') ?>
                   <div class="wpf-main-fields">
                        <?php if(WPF()->perm->forum_can('s', $forumid)) : ?>
                            <input id="t_sticky" name="topic[type]" type="checkbox" value="1">&nbsp;&nbsp;
                            <i class="fa fa-exclamation wpfsx"></i>&nbsp;&nbsp;<label for="t_sticky" style="padding-bottom:2px; cursor: pointer;"><?php wpforo_phrase('Set Topic Sticky'); ?>&nbsp;</label>
                            <span class="wpfbs">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                        <?php endif; ?>
                        <?php if(WPF()->perm->forum_can('p', $forumid) || WPF()->perm->forum_can('op', $forumid)) : ?>
                            <input id="t_private" name="topic[private]" type="checkbox" value="1">&nbsp;&nbsp;
                            <i class="fa fa-eye-slash wpfsx"></i>&nbsp;&nbsp;<label for="t_private" style="padding-bottom:2px; cursor: pointer;" title="<?php wpforo_phrase('Only Admins and Moderators can see your private topics.'); ?>"><?php wpforo_phrase('Private Topic'); ?>&nbsp;</label>
                            
                        <?php endif; ?>
                        <?php do_action('wpforo_topic_form_buttons_hook'); ?>&nbsp;&nbsp;
                    </div>
                    <?php if( WPF()->perm->can_attach() ): ?>
                        <?php if(!defined('WPFOROATTACH_BASENAME') && WPF()->perm->forum_can('a', $forumid)): ?>
                            <div class="wpf-default-attachment" style="padding-top:5px;">
                                <label for="file"><?php wpforo_phrase('Attach file:') ?> </label> <input id="file" type="file" name="attachfile" />
                                <p><?php wpforo_phrase('Maximum allowed file size is'); echo ' ' . wpforo_print_size(WPF()->post->options['max_upload_size']); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php do_action('wpforo_topic_form_extra_fields_after') ?>
                </div>
                <?php if( wpforo_feature('subscribe_checkbox_on_post_editor') ) : ?>
                	<div class="wpf-topic-sbs"><input id="wpf-topic-sbs" type="checkbox" name="wpforo_topic_subs" value="1" <?php echo ( wpforo_feature('subscribe_checkbox_default_status') ) ? 'checked="true" ' : ''; ?>/>&nbsp;<label for="wpf-topic-sbs"><?php wpforo_phrase('Subscribe to this topic') ?></label></div>
				<?php endif; ?>
				<input id="formbutton" type="submit" name="topic[save]" class="button button-primary forum_submit" value="<?php wpforo_phrase('Add Topic') ?>">
                <div class="wpf-clear"></div>
			</form>
		</div>
		
		<?php
	}
	
	/**
	* 
	* @param array $args
	*  
	* Please note that all array elements are required!
	* example of args
	* $default = array(
	*	"topic_closed" => $topic['closed'], 	// is topic closed or opened (values 1 or 0)
	* 	"topicid" => $topic['topicid'],  		// the id of topic
	* 	"forumid" => $forum_data['forumid'],
	* 	"layout" => $cat_layout,
	* 	"topic_title" => $topic['title']		// the title of topic
	* );
	* 
	* @return html form
	*/
		
	function reply_form($args){ 
		extract($args, EXTR_OVERWRITE); ?>
		<!-- Report Dialog  -->
		
		<div id="reportdialog" title="<?php esc_attr( wpforo_phrase('Report to Administration') ) ?>" style="display: none">
			<form id="reportform">
				<input type="hidden" id="reportpostid" value=""/>
				<textarea required style="width:100%; height:105px;" id="reportmessagecontent" placeholder="<?php esc_attr( wpforo_phrase('Write message') ) ?>"></textarea>
			</form>
			<input style="float: right;" id="sendreport" type="submit" value="<?php wpforo_phrase('Send Report') ?>"/>
		</div>
		
		<!-- Report Dialog end -->
		
		<!-- Move Dialog  -->
		
		<div id="movedialog" title="<?php esc_attr( wpforo_phrase('Move topic') ) ?>" style="display: none">
			<div class="form-field">
				<label for="parent"><?php wpforo_phrase('Choose target forum') ?></label>
				<form id="topicmoveform" method="POST">
                <?php wp_nonce_field( 'wpforo_verify_form', 'wpforo_form' ); ?>
				<input type="hidden" name="movetopicid" value="<?php echo intval($topicid) ?>"/>
				<input type="hidden" name="post[save]" value="move"/>
					<select id="parent" name="topic[forumid]" class="postform">
						<?php WPF()->forum->tree('select_box', FALSE, $topicid ); ?>
					</select>
					<input type="submit"  value="<?php wpforo_phrase('Move') ?>"/>
				</form>
			</div>
		</div>
		
		<!-- move Dialog end -->
		<?php
		if( $topic_closed ) return;
		
		$head_html = '<p id="wpf-reply-form-title">'.wpforo_phrase('Leave a reply', false).'</p>';
		$head_html = apply_filters( 'wpforo_reply_form_head', $head_html, $args ); 
		if(!isset(WPF()->post->options['max_upload_size']) || !WPF()->post->options['max_upload_size']){$server_mus = wpforo_human_size_to_bytes(ini_get('upload_max_filesize')); if( !$server_mus || $server_mus > 10485760 ) $server_mus = 10485760; WPF()->post->options['max_upload_size'] = $server_mus;}
		?>
		<div id="wpf-form-wrapper">
			<?php echo $head_html; //this is a HTML content ?>
			<div id="wpf-post-create" class="wpf-post-create">
				<form name="post" action="" enctype="multipart/form-data" method="POST" class="editor">
					<?php wp_nonce_field( 'wpforo_verify_form', 'wpforo_form' ); ?>
                    <input type="hidden" id="formaction" name="post[action]" value="add"/>
					<input type="hidden" id="formtopicid" name="post[topicid]" value="<?php echo intval($topicid) ?>"/>
					<input type="hidden" id="postparentid" name="post[parentid]" value="0"/>
					<input type="hidden" id="formpostid" name="post[postid]" value=""/>
					<input type="hidden" id="parent" name="post[forumid]" value="<?php echo intval($forumid) ?>" />
                    <?php if(!is_user_logged_in()): ?>
                		<?php $guest = WPF()->member->get_guest_cookies(); ?>
                        <div class="wpf-post-guest-fields">
                            <div class="wpf-post-guest-name">
                                <label style="padding-left:8px;"> <?php wpforo_phrase('Author Name') ?> * </label>
                                <input id="wpf_user_name" type="text" placeholder="<?php esc_attr( wpforo_phrase('Your name') ) ?>" name="post[name]" value="<?php echo esc_attr($guest['name']) ?>" />
                            </div>
                            <div class="wpf-post-guest-email">
                                <label style="padding-left:8px;"> <?php wpforo_phrase('Author Email') ?> * </label>
                                <input id="wpf_user_email" type="text" placeholder="<?php esc_attr( wpforo_phrase('Your email') ) ?>" name="post[email]" value="<?php echo esc_attr($guest['email']) ?>" />
                            </div>
                            <div class="wpf-clear"></div>
                        </div>
                        <label style="padding-left:8px;"> <?php wpforo_phrase('Post Title') ?> * </label>
                	<?php endif; ?>
	                <?php 
					$reply_title = wpforo_phrase('RE', false) . ': '. $topic_title; 
					$reply_title = apply_filters( 'wpforo_reply_form_field_title', $reply_title, $args );
					$reply_title = esc_attr($reply_title);
					?>
					<input id="title" required="true" type="text" name="post[title]" class="wpf-subject" value="<?php if($reply_title) echo esc_attr($reply_title); ?>" autocomplete="off" placeholder="<?php if($reply_title) echo esc_attr($reply_title); ?>"><br/>
					<?php
					$content   = '';
					$editor_id = 'postbody';
					$settings  = array(
						'wpautop'      => true,// use wpautop?
						'media_buttons'=> FALSE,// show insert / upload button(s)
						'textarea_name'=> $editor_id,// set the textarea name to something different, square brackets [] can be used here
						'textarea_rows'=> get_option('default_post_edit_rows', 5),// rows = "..."
						'editor_class'=> 'wpeditor',	// add extra class(es) to the editor textarea
						'teeny'=> false,		// output the minimal editor config used in Press This
						'dfw'=> false,			// replace the default fullscreen with DFW (supported on the front - end in WordPress 3.4)
						'editor_height' => '180',
						'tinymce'=> array(
							'toolbar1' => 'fontsizeselect,bold,italic,underline,strikethrough,forecolor,bullist,numlist,hr,alignleft,aligncenter,alignright,alignjustify,link,unlink,blockquote,pre,undo,redo,pastetext,source_code,emoticons',
							'toolbar2' => '', 
							'toolbar3' => '', 
							'toolbar4' => '',
							'content_style' => 'blockquote{border: #cccccc 1px dotted; background: #F7F7F7; padding:10px;font-size:12px; font-style:italic; margin: 20px 10px;}',
						    'object_resizing' => false
                        ),		// load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
						'quicktags'=> true, 		// load Quicktags, can be used to pass settings directly to Quicktags using an array()
						'default_editor' => 'tinymce', 		// load Quicktags, can be used to pass settings directly to Quicktags using an array()
					);
					wp_editor( $content, $editor_id, $settings );
					?>
					<div class="wpf-extra-fields">
                        <?php do_action('wpforo_reply_form_extra_fields_before') ?>
						<?php do_action('wpforo_reply_form_buttons_hook'); ?>&nbsp;&nbsp;
	                    <?php if( WPF()->perm->can_attach() ): ?>
							<?php if(!defined('WPFOROATTACH_BASENAME') && WPF()->perm->forum_can('a', $forumid)): ?>
                                <div class="wpf-default-attachment">
                                    <label for="file"><?php wpforo_phrase('Attach file:') ?> </label> <input id="file" type="file" name="attachfile" />
                                    <p><?php wpforo_phrase('Maximum allowed file size is'); echo ' ' . wpforo_print_size(WPF()->post->options['max_upload_size']); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php do_action('wpforo_reply_form_extra_fields_after') ?>
	                </div>
	                <?php if( wpforo_feature('subscribe_checkbox_on_post_editor') ) :
		                $args = array( "userid" => WPF()->current_userid , "itemid" => intval($topicid), "type" => "topic" );
		                $subscribe = WPF()->sbscrb->get_subscribe( $args );
	                	if( !isset($subscribe['subid']) ) : ?>
	                		<div class="wpf-topic-sbs"><input id="wpf-topic-sbs" type="checkbox" name="wpforo_topic_subs" value="1" <?php echo ( wpforo_feature('subscribe_checkbox_default_status') ) ? 'checked="true" ' : ''; ?> />&nbsp;<label for="wpf-topic-sbs"><?php wpforo_phrase('Subscribe to this topic') ?></label></div>
						<?php endif;
					endif; ?>
					<input id="formbutton" type="submit" name="post[save]" class="button button-primary forum_submit" value="<?php wpforo_phrase('Add Reply') ?>">
	                <div class="wpf-clear"></div>
				</form>
			</div>
		</div>
		<?php
	}
	
	function pagenavi($paged, $items_count, $permalink = TRUE, $class = ''){
		$items_per_page = ( WPF()->current_object['template'] == 'topic' ? WPF()->post->options['topics_per_page'] : WPF()->post->options['posts_per_page'] );
		if($items_count <= $items_per_page) return;
		
		$pages_count = ceil($items_count/$items_per_page);
		
		if($permalink){
			$url = trim( preg_replace('#\/paged\/[\d]+\/*.*$#is', '', wpforo_get_request_uri()), '/' ) . '/paged/';
		}else{
			$url = trim( preg_replace('#[\&\?]wpfpaged=[\d]*.*$#is', '', wpforo_get_request_uri()), '/' );
			$url .= (strpos($url, '?') === FALSE ? '?' : '&') . 'wpfpaged=';
		}
		?>
		
		<div class="wpf-navi <?php echo esc_attr($class) ?>">
            <div class="wpf-navi-wrap">
                <span class="wpf-page-info"><?php wpforo_phrase('Page') ?> <?php echo intval($paged) ?> / <?php echo intval($pages_count) ?></span>
                <?php if( $paged - 1 > 0 ): ?><a href="<?php echo esc_url($url) . ($paged - 1) ?>" class="wpf-prev-button"><i class="fa fa-chevron-left fa-sx"></i> <?php wpforo_phrase('prev') ?></a><?php endif ?>
                <select class="wpf-navi-dropdown" onchange="if (this.value) window.location.href=this.value" title="<?php esc_attr( wpforo_phrase('Select Page') ) ?>">	
                    <?php for($i = 1; $i <= $pages_count; $i++) : ?>
                        <option value="<?php echo esc_url($url) . $i ?>" <?php echo $paged == $i ? ' selected="selected"' : '' ?>><?php echo intval($i); ?></option>
                    <?php endfor; ?>
                </select>
                <?php if( $paged + 1 <= $pages_count ): ?><a href="<?php echo esc_url($url) . ($paged + 1) ?>" class="wpf-prev-button"><?php wpforo_phrase('next') ?> <i class="fa fa-chevron-right fa-sx"></i></a><?php endif ?>
            </div>
		</div>
		
		<?php 
	} 
	
	function likers($postid){
		if(!$postid) return '';
		
		$post = wpforo_post($postid);
		
		$l_count = wpforo_post($postid, 'likes_count');
		$l_usernames = wpforo_post($postid, 'likers_usernames');
		$return = '';
		
		if( $l_count ){
			if($l_usernames[0]['ID'] == WPF()->current_userid) $l_usernames[0]['display_name'] = wpforo_phrase('You', FALSE);
			if($l_count == 1){
				$return = sprintf( wpforo_phrase('%s liked', FALSE), '<a href="' . esc_url(WPF()->member->get_profile_url($l_usernames[0]['ID'])) . '">'.esc_html($l_usernames[0]['display_name']).'</a>' );
			}elseif($l_count == 2){
				$return = sprintf( wpforo_phrase('%s and %s liked', FALSE), '<a href="' . esc_url(WPF()->member->get_profile_url($l_usernames[0]['ID'])) . '">'.esc_html($l_usernames[0]['display_name']).'</a>', '<a href="'.esc_url(WPF()->member->get_profile_url($l_usernames[1]['ID'])).'">'.esc_html($l_usernames[1]['display_name']).'</a>' );
			}elseif($l_count == 3){
				$return = sprintf( wpforo_phrase('%s, %s and %s liked', FALSE), '<a href="' . esc_url(WPF()->member->get_profile_url($l_usernames[0]['ID'])) .'">'.esc_html($l_usernames[0]['display_name']).'</a>', '<a href="'.esc_url(WPF()->member->get_profile_url($l_usernames[1]['ID'])).'">'.esc_html($l_usernames[1]['display_name']).'</a>', '<a href="'.esc_url(WPF()->member->get_profile_url($l_usernames[2]['ID'])).'">'.esc_html($l_usernames[2]['display_name']).'</a>' );
			}elseif($l_count >= 4){
				$l_count = $l_count - 3;
				$return = sprintf( wpforo_phrase('%s, %s, %s and %d people liked', FALSE), '<a href="' . esc_url(WPF()->member->get_profile_url($l_usernames[0]['ID'])) .'">'.esc_html($l_usernames[0]['display_name']).'</a>', '<a href="'.esc_url(WPF()->member->get_profile_url($l_usernames[1]['ID'])).'">'.esc_html($l_usernames[1]['display_name']).'</a>', '<a href="'.esc_url(WPF()->member->get_profile_url($l_usernames[2]['ID'])).'">'.esc_html($l_usernames[2]['display_name']).'</a>', $l_count );
			}
		}
		return $return;
	}

	
	/**
	* Get actions buttons
	* 
	* @since 1.0.0
	* 
	* @param array buttons names function will return buttons by this array
	* 
	* @param array $forum required
	* 
	* @param array $topic required
	* 
	* @param array $post required
	* 
	* @param int $is_topic required this is a first post in the loop
	* 
	* $buttons = array( 'reply', 'answer', 'comment', 'quote', 'like', 'report', 'sticky', 'close', 'move', 'edit', 'delete', 'link' );
	* 
	* @return html ( buttons )
	*/
	
	function buttons( $buttons, $forum = array(), $topic = array(), $post = array(), $is_topic = FALSE ){
		
		$button_html = array(); 
		$login = is_user_logged_in();
		
		$forumid = (isset($forum['forumid'])) ? $forum['forumid'] : 0;
		$topicid = (isset($topic['topicid'])) ? $topic['topicid'] : 0;
		$postid = (isset($post['postid'])) ? $post['postid'] : 0;
		
		$is_sticky = (isset($topic['type'])) ? $topic['type'] : 0;
		$is_closed = (isset($topic['closed'])) ? $topic['closed'] : 0;
		$is_private = (isset($topic['private'])) ? $topic['private'] : 0;
		$is_solved = (isset($post['is_answer'])) ? $post['is_answer'] : 0;
        $is_approve = (isset($post['status'])) ? $post['status'] : 0;
		
		foreach($buttons as $button){
			
			switch($button){
				
				case 'reply': 
					if($is_closed) break;
					if( WPF()->perm->forum_can('cr', $forumid) ){
			   			$button_html[] = '<span id="parentpostid'.intval($postid).'" class="wpforo-reply wpf-action add_post_button"><i class="fa fa-reply fa-rotate-180"></i>' . wpforo_phrase('Reply', false).'</span>';
			   		}else{
			   			$button_html[] = '<span class="wpf-action not_reg_user"><i class="fa fa-reply fa-rotate-180"></i> ' . wpforo_phrase('Reply', false).'</span>';
			   		}
					break; 
				case 'answer': 
					if( WPF()->perm->forum_can('cr', $forumid) ){
			   			$button_html[] = '<span class="wpforo-answer wpf-button add_post_button"><i class="fa fa-pencil"></i> ' . wpforo_phrase('Answer', false).'</span>';
			   		}else{
			   			$button_html[] = '<span class="wpf-button not_reg_user"><i class="fa fa-pencil"></i> ' . wpforo_phrase('Answer', false).'</span>';
			   		}
				 	break; 
				case 'comment': 
					if($is_closed) break;
					$title = wpforo_phrase('Use comments to ask for more information or suggest improvements. Avoid answering questions in comments.', false);
					if( WPF()->perm->forum_can('cr', $forumid) ) {
						$button_html[] = '<span id="parentpostid'.intval($postid).'" class="wpforo-childreply wpf-button add_post_button" title="'.esc_attr($title).'"><i class="fa fa-comment"></i> ' . wpforo_phrase('Add a comment', false).'</span>';
			   		}else{
			   			$button_html[] = '<span class="not_reg_user wpf-button add_post_button" title="'.esc_attr($title).'"><i class="fa fa-comment"></i> ' . wpforo_phrase('Add a comment', false).'</span>';
			   		}
				 	break; 
				case 'quote':
					if($is_closed) break;
					if( WPF()->perm->forum_can('cr', $forumid) ) {
						$button_html[] = '<span id="wpfquotepost'.intval($postid).'" class="wpforo-quote wpf-action"><i class="fa fa-quote-left wpfsx"></i>' . wpforo_phrase('Quote', false).'</span>';
			   		}else{
			   			$button_html[] = '<span class="wpf-action not_reg_user"><i class="fa fa-quote-left wpfsx"></i>' . wpforo_phrase('Quote', false).'</span>';
			   		}	
					 break; 
				case 'like':
					if( WPF()->perm->forum_can('l', $forumid) && $login ) {
						$like_status = ( WPF()->post->is_liked( $postid, WPF()->current_userid ) === FALSE ? 'wpforo-like' : 'wpforo-unlike' );
						$like_icon = ( $like_status == 'wpforo-like') ? 'up' : 'down';
						$button_html[] = '<span id="wpflike'. intval($postid) .'" class="wpf-action '. sanitize_html_class($like_status) .'"><i id="likeicon'. intval($postid) .'" class="fa fa-thumbs-o-'. esc_attr($like_icon) .' wpfsx"></i><span id="liketext'. intval($postid) .'">' . wpforo_phrase( str_replace('wpforo-', '', $like_status), false) . '</span></span>';
					}	
				 	break; 
				case 'report':
					if( WPF()->perm->forum_can('r', $forumid) && $login ) {
						$button_html[] = '<span id="wpfreport'. intval($postid) .'" class="wpf-action wpforo-report"><i class="fa fa-exclamation-triangle"></i>' . wpforo_phrase('Report', false).'</span>';
					}	
				 	break; 
				case 'sticky':
					$sticky_status = ( $is_sticky ? 'wpforo-unsticky' : 'wpforo-sticky');
					if( WPF()->perm->forum_can('s', $forumid) ) {
						$button_html[] = '<span id="wpfsticky'. intval($topicid) .'" class="wpf-action '. sanitize_html_class($sticky_status) .'"><i class="fa fa-exclamation wpfsx"></i><span id="stickytext'. intval($topicid) .'">' . wpforo_phrase( str_replace('wpforo-', '', $sticky_status), false).'</span></span>';
					}
				 	break; 
				case 'private':
					if( $login ){
						if( WPF()->perm->forum_can('p', $forumid) || (WPF()->current_userid == $post['userid'] && WPF()->perm->forum_can('op', $forumid)) ) {
							$private_status = ( $is_private ? 'wpforo-public' : 'wpforo-private');
							$private_icon = ( $private_status == 'wpforo-public') ? 'eye' : 'eye-slash';
							$button_html[] = '<span id="wpfprivate'. intval($topicid) .'" class="wpf-action '. sanitize_html_class($private_status) .'"><i id="privateicon'. intval($topicid) .'"  class="fa fa-'. esc_attr($private_icon) .' wpfsx"></i><span id="privatetext'. intval($topicid) .'">' . wpforo_phrase( str_replace('wpforo-', '', $private_status), false).'</span></span>';
						}
					}
				 	break; 
				case 'solved':
					$solved_status = ( $is_solved ? 'wpforo-unsolved' : 'wpforo-solved');
					if( WPF()->perm->forum_can('sv', $forumid) || (WPF()->current_userid == $post['userid'] && WPF()->perm->forum_can('osv', $forumid)) ) {
						$button_html[] = '<span id="wpfsolved'. intval($postid) .'" class="wpf-action '. sanitize_html_class($solved_status) .'"><i class="fa fa-check-circle wpfsx"></i><span id="solvedtext'. intval($postid) .'">' . wpforo_phrase( str_replace('wpforo-', '', $solved_status), false).'</span></span>';
                    }
				 	break;
                case 'approved':
                    if( WPF()->perm->forum_can('au', $forumid) && $login ) {
                        $approve_status = ( !$is_approve ? 'wpforo-unapprove' : 'wpforo-approve');
                        $approve_icon = ( $approve_status == 'wpforo-unapprove') ? 'fa-exclamation-circle' : 'fa-check';
                        $button_html[] = '<span id="wpfapprove'. intval($postid) .'" class="wpf-action '. sanitize_html_class($approve_status) .'"><i id="approveicon'. intval($postid) .'"   class="fa '. esc_attr($approve_icon) .' wpfsx"></i><span id="approvetext'. intval($postid) .'">' . wpforo_phrase( str_replace('wpforo-', '', $approve_status), false).'</span></span>';
                    }
                    break;
                case 'close':
					if( WPF()->perm->forum_can('cot', $forumid) && $login ) {
						$open_status = ( $is_closed ? 'wpforo-open' : 'wpforo-close' );
						$open_icon = ($open_status == 'wpforo-open') ? 'unlock' : 'lock';
						$button_html[] = '<span id="wpfclose'. intval($topicid) .'" class="wpf-action '. sanitize_html_class($open_status) .'"><i id="closeicon'. intval($topicid) .'" class="fa fa-'. esc_attr($open_icon) .' wpfsx"></i><span id="closetext'. intval($topicid) .'">' . wpforo_phrase( str_replace('wpforo-', '', $open_status), false).'</span></span>';
					}
				 	break; 
				case 'move':
					if( WPF()->perm->forum_can('mt', $forumid) && $login ) {
						$button_html[] = '<span class="wpf-action wpforo-move"><i class="fa fa-share-square-o wpfsx"></i>' . wpforo_phrase('Move', false).'</span>';	
					}
				 	break; 
				case 'edit':
						if($is_closed) break;
						$diff = current_time( 'timestamp', 1 ) - strtotime($post['created']);
						if( WPF()->member->current_user_is_new() && $post['status'] ){
								//New registered user's unapproved topic/post | No Edit button. 
						}
						elseif( !$login && isset($post['email']) 
							   			&& wpforo_is_owner($post['userid'], $post['email'])
							   			&& WPF()->perm->forum_can( ($is_topic ? 'eot' : 'eor' ), $forumid ) 
										&& $diff < WPF()->post->options[($is_topic ? 'eot' : 'eor' ).'_durr'] 
							  ) {
								$a = ( $is_topic ) ? 'wpfedittopicpid' : ''; 
								$b = ( $is_topic ) ? $postid : $postid;
								$button_html[] = '<span id="'. esc_attr( $a . $b ) .'" class="wpforo-edit wpf-action"><i class="fa fa-edit wpfsx"></i>' . wpforo_phrase('Edit', false).'</span>';
							
						}
						elseif( $login ) {
							if( WPF()->perm->forum_can( ($is_topic ? 'et' : 'er'), $forumid ) || 
							   		(	WPF()->current_userid == $post['userid'] 
									 	&& WPF()->perm->forum_can( ($is_topic ? 'eot' : 'eor' ), $forumid ) 
									 	&& $diff < WPF()->post->options[($is_topic ? 'eot' : 'eor' ).'_durr'] 
									) 
							  ) {
								$a = ( $is_topic ) ? 'wpfedittopicpid' : ''; 
								$b = ( $is_topic ) ? $postid : $postid;
								$button_html[] = '<span id="'. esc_attr( $a . $b ) .'" class="wpforo-edit wpf-action"><i class="fa fa-edit wpfsx"></i>' . wpforo_phrase('Edit', false).'</span>';
							}
						} 
				 	break; 
				case 'delete':
					if( $login ){
						if( WPF()->member->current_user_is_new() && $post['status'] ){
							//New registered user's unapproved topic/post | No Delete button. 
						}
						else{
							$diff = current_time( 'timestamp', 1 ) - strtotime($post['created']);
							if( WPF()->perm->forum_can( ($is_topic ? 'dt' : 'dr' ), $forumid ) || (WPF()->current_userid == $post['userid'] && WPF()->perm->forum_can( ($is_topic ? 'dot' : 'dor' ), $forumid ) && $diff < WPF()->post->options[($is_topic ? 'dot' : 'dor' ).'_durr']) ){
								$a = ( $is_topic ) ? 'wpftopicdelete' : 'wpfreplydelete'; 
								$b = ( $is_topic ) ? $topicid : $postid;
								$button_html[] = '<span id="'. esc_attr( $a . $b ) .'" class="wpf-action wpforo-delete"><i class="fa fa-times wpfsx"></i>' . wpforo_phrase('Delete', false).'</span>';
							}
						}
					}
				 	break; 
				case 'link':
					$url = ( $is_topic ) ? WPF()->topic->get_topic_url( $topic ) : wpforo_post( $postid, 'url' );
					$button_html[] = '<a href="'. esc_url($url) .'"><i class="fa fa-link wpfsx"></i></a>';
				 	break; 
				case 'positivevote':
					if( WPF()->perm->forum_can('v', $forumid) && $login ) {
						$button_html[] = '<i itemtype="' . ( $is_topic ? 'topic' : 'reply' ) . '" id="wpfvote-up-'. wpforo_bigintval($postid) .'" class="voteup fa fa-play fa-rotate-270 wpfcl-0"></i>';
					}else{
						$button_html[] = '<i class="not_reg_user fa fa-play fa-rotate-270 wpfcl-0"></i>';
					}
				 	break; 
				case 'negativevote':
					if( WPF()->perm->forum_can('v', $forumid) && $login ) {
						$button_html[] = '<i itemtype="' . ( $is_topic ? 'topic' : 'reply' ) . '" id="wpfvote-down-'. wpforo_bigintval($postid) .'" class="votedown fa fa-play fa-rotate-90 wpfcl-0"></i>';
					}else{
						$button_html[] = '<i class="not_reg_user fa fa-play fa-rotate-90 wpfcl-0"></i>';
					}
				 	break; 
				case 'isanswer': 
					$is_answer = WPF()->post->is_answered( $postid );
					$is_answer = ( $is_answer == 0 )  ? '-not' : '';
					if( $login ){
						$button_html[] = '<div id="wpf-answer-'. intval($postid) .'" class="wpf-toggle'. esc_attr($is_answer) .'-answer"><i class="fa fa-check"></i></div>';
					}else{
						$button_html[] = '<div class="wpf-toggle'. esc_attr($is_answer) .'-answer not_reg_user"><i class="fa fa-check"></i></div>';
					}
				 	break; 
			} //switch
		} //foreach
		
		echo implode('', $button_html);
		
	}
	
	function breadcrumb($url_data){
		extract($url_data, EXTR_OVERWRITE);
		
		switch($template) :
			case 'search': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i></a>
			        
			        <a href="#" class="active"><?php wpforo_phrase('Search') ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
			    
			<?php break;
			case 'signup': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i></a>
			        
			        <a href="#" class="active"><?php wpforo_phrase('Register') ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
			    
			<?php break;
			case 'signin': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i></a>
			        
			        <a href="#" class="active"><?php wpforo_phrase('Login') ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
			    
			<?php break;
			case 'members': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i></a>
			        
			        <?php if(isset($_GET['wpfms'])) : ?>
			        	
			        	<a href="<?php echo wpforo_home_url('members') ?>"><?php wpforo_phrase('Members') ?></a>
			        	<a href="#" class="active"><?php wpforo_phrase('Search') ?></a>
			        	
			        <?php else : ?>
			        	
			        	<a href="#" class="active"><?php wpforo_phrase('Members') ?></a>
			        	
			        <?php endif ?>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
			    
			<?php break;
			case 'recent': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i></a>
			        
			        	<a href="#" class="active"><?php wpforo_phrase('Recently Added') ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
			    
			<?php break;
			case 'profile': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i></a>
			        
			        <a href="<?php echo wpforo_home_url('members') ?>"><?php wpforo_phrase('Members') ?></a>
			        <a href="#" class="active"><?php @wpforo_text( wpforo_make_dname($user['display_name'], $user['user_nicename']), 19 ) ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
			    
			<?php break;
			case 'account': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i>
			        
			        <a href="<?php echo wpforo_home_url('members') ?>"><?php wpforo_phrase('Members') ?></a>
			        <a href="<?php echo esc_url($user['profile_url']) ?>"><?php wpforo_text( wpforo_make_dname($user['display_name'], $user['user_nicename']), 19 ) ?></a>
			        <a href="#" class="active"><?php wpforo_phrase('Account') ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
				
			<?php break;
			case 'activity': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i>
			        
			        <a href="<?php echo wpforo_home_url('members') ?>"><?php wpforo_phrase('Members') ?></a>
			        <a href="<?php echo esc_url($user['profile_url']) ?>"><?php wpforo_text( wpforo_make_dname($user['display_name'], $user['user_nicename']), 19 ) ?></a>
			        <a href="#" class="active"><?php wpforo_phrase('Activity') ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
				
			<?php break;
			case 'subscriptions': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i>
			        
			        <a href="<?php echo wpforo_home_url('members') ?>"><?php wpforo_phrase('Members') ?></a>
			        <a href="<?php echo esc_url($user['profile_url']) ?>"><?php wpforo_text( wpforo_make_dname($user['display_name'], $user['user_nicename']), 19 ) ?></a>
			        <a href="#" class="active"><?php wpforo_phrase('Subscriptions') ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
				
			<?php break;
//			TODO: move code to pm plugin
			case 'messages': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo wpforo_home_url() ?>" class="wpf-root" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i>
			        
			        <a href="<?php echo wpforo_home_url('members') ?>"><?php wpforo_phrase('Members') ?></a>
			        
			        <?php if(!empty($user)) : ?>
			        	
			        	<a href="<?php echo esc_url($user['profile_url']) ?>"><?php wpforo_text( wpforo_make_dname($user['display_name'], $user['user_nicename']), 19 ) ?></a>
			        	
			        <?php endif ?>
			        
			        <a href="#" class="active"><?php wpforo_phrase('Messages') ?></a>
			        
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
				
			<?php break;
			case 'topic': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo ( !isset($forumid) ? '#' : wpforo_home_url() ) ?>" class="wpf-root<?php echo ( !isset($forumid) ? ' active' : '' ) ?>" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i></a>
			        
					<?php if(isset($forumid)) : ?>
						<?php $relative_ids = array();
						WPF()->forum->get_all_relative_ids($forumid, $relative_ids);
						foreach( $relative_ids as $key => $rel_forumid ) : ?>
							<?php $forum = wpforo_forum($rel_forumid) ?>
                            <?php if(!empty($forum)): ?>
								<?php if( $key != ( count($relative_ids) - 1 ) ) : ?>
                                    <a href="<?php echo esc_url( $forum['url'] ) ?>" title="<?php echo esc_attr($forum['title']) ?>"><?php wpforo_text($forum['title'], 19) ?></a>
                                <?php else : ?>
                                    <a href="#" class="active" title="<?php echo esc_attr($forum['title']) ?>"><?php wpforo_text($forum['title'], 19) ?></a>
                                <?php endif ?>
                            <?php endif ?>
						<?php endforeach ?>
					<?php endif ?>
					
					<a href="#" class="wpf-end">&nbsp;</a>
				</div>
				
			<?php break;
			case 'post': ?>
				
				<div class="wpf-breadcrumb">
			        <a href="<?php echo ( !isset($forumid) ? '#' : wpforo_home_url() ) ?>" class="wpf-root<?php echo ( !isset($forumid) ? ' active' : '' ) ?>" title="<?php esc_attr( wpforo_phrase('Forums') ) ?>"><i class="fa fa-home"></i></a>
			        
					<?php if(isset($forumid)) : ?>
						<?php $relative_ids = array();
						WPF()->forum->get_all_relative_ids($forumid, $relative_ids);
						foreach( $relative_ids as $key => $rel_forumid ) : ?>
							<?php $forum = wpforo_forum($rel_forumid) ?>
							<?php if(!empty($forum)): ?>
                            	<a href="<?php echo esc_url( $forum['url'] ) ?>" title="<?php echo esc_attr($forum['title']) ?>"><?php wpforo_text($forum['title'], 19) ?></a>
							<?php endif ?>
						<?php endforeach ?>
					<?php endif ?>
					<?php if(!empty($topic)) : ?>
						
						<a href="#" class="active" title="<?php echo esc_attr($topic['title']) ?>"><?php wpforo_text($topic['title'], 19) ?></a>
						
					<?php endif ?>
					<a href="#" class="wpf-end">&nbsp;</a>
				</div>
				
			<?php break;
			default: ?>
				
				<div class="wpf-breadcrumb">
			        <a href="#" class="wpf-root active"><?php wpforo_phrase('Forums') ?></a>
			        <a href="#" class="wpf-end">&nbsp;</a>
			    </div>
			    
			<?php
		endswitch;
		
	}
	
	function icon($type, $item = array(), $echo = true, $data = 'icon' ){
		
		$icon = array();
		$status = false;
		
		if( isset($item['status']) && $item['status'] ){
			$icon['class'] = 'fa-exclamation-circle';
			$icon['color'] = 'wpfcl-5';
			$icon['title'] = wpforo_phrase('Unapproved', false);
			if($echo) { 
				$status = true; echo ($data == 'icon') ? implode(' ', $icon) : $icon['title']; 
			} 
			else{ 
				return ($data == 'icon') ? implode(' ', $icon) : $icon['title']; 
			}
		}
		
		if(isset($item['type'])){
			
			if( $type == 'topic' ){
				if(WPF()->topic->is_private($item['topicid'])){
					$icon['class'] = 'fa-eye-slash';
					$icon['color'] = 'wpfcl-1';
					$icon['title'] = wpforo_phrase('Private', false);
					if($echo) { 
						$status = true; echo ($data == 'icon') ? implode(' ', $icon) : $icon['title']; 
					} 
					else{ 
						return ($data == 'icon') ? implode(' ', $icon) : $icon['title']; 
					}
				}
				if( wpforo_topic($item['topicid'], 'is_answer') ){
					$icon['class'] = 'fa-check-circle';
					$icon['color'] = 'wpfcl-8';
					$icon['title'] = wpforo_phrase('Solved', false);
					if($echo) { 
						$status = true; echo ($data == 'icon') ? implode(' ', $icon) : $icon['title']; 
					} 
					else{ 
						return ($data == 'icon') ? implode(' ', $icon) : $icon['title']; 
					}
				}
			}
			
			if( $item['closed'] && $item['type'] == 1 ){
				$icon['class'] = 'fa-lock';
				$icon['color'] = 'wpfcl-1';
				$icon['title'] = wpforo_phrase('Closed', false);
				if($echo) { $status = true; echo ($data == 'icon') ? implode(' ', $icon) : $icon['title']; } else{ return ($data == 'icon') ? implode(' ', $icon) : $icon['title']; }
			}
			elseif( $item['closed'] && $item['type'] != 1  ){
				$icon['class'] = 'fa-lock';
				$icon['color'] = 'wpfcl-1';
				$icon['title'] = wpforo_phrase('Closed', false);
				if($echo) { $status = true; echo ($data == 'icon') ? implode(' ', $icon) : $icon['title']; } else{ return ($data == 'icon') ? implode(' ', $icon) : $icon['title']; }
			}
			elseif( !$item['closed'] && $item['type'] == 1  ){
				$icon['class'] = 'fa-thumb-tack';
				$icon['color'] = 'wpfcl-5';
				$icon['title'] = wpforo_phrase('Sticky', false);
				if($echo) { $status = true; echo ($data == 'icon') ? implode(' ', $icon) : $icon['title']; } else{ return ($data == 'icon') ? implode(' ', $icon) : $icon['title']; }
			}
			
			if( $status ){
				//do nothing
			}
			else{
				if( $type == 'forum' ){
					$icon['class'] = 'fa-comments';
					$icon['color'] = 'wpfcl-2';
				}
				elseif( $type == 'topic' ){
					if( $item['posts'] == 1 ){
						$icon['class'] = 'fa-file-o';
						$icon['color'] = 'wpfcl-2';
						$icon['title'] = '';
					}
					elseif( $item['posts'] > 1 && $item['posts'] <= 5 ){
						$icon['class'] = 'fa-file-text-o';
						$icon['color'] = 'wpfcl-2';
						$icon['title'] = '';
					}
					elseif( $item['posts'] > 5 && $item['posts'] <= 20 ){
						$icon['class'] = 'fa-file-text';
						$icon['color'] = 'wpfcl-2';
						$icon['title'] = '';
					}
					elseif( $item['posts'] > 20 ){
						$icon['class'] = 'fa-file-text';
						$icon['color'] = 'wpfcl-5';
						$icon['title'] = '';
					}
					else{
						$icon['class'] = 'fa-file-o';
						$icon['color'] = 'wpfcl-2';
						$icon['title'] = '';
					}
				}
				if($echo) { echo ($data == 'icon') ? implode(' ', $icon) : $icon['title']; } else{ return ($data == 'icon') ? implode(' ', $icon) : $icon['title']; }
			}
			
		}
		else{
			return false;
		}
		
	}
	
	public function member_buttons( $member ){
		
		if(empty($member)) return false;
		$profile_access = ( WPF()->perm->usergroup_can('vprf') ?  true : false );
		
		if( $profile_access ){
			?>
			<a class="wpf-member-profile-button" title="<?php wpforo_phrase('Profile') ?>" href="<?php echo esc_url(WPF()->member->profile_url($member)) ?>">
				<i class="fa fa-user"></i>
			</a>
			<a class="wpf-member-profile-button" title="<?php wpforo_phrase('Activity') ?>" href="<?php echo esc_url(WPF()->member->profile_url($member, 'activity')) ?>">
				<i class="fa fa-comments-o"></i>
			</a>
			<a class="wpf-member-profile-button" title="<?php wpforo_phrase('Subscriptions') ?>" href="<?php echo esc_url(WPF()->member->profile_url($member, 'subscriptions')) ?>">
				<i class="fa fa-rss"></i>
			</a>
			<?php do_action( 'wpforo_member_info_buttons', $member ); ?>
			<?php
		}
	}
	
	public function member_social_buttons( $member ){
		
		$socnets = array();
		if(empty($member)) return false;
		$social_access = ( WPF()->perm->usergroup_can('vmsn') ?  true : false );
		
		if( $social_access ){
			
			if( isset($member['facebook']) && $member['facebook'] ){
				$socnets['facebook']['set'] = $member['facebook'];
				$member['facebook'] = ( strpos($member['facebook'], 'facebook.com') === FALSE ) ? 'https://www.facebook.com/' . trim($member['facebook'], '/') : $member['facebook'] ;
				$socnets['facebook']['value'] = $member['facebook'];
				$socnets['facebook']['protocol'] = 'https://';
				$socnets['facebook']['title'] = wpforo_phrase('Facebook', false);
			}
			
			if( isset($member['twitter']) && $member['twitter'] ){
				$socnets['twitter']['set'] = $member['twitter'];
				$member['twitter'] = ( strpos($member['twitter'], 'twitter.com') === FALSE ) ? 'http://twitter.com/' . trim($member['twitter'], '/') : $member['twitter'] ;
				$socnets['twitter']['value'] = $member['twitter'];
				$socnets['twitter']['protocol'] = 'https://';
				$socnets['twitter']['title'] = wpforo_phrase('Twitter', false);
			}
			
			if( isset($member['gtalk']) && $member['gtalk'] ){
				$socnets['gtalk']['set'] = $member['gtalk'];
				$socnets['gtalk']['value'] = $member['gtalk'];
				$socnets['gtalk']['protocol'] = 'https://';
				$socnets['gtalk']['title'] = wpforo_phrase('Google+', false);
			}
			
			if( isset($member['yahoo']) && $member['yahoo'] ){
				$socnets['yahoo']['set'] = $member['yahoo'];
				$socnets['yahoo']['value'] = $member['yahoo'];
				$socnets['yahoo']['protocol'] = 'mailto:';
				$socnets['yahoo']['title'] = wpforo_phrase('Yahoo', false);
			}
			
			if( isset($member['aim']) && $member['aim'] ){
				$socnets['aim']['set'] = $member['aim'];
				$socnets['aim']['value'] = $member['aim'];
				$socnets['aim']['protocol'] = 'mailto:';
				$socnets['aim']['title'] = wpforo_phrase('AOL IM', false);
			}
			
			if( isset($member['icq']) && $member['icq'] ){
				$socnets['icq']['set'] = $member['icq'];
				$socnets['icq']['value'] = 'www.icq.com/whitepages/cmd.php?uin=' . $member['icq'] . '&action=message';
				$socnets['icq']['protocol'] = 'https://';
				$socnets['icq']['title'] = wpforo_phrase('ICQ', false);
			}
			
			if( isset($member['msn']) && $member['msn'] ){
				$socnets['msn']['set'] = $member['msn'];
				$socnets['msn']['value'] = $member['msn'];
				$socnets['msn']['protocol'] = 'mailto:';
				$socnets['msn']['title'] = wpforo_phrase('MSN', false);
			}
			
			if( isset($member['skype']) && $member['skype'] ){
				$socnets['skype']['set'] = $member['skype'];
				$socnets['skype']['value'] = $member['skype'];
				$socnets['skype']['protocol'] = 'skype:';
				$socnets['skype']['title'] = wpforo_phrase('Skype', false);
			}
			
			?>
            <div class="wpf-member-socnet-wrap">
				<?php if(!empty($socnets)): ?>
					<?php foreach( $socnets as $key => $socnet ): ?>
                        <?php if( !$socnet['set'] ) continue; ?>
                        <?php $title = $member['display_name'] . ' - ' . $socnet['title']; ?>
                        <?php $url = ($key == 'skype') ? 'skype:' . esc_attr($socnet['value']) : esc_url($socnet['protocol'] . str_replace( array('https://', 'http://', 'skype:', 'mailto:'), '', $socnet['value'])); ?>
                        <a href="<?php echo $url ?>" class="wpf-member-socnet-button" title="<?php echo esc_attr($title) ?>">
                            <img src="<?php echo esc_url(WPFORO_URL) ?>/wpf-assets/images/sn/<?php echo $key ?>.png" alt="<?php echo esc_attr($title) ?>" title="<?php echo esc_attr($title) ?>" />
                        </a> 
                    <?php endforeach; ?>
                <?php endif; ?>
            	<?php do_action( 'wpforo_member_socnet_buttons', $member ); ?>
            </div>
			<?php
		}
	}
	
	public function init_member_templates(){
		WPF()->member_tpls = array(
			'account' => wpftpl('profile-account.php'),
			'activity' => wpftpl('profile-activity.php'),
			'subscriptions' => wpftpl('profile-subscriptions.php')
		);
		WPF()->member_tpls = apply_filters('wpforo_member_templates_filter', WPF()->member_tpls);
		WPF()->member_tpls['profile'] = wpftpl('profile-home.php');
	}
	
	function has_menu(){
		return has_nav_menu( 'wpforo-menu' );
	}
	
	function nav_menu(){
		if ( has_nav_menu( 'wpforo-menu' ) ){
			$defaults = array(
				'theme_location'  => 'wpforo-menu',
				'menu'            => '',
				'container'       => '',
				'container_class' => '',
				'container_id'    => '',
				'menu_class'      => 'wpf-menu',
				'menu_id'         => 'wpf-menu',
				'echo'            => true,
				'fallback_cb'     => 'wp_page_menu',
				'before'          => '',
				'after'           => '',
				'link_before'     => '',
				'link_after'      => '',
				'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'depth'           => 0,
				'walker'          => ''
			);
			wp_nav_menu( $defaults );
		}
	}
	
	function init_nav_menu(){
		
		if(isset(WPF()->current_object) && !empty(WPF()->current_object)){
			
			extract(WPF()->current_object, EXTR_OVERWRITE);
			
			WPF()->menu['wpforo-home'] = array(
				'href' => wpforo_home_url(),
				'label' => wpforo_phrase('forums', FALSE),
				'attr' => ( $template == 'forum' || $template == 'topic' || $template == 'post' ? ' class="wpforo-active"' : '' ),
				'submenues' => array()
			);
			
			if(WPF()->perm->usergroup_can('vmem')){
				WPF()->menu['wpforo-members'] = array(
					'href' => wpforo_home_url('members'),
					'label' => wpforo_phrase('members', FALSE),
					'attr' => ( $template == 'members' ? ' class="wpforo-active"' : '' ),
					'submenues' => array()
				);
			}
			
			WPF()->menu['wpforo-recent'] = array(
				'href' => wpforo_home_url('recent'),
				'label' => wpforo_phrase('Recent Posts', FALSE),
				'attr' => ( $template == 'recent' ? ' class="wpforo-active"' : '' ),
				'submenues' => array()
			);
			
			if( is_user_logged_in() ){
				
				WPF()->menu['wpforo-profile-home'] = array(
					'href' => WPF()->member->get_profile_url(WPF()->current_userid),
					'label' => wpforo_phrase('my profile', FALSE),
					'attr' => ( isset(WPF()->member_tpls[$template]) && WPF()->member_tpls[$template] && WPF()->current_object['user_is_same_current_user'] ? ' class="wpforo-active"' : '' ),
					'submenues' => array()
				);
				WPF()->menu['wpforo-profile-account'] = array(
					'href' => WPF()->member->get_profile_url(WPF()->current_userid, 'account'),
					'label' => wpforo_phrase('account', FALSE),
					'attr' => ( $template == 'account' && WPF()->current_object['user_is_same_current_user'] ? ' class="wpforo-active"' : '' ),
					'submenues' => array()
				);
				WPF()->menu['wpforo-profile-activity'] = array(
					'href' => WPF()->member->get_profile_url(WPF()->current_userid, 'activity'),
					'label' => wpforo_phrase('activity', FALSE),
					'attr' => ( $template == 'activity' && WPF()->current_object['user_is_same_current_user'] ? ' class="wpforo-active"' : '' ),
					'submenues' => array()
				);
				WPF()->menu['wpforo-profile-subscriptions'] = array(
					'href' => WPF()->member->get_profile_url(WPF()->current_userid, 'subscriptions'),
					'label' => wpforo_phrase('subscriptions', FALSE),
					'attr' => ( $template == 'subscriptions' && WPF()->current_object['user_is_same_current_user'] ? ' class="wpforo-active"' : '' ),
					'submenues' => array()
				);
				WPF()->menu['wpforo-logout'] = array(
					'href' => wpforo_home_url('?wpforo=logout'),
					'label' => wpforo_phrase('logout', FALSE),
					'attr' => '',
					'submenues' => array()
				);
				
			}else{
				
				if( wpforo_feature('user-register') ){
					WPF()->menu['wpforo-register'] = array(
						'href' => wpforo_register_url(),
						'label' => wpforo_phrase('register', FALSE),
						'attr' => ( isset($_GET['wpforo']) && $_GET['wpforo'] == 'signup' ? ' class="wpforo-active"' : '' ),
						'submenues' => array()
					);
				}
				WPF()->menu['wpforo-login'] = array(
					'href' => wpforo_login_url(),
					'label' => wpforo_phrase('login', FALSE),
					'attr' => ( isset($_GET['wpforo']) && $_GET['wpforo'] == 'signin' ? ' class="wpforo-active"' : '' ),
					'submenues' => array()
				);
			}
			
			WPF()->menu = apply_filters('wpforo_menu_array_filter', WPF()->menu);
		}
	}
	
	/**
	*
	* Checks in current active theme options if certain layout exists.
	*
	* @since 1.0.0
	*
	* @param  mixed 	$identifier			Layout id (folder name) OR @layout variable in header ( 1 or Extended )
	* @param  string	$identifier_type	The type of first parameter 'id' OR 'name' (@layout)
	*
	* @return boolean						true/false
	* 
	**/
	function layout_exists( $identifier, $identifier_type = 'id' ){
		
		$layouts = $this->options['layouts'];
		
		if( $identifier_type == 'id' ){
			if( isset($layouts[$identifier]) && !empty($layouts[$identifier])){
				return true;
			}
			else{
				return false;
			}
		}
		elseif( $identifier_type = 'name' ){
			foreach( $layouts as $id => $layout ){
				if( !isset($layout['name']) && $layout['name'] == $identifier ){
					return true;
				}
			}
			return false;
		}
	}
	
	/**
	*
	* Finds and returns all layouts information in array from theme's /layouts/ folder
	*
	* @since 1.0.0
	*
	* @param  string 	$theme		Theme id ( folder name ) e.g. 'classic'
	*
	* @return array
	* 
	**/
	function find_layouts( $theme ){
		$layout_data = array();
		$layouts = $this->find_themes('/'.$theme.'/layouts', 'php', 'layout');
		if(!empty($layouts)){
			foreach( $layouts as $layout ){
				$lid = trim(basename(dirname( $layout['file']['value'] )), '/');
				$layout_data[$lid]['id'] = $lid;
				$layout_data[$lid]['name'] = $layout['name']['value'];
				$layout_data[$lid]['version'] = $layout['version']['value'];
				$layout_data[$lid]['description'] = $layout['description']['value'];
				$layout_data[$lid]['author'] = $layout['author']['value'];
				$layout_data[$lid]['url'] = $layout['layout_url']['value'];
				$layout_data[$lid]['file'] = $layout['file']['value'];
			}
		}
		return $layout_data;
	}
	
	function show_layout_selectbox($layoutid = 0){
		$layouts = $this->find_layouts( WPFORO_THEME );
		if( !empty($layouts) ){
			foreach( $layouts as $layout ) : ?>  
				<option value="<?php echo esc_attr(trim($layout['id'])) ?>" <?php echo ( $layoutid == $layout['id'] ? 'selected' : '' ); ?> ><?php echo esc_html($layout['name']) ?></option>
				<?php
			endforeach;
		}
	}
	
	/**
	*
	* Finds and returns styles array from theme's /styles/colors.php file
	*
	* @since 1.0.0
	*
	* @param  string 	$theme		Theme id ( folder name ) e.g. 'classic'
	*
	* @return array
	* 
	**/
	function find_styles( $theme ){
		$colors = array();
		$color_file = WPFORO_THEME_DIR . '/' . $theme . '/styles/colors.php';
		if( file_exists($color_file) ){
			include( $color_file );
		}
		return $colors;
	}
	
	/**
	*
	* Scans certain theme directory and returns all information in array ( theme header, layouts, styles ).
	*
	* @since 1.0.0
	*
	* @param  string 	$theme_file			Theme folder name or main css file base path ( 'classic' OR classic/style.css' )
	*
	* @return array
	* 
	**/
	function find_theme( $theme_file ){
		$theme = array();
		$theme_file = trim(trim($theme_file, '/'));
		
		if( preg_match('|\.[\w\d]{2,4}$|is', $theme_file) ){
			$theme_folder = trim(basename(dirname($theme_file)), '/');
		}
		else{
			$theme_folder = $theme_file;
			$theme_file = $theme_file . '/style.css';
		}
		
		if( !is_readable( WPFORO_THEME_DIR . '/' . $theme_file ) ){
			$theme['error'] = __('Theme file not readable', 'wpforo') .' ('.$theme_file.')';
		}
		else{
			$theme_data = $this->find_theme_headers( WPFORO_THEME_DIR . '/' . $theme_file );
			$theme['id'] = $theme_folder;
			$theme['name'] = $theme_data['name']['value'];
			$theme['version'] = $theme_data['version']['value'];
			$theme['description'] = $theme_data['description']['value'];
			$theme['author'] = $theme_data['author']['value'];
			$theme['url'] = $theme_data['theme_url']['value'];
			$theme['file'] = $theme_file;
			$theme['folder'] = $theme_folder;
			$theme['layouts'] = $this->find_layouts( $theme_folder );
			$styles = $this->find_styles( $theme_folder );
			if(!empty($styles)){
				reset($styles);
				$theme['style'] = key($styles);
				$theme['styles'] = $styles;
			}
        }

        return $theme;
    }
	
	/**
	*
	* Scans wpForo themes (wpf-themes) folder, reads main files' headers and returns information about all themes in array.
	* This function can also be used to scan and get information about layouts in each theme /layouts/ folder.
	*
	* @since 1.0.0
	*
	* @param  string 	$base_dir		Absolute path to scan directory (e.g. /home/public_html/wp-content/plugins/wpforo/wpf-themes/) 
	* @param  string 	$ext			File extension which may contain header information
	* @param  string 	$mode			'theme' or 'layout'
	*
	* @return array
	* 
	**/
	function find_themes( $base_dir = '', $ext = 'css', $mode = 'theme' ){
		$themes = array ();
		$themes_dir = @opendir( WPFORO_THEME_DIR . $base_dir );
		$theme_files = array();
		if( $themes_dir ){
			while( ($file = readdir( $themes_dir )) !== false ){
				if( substr($file, 0, 1) == '.' ) continue;
				if( is_dir( WPFORO_THEME_DIR . $base_dir .'/'.$file ) ){
					$themes_subdir = @opendir( WPFORO_THEME_DIR . $base_dir .'/'.$file );
					if( $themes_subdir ){
						while(($subfile = readdir( $themes_subdir ) ) !== false ){
							if( substr($subfile, 0, 1) == '.' ) continue;
							if( substr($subfile, -4) == '.' . $ext ) $theme_files[] = "$file/$subfile";
						}
						closedir( $themes_subdir );
					}
				} 
				else{
					if( substr($file, -4) == '.' . $ext ) $theme_files[] = $file;
				}
			}
			closedir( $themes_dir );
		}
		if( empty($theme_files) ) return $themes;
		foreach( $theme_files as $theme_file ){
			if( !is_readable( WPFORO_THEME_DIR . $base_dir . '/' . $theme_file ) ) continue;
			if( $mode == 'theme' ){
				$theme_data = $this->find_theme_headers( WPFORO_THEME_DIR . $base_dir . '/' . $theme_file );
			}
			elseif( $mode == 'layout' ){
				$theme_data = $this->find_layout_headers( WPFORO_THEME_DIR . $base_dir . '/' . $theme_file );
			}
			if( empty($theme_data['name']['value']) ) continue;
			$themes[wpforo_clear_basename($theme_file)] = $theme_data;
		}
		return $themes;
	}
	
	/**
	*
	* Reads theme main file's header variables and returns information in array.
	*
	* @since 1.0.0
	*
	* @param  string 	$file	Absolute path to file (e.g. /home/public_html/wp-content/plugins/wpforo/wpf-themes/style.css) 
	*
	* @return array
	* 
	**/
	function find_theme_headers( $file ){
		$theme_headers = array();
		$headers = array(
			'name' => 'Theme Name',
			'version' => 'Version',
			'description' => 'Description',
			'author' => 'Author',
			'theme_url' => 'Theme URI',
		);
		$fp = fopen( $file, 'r' );
		$data = fread( $fp, 8192 );
		fclose( $fp );
		$data = str_replace( "\r", "\n", $data );
		foreach ( $headers as $header_key => $header_name ){
			if ( preg_match( '|^[\s\t\/*#@]*' . preg_quote( $header_name, '|' ) . ':(.*)$|mi', $data, $match ) && $match[1] ){
				$theme_headers[$header_key]['name'] = $header_name;
				$theme_headers[$header_key]['value'] = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[1]));
			}
			else{
				$theme_headers[$header_key]['name'] = $header_name;
				$theme_headers[$header_key]['value'] = '';
			}
		}
		$theme_headers['file']['name'] = 'file';
		$theme_headers['file']['value'] = $file;
		return $theme_headers;
	}
	
	/**
	*
	* Reads layout main file's header variables and returns information in array.
	*
	* @since 1.0.0
	*
	* @param  string 	$file	Absolute path to file (e.g. /home/public_html/wp-content/plugins/wpforo/wpf-themes/layouts/1/forum.php) 
	*
	* @return array
	* 
	**/
	function find_layout_headers( $file ){
		$theme_headers = array();
		$headers = array(
			'name' => 'layout',
			'version' => 'version',
			'description' => 'description',
			'author' => 'author',
			'layout_url' => 'url',
		);
		$fp = fopen( $file, 'r' );
		$data = fread( $fp, 8192 );
		fclose( $fp );
		$data = str_replace( "\r", "\n", $data );
		foreach ( $headers as $header_key => $header_name ){
			if ( preg_match( '|^[\s\t\/*#@]*' . preg_quote( $header_name, '|' ) . ':(.*)$|mi', $data, $match ) && $match[1] ){
				$theme_headers[$header_key]['name'] = $header_name;
				$theme_headers[$header_key]['value'] = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[1]));
			}
			else{
				$theme_headers[$header_key]['name'] = $header_name;
				$theme_headers[$header_key]['value'] = '';
			}
		}
		$theme_headers['file']['name'] = 'file';
		$theme_headers['file']['value'] = trim(str_replace( WPFORO_THEME_DIR, '', $file), '/');
		return $theme_headers;
	}

	public function copyright(){
		if( wpforo_feature('copyright') ): ?>
			<div id="wpforo-poweredby">
		        <p class="wpf-by">
					<span onclick='javascript:document.getElementById("bywpforo").style.display = "inline";document.getElementById("awpforo").style.display = "none";' id="awpforo"> <img align="absmiddle" title="<?php esc_attr( wpforo_phrase('Powered by') ) ?> wpForo version <?php echo esc_html(WPFORO_VERSION) ?>" alt="Powered by wpForo" class="wpdimg" src="<?php echo WPFORO_URL ?>/wpf-assets/images/wpforo-info.png" alt="wpForo"> </span><a id="bywpforo" target="_blank" href="http://wpforo.com/">&nbsp;<?php wpforo_phrase('Powered by') ?> wpForo version <?php echo esc_html(WPFORO_VERSION) ?></a>
				</p>
		    </div>
			<?php 
		endif; 
	}

	public function member_menu( $userid, $menu = array() ){ 
		if( empty($menu) ) $menu = array('profile' => 'fa-user', 'account' => 'fa-cog', 'activity' => 'fa-comments-o', 'subscriptions' => 'fa-rss');
		$menu = apply_filters('wpforo_member_menu_filter', $menu, $userid);
		if( !($userid == WPF()->current_userid || WPF()->perm->usergroup_can('em')) ) unset($menu['account']);
		if( !($userid == WPF()->current_userid || WPF()->perm->usergroup_can('vpra')) ) unset($menu['activity']);
		if( !($userid == WPF()->current_userid || WPF()->perm->usergroup_can('vprs')) ) unset($menu['subscriptions']);
		foreach( $menu as $key => $value ) :
            ?>
	        <a class="wpf-profile-menu <?php echo ( WPF()->current_object['template'] == $key ? ' wpforo-active' : '' ) ?>" href="<?php echo esc_url( WPF()->member->get_profile_url($userid, $key) ) ?>">
	        	<i class="fa <?php echo sanitize_html_class($value) ?>"></i> <?php wpforo_phrase($key) ?>
	        </a>
			<?php
		endforeach;
	}

	public function member_template(){
		$permission  = true;
		extract(WPF()->current_object, EXTR_OVERWRITE);
		extract($user, EXTR_OVERWRITE);
		if( $template == 'account' && !($userid == WPF()->current_userid || WPF()->perm->usergroup_can('em')) ) $permission = false;
		if( $template == 'activity' && !($userid == WPF()->current_userid || WPF()->perm->usergroup_can('vpra')) ) $permission = false;
		if( $template == 'subscriptions' && !($userid == WPF()->current_userid || WPF()->perm->usergroup_can('vprs')) ) $permission = false;
		if( $permission ){
			include( (isset(WPF()->member_tpls[$template]) && WPF()->member_tpls[$template] ? WPF()->member_tpls[$template] : WPF()->member_tpls['profile']) );
		}
		else{
			?>
            <div class="wpfbg-7 wpf-page-message-wrap">
				<div class="wpf-page-message-text">
					<?php wpforo_phrase('You do not have permission to view this page') ?>
				</div>
			</div>
            <?php
		}
	}
	
	public function member_error(){
		echo apply_filters('wpforo_member_error_filter', wpforo_phrase('Members not found', FALSE));
	}

	public function field( $args, $wrap = true ){
       
        $default = array(
            'label' => '',
            'title' => '',
            'name' => '',
            'value' => '', //url
            'values' => '', 
            'type' => 'text',
            'placeholder' => '',
            'description' => '',
            'id' => '',
            'class' => '',
			'attributes' => '',
			'isWrapItem' => '',
			'isLabelFirst' => '',
            'isDisabled' => false,
			'isEditable' => 1,
            'isRequired' => 1,
            'isMultiChoice' => 0,
            'isConfirmPassword' => 1,
            'minLength' => 0,
            'maxLength' => 0,
            'faIcon' => '',
            'html' => '',
			'varname' => (( isset(WPF()->form['varname']) ) ? WPF()->form['varname'] : 'wpfdata'),
			'template' => (( isset(WPF()->form['template']) ) ? WPF()->form['template'] : WPF()->current_object['template']),
			'canBeInactive' => 1,
			'canEdit' => array('1'),
			'canView' => array('1', '2', '3', '5'),
			'can' => ''
        );

		
        $args = wpforo_parse_args( $args, $default );
        extract( $args );
		$field_html = ''; 
		$minLength_attr = ''; 
		$maxLength_attr = '';
        $isRequired = ( $isRequired ) ? ' required="required" ' : '';
        $isDisabled = ( $isDisabled ) ? ' disabled="disabled" ' : '';
		$isDisabled = ( !$isEditable && $template != 'register' && $name != 'user_login') ? ' disabled="disabled" style="display:none" ' : '';
		if( !$isDefault ){ $varname = 'data'; }
		$fieldName = ( !empty($varname) ? $varname . '[' . $name . ']' : $name );
		$fieldId = ( !empty($varname) ? $varname . '_' : '' ) . ( ($id) ? $id : $name );
		$minLength = ($minLength) ? intval($minLength): '';
		$maxLength = ($maxLength) ? intval($maxLength): '';
		if( $minLength ) { $minLength_attr = ($type == 'date' || $type == 'number' || $type == 'range') ? ' min="' . $minLength . '" ' : ' minlength="' . $minLength . '" '; }
		if( $maxLength ) { $maxLength_attr = ($type == 'date' || $type == 'number' || $type == 'range') ? ' max="' . $maxLength . '" ' : ' maxlength="' . $maxLength . '" '; }
		$minmax = $minLength_attr . ' ' . $maxLength_attr;
		
		$args['value'] = ( isset(WPF()->form['value'][$name]) ) ? WPF()->form['value'][$name] : $args['value'];
		if( !$isDefault && $varname ) $args['value'] = ( isset(WPF()->form['value'][$varname][$name]) ) ? WPF()->form['value'][$varname][$name] : $args['value'];
		$value = $args['value'];

        if( $type == 'textarea' ){
            $field_html = '<textarea '. $isRequired .' name="' . esc_attr($fieldName) . '" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isDisabled . ' '.$attributes.' ' . trim($minmax) . ' placeholder="' . esc_attr($placeholder) . '">' . esc_textarea($value) . '</textarea>';
        }
        elseif( $type == 'password' ){
			$password_html = '';
			if( $template == 'account' ){
				$isRequired = 0;
				$args['label'] = wpforo_phrase('Old password', false); $args['description'] = '';
				$password_html = '<input '. $isRequired .' type="password" name="' . esc_attr($varname) . '[old_pass]" value="" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isDisabled . ' '.$attributes.'  placeholder="' . esc_attr( wpforo_phrase('Old password', false) ) . '"/><i class="fa fa-eye-slash wpf-show-password"></i>';
				$field_html .= ( $wrap ) ? $this->field_wrap( $args, $password_html ) : $password_html;
			}
			if( $template == 'register' && wpforo_feature('user-register-email-confirm') ){
				//If the option "User Registration with Email Confirmation" is enabled password fields should be removed on registration page.
			}
			else{
				if( $isConfirmPassword ) { $p1 = '1'; $p2 = '2'; } else{ $p1 = ''; $p2 = ''; } $fieldName = ( !empty($varname) ? $varname . '[' . $name . $p1 . ']' : $name . $p1 );
				if( $template == 'account' ) { $label = wpforo_phrase('New', false) . ' ' . $label; } $args['label'] = $label; $args['description'] = $description;
				$password_html = '<input '. $isRequired .' type="password" name="' . esc_attr($fieldName) . '" value="" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isDisabled . ' '.$attributes.'  ' . trim($minmax) . ' placeholder="' . esc_attr($placeholder) . '"/><i class="fa fa-eye-slash wpf-show-password"></i>';
				$field_html .= ( $wrap ) ? $this->field_wrap( $args, $password_html ) : $password_html;
				if( $isConfirmPassword ){ 
					$args['label'] = wpforo_phrase('Confirm Password', false); $args['description'] = '';
                    $fieldName = ( !empty($varname) ? $varname . '[' . $name . $p2 . ']' : $name . $p2 );
					$password_html = '<input '. $isRequired .' type="password" name="' . esc_attr($fieldName) . '" value="" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isDisabled . ' ' . $attributes . '  ' . trim($minmax) . ' placeholder="' . esc_attr($placeholder) . '"/><i class="fa fa-eye-slash wpf-show-password"></i>';
					$field_html .= ( $wrap ) ? $this->field_wrap( $args, $password_html ) : $password_html;
				}
			}
		}
        elseif( $type == 'file' ){
			$extensions = '';
			if( $fileExtensions ) {
				$fileExtensions = wpforo_parse_args($fileExtensions);
				foreach($fileExtensions as $key => $ext ){ if( strpos($ext, '.') === FALSE ) $fileExtensions[ $key ] = '.' . $ext; }
				$fileExtensions = implode(', ', $fileExtensions);
				if( $fileExtensions ) $extensions = 'accept="' .$fileExtensions . '"';
			}
			$field_html = '<input '. $isRequired .' type="file" name="' . esc_attr($fieldName) . '" value="" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isDisabled . ' ' . $attributes . ' ' . $extensions . ' />';
        }
        elseif( $type == 'checkbox' ){
            $step = 0;
			$field_html = '';
			$value = (is_serialized($value)) ? unserialize($value) : (array)$value;
            if( !is_array($values) ) $values = wpforo_string2array($values);
			if( !empty($values) ){
				foreach( $values as $v ){
					$v = trim($v);
					$data = explode('=>', $v);
					$item_value = isset($data[0]) ? $data[0] : 'no_value';
					$item_label = isset($data[1]) ? $data[1] : $item_value;
					$item_fieldid = $fieldId . '_' . ($step + 1);
					$item_fieldname = $fieldName . '[]';
					$checked = ( in_array($item_value, $value) ) ? 'checked="checked"' : '';
					$field_html .= '<div class="wpf-field-item">';
					$input_html = '<input type="checkbox" name="' . esc_attr($item_fieldname) . '" id="' . esc_attr($item_fieldid) . '" class="wpf-input-checkbox ' . esc_attr($class) . '" value="' . esc_attr($item_value) . '" ' . $checked . ' ' . $isDisabled . ' '.$attributes.' />';
					if ($isWrapItem) {
						$field_html .= '<label>';
						if ($isLabelFirst) { $field_html .= '<span class="wpf-checkbox-label">'. stripslashes($item_label) .'</span> ' . $input_html; } else { $field_html .= $input_html . ' <span class="wpf-checkbox-label">' . stripslashes($item_label) . '</span>'; }
						$field_html .= '</label>';
					} 
					else {
						if ($isLabelFirst) { $field_html .= '<span class="wpf-checkbox-label">' . stripslashes($item_label) . '</span> ' . $input_html; } else { $field_html .= $input_html . ' <span class="wpf-checkbox-label">' . stripslashes($item_label) . '</span>'; }
					}
					$field_html .= '</div>';
					$step++;
				}
			}
        }
        elseif( $type == 'radio' ){
            $step = 0;
            $field_html = '';
            if (!is_array($values)) $values = wpforo_string2array($values);
            if (!empty($values)) {
                $item_values = array();
                foreach ($values as $v) {
                    $v = trim($v);
                    $data = explode('=>', $v);
                    $item_value = $data[0];
                    $item_label = isset($data[1]) ? $data[1] : $item_value;
                    $item_fieldid = $fieldId . '_' . ($step + 1);
                    $attrs = ($isRequired) ? 'required="required"' : '';
                    $attrs .= ($item_value == $value) ? ' checked="checked"' : '';

                    $field_html .= '<div class="wpf-field-item">';
                    $input_html = '<input type="radio" name="' . esc_attr($fieldName) . '" id="' . esc_attr($item_fieldid) . '" class="wpf-input-radio ' . esc_attr($class) . '" value="' . esc_attr($item_value) . '" ' . $attrs . ' ' . $isDisabled . ' ' . $attributes . ' />';
                    if ($isWrapItem) {
                        $field_html .= '<label>';
                        if ($isLabelFirst) {
                            $field_html .= '<span class="wpf-radio-label">' . stripslashes($item_label) . '</span> ' . $input_html;
                        } else {
                            $field_html .= $input_html . ' <span class="wpf-radio-label">' . stripslashes($item_label) . '</span>';
                        }
                        $field_html .= '</label>';
                    } else {
                        if ($isLabelFirst) {
                            $field_html .= '<span class="wpf-radio-label">' . stripslashes($item_label) . '</span> ' . $input_html;
                        } else {
                            $field_html .= $input_html . ' <span class="wpf-radio-label">' . stripslashes($item_label) . '</span>';
                        }
                    }
                    $field_html .= '</div>';
                    $step++;
                }

            }
        }
        elseif( $type == 'select' ){
			
			$isMultiChoice = $isMultiChoice ? 'multiple="multiple"' : '';
			$field_html = '<select '. $isRequired .' name="' . esc_attr($fieldName) . '" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isMultiChoice . ' ' . $isDisabled . ' '.$attributes.'>';
			if(!$isRequired) $field_html .= '<option value="">' . wpforo_phrase('--- Choose ---', false) . '</option>';
			if( !empty($values) ){
				foreach ($values as $k => $v) {
				    if( is_array($v) ){
				        $field_html .= '<optgroup label="' . esc_attr($k) . '">';
				        foreach ($v as $_k => $_v){
                            $data = explode('=>', $_v);
                            $item_value = isset($data[0]) ? $data[0] : 'no_value';
                            $item_label = isset($data[1]) ? $data[1] : $item_value;
                            $value = stripslashes(htmlspecialchars($value));
                            $item_value = stripslashes(htmlspecialchars($item_value));
                            $selected = ( $item_value == $value ) ? 'selected="selected"' : '';
                            $field_html .= '<option value="' . esc_attr($item_value) . '" ' . $selected . '>' . stripslashes($item_label) . '</option>';
                        }
                        $field_html .= '</optgroup>';
                    }else{
                        $data = explode('=>', $v);
                        $item_value = isset($data[0]) ? $data[0] : 'no_value';
                        $item_label = isset($data[1]) ? $data[1] : $item_value;
                        $value = stripslashes(htmlspecialchars($value));
                        $item_value = stripslashes(htmlspecialchars($item_value));
                        $selected = ( $item_value == $value ) ? 'selected="selected"' : '';
                        $field_html .= '<option value="' . esc_attr($item_value) . '" ' . $selected . '>' . stripslashes($item_label) . '</option>';
                    }
				}
			}
			$field_html .= '</select>';
        }
		elseif ($type == 'usergroup') {
            $groupids = array();

            if ($allowedGroupIds) {
                if (!is_array($allowedGroupIds)) $allowedGroupIds = explode(',', trim($allowedGroupIds));
                $groupids = $allowedGroupIds;
            }
            if ( !WPF()->current_object['user_is_same_current_user'] && (WPF()->current_user_groupid == 1 || current_user_can('administrator') ) ) $groupids = WPF()->usergroup->get_usergroups('groupid');

            if( WPF()->current_object['user_is_same_current_user'] && !in_array(WPF()->current_user_groupid, $allowedGroupIds) ) $groupids = array();

            $groupids = array_filter($groupids);
            if( $groupids ){
                $field_html = '<select ' . $isRequired . ' name="' . esc_attr($fieldName) . '" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isDisabled . ' ' . $attributes . '>';
                if (!$isRequired) $field_html .= '<option value="">' . wpforo_phrase('--- Choose ---', false) . '</option>';
                foreach ($groupids as $groupid) {
                    if ( $group = WPF()->usergroup->get_usergroup($groupid) ) {
                        $selected = ($groupid == $value) ? 'selected="selected"' : '';
                        $field_html .= '<option value="' . esc_attr($groupid) . '" ' . $selected . '>' . $group['name'] . '</option>';
                    }
                }
                $field_html .= '</select>';
            }
        }
		elseif( $type == 'avatar' ){
			$field_html = '<ul>
				<li><input ' . $isRequired . ' name="' . esc_attr($varname) . '[avatar_type]" id="wpfat_gravatar" value="gravatar" ' . ( $value == '' || $value == NULL ? 'checked="checked"' : '' ) . ' type="radio" />&nbsp; <label for="wpfat_gravatar">' . wpforo_phrase('Wordpress avatar system', false) . '</label></li>
				<li><input name="' . esc_attr($varname) . '[avatar_type]" id="wpfat_remote" value="remote" ' . ( $value && strpos($value, 'wpforo/avatars') === FALSE ? 'checked="checked"' : '' ) . ' type="radio" />&nbsp; <label for="wpfat_remote">' . wpforo_phrase('Specify avatar by URL:', false) . '</label> <input autocomplete="off" name="' . esc_attr($varname) . '[avatar_url]" value="" maxlength="300" data-wpfucf-minmaxlength="1,300" type="url" /></li>';
				if( WPF()->perm->usergroup_can('upa') ) {
					if( strpos($value, 'gravatar.com') === FALSE && strpos($value, 'facebook.com') === FALSE ){
						$url = $value . '?lm=' . time();
					}
					$field_html .= '<li><input name="' . esc_attr($varname) . '[avatar_type]" id="wpfat_custom" value="custom" type="radio" ' . ( (strpos($url, 'wpforo/avatars') !== FALSE) ? 'checked' : '' ) . ' />&nbsp;<label for="wpfat_custom"> ' . wpforo_phrase('Upload an avatar', false) . '</label>' . ( strpos($url, 'wpforo/avatars') !== FALSE ? '<br /><img src="' . esc_url($url) . '" class="wpf-custom-avatar-img"/>' : '' ) .'&nbsp; <input class="wpf-custom-avatar" name="avatar" type="file" />&nbsp;</li>';
				}
			$field_html .= '</ul>
			<script type="text/javascript">jQuery(document).ready(function($){$( "input[name=\'member\[avatar_url\]\']" ).click(function(){$( "#wpfat_remote" ).prop(\'checked\', true);}); $( "input[name=\'avatar\']" ).click(function(){$( "#wpfat_custom" ).prop(\'checked\', true);});});</script>';
		}
		elseif( $type == 'html' ){
			$field_html = stripslashes($html);
		}
        elseif( $type == 'url' || $name == 'user_nicename' ){
          	$field_html = '<input ' . $isRequired . ' type="' . $type .'" value="' . esc_attr( urldecode($value) ) . '" name="' . esc_attr($fieldName) .'" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isDisabled . ' '.$attributes.' ' . trim($minmax) . ' placeholder="' . esc_attr($placeholder) . '"/>';
        }
        else{
          	$field_html = '<input ' . $isRequired . ' type="' . $type .'" value="' . esc_attr($value) . '" name="' . esc_attr($fieldName) .'" id="' . esc_attr($fieldId) . '" class="' . esc_attr($class) . '" ' . $isDisabled . ' '.$attributes.' ' . trim($minmax) . ' placeholder="' . esc_attr($placeholder) . '"/>';
        }

		if( $wrap && $type != 'password' && $field_html ) $field_html = $this->field_wrap( $args, $field_html );

        return $field_html;
		
    }


	public function field_wrap( $args, $field_html ){
		if( !is_array($args) || empty($args) ) return $field_html; extract( $args ); $field_wrap_html = ''; $is_owner = false; $rIcon = '';
		if( isset(WPF()->current_object['user']['ID']) ) { $is_owner = wpforo_is_owner( WPF()->current_object['user']['ID'] ); }
		$field_name_class = sanitize_text_field($name);
		if( $isRequired ) $rIcon = ' <span class="wpf-field-required-icon" title="' . esc_attr(wpforo_phrase('Required field', false))  . '">*</span>';
		$field_required_class = ( $isRequired ) ? 'wpf-field-required' : '';
		if( $template == 'register' ){
			$field_wrap_html .= '<div class="wpf-field wpf-field-type-' . esc_attr($type) . ' wpf-field-name-' . esc_attr($field_name_class) . ' ' . esc_attr($field_required_class) . '" title="' .  esc_attr($title) . '">';
			if( $type == 'html' ){
				$field_wrap_html .= $field_html;
			}
			else{
				if ( $label || $description ) {
					$field_wrap_html .= '<div class="wpf-label-wrap">';
					if ($label){ $field_wrap_html .= '<p class="wpf-label wpfcl-1">' . stripslashes($label) . $rIcon . '</p>'; }
					if ($description){ $field_wrap_html .= '<div class="wpf-desc wpfcl-2">' .  $description . '</div>'; }
					$field_wrap_html .= '</div>';
				}
				$field_wrap_html .= '<div class="wpf-field-wrap">';
				if($faIcon){ $field_wrap_html .= '<i class="fa ' .  esc_attr($faIcon) . ' wpf-field-icon"></i>'; } 
				$field_wrap_html .= $field_html;
				$field_wrap_html .= '</div>';
			}
			$field_wrap_html .= '<div class="wpf-field-cl"></div></div>';
		}
		elseif( $template == 'account' ){
			//if( !$isEditable && !$value ) return;
			if( isset(WPF()->current_user_groupid) && isset($canEdit) && !empty($canEdit) ){
				$canEdit = ( is_array($canEdit) ) ? $canEdit : array(1);
				if( !$is_owner && !in_array( WPF()->current_user_groupid, $canEdit )) return;
				if( $type == 'usergroup' && (!WPF()->current_user_groupid == 1 || !current_user_can('administrator'))) return;
				if( $type == 'avatar' && (!WPF()->perm->usergroup_can('va') || !wpforo_feature('custom-avatars') || !wpforo_feature('avatars'))) return;
				if( $name == 'signature' && (!WPF()->perm->usergroup_can('ups') || !wpforo_feature('signature'))) return;
				if( $name == 'user_login' ){ $description = ''; $faIcon = ''; $field_html = '<span class="wpf-username">' . $value . '</span>'; }
				if( !$isEditable && $name != 'user_login' ){ 
					$description = ''; 
					$field_html = '<span class="wpf-filed-value"><i class="fa ' .  esc_attr($faIcon) . '"></i> ' . $value . '</span>' . $field_html; 
					$faIcon = ''; 
				}
			}
			$field_wrap_html .= '<div class="wpf-field wpf-field-type-' . esc_attr($type) . ' wpf-field-name-' . esc_attr($field_name_class)  . ' ' . esc_attr($field_required_class) .  '" title="' .  esc_attr($title) . '">';
			if( $type == 'html' ){
				$field_wrap_html .= $field_html;
			}
			else{
				if ( $label || $description ) {
					$field_wrap_html .= '<div class="wpf-label-wrap">';
					if ($label){ $field_wrap_html .= '<p class="wpf-label wpfcl-1">' . stripslashes($label) . $rIcon . '</p>'; }
					if ($description){ $field_wrap_html .= '<div class="wpf-desc wpfcl-2">' .  $description . '</div>'; }
					$field_wrap_html .= '</div>';
				}
				$field_wrap_html .= '<div class="wpf-field-wrap">';
				if($faIcon){ $field_wrap_html .= '<i class="fa ' .  esc_attr($faIcon) . ' wpf-field-icon"></i>'; } 
				$field_wrap_html .= $field_html;

				switch ($type){
                    case 'file':
                        if( !empty($value) ) {
                            $wp_upload_dir = wp_upload_dir();
                            $value = $wp_upload_dir['baseurl'] . "/" . trim($value, '/');
                            $field_wrap_html .= '<br/>' . sprintf('<a href="%s" target="_blank">%s</a>', $value, basename($value));
                        }
                    break;
                }

				$field_wrap_html .= '</div>';
			}
			$field_wrap_html .= '<div class="wpf-field-cl"></div></div>';
		}
		elseif( $template == 'profile' ){
			if( !$is_owner && !in_array( WPF()->current_user_groupid, $canView ) ){ return ''; }
			if( $type != 'html' && (!isset($value) || (!is_numeric($value) && empty($value))) ){ return ''; }
			if( $type == 'textarea' ) $value = wpautop(wpforo_kses(stripslashes($value)));
			$field_wrap_html .= '<div class="wpf-field wpf-field-type-' . esc_attr($type) . ' wpf-field-name-' . esc_attr($field_name_class)  . ' ' . esc_attr($field_required_class) .  '" title="' .  esc_attr($title) . '">';
			if( $type == 'html' ){
				$field_wrap_html .= $field_html;
			}
			else{
				if( !$faIcon ) { $faIcon .= 'fa-address-card'; }
				if( $label ) { $field_wrap_html .= '<div class="wpf-label-wrap">'; if ($label){ $field_wrap_html .= '<p class="wpf-label wpfcl-1"><i class="fa ' .  esc_attr($faIcon) . ' wpf-field-icon"></i> ' . stripslashes($label) . '</p>'; } $field_wrap_html .= '</div>';}
				if( $faIcon ) { $field_wrap_html .= ''; } 
				if( isset($value) && !empty($value) ){
					if( is_array($value) ){
						$field_wrap_html .= esc_html(implode( ', ', $value));
					}
					else{
					    switch ($args['type']){
                            case 'url':
                                $value = sprintf('<a href="%s" target="_blank" rel="nofollow">%s</a>', $value, $value);
                            break;
                            case 'email':
                                $value = sprintf('<a href="mailto:%s" rel="nofollow">%s</a>', $value, $value);
                            break;
                            case 'phone':
                                $value = sprintf('<a href="tel:%s" rel="nofollow">%s</a>', $value, $value);
                            break;
                            case 'file':
                                if( !empty($value) ){
                                    $wp_upload_dir = wp_upload_dir();
                                    $value = $wp_upload_dir['baseurl'] . "/" . trim($value, '/');
                                    $value = sprintf('<a href="%s" target="_blank">%s</a>', $value, basename($value));
                                }
                            break;
                        }

                        switch ($args['name']){
                            case 'skype':
                                $value = sprintf('<a href="skype:%s?userinfo" rel="nofollow">%s</a>', $value, $value);
                            break;
                            case 'location':
                                $value = sprintf('<a href="//maps.google.com/?q=%s" target="_blank" rel="nofollow">%s</a>', $value, $value);
                            break;
							case 'signature':
                                $value = wpforo_signature( $value, array('echo' => 0) );
                            break;
							case 'about':
                                $value = wpforo_nofollow_tag( $value );
                            break;
                        }


						$field_wrap_html .= '<div class="wpf-field-wrap">';
						$field_wrap_html .= $value;
						$field_wrap_html .= '</div>';
					}
				}
			}
			$field_wrap_html .= '<div class="wpf-field-cl"></div></div>';
		}
		elseif( $template == 'members' ){
			$field_wrap_html .= '<div class="wpf-field wpf-field-type-' . esc_attr($type) . ' wpf-field-name-' . esc_attr($field_name_class)  . ' ' . esc_attr($field_required_class) .  '" title="' .  esc_attr($title) . '">';
			if( $type == 'html' ){
				$field_wrap_html .= $field_html;
			}
			else{
				if ( $label || $description ) {
					$field_wrap_html .= '<div class="wpf-label-wrap">';
					if ($label){ $field_wrap_html .= '<p class="wpf-label wpfcl-1">' .  stripslashes($label) . $rIcon . '</p>'; }
					if ($description){ $field_wrap_html .= '<div class="wpf-desc wpfcl-2">' .  $description . '</div>'; }
					$field_wrap_html .= '</div>';
				}
				$field_wrap_html .= '<div class="wpf-field-wrap">';
				if($faIcon){ 
					$field_wrap_html .= '<i class="fa ' .  esc_attr($faIcon) . ' wpf-field-icon"></i>'; 
				} 
				$field_wrap_html .= $field_html;
				$field_wrap_html .= '</div>';
			}
			$field_wrap_html .= '<div class="wpf-field-cl"></div></div>';
		}
		else{
            $field_wrap_html .= '<div class="wpf-field wpf-field-type-' . esc_attr($type) . ' wpf-field-name-' . esc_attr($field_name_class)  . ' ' . esc_attr($field_required_class) .  '" title="' .  esc_attr($title) . '">';
			$field_wrap_html .= '<div class="wpf-field-wrap">' . $field_html . '<div class="wpf-field-cl"></div></div>';
			$field_wrap_html .= '<div class="wpf-field-cl"></div></div>';
        }
		return $field_wrap_html;
	}
	
	
	public function form_fields( $fields ){
		if(empty($fields)) return '';
		$html = '';
		foreach ($fields as $row_key => $row){
            $rowClasses = "row-$row_key " . apply_filters('wpforo_row_classes', '', $row_key);
            $html .= '<div class="wpf-tr ' . esc_attr( $rowClasses ) . '">';
                foreach ( $row as $col_key => $col ){
                    $colClasses = "row_$row_key-col_$col_key " . apply_filters('wpforo_col_classes', '', $row_key, $col_key);
                    $html .= '<div class="wpf-td wpfw-' . count($row) . ' ' . esc_attr( $colClasses ) . '">';
                        foreach ( $col as $field ){
                            if( !empty($field) ) $html .= $this->field( $field );
                        }
                    $html .= '</div>';
                }
            $html .= '<div class="wpf-cl"></div></div>';
        }

		return $html;
	}

	public function forum_subscribe_link(){
	    if ( WPF()->current_userid || WPF()->current_user_email ): ?>
            <?php
            $args = array( "userid" => WPF()->current_userid, "itemid" => WPF()->current_object['forumid'], "type" => "forum", 'user_email' => WPF()->current_user_email );
            $subscribe = WPF()->sbscrb->get_subscribe( $args );
            if( isset( $subscribe['subid'] ) ): ?>
                <span class="wpf-unsubscribe-forum wpf-action" id="wpfsubscribe-<?php echo WPF()->current_object['forumid'] ?>"><?php wpforo_phrase('Unsubscribe') ?></span>
            <?php else: ?>
                <span class="wpf-subscribe-forum wpf-action" id="wpfsubscribe-<?php echo WPF()->current_object['forumid'] ?>"><?php wpforo_phrase('Subscribe for new topics') ?></span>
            <?php endif; ?>
        <?php endif;
    }

    public function topic_subscribe_link(){
        if ( WPF()->current_userid || WPF()->current_user_email ){
            $args = array( "userid" => WPF()->current_userid , "itemid" => WPF()->current_object['topicid'], "type" => "topic", 'user_email' => WPF()->current_user_email );
            $subscribe = WPF()->sbscrb->get_subscribe( $args );
            if( isset( $subscribe['subid'] ) ): ?>
                <span class="wpf-unsubscribe-topic wpf-action" id="wpfsubscribe-<?php echo WPF()->current_object['topicid'] ?>" ><?php wpforo_phrase('Unsubscribe') ?></span>
            <?php else: ?>
                <span class="wpf-subscribe-topic wpf-action" id="wpfsubscribe-<?php echo WPF()->current_object['topicid'] ?>"  ><?php wpforo_phrase('Subscribe for new replies') ?></span>
            <?php endif;
        }
    }

}