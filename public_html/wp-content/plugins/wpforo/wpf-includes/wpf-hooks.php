<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;


add_action( 'admin_init', 'wpforo_do_uninstall', 10);
function wpforo_do_uninstall(){
	if( isset($_GET['action']) && $_GET['action'] == 'wpforo-uninstall' ){
		if( check_admin_referer( 'wpforo_uninstall' ) && current_user_can('administrator') ) wpforo_uninstall();
		wp_redirect( admin_url( 'plugins.php' ) );
		exit();
	}
}

add_filter( 'plugin_action_links_' . WPFORO_BASENAME, 'wpforo_action_link', 10, 2 );
function wpforo_action_link( $links, $file ) {
	
	$uninstall_url = wp_nonce_url( admin_url( 'plugins.php?action=wpforo-uninstall' ), 'wpforo_uninstall' );
	
	$links[] = '<a href="'.esc_url( $uninstall_url ).'" style="color:#a00;" onclick="return confirm(\'' . __('IMPORTANT! Uninstall is not a simple deactivation action. This action will permanently remove all forum data (forums, topics, replies, attachments...) from database. Please backup database before this action, you may need this forum data in future. If you are sure that you want to delete all forum data please confirm. If not, just cancel it, then you can deactivate this plugin, that will not remove forum data.', 'wpforo').'\')">' . __( 'Uninstall', 'wpforo' ) . '</a>';

	$settings_link = '<a href="'.esc_url( admin_url( 'admin.php?page=wpforo-community' ) ).'">' . __( 'Settings', 'wpforo' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

function wpforo_notice_show(){
	WPF()->notice->show();
}
add_action( 'wpforo_top_hook', 'wpforo_notice_show', 10, 0 );

function wpforo_user_admin_bar(){
	if( !is_super_admin() && is_user_logged_in() ) show_admin_bar( wpforo_feature('user-admin-bar') );
}
add_action('init', 'wpforo_user_admin_bar');

function wpforo_admin_notice__menu_help(){
	if(strpos(wpforo_get_request_uri(), 'nav-menus.php') !== FALSE){

		$message = 'wpForo Menu Shortcodes<hr/><table>';
		foreach( WPF()->menu as $key => $value ){
			$message .= "<tr><td> " . $value['label'] . ": </td><td> /%$key%/ </td></tr>";
		}
		$message .= "<tr><td> " . wpforo_phrase('register', FALSE) . ": </td><td> /%wpforo-register%/ </td></tr>
			<tr><td> " . wpforo_phrase('login', FALSE) . ": </td><td> /%wpforo-login%/ </td></tr>
			</table>";
		
		$class = 'notice notice-warning';
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	}
}
//add_action( 'admin_notices', 'wpforo_admin_notice__menu_help' );

function wpforo_disable_comments( $open, $post_id ) {
	if(is_wpforo_page()) return FALSE; 
	return $open;
}
add_filter( 'comments_open', 'wpforo_disable_comments', 10, 2 );

function wpforo_disable_comments_hide_existing_comments($comments) {
	if(is_wpforo_page()) return array();
	return $comments;
}
add_filter('comments_array', 'wpforo_disable_comments_hide_existing_comments', 10, 2);

function wpforo_remove_comment_support() {
	if(is_wpforo_page()){
   	 	remove_post_type_support( 'post', 'comments' );
	    remove_post_type_support( 'page', 'comments' );
	}
}
add_action('init', 'wpforo_remove_comment_support', 100);

function wpforo_change_author_default_page( $link ){
	if(!wpforo_feature('author-link')) return $link;
	return WPF()->member->get_profile_url($link);
}
function wpforo_change_comment_author_default_page( $link, $ID = 0, $object = NULL ){
	if(!wpforo_feature('comment-author-link')) return $link;
	if(!isset($link) || !$link){
		if(isset($object->user_id) && $object->user_id){
			return WPF()->member->get_profile_url($object->user_id);
		}
	}
	return $link;
}
add_filter( 'author_link', 'wpforo_change_author_default_page', 10, 1 );	 	 
add_filter( 'get_comment_author_url', 'wpforo_change_comment_author_default_page', 10, 3 );

function wpforo_change_default_register_page( $register_url ) {
    if(!wpforo_feature('register-url')) return $register_url;
	return wpforo_home_url('?wpforo=signup');
}
add_filter( 'register_url', 'wpforo_change_default_register_page' );

function  wpforo_change_default_login_page( $login_url, $redirect ) {
    if(!wpforo_feature('login-url')) return $login_url;
	return wpforo_home_url('?wpforo=signin');
}
add_filter( 'login_url', 'wpforo_change_default_login_page', 10, 2 );

function wpftpl( $filename ){
	$find = array();
	if ( $filename ) {
		$find[] = 'wpforo/'. $filename;
		$template = locate_template( array_unique( $find ) );
		if ( !$template ) $template = WPFORO_THEME_DIR . '/'. WPF()->tpl->theme .'/' . $filename;

		return apply_filters('wpforo_wpftpl', $template);
	}
}

function wpforo_init_template(){
	if(wpforo_is_admin()) return;
	include_once( wpftpl('index.php') );
}

add_shortcode( 'wpforo', 'wpforo_load' );
function wpforo_load( $atts ){
	if(wpforo_is_admin()) return '';

	if( is_wpforo_shortcode_page() ){
		$url = wpforo_home_url();
		
		$args = shortcode_atts( array(
	        'item' => 'forum',
	        'id' => 0,
	        'slug' => '',
	    ), $atts );
	    
	    if( $args['id'] || $args['slug'] ){
	    	$getid = ( $args['slug'] ? $args['slug'] : $args['id'] );
		    if( $args['item'] == 'topic' ){
				$url = WPF()->topic->get_topic_url($getid);
			}elseif( $args['item'] == 'profile' ){
				$url = WPF()->member->get_profile_url($getid);
			}else{
				$url = WPF()->forum->get_forum_url($getid);
			}
		}
		
		WPF()->init_current_object($url);
		WPF()->tpl->init_nav_menu();
	}
	
	if(wpforo_feature('output-buffer') && function_exists('ob_start')){
		if( wpforo_feature('html_cashe') ){
			$html = WPF()->cache->get_html(); if( $html ) return $html;
		}
		ob_start();
		wpforo_init_template();
		$output = ob_get_clean();
		WPF()->cache->html($output);
		return $output;	
	}
	else{
		wpforo_init_template();
	}
	
}

function wpforo_template_include($template){
	if( is_wpforo_page() && !is_wpforo_shortcode_page() && ($wpforo_template = wpftpl('index.php')) ){
		return $wpforo_template;
	} 
	return $template;
}

add_action('wp', 'wpforo_set_header_status');
function wpforo_set_header_status(){
	if( is_wpforo_page() ){
		global $wp_query;

        $status = ( WPF()->current_object['is_404'] ? 404 : 200 );
        status_header( $status );
        $wp_query->is_404 = FALSE;
    }
}

function wpforo_do_rewrite(){
    if( is_wpforo_page() ){
    	if( WPF()->use_home_url ){
			add_rewrite_rule( '(.*)', 'index.php?page_id=' . WPF()->pageid, 'top');
    		add_filter('template_include', 'wpforo_template_include');
    	}
    }
}
add_action('setup_theme', 'wpforo_do_rewrite');

function wpforo_rewrite_rules_array($rules){
	$permastruct = utf8_uri_encode( WPF()->permastruct );
	$permastruct = preg_replace('#^/?index\.php/?#isu', '', $permastruct);
	$permastruct = trim($permastruct, '/');
	$pattern = '('.preg_quote($permastruct).'(?:/|$).*)$';
	$to_url = 'index.php?page_id=' . WPF()->pageid;
	if( !WPF()->use_home_url && !in_array($to_url, $rules) ) $rules = array_merge( array($pattern => $to_url), $rules );

	return $rules;
}
add_filter( 'rewrite_rules_array', 'wpforo_rewrite_rules_array' );

function wpforo_theme_functions(){
	$path = wpftpl('functions.php');
	if( file_exists($path) ){ 
		include_once($path);
	}
}
add_action('init', 'wpforo_theme_functions');

function wpforo_theme_functions_wp(){
	$path = wpftpl('functions-wp.php');
	if( file_exists($path) ){ 
		include_once($path);
	}
}
add_action('after_setup_theme', 'wpforo_theme_functions_wp');

function wpforo_meta_title($title) {
	$is404 = false;
	$meta_title = array();
	
	if(!wpforo_feature('seo-title')) return $title;
	
	if(is_wpforo_page()){
		$template = WPF()->current_object['template'];
		if( ($template == 'post' && WPF()->current_object['topicid'] == 0) ||
			($template == 'topic' && WPF()->current_object['forumid'] == 0) ||
			($template == 'profile' && WPF()->current_object['userid'] == 0) ){
			$is404 = true;
		}
		if(!$is404){
			$paged = ( WPF()->current_object['paged'] > 1 ) ? ' - ' .  wpforo_phrase( 'page', false) . ' ' . WPF()->current_object['paged'] .' ' : '';
			if(!empty(WPF()->current_object['forum'])) $forum = WPF()->current_object['forum'];
			if(!empty(WPF()->current_object['topic'])) $topic = WPF()->current_object['topic'];
			if(!empty(WPF()->current_object['user'])) $user = WPF()->current_object['user'];
			if(isset($topic['title']) && isset($forum['title']) && isset(WPF()->general_options['title'])){
				$meta_title = array($topic['title'] . $paged, $forum['title'], WPF()->general_options['title']);
			}
			elseif(!isset($topic['title']) && isset($forum['title']) && isset(WPF()->general_options['title'])){
				$meta_title = array($forum['title'] . $paged, WPF()->general_options['title']);
			}
			elseif( $template != 'forum' && $template != 'topic' && $template != 'post' ){
				if( $template == 'profile' || $template == 'account' || $template == 'activity' || $template == 'subscriptions' ){
					if(isset($user['display_name'])){
						$meta_title = array($user['display_name'], wpforo_phrase( ucfirst($template), false), WPF()->general_options['title']);
					}
					elseif(isset(WPF()->current_object['user_nicename'])){
						$meta_title = array(WPF()->current_object['user_nicename'], wpforo_phrase( ucfirst($template), false), WPF()->general_options['title']);
					}
					else{
						$meta_title = array(wpforo_phrase( 'Member', false), wpforo_phrase( ucfirst($template), false), WPF()->general_options['title']);
					}
				}
				elseif( $template == 'recent' ){
					$wpfpaged = ( isset($_GET['wpfpaged']) && $_GET['wpfpaged'] > 1 ) ? ' - ' .  wpforo_phrase( 'page', false) . ' ' . $_GET['wpfpaged'] .' ' : '';
					$meta_title = array( wpforo_phrase( 'Recent Posts', false) . $wpfpaged, WPF()->general_options['title']);
				}
				elseif($template){
					$wpfpaged = ( isset($_GET['wpfpaged']) && $_GET['wpfpaged'] > 1 ) ? ' - ' .  wpforo_phrase( 'page', false) . ' ' . $_GET['wpfpaged'] .' ' : '';
					$meta_title = array(wpforo_phrase( ucfirst($template), false) . $wpfpaged, WPF()->general_options['title']);
				}
				elseif($title){
					$meta_title = (is_array($title)) ? $title : array($title);
				}
				else{
					$meta_title = array(wpforo_phrase('Forum', false), get_bloginfo('name'));
				}
			}
			elseif( isset(WPF()->general_options['title']) && WPF()->general_options['title'] ){
				$meta_title = array(WPF()->general_options['title'], get_bloginfo('name'));
			}
			elseif($title){
				$meta_title = (is_array($title)) ? $title : array($title);
			}
			else{
				$meta_title = array(wpforo_phrase('Forum', false), get_bloginfo('name'));
			}
		}
		else{
			$meta_title = array(wpforo_phrase( '404 - Page not found', false), WPF()->general_options['title']);
		}
	}
	if(!empty($meta_title)) {
		return $meta_title;
	}
	else{
		return $title;
	}
}
add_filter('document_title_parts', 'wpforo_meta_title', 100);

function wpforo_meta_wp_title($title){
	if(!wpforo_feature('seo-title')) return $title;
	$meta_title = wpforo_meta_title($title);
	if(is_array($meta_title) && !empty($meta_title)){
		$title = implode(' &#8211; ', $meta_title);
	}
	return $title;
}
add_filter( 'wp_title', 'wpforo_meta_wp_title', 100);

function wpforo_add_meta_tags(){
	if(!wpforo_feature('seo-meta')) return;
	
	if(is_wpforo_page()){
		$title = '';
		$og_img = '';
		$noindex = '';
		$template = '';
		$description = '';
		$udata = array();
		$canonical = wpforo_get_request_uri();
		$noindex_urls = WPF()->tools_misc['noindex'];
		if(!empty($noindex_urls)){
			$noindex_urls = explode("\n", $noindex_urls); 
			if(!empty($noindex_urls)){ 
				$noindex_urls = array_map("trim", $noindex_urls);
				foreach( $noindex_urls as $noindex_url){ 
					$noindex_url = strtok($noindex_url, "#"); 
					if( $canonical == $noindex_url ) { 
						$noindex = "<meta name=\"robots\" content=\"noindex\">\r\n"; break; 
					} 
				}
			}
		}
		$paged = ( WPF()->current_object['paged'] > 1 ) ? wpforo_phrase( 'page', false) . ' ' . WPF()->current_object['paged'] .' | ' : '';
		if(isset(WPF()->current_object['template'])) $template = WPF()->current_object['template'];
		if(!empty(WPF()->current_object['forum'])) $forum = WPF()->current_object['forum'];
		if(!empty(WPF()->current_object['topic'])) $topic = WPF()->current_object['topic'];
		if(!empty(WPF()->current_object['user'])) $user = WPF()->current_object['user'];
		if(isset(WPF()->current_object)){
			if( isset(WPF()->current_object['forumid']) && !isset(WPF()->current_object['topicid']) ){
				if(isset($forum['title'])) $title = $forum['title'];
				if(isset(WPF()->current_object['forum_meta_desc']) && WPF()->current_object['forum_meta_desc'] !=''){
					$description = $paged . WPF()->current_object['forum_meta_desc'];
				}
				elseif(isset(WPF()->current_object['forum_desc']) && WPF()->current_object['forum_desc'] !=''){
					$description = $paged . WPF()->current_object['forum_desc'];
				}
			}elseif( isset(WPF()->current_object['topicid']) && isset($topic['first_postid']) ){
				$post = WPF()->post->get_post($topic['first_postid']);
				$image = wpforo_get_image_url($post['body']);
				if($image) $og_img = '<meta property="og:image" content="' . $image . '" />' . "\r\n";
				if(isset($post['title'])) $title = wpforo_text($paged . $post['title'], 60, false);
				if(isset($post['body'])) $description = wpforo_text($paged . $post['body'], 150, false);
			}elseif( $template == 'profile' || $template == 'account' || $template == 'activity' || $template == 'subscriptions' ){
				if( isset(WPF()->general_options['title']) ) $title = $paged . WPF()->general_options['title'];
				$udata['name'] = (isset($user['display_name']) && $user['display_name']) ? wpforo_phrase( 'User', false ) . ': ' . $user['display_name'] : '';
				$udata['title'] = (isset($user['stat']['title']) && $user['stat']['title']) ?  wpforo_phrase( 'Title', false ) . ': ' . $user['stat']['title'] : '';
				$udata['about'] = (isset($user['about']) && $user['about']) ? wpforo_phrase( 'About', false ) . ': ' . wpforo_text($user['about'], 150, false) : '';
				$description =  $title . ' - ' . wpforo_phrase('Member Profile', false) . ' &gt; ' . wpforo_phrase( ucfirst($template), false ) . ' ' . wpforo_phrase( 'Page', false ) . '. ' . implode(', ', $udata);
				if(!wpforo_feature('seo-profile')){ $noindex = "<meta name=\"robots\" content=\"noindex\">\r\n"; }
			}elseif(isset(WPF()->current_object['template']) && WPF()->current_object['template'] == 'member'){
				$wpfpaged = ( isset($_GET['wpfpaged']) && $_GET['wpfpaged'] > 1 ) ? wpforo_phrase( 'Page', false) . ' ' . $_GET['wpfpaged'] .' | ' : '';
				$description = $wpfpaged . wpforo_phrase( 'Forum Members List', false);
			}elseif(isset(WPF()->current_object['template']) && WPF()->current_object['template'] == 'recent'){
				$wpfpaged = ( isset($_GET['wpfpaged']) && $_GET['wpfpaged'] > 1 ) ? wpforo_phrase( 'Page', false) . ' ' . $_GET['wpfpaged'] .' | ' : '';
				$description = $wpfpaged . wpforo_phrase( 'Recent Posts', false);
			}
			else{
				if( isset(WPF()->general_options['title']) ) $title = $paged . WPF()->general_options['title'];
				if( isset(WPF()->general_options['description']) ) $description = $paged . WPF()->general_options['description'];
				if(isset(WPF()->current_object['template']) && ( WPF()->current_object['template'] == 'login' || WPF()->current_object['template'] == 'register' ) ){ 
					$noindex = "<meta name=\"robots\" content=\"noindex\">\r\n"; 
				}
			}
			$description = preg_replace('#[\t\r\n]+#isu', ' ', $description);
			echo "\r\n<!-- wpForo SEO -->\r\n" . $noindex . "<link rel=\"canonical\" href=\"".$canonical."\" />\r\n<meta name=\"description\" content=\"" . esc_html($description) . "\" />\r\n<meta property=\"og:title\" content=\"" . esc_html($title) . "\" />\r\n<meta property=\"og:description\" content=\"" . esc_html($description) . "\" />\r\n<meta property=\"og:url\" content=\"" . $canonical . "\" />\r\n". $og_img . "<meta property=\"og:site_name\" content=\"" . get_bloginfo('name') . "\" />\r\n<meta name=\"twitter:description\" content=\"" . esc_html($description) . "\"/>\r\n<meta name=\"twitter:title\" content=\"" . esc_html($title) . "\" />\r\n<!-- wpForo SEO End -->\r\n\r\n";
		}
	}
}
add_action('wp_head', 'wpforo_add_meta_tags', 1);


add_action('wp_ajax_wpforo_like_ajax', 'wpf_like');

function wpf_like(){
	$response = array('stat' => 0, 'likers' => '', 'notice' => WPF()->notice->get_notices());
	if( !is_user_logged_in() ){
		WPF()->notice->add( sprintf( wpforo_phrase('Please %s or %s', FALSE), '<a href="' . wpforo_login_url() . '">'.wpforo_phrase('Login', FALSE).'</a>', '<a href="' . wpforo_register_url() . '">'.wpforo_phrase('Register', FALSE).'</a>' ) );
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( !isset($_POST['likestatus']) || !isset($_POST['postid']) || !($postid = intval($_POST['postid'])) ){
		WPF()->notice->add('action error', 'error');
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( !$post = WPF()->post->get_post( $postid ) ){
		WPF()->notice->add('post not found', 'error');
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( !WPF()->perm->forum_can( 'l', $post['forumid']) ){
		WPF()->notice->add('You don\t have permission to like posts from this forum', 'error');
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( $_POST['likestatus'] ){
		if( WPF()->db->insert(
			WPF()->db->prefix . 'wpforo_likes',
			array(
				'postid'	=> $postid, 
				'userid' 	=>  WPF()->current_userid,
				'post_userid' 	=> $post['userid']
			), 
			array('%d','%d','%d')
		) ){
			wpforo_clean_cache($postid, 'post-soft');
			do_action('wpforo_like', $post, WPF()->current_userid);
			WPF()->notice->add('done', 'success');
			$response['stat'] = 1;
			$response['notice'] = WPF()->notice->get_notices();
		}
	}else{
		if( WPF()->db->delete(
			WPF()->db->prefix . 'wpforo_likes',
			array(
				'postid'	=> $postid, 
				'userid' 	=>  WPF()->current_userid
			), 
			array('%d','%d')
		) ){
			wpforo_clean_cache($postid, 'post-soft');
			do_action('wpforo_dislike', $post, WPF()->current_userid);
			WPF()->notice->add('done', 'success');
			$response['stat'] = 1;
			$response['notice'] = WPF()->notice->get_notices();
		}
	}
	if(!isset($post['userid'])) WPF()->member->reset($post['userid']);
	if(!isset(WPF()->current_userid)) WPF()->member->reset(WPF()->current_userid);
	$response['likers'] = WPF()->tpl->likers($postid);
	echo json_encode($response);
	exit();
}


add_action('wp_ajax_wpforo_vote_ajax', 'wpf_vote');
function wpf_vote(){
	
	if(!is_user_logged_in()) return;
	
	if( !isset($_POST['postid']) || !$_POST['postid'] ){
		WPF()->notice->add('Wrong post data', 'error');
		echo json_encode(array('stat' => 0, 'notice' => WPF()->notice->get_notices()));
		exit();
	}

    $reaction = 1;
    if( $_POST['votestatus'] == 'down' ) $reaction = -1;

	if( WPF()->db->get_var( "SELECT `voteid` FROM `".WPF()->db->prefix."wpforo_votes` WHERE `postid` = " . wpforo_bigintval($_POST['postid']) . " AND `userid` = " . wpforo_bigintval(WPF()->current_userid) . " AND `reaction` = '" . $reaction . "'" )){
		WPF()->notice->add('You are already voted this post');
		echo json_encode(array('stat' => 0, 'notice' => WPF()->notice->get_notices()));
		exit();
	}else{
	    WPF()->db->delete(
        WPF()->db->prefix . 'wpforo_votes',
            array( 'postid' => $_POST['postid'], 'userid' => WPF()->current_userid ),
            array('%d', '%d')
        );
    }
	
	$postid = wpforo_bigintval($_POST['postid']);
	$post = WPF()->post->get_post( $postid );
	
	$voted = WPF()->db->insert(
		WPF()->db->prefix . 'wpforo_votes',
		array(
			'postid'	=> $postid, 
			'userid' 	=>  WPF()->current_userid,
			'reaction' 	=>  $reaction,
			'post_userid' 	=>  $post['userid']
		), 
		array( 
			'%d', 
			'%d',
			'%d',
			'%d'
		)
	);	
	
	if(!isset($post['userid'])) WPF()->member->reset($post['userid']);
	if(!isset(WPF()->current_userid)) WPF()->member->reset(WPF()->current_userid);
	
	if( $voted !== FALSE ){
        $incr = $incr2 = true;

		if( $_POST['itemtype'] == 'topic' ){
			$incr = WPF()->db->query( "UPDATE ".WPF()->db->prefix . 'wpforo_topics'." SET `votes` = `votes` + $reaction  WHERE topicid = " . wpforo_bigintval($post['topicid']) );
		}
        $incr2 = WPF()->db->query( "UPDATE ".WPF()->db->prefix . 'wpforo_posts'." SET `votes` = `votes` + $reaction  WHERE postid = " . wpforo_bigintval($post['postid']) );

        if($incr !== FALSE && $incr2 !== FALSE){
			wpforo_clean_cache($postid, 'post', $post);
			do_action('wpforo_vote', $reaction, $post, WPF()->current_userid );
			WPF()->notice->add('Successfully voted', 'success');
			echo json_encode(array('stat' => 1, 'notice' => WPF()->notice->get_notices()));
			exit();
		}
	}
	
	WPF()->notice->add('Wrong post data', 'error');
	echo json_encode(array('stat' => 0, 'notice' => WPF()->notice->get_notices()));
	exit();
}

add_action('wp_ajax_wpforo_answer_ajax', 'wpf_answer');
function wpf_answer(){
	$response = array('stat' => 0, 'notice' => WPF()->notice->get_notices());
	if(!is_user_logged_in()){
		WPF()->notice->add( sprintf( wpforo_phrase('Please %s or %s', FALSE), '<a href="' . wpforo_login_url() . '">'.wpforo_phrase('Login', FALSE).'</a>', '<a href="' . wpforo_register_url() . '">'.wpforo_phrase('Register', FALSE).'</a>' ) );
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( !isset($_POST['answerstatus']) || !isset($_POST['postid']) || !$postid = intval($_POST['postid']) ){
		WPF()->notice->add('action error', 'error');
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( !$post = WPF()->post->get_post( $postid ) ){
		WPF()->notice->add('post not found', 'error');
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( !$topic = WPF()->topic->get_topic( $post['topicid'] ) ){
		WPF()->notice->add('topic not found', 'error');
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( !(WPF()->perm->forum_can( 'at', $post['forumid'] ) ||  ( WPF()->perm->forum_can( 'oat', $post['forumid']) && WPF()->current_userid == $topic['userid'] ) ) ){
		WPF()->notice->add('You don\t have permission to make topic answered', 'error');
		$response['notice'] = WPF()->notice->get_notices();
		echo json_encode($response);
		exit();
	}
	if( FALSE !== WPF()->db->query( "UPDATE ".WPF()->db->prefix ."wpforo_posts SET is_answer = ".intval($_POST['answerstatus'])." WHERE postid = " . intval($postid) ) ){
		wpforo_clean_cache($postid, 'post', $post);
		do_action('wpforo_answer', intval($_POST['answerstatus']), $post);
		WPF()->notice->add('done', 'success');
		$response['stat'] = 1;
		$response['notice'] = WPF()->notice->get_notices();
	}
	echo json_encode($response);
	exit();
}

add_action('wp_ajax_wpforo_quote_ajax', 'wpf_quote');
add_action('wp_ajax_nopriv_wpforo_quote_ajax', 'wpf_quote' );
function wpf_quote(){
	$post  = WPF()->db->get_row('SELECT `userid`, `name`, `email`, `body` FROM '.WPF()->db->prefix.'wpforo_posts WHERE postid =' . intval($_POST['postid']), ARRAY_A);
	if( !WPF()->perm->forum_can( 'cr', $post['forumid']) ) return;
	$post = apply_filters('wpforo_quote_post_ajax', $post);
	$poster = wpforo_member( $post );
	echo '<blockquote><div class="wpforo-post-quote-author"><strong>' . wpforo_phrase('Posted by', FALSE) . ': ' . ( $poster['display_name'] ? esc_textarea($poster['display_name']) : esc_textarea($poster['user_login']) ) . '</strong></div>' . wpautop($post['body']) . '</blockquote><br />';
	exit();
}

add_action('wp_ajax_wpforo_report_ajax', 'wpf_report');
function wpf_report(){
	
	if(!is_user_logged_in()) return;
	
	if( !isset($_POST['reportmsg']) || !$_POST['reportmsg'] || !isset($_POST['postid']) || !$_POST['postid'] ){
		WPF()->notice->add('Error: please insert some text to report.', 'error');
		echo json_encode( WPF()->notice->get_notices() );
		exit();
	}
	
	############### Sending Email  ##################
		$report_text = substr($_POST['reportmsg'], 0, 1000);
		$postid = intval($_POST['postid']);
		$reporter = '<a href="'.WPF()->current_user['profile_url'].'">'.(WPF()->current_user['display_name'] ? WPF()->current_user['display_name'] : urldecode(WPF()->current_user['user_nicename'])).'</a>';
		$reportmsg = wpforo_kses($report_text, 'email');
		$post_url = '<a target="_blank" href="'. esc_attr(WPF()->post->get_post_url($postid)).'">' . wpforo_phrase('Post link', false) . '&raquo;</a>';
		
		$subject = WPF()->sbscrb->options['report_email_subject'];
		$message = WPF()->sbscrb->options['report_email_message'];
		
		$from_tags = array("[reporter]", "[message]", "[post_url]");
		$to_words   = array(sanitize_text_field($reporter), $reportmsg, $post_url);
		
		$subject = stripslashes(strip_tags(str_replace($from_tags, $to_words, $subject)));
		$message = stripslashes(str_replace($from_tags, $to_words, $message));
		
		$admin_email = get_option( 'admin_email' );
		$admin_emails = WPF()->sbscrb->options['admin_emails'];
		$admin_emails = trim($admin_emails);
		$admin_emails = explode(',', $admin_emails);
		$admin_emails = array_map('sanitize_email', $admin_emails);
		$admin_email = (isset($admin_emails[0]) && $admin_emails[0]) ? $admin_emails[0] : $admin_email;
		$headers = wpforo_admin_mail_headers();
		
		add_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
		if( wp_mail( $admin_email, $subject, $message, $headers ) ){
			remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
		}else{
			remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
			WPF()->notice->add('Can\'t send report email', 'error');
			echo json_encode( WPF()->notice->get_notices() );
			exit();
		}
		
	############### Sending Email end  ##############
	WPF()->notice->add('Message has been sent', 'success');
	echo json_encode( WPF()->notice->get_notices() );
	exit();
}

add_action('wp_ajax_wpforo_sticky_ajax', 'wpf_sticky');
add_action('wp_ajax_nopriv_wpforo_sticky_ajax', 'wpf_sticky' );
function wpf_sticky(){
	if( !isset($_POST['postid']) || !( $p_id = intval($_POST['postid']) ) ){ echo 0; exit(); }
	$topic  = WPF()->db->get_row('SELECT `forumid` FROM '.WPF()->db->prefix.'wpforo_topics WHERE topicid =' . intval($p_id), ARRAY_A);
	if( !WPF()->perm->forum_can( 's', $topic['forumid']) ) return;
	if( $_POST['status'] == 'sticky' ){
		$sql = "UPDATE " . WPF()->db->prefix . "wpforo_topics SET type = 1 WHERE topicid = " . intval($p_id);
		WPF()->db->query( $sql );
	}elseif( $_POST['status'] == 'unsticky' ){
		$sql = "UPDATE ".WPF()->db->prefix ."wpforo_topics SET type = 0 WHERE topicid = " . intval($p_id);
		WPF()->db->query( $sql );
	}
	wpforo_clean_cache($p_id, 'topic');
	echo 1;
	exit();
}

add_action('wp_ajax_wpforo_private_ajax', 'wpf_private');
function wpf_private(){
	if(!is_user_logged_in()) return;
	if( !isset($_POST['postid']) || !( $p_id = intval($_POST['postid']) ) ){ echo 0; exit(); }
	if( $_POST['status'] == 'private' ){
		$sql = "UPDATE " . WPF()->db->prefix . "wpforo_topics SET private = 1 WHERE topicid = " . intval($p_id);
		WPF()->db->query( $sql );
		$sql = "UPDATE " . WPF()->db->prefix . "wpforo_posts SET private = 1 WHERE topicid = " . intval($p_id);
		WPF()->db->query( $sql );
	}elseif( $_POST['status'] == 'public' ){
		$sql = "UPDATE ".WPF()->db->prefix ."wpforo_topics SET private = 0 WHERE topicid = " . intval($p_id);
		WPF()->db->query( $sql );
		$sql = "UPDATE ".WPF()->db->prefix ."wpforo_posts SET private = 0 WHERE topicid = " . intval($p_id);
		WPF()->db->query( $sql );
	}
	wpforo_clean_cache();
	echo 1;
	exit();
}

add_action('wp_ajax_wpforo_solved_ajax', 'wpf_solved');
add_action('wp_ajax_nopriv_wpforo_solved_ajax', 'wpf_solved' );
function wpf_solved(){
	if( !isset($_POST['postid']) || !( $p_id = intval($_POST['postid']) ) ){ echo 0; exit(); }
	$post  = WPF()->post->get_post($_POST['postid']);
	if( WPF()->perm->forum_can( 'sv', $post['forumid']) || WPF()->perm->forum_can( 'osv', $post['forumid']) ){
		if( $_POST['status'] == 'solved' ){
			$sql = "UPDATE " . WPF()->db->prefix . "wpforo_posts SET is_answer = 1 WHERE postid = " . intval($p_id);
			WPF()->db->query( $sql );
		}elseif( $_POST['status'] == 'unsolved' ){
			$sql = "UPDATE ".WPF()->db->prefix ."wpforo_posts SET is_answer = 0 WHERE postid = " . intval($p_id);
			WPF()->db->query( $sql );
		}
		if( isset($post['topicid']) && $post['topicid'] ) wpforo_clean_cache($post['topicid'], 'topic');
		echo 1;
		exit();
	}else{
		 return;
	}
}

add_action('wp_ajax_wpforo_approve_ajax', 'wpf_approved');
function wpf_approved(){
    if(!is_user_logged_in()) return;

    if( !isset($_POST['postid']) || !( $p_id = intval($_POST['postid']) ) ){ echo 0; exit(); }
    if( $_POST['status'] == 'approve' ){
        $sql = "UPDATE " . WPF()->db->prefix . "wpforo_posts SET status = 0 WHERE postid = " . intval($p_id);
        WPF()->db->query( $sql );
        $sql = "SELECT is_first_post FROM " . WPF()->db->prefix . "wpforo_posts WHERE `postid` = " . intval($p_id);
        $is_first_post = WPF()->db->get_var($sql);
        if( $is_first_post ){
            $sql = "UPDATE " . WPF()->db->prefix . "wpforo_topics SET status = 0 WHERE first_postid = " . intval($p_id);
            WPF()->db->query($sql);
        }
    }elseif( $_POST['status'] == 'unapprove' ){
        $sql = "UPDATE ".WPF()->db->prefix ."wpforo_posts SET status = 1 WHERE postid = " . intval($p_id);
        WPF()->db->query( $sql );
        $sql = "SELECT is_first_post FROM " . WPF()->db->prefix . "wpforo_posts WHERE postid = " . intval($p_id);
        $is_first_post = WPF()->db->get_var($sql);
        if( $is_first_post ){
            $sql = "UPDATE " . WPF()->db->prefix . "wpforo_topics SET status = 1 WHERE first_postid = " . intval($p_id);
            WPF()->db->query($sql);
        }
    }
	wpforo_clean_cache($p_id, 'post');
    echo 1;
    exit();
}

add_action('wp_ajax_wpforo_close_ajax', 'wpf_close');
function wpf_close(){
	if(!is_user_logged_in()) return;
	
	if( !isset($_POST['postid']) || !( $p_id = intval($_POST['postid']) ) ){ echo 0; exit(); }
	if( $_POST['status'] == 'closed' ){
		$sql = "UPDATE ".WPF()->db->prefix ."wpforo_topics SET closed = 0 WHERE topicid = " . intval($p_id);
		WPF()->db->query( $sql );
		wpforo_clean_cache($p_id, 'topic');
	}elseif( $_POST['status'] == 'close' ){
		$sql = "UPDATE ".WPF()->db->prefix ."wpforo_topics SET closed = 1 WHERE topicid = " . intval($p_id);
		WPF()->db->query( $sql );
		wpforo_clean_cache($p_id, 'topic');
		echo 1;
		exit();
	}
	echo WPF()->topic->get_topic_url($p_id);
	exit();
}

add_action('wp_ajax_wpforo_edit_ajax', 'wpf_edit');
add_action('wp_ajax_nopriv_wpforo_edit_ajax', 'wpf_edit' );
function wpf_edit(){
	
	if( !isset($_POST['postid']) || !$_POST['postid'] ){ echo 0; exit(); }
	$sql = 'SELECT t.forumid AS forumid, t.title AS topic_title, p.title AS post_title, p.`body` 
      FROM '.WPF()->db->prefix.'wpforo_posts p 
      INNER JOIN '.WPF()->db->prefix.'wpforo_topics t ON t.topicid = p.topicid 
      WHERE p.postid =' . intval($_POST['postid']);
	if($post = WPF()->db->get_row($sql, ARRAY_A) ){
		if( WPF()->perm->forum_can('eor', $post['forumid']) || WPF()->perm->forum_can('eot', $post['forumid']) ){
			$post = apply_filters('wpforo_edit_post_ajax', $post);
			$post['body'] = wpautop($post['body']);
			echo json_encode($post);
			exit();
		}
	}
	echo 0;
	exit();
}

add_action('wp_ajax_wpforo_delete_ajax', 'wpf_delete');
function wpf_delete(){
	if(!is_user_logged_in()) return;
	
	$resp = array();
	if( $_POST['status'] == 'topic' ){
		if( WPF()->topic->delete(intval($_POST['postid'])) ){
			$resp = array(
				'postid' => intval($_POST['postid']),
				'location' => WPF()->forum->get_forum_url(intval($_POST['forumid']))
			);
			$return = 1;
		}else{
			$return = 0;
		}
	}elseif($_POST['status'] == 'reply'){
		if( WPF()->post->delete(intval($_POST['postid'])) ){
			$resp = array(
				'postid' => intval($_POST['postid'])
			);
			$return = 1;
		}else{
			$return = 0;
		}
	}
	
	$resp['stat'] = $return;
	$resp['notice'] = WPF()->notice->get_notices();
	echo json_encode( $resp );
	exit();
}

add_action('wp_ajax_wpforo_subscribe_ajax', 'wpf_subscribe');
add_action('wp_ajax_nopriv_wpforo_subscribe_ajax', 'wpf_subscribe');
function wpf_subscribe(){
	$args = array(
		'itemid' => wpforo_bigintval($_POST['itemid']),
		'type'   => sanitize_text_field($_POST['type']),
		'userid' => WPF()->current_userid,
        'active' => 0,
        'user_name' => '',
        'user_email' => ''
	);

    if( !WPF()->current_userid ){
        if( WPF()->current_user_email ) $args['user_email'] = WPF()->current_user_email;
        if( WPF()->current_user_display_name ) $args['user_name'] = WPF()->current_user_display_name;
    }
    if( !$args['userid'] && !$args['user_email'] ) return false;
	
	if(isset($_POST['status']) && $_POST['status'] == 'subscribe'){
		
		if($_POST['type'] == 'forum'){
			$forum = WPF()->forum->get_forum(wpforo_bigintval($_POST['itemid']));
			if( isset($forum['forumid']) && $forum['forumid'] ){
				if( !WPF()->perm->forum_can('vf', $forum['forumid']) ){
					WPF()->notice->add('You are not permitted to subscribe here', 'error');
					$return = 0;
				}
			}
		}elseif($_POST['type'] == 'topic'){
			$topic  = WPF()->topic->get_topic(wpforo_bigintval($_POST['itemid']));
			if( isset($topic['forumid']) && $topic['forumid'] ){
				if( isset($topic['private']) && $topic['private'] && !wpforo_is_owner($topic['userid'], $topic['email']) ){
					if( !WPF()->perm->forum_can('vp', $topic['forumid']) ){
						WPF()->notice->add('You are not permitted to subscribe here', 'error');
						$return = 0;
					}
				}
			}
		}
		
		$args['confirmkey'] = WPF()->sbscrb->get_confirm_key();
		
		if( wpforo_feature('subscribe_conf') ){
			############### Sending Email  ##################
			$confirmlink = WPF()->sbscrb->get_confirm_link($args);
            $member_name = ( WPF()->current_userid ? wpforo_make_dname( WPF()->current_user['display_name'], WPF()->current_user['user_nicename'] ) : ( $args['user_name'] ? $args['user_name'] : $args['user_email'] ) );
			if($_POST['type'] == 'forum'){
				$item_title = $forum['title'];
			}elseif($_POST['type'] == 'topic'){
				$item_title = $topic['title'];
			}
			$subject = WPF()->sbscrb->options['confirmation_email_subject'];
			$message = WPF()->sbscrb->options['confirmation_email_message'];
			$from_tags = array("[member_name]", "[entry_title]", "[confirm_link]");
			$to_words   = array(sanitize_text_field($member_name),  '<strong>' . sanitize_text_field($item_title) . '</strong>', '<br><br><a href="' . esc_url($confirmlink) . '"> ' . wpforo_phrase('Confirm my subscription', false) . ' </a>');
			$subject = stripslashes(strip_tags(str_replace($from_tags, $to_words, $subject)));
			$message = stripslashes(str_replace($from_tags, $to_words, $message));
			$message = wpforo_kses($message, 'email');
			
			add_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
			$headers = wpforo_mail_headers();
			
			if( wp_mail( WPF()->current_user_email , sanitize_text_field($subject), $message, $headers ) ){
				if( WPF()->sbscrb->add($args) ){
					$return = 1;
				}else{
					$return = 0;
				}
			}else{
				WPF()->notice->add('Can\'t send confirmation email', 'error');
				$return = 0;
			}
			remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
			############### Sending Email end  ##############
		}
		else{
			$args['active'] = 1;
			if( WPF()->sbscrb->add($args) ){
				$return = 1;
			}else{
				$return = 0;
			}
		}
		
	}elseif(isset($_POST['status']) && $_POST['status'] == 'unsubscribe'){
		$subscribe = WPF()->sbscrb->get_subscribe( $args );
		$return = (int) WPF()->sbscrb->delete( $subscribe['confirmkey'] );
	}
	
	$resp['stat'] = $return;
	$resp['notice'] = WPF()->notice->get_notices();
	echo json_encode( $resp );
	exit();
}

############### Sending Email  ##################
function wpforo_set_html_content_type(){
	return 'text/html';
}

function wpforo_wp_mail_from_name($name){
	if(isset(WPF()->sbscrb->options['from_name']) && WPF()->sbscrb->options['from_name']){
		return WPF()->sbscrb->options['from_name'];
	}
	else{
		return $name;
	}
}

function wpforo_wp_mail_from_email($email){
	if(isset(WPF()->sbscrb->options['from_email']) && WPF()->sbscrb->options['from_email']){
		return WPF()->sbscrb->options['from_email'];
	}
	else{
		return $email;
	}
}

function wpforo_mail_from_name(){
	if(isset(WPF()->sbscrb->options['from_name']) && WPF()->sbscrb->options['from_name']){ return WPF()->sbscrb->options['from_name']; } else {return get_option('blogname');}
}

function wpforo_mail_from_email(){
	if(isset(WPF()->sbscrb->options['from_email']) && WPF()->sbscrb->options['from_email']){return WPF()->sbscrb->options['from_email'];} else {return get_option( 'admin_email' );}
}

function wpforo_mail_headers($from_name = '', $from_email = '', $cc = array(), $bcc = array()){
	$H = array();
	if(!$from_name) $from_name = wpforo_mail_from_name();
	if(!$from_email) $from_email = wpforo_mail_from_email();
	$H[] = 'From: ' . $from_name . ' <' . $from_email . '>';
	if(!empty($cc)){
		foreach($cc as $c){ $c = sanitize_email($c); $H[] = 'CC: ' . $c; }
	}
	if(!empty($bcc)){
		foreach($bcc as $b){ $b = sanitize_email($b); $H[] = 'BCC: ' . $b; }
	}
	return $H;
}

function wpforo_admin_mail_headers($from_name = '', $from_email = '', $cc = array(), $bcc = array()){
	$H = array();
	if(!$from_name) $from_name = wpforo_mail_from_name();
	if(!$from_email) $from_email = wpforo_mail_from_email();
	$H[] = 'From: ' . $from_name . ' <' . $from_email . '>';
	if(empty($cc)){
		$cc = trim(WPF()->sbscrb->options['admin_emails']);
		$cc = explode(',', $cc);
		$cc = array_map('trim', $cc);
	}
	if(!empty($cc)){
		foreach($cc as $c){ $c = sanitize_email($c); $H[] = 'CC: ' . $c; }
	}
	if(!empty($bcc)){
		foreach($bcc as $b){ $b = sanitize_email($b); $H[] = 'BCC: ' . $b; }
	}
	return $H;
}

############### Sending Email end  ##############

function wpforo_frontend_enqueue(){
	if( is_wpforo_page() ){
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_register_script( 'wpforo-frontend-js', WPFORO_URL . '/wpf-assets/js/frontend.js', array('jquery'), WPFORO_VERSION, false );
        wp_enqueue_script('wpforo-frontend-js');
        wp_localize_script('wpforo-frontend-js', 'wpforo_phrases', WPF()->phrase->phrases);
        if( wpforo_feature( 'font-awesome') ){
            wp_register_style('wpforo-font-awesome', WPFORO_URL . '/wpf-assets/css/font-awesome/css/font-awesome.min.css', false, '4.7' );
            wp_enqueue_style('wpforo-font-awesome');
            if (is_rtl()) {
                wp_register_style('wpforo-font-awesome-rtl', WPFORO_URL . '/wpf-assets/css/font-awesome/font-awesome-rtl.css', false, WPFORO_VERSION );
                wp_enqueue_style('wpforo-font-awesome-rtl');
            }
        }
		wp_register_script('wpforo-ajax', WPFORO_URL . '/wpf-assets/js/ajax.js', array('jquery'), WPFORO_VERSION, false);
		wp_enqueue_script('wpforo-ajax');
		wp_localize_script('wpforo-ajax', 'wpf_ajax_obj', array( 'url' => admin_url('admin-ajax.php') ));
        if (is_rtl()) {
			wp_register_style('wpforo-style-rtl', WPFORO_TEMPLATE_URL . '/style-rtl.css', false, WPFORO_VERSION );
			wp_enqueue_style('wpforo-style-rtl');
		}
		else{
			wp_register_style('wpforo-style', WPFORO_TEMPLATE_URL . '/style.css', false, WPFORO_VERSION );
			wp_enqueue_style('wpforo-style');
		}
	}
	
	if (is_rtl()) {
		wp_register_style('wpforo-widgets-rtl', WPFORO_TEMPLATE_URL . '/widgets-rtl.css', false, WPFORO_VERSION );
		wp_enqueue_style('wpforo-widgets-rtl');
	}
	else{
		wp_register_style('wpforo-widgets', WPFORO_TEMPLATE_URL . '/widgets.css', false, WPFORO_VERSION );
		wp_enqueue_style('wpforo-widgets');
	}
}
add_action('wp_enqueue_scripts', 'wpforo_frontend_enqueue');

function wpforo_add_into_wp_head(){
	if(!WPF()->perm->forum_can('va')){
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$(document).on('click','.attach_cant_view', function(){
                    var msg_box = jQuery("#wpf-msg-box");
                    var load = jQuery('#wpforo-load');
                    msg_box.hide();
                    load.visible();
                    msg_box.html("<p><?php echo addslashes( ( is_user_logged_in() ? WPF()->post->options['attach_cant_view_msg'] : sprintf( wpforo_phrase('Please %s or %s', FALSE), '<a href="' . wpforo_login_url() . '">'.wpforo_phrase('Login', FALSE).'</a>', '<a href="' . wpforo_register_url() . '">'.wpforo_phrase('Register', FALSE).'</a>' ) ) )  ?></p>");
                    msg_box.show(150).delay(1000);
                    load.invisible();
				});
			});
		</script>
		<?php
	}
}
add_action('wp_head', 'wpforo_add_into_wp_head');

function wpforo_dynamic_style() {
	
	if(!is_wpforo_page()) return false;
	
	$inline = false;
	$dynamic_css_file = WPFORO_TEMPLATE_DIR . '/colors.css';
	$dynamic_css_matrix = WPFORO_TEMPLATE_DIR . '/styles/css.php';
	if( isset(WPF()->tpl->options) ){
		if( !isset(WPF()->tpl->options['style']) || !isset(WPF()->tpl->options['styles']) ) return false;
		$style = WPF()->tpl->options['style'];
		$styles = WPF()->tpl->options['styles'];
		if( !empty($style) && !empty($styles) ){ 
			foreach( $styles[$style] as $color_key => $color_value ){ 
				if( $color_value ) {
					$COLORS[ 'WPFCOLOR_' . $color_key ] = $color_value; 
				}
			}
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
	extract($COLORS, EXTR_OVERWRITE);
	if( file_exists( $dynamic_css_matrix ) ){
		require_once( $dynamic_css_matrix );
		$css = apply_filters('wpforo_dynamic_css_filter', $css, $COLORS);
	}
	else{
		return false;
	}
	$hach_new = md5($css);
	if( file_exists( $dynamic_css_file ) && filesize($dynamic_css_file) ){
		$css_current = wpforo_get_file_content( $dynamic_css_file );
		if( $css_current ){
			$hach_current = md5($css_current);
			if( $hach_new == $hach_current ){
				//CSS not changed
			}
			else{
				$result = wpforo_write_file( $dynamic_css_file, $css );
				if( isset($result['error']) && $result['error'] ){
					$inline = true;
				}
				else{
					//CSS updated
				}
			}
		}
	}
	else{
		$result = wpforo_write_file( $dynamic_css_file, $css );
		if( isset($result['error']) && $result['error'] ){
			$inline = true;
		}
	}
	if( $inline ){
		$css = preg_replace('|[\r\n\t]+|', '', $css );
		wp_add_inline_style( 'wpforo-dynamic-style', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'wpforo_dynamic_style', 12 );

function wpforo_style_options($css, $COLORS){
	if( !isset($css)) return;
	if( isset(WPF()->tpl->style['font_size_forum']) && WPF()->tpl->style['font_size_forum'] != 17 ){
		$css .= "\r\n#wpforo-wrap .wpforo-forum-title{font-size: " . intval(WPF()->tpl->style['font_size_forum']) . "px!important; line-height: " . (intval(WPF()->tpl->style['font_size_forum']) + 1) . "px!important;}";
	}
	if( isset(WPF()->tpl->style['font_size_topic']) && WPF()->tpl->style['font_size_topic'] != 16 ){
		$css .= "\r\n#wpforo-wrap .wpforo-topic-title a { font-size: " . intval(WPF()->tpl->style['font_size_topic']) . "px!important; line-height: " . (intval(WPF()->tpl->style['font_size_topic']) + 4) . "px!important; }";
	}
	if( isset(WPF()->tpl->style['font_size_post_content']) && WPF()->tpl->style['font_size_post_content'] != 14 ){
		$css .= "\r\n#wpforo-wrap .wpforo-post .wpf-right .wpforo-post-content {font-size: " . intval(WPF()->tpl->style['font_size_post_content']) . "px!important; line-height: " . (intval(WPF()->tpl->style['font_size_post_content']) + 4) . "px!important;}\r\n#wpforo-wrap .wpforo-post .wpf-right .wpforo-post-content p {font-size: " . intval(WPF()->tpl->style['font_size_post_content']) . "px;}";
	}
	if( isset(WPF()->tpl->style['custom_css']) ){
		$css .= "\r\n" . stripslashes(WPF()->tpl->style['custom_css']);
	}
	return $css;
}
add_filter( 'wpforo_dynamic_css_filter' , 'wpforo_style_options' , 10, 2 );

function wpforo_admin_enqueue(){
	$phrases = array(
		'move' => __('Move', 'wpforo'), 
		'delete' => __('Delete', 'wpforo')
	);
	if( !empty($_GET['page']) && FALSE !== strpos( $_GET['page'], 'wpforo' ) ){
		if( wpforo_feature( 'font-awesome') ){
			wp_register_style('wpforo-font-awesome', WPFORO_URL . '/wpf-assets/css/font-awesome/css/font-awesome.min.css', false, '4.6.3' );
			wp_enqueue_style('wpforo-font-awesome');
		}
		wp_register_style('wpforo-admin', WPFORO_URL . '/wpf-admin/css/admin.css', false, WPFORO_VERSION );	
		wp_enqueue_style('wpforo-admin');
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-mouse');
		wp_enqueue_script('jquery-ui-position');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-menu');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-color');
		wp_enqueue_script('wp-lists');
		if( $_GET['page'] == 'wpforo-forums' ){
			if( !empty($_GET['action']) ){
				//Just for excluding 'nav-menu' js loading//
				wp_enqueue_script('postbox');
				wp_enqueue_script('link');
			}
			else{
				wp_enqueue_script('nav-menu');
			}
		}
		elseif( $_GET['page'] == 'wpforo-settings' && !empty($_GET['tab']) && $_GET['tab'] == 'styles' ){
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('iris', admin_url('js/iris.min.js'),array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), false, 1);
			wp_enqueue_script('wp-color-picker', admin_url('js/color-picker.min.js'), array('iris'), false,1);
			$colorpicker_l10n = array('clear' => __('Clear'), 'defaultString' => __('Default'), 'pick' => __('Select Color'));
			wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n ); 
		}
		elseif(  $_GET['page'] == 'wpforo-community' ){
			wp_enqueue_script('postbox');
			wp_enqueue_script('link');
		}
		elseif(  $_GET['page'] == 'wpforo-addons' ){
			wp_register_script( 'wpforo-contenthover', WPFORO_URL . '/wpf-admin/js/contenthover/jquery.contenthover.min.js', array('jquery'), WPFORO_VERSION, false );
			wp_enqueue_script( 'wpforo-contenthover' );
		}
		wp_register_script( 'wpforo-contenthover', WPFORO_URL . '/wpf-admin/js/functions.js', array('jquery'), WPFORO_VERSION, false );
		wp_enqueue_script( 'wpforo-contenthover' );
		wp_localize_script( 'wpforo-contenthover', 'wpforo_admin', array('phrases' => $phrases) );
		
	}
}
add_action( 'admin_enqueue_scripts', 'wpforo_admin_enqueue' );

function wpforo_admin_permalink_notice() {
    $permalink_structure = get_option( 'permalink_structure' );
	if( !$permalink_structure ){
		$class = 'notice notice-warning';
		$message = __( 'IMPORTANT: wpForo can\'t work with default permalink, please change permalink structure', 'wpforo' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	}
   
}
add_action( 'admin_notices', 'wpforo_admin_permalink_notice' );

function wpforo_userform_to_wpuser_html_form($wp_user){
	if( is_super_admin() ){
		if( is_object($wp_user) ){
			$user = WPF()->member->get_member($wp_user->ID);
			$groupid = $user['groupid'];
			$timezone = $user['timezone'];
		}
		if( !isset($groupid) ) $groupid = 0;
		if( !isset($timezone) ) $timezone = '';
		
		echo '<table class="form-table">
				<tr class="form-field">
					<th scope="row"><label for="wpforo_usergroup">' . __('wpForo Usergroup', 'wpforo') . '</label></th>
					<td>
						<select name="wpforo_usergroup" id="wpforo_usergroup">';
							WPF()->usergroup->show_selectbox($groupid);
		echo '			</select>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="wpforo_usertimezone">' . __('wpForo User Timezone', 'wpforo') . '</label></th>
					<td>
						<select name="wpforo_usertimezone" id="wpforo_usertimezone">
						' . wp_timezone_choice($timezone) .	
						'</select>
					</td>
				</tr>
			</table>';
			
	}
}
add_action( 'user_new_form', 'wpforo_userform_to_wpuser_html_form' );
add_action( 'show_user_profile', 'wpforo_userform_to_wpuser_html_form' );
add_action( 'edit_user_profile', 'wpforo_userform_to_wpuser_html_form' );

function wpforo_do_hook_user_register($userid){
	WPF()->member->synchronize_user($userid);
}
add_action( 'user_register', 'wpforo_do_hook_user_register', 10, 1 );

function wpforo_do_hook_update_profile($userid){
	if( isset($_POST['wpforo_usergroup']) && $_POST['wpforo_usergroup'] ){
		WPF()->member->edit_profile( array( 'userid' => intval($userid),
												'groupid' => intval($_POST['wpforo_usergroup']), 
													'site' => esc_url($_POST['url']), 
														'about' => wpforo_kses($_POST['description'], 'user_description'), 
															'timezone' => ( isset($_POST['wpforo_usertimezone']) ? sanitize_text_field($_POST['wpforo_usertimezone']) : '' ) ) );
	}
	WPF()->member->reset($userid);
}
add_action('personal_options_update', 'wpforo_do_hook_update_profile');
add_action('edit_user_profile_update', 'wpforo_do_hook_update_profile');

function wpforo_update_last_login_date($user_login, $user = array()){
	if(empty($user)) return;
	WPF()->member->edit_profile( array( 'userid' => intval($user->ID), 'last_login' => current_time( 'mysql', 1 ) ) );
}
add_action('wp_login', 'wpforo_update_last_login_date', 10, 2);

function wpforo_do_hook_deleted_user($userid){
	if( !empty($_REQUEST['wpforo_user_delete_option']) && $_REQUEST['wpforo_user_delete_option'] == 'reassign' && !empty($_REQUEST['wpforo_reassign_user']) ){
		WPF()->member->delete( $userid, $_REQUEST['wpforo_reassign_user'] );
	}else{
		WPF()->member->delete( $userid );
	}
	WPF()->notice->clear();
}
add_action( 'deleted_user', 'wpforo_do_hook_deleted_user' );

function wpforo_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
	if(!wpforo_feature('replace-avatar')) return $avatar;
    $user = false;
    if ( is_numeric( $id_or_email ) ) {
        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );
    }elseif( is_object( $id_or_email ) ) {
        if ( ! empty( $id_or_email->ID ) ) {
            $id = (int) $id_or_email->ID;
            $user = get_user_by( 'id' , $id );
        }
    }else{
        $user = get_user_by( 'email', $id_or_email );	
    }

    if( $user && is_object( $user ) ){
        if( $src = WPF()->member->get_avatar_url($user->data->ID) ){
            $avatar = "<img alt='" . esc_attr($alt) . "' src='" . esc_url($src) . "' class='avatar avatar-" . esc_attr($size) . " photo' height='" . esc_attr($size) . "' width='" . esc_attr($size) . "' />";
        }
    }
    return $avatar;
}
add_filter( 'get_avatar' , 'wpforo_avatar' , 10, 5 );

function wpforo_mention_nickname_to_link($match){
    $return = $match[0];
    if( $member = WPF()->member->get_member($match[1]) ){
        $href = WPF()->member->profile_url($member);
        $dname   = wpforo_make_dname($member['display_name'], $member['user_nicename']);
        $return = sprintf('<a href="%s" title="%s">@%s</a>%s', $href, $dname, $match[1], $match[2]);
    }

    return  $return;
}

function wpforo_mentioned_code_to_link($text){
    //$text = htmlspecialchars_decode($text);
    $text = preg_replace_callback('#@([^\r\n\t\s\0<>\[\]!,\.\(\)\'\"\|\?\@]+)($|[\r\n\t\s\0<>\[\]!,\.\(\)\'\"\|\?\@])#isu', 'wpforo_mention_nickname_to_link', $text);
    return $text;
}
add_filter('wpforo_body_text_filter', 'wpforo_mentioned_code_to_link');

function wpforo_send_mail_to_mentioned_users($item){
    if( !WPF()->sbscrb->options['user_mention_notify'] ) return false;
    $return = false;
    $body = strip_tags($item['body']);
    if( preg_match_all('#@([^\r\n\t\s\0<>\[\]!,\.\(\)\'\"\|\?\@]+)(?:$|[\r\n\t\s\0<>\[\]!,\.\(\)\'\"\|\?\@])#isu', $body, $matches, PREG_SET_ORDER) ){

        $dname = wpforo_make_dname( WPF()->current_user['display_name'], WPF()->current_user['user_nicename'] );
        $_to_words = array($dname);
        if( array_key_exists('first_postid', $item) ){
            $_to_words[] = $item['title'];
            $_to_words[] = $item['topicurl'];
        }else{
            $_to_words[] = wpforo_topic($item['topicid'], 'title');
            $_to_words[] = $item['posturl'];
        }

        $_subject = WPF()->sbscrb->options['user_mention_email_subject'];
        $_message = WPF()->sbscrb->options['user_mention_email_message'];
        $_from_tags = array("[author-user-name]", "[topic-title]", "[post-url]");
        $_subject = str_replace($_from_tags, $_to_words, $_subject);
        $_message = str_replace($_from_tags, $_to_words, $_message);

        add_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
        foreach ( $matches as $match ){
            $member = WPF()->member->get_member($match[1]);
            if( !empty($member['user_email']) ){

                if( WPF()->current_userid == $member['userid'] ) continue;

                if( in_array($member['user_email'], WPF()->sbscrb->already_sent_emails) ) continue;

                if( isset($item['private']) && $item['private'] ){
                    if( !WPF()->perm->forum_can('vp', $item['forumid'], $member['groupid']) ) continue;
                }
                if( isset($item['status']) && $item['status'] ){
                    if( !WPF()->perm->forum_can('au', $item['forumid'], $member['groupid']) ) continue;
                }

                $dname   = wpforo_make_dname($member['display_name'], $member['user_nicename']);
                $subject = stripslashes(str_replace('[mentioned-user-name]', $dname, $_subject));
                $message = stripslashes(str_replace('[mentioned-user-name]', $dname, $_message));
                $message = wpforo_kses($message, 'email');

                if( $return = wp_mail( $member['user_email'], sanitize_text_field($subject), $message, wpforo_mail_headers() ) ){
                    WPF()->sbscrb->already_sent_emails[] = $member['user_email'];
                }
            }
        }
        remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );

    }

    return $return;
}
add_action( 'wpforo_after_add_topic', 'wpforo_send_mail_to_mentioned_users' );
add_action( 'wpforo_after_add_post', 'wpforo_send_mail_to_mentioned_users' );

function wpforo_topic_auto_subscribe($item){
	if(!isset($_POST['wpforo_topic_subs']) || !$_POST['wpforo_topic_subs'] ) return FALSE;
	
	if( isset($item['forumid']) && $item['forumid'] ){
		if( isset($item['private']) && $item['private'] && !wpforo_is_owner($item['userid']) ){
			if( !WPF()->perm->forum_can('vp', $item['forumid']) ){
				WPF()->notice->add('You are not permitted to subscribe here', 'error');
		 		return FALSE;
			}
		}
		else{
			//This is not a Private Topic or Current User is the owner. 
		}
	}
	else{
		 WPF()->notice->add('Forum ID is not detected', 'error');
		 return FALSE;
	}
	
	$args = array(
		'itemid' => wpforo_bigintval($item['topicid']),
		'type'   => 'topic',
		'userid' => WPF()->current_userid,
        'user_name' => '',
        'user_email' => ''
	);

    if( !WPF()->current_userid ){
        if( WPF()->current_user_email ) $args['user_email'] = WPF()->current_user_email;
        if( WPF()->current_user_display_name ) $args['user_name'] = WPF()->current_user_display_name;
    }
    if( !$args['userid'] && !$args['user_email'] ) return false;
	
	$args['confirmkey'] = WPF()->sbscrb->get_confirm_key();
	
	if( wpforo_feature('subscribe_conf') ){
		############### Sending Email  ##################
		$confirmlink = WPF()->sbscrb->get_confirm_link($args);
        $member_name = ( WPF()->current_userid ? wpforo_make_dname( WPF()->current_user['display_name'], WPF()->current_user['user_nicename'] ) : ( $args['user_name'] ? $args['user_name'] : $args['user_email'] ) );
        $subject = WPF()->sbscrb->options['confirmation_email_subject'];
		$message = WPF()->sbscrb->options['confirmation_email_message'];
		$topic  = WPF()->topic->get_topic( $item['topicid'] );
		$from_tags = array("[member_name]", "[entry_title]", "[confirm_link]");
		$to_words   = array(sanitize_text_field($member_name),  '<strong>' . sanitize_text_field($topic['title']) . '</strong>', '<br><br><a href="' . esc_url($confirmlink) . '"> ' . wpforo_phrase('Confirm my subscription', false) . ' </a>');
		$subject = stripslashes(str_replace($from_tags, $to_words, $subject));
		$message = stripslashes(str_replace($from_tags, $to_words, $message));
		$message = wpforo_kses($message, 'email');
		
		add_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
		$headers = wpforo_mail_headers();

		if( wp_mail( WPF()->current_user_email, sanitize_text_field($subject), $message, $headers ) ){
			if( $response = WPF()->sbscrb->add($args) ) return $response;
		}else{
			WPF()->notice->add('Can\'t send confirmation email', 'error');
			return FALSE;
		}
		remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
		############### Sending Email end  ##############
	}else{
		$args['active'] = 1;
		if( $response = WPF()->sbscrb->add($args) ) return $response;
	}
	return FALSE;
}
add_action( 'wpforo_after_add_topic', 'wpforo_topic_auto_subscribe' );
add_action( 'wpforo_after_add_post', 'wpforo_topic_auto_subscribe' );

function wpforo_forum_subscribers_mail_sender( $topic ){

	if( defined('IS_GO2WPFORO') && IS_GO2WPFORO ) return;

	$subscribers = WPF()->sbscrb->get_subscribes( array( 'itemid' => $topic['forumid'], 'type' => 'forum' ) );
	if( WPF()->sbscrb->options['new_topic_notify'] ){
		$admin_emails = explode(',', WPF()->sbscrb->options['admin_emails']);
		foreach( $admin_emails as $admin_email ) $subscribers[] = sanitize_email( $admin_email );
	}
	
	$subscribers = apply_filters('wpforo_forum_subscribers', $subscribers);
	
	foreach($subscribers as $subscriber){
		
		if( is_array($subscriber) ){
		    if( $subscriber['userid'] ){
                $member = WPF()->member->get_member( $subscriber['userid'] );
            }elseif( $subscriber['user_email'] ){
                $member = array('groupid' => 4, 'display_name' => ($subscriber['user_name'] ? $subscriber['user_name'] : $subscriber['user_email']), 'user_email' => $subscriber['user_email']);
            }else{
		        continue;
            }
			$unsubscribe_link = WPF()->sbscrb->get_unsubscribe_link($subscriber['confirmkey']);
		}else{
			$member = array('display_name' => $subscriber, 'user_email' => $subscriber);
			$unsubscribe_link = '#';
		}
		
		if( isset($topic['forumid']) && $topic['forumid'] ){
			if( isset($topic['private']) && $topic['private'] && isset($subscriber['userid'])
                && ( ( $topic['userid'] && $subscriber['userid'] && $topic['userid'] != $subscriber['userid'] )
                    || ( $topic['email'] && $subscriber['user_email'] && $topic['email'] != $subscriber['user_email'] ) ) ){
				$subscriber_groupid = ( isset($member['groupid']) && $member['groupid'] ) ? $member['groupid'] : WPF()->usergroup->get_groupid_by_userid($subscriber['userid']);
				if( !WPF()->perm->forum_can('vp', $topic['forumid'], $subscriber_groupid) ){
					continue;
				}
			}
			if( isset($topic['status']) && $topic['status'] == 1 && isset($subscriber['userid']) ){
				$subscriber_groupid = ( isset($member['groupid']) && $member['groupid'] ) ? $member['groupid'] : WPF()->usergroup->get_groupid_by_userid($subscriber['userid']);
				if( !WPF()->perm->forum_can('au', $topic['forumid'], $subscriber_groupid) ){
					continue;
				}
			}
		}
		
		$owner = wpforo_member( $topic );
		
		if($owner['user_email'] == $member['user_email']) continue;
        if( in_array($member['user_email'], WPF()->sbscrb->already_sent_emails) ) continue;
		
		$forum  = WPF()->forum->get_forum( $topic['forumid'] );
		
		############### Sending Email  ##################
			
			if( isset($topic['status']) && $topic['status'] ){
				$subject_prefix = __('Please Moderate: ', 'wpforo');
				$mod_text = '<br /><br /><p style="color:#DD0000">' . __('This topic is currently unapproved. You can approve topics in Dashboard &raquo; Forums &raquo; Moderation admin page.', 'wpforo') . '</p>';
			}
			else{
				$subject_prefix = '';
				$mod_text = '';
			}
			
			$subject = WPF()->sbscrb->options['new_topic_notification_email_subject'];
		 	$message = WPF()->sbscrb->options['new_topic_notification_email_message'];
			
			$from_tags = array( "[member_name]", "[forum]", "[unsubscribe_link]", "[topic_title]", "[topic_desc]");
			$to_words  = array( sanitize_text_field($member['display_name']), 
									'<a href="' . esc_url($forum['url']) . '">' . sanitize_text_field($forum['title']) . '</a>', 
										'<br><a target="_blank" href="' . esc_url($unsubscribe_link) . '">' . wpforo_phrase('Unsubscribe', false) . '</a>' , 
											'<a target="_blank" href="' . esc_url($topic['topicurl']) . '">' . sanitize_text_field($topic['title']) . '</a>' , 
												wpforo_text( wpforo_kses( $topic['body'], 'email'), 70, FALSE ) );
			
			$subject = stripslashes(strip_tags(str_replace($from_tags, $to_words, $subject)));
			$message = stripslashes(str_replace($from_tags, $to_words, $message));
			
			add_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
			$headers = wpforo_mail_headers();
			$subject = $subject_prefix . $subject;
			$message = $message . $mod_text;
	 		$email_status = wp_mail( $member['user_email'] , $subject, $message, $headers );
	 		remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
	 		
	 	############### Sending Email end  ##############
		
	}
}
add_action( 'wpforo_after_add_topic', 'wpforo_forum_subscribers_mail_sender', 12);



function wpforo_topic_subscribers_mail_sender( $post ){

	if( defined('IS_GO2WPFORO') && IS_GO2WPFORO ) return;
	
	$subscribers = WPF()->sbscrb->get_subscribes( array( 'itemid' => $post['topicid'], 'type' => 'topic' ) );
	if( WPF()->sbscrb->options['new_reply_notify'] ){
		$admin_emails = explode(',', WPF()->sbscrb->options['admin_emails']);
		foreach( $admin_emails as $admin_email ) $subscribers[] = sanitize_email( $admin_email );
	}
	
	$topic  = WPF()->topic->get_topic( $post['topicid'] );
	
	$subscribers = apply_filters('wpforo_topic_subscribers', $subscribers);
	
	foreach($subscribers as $subscriber){
		
		if( is_array($subscriber) ){
            if( $subscriber['userid'] ){
                $member = WPF()->member->get_member( $subscriber['userid'] );
            }elseif( $subscriber['user_email'] ){
                $member = array('groupid' => 4, 'display_name' => ($subscriber['user_name'] ? $subscriber['user_name'] : $subscriber['user_email']), 'user_email' => $subscriber['user_email']);
            }else{
                continue;
            }
			$unsubscribe_link = WPF()->sbscrb->get_unsubscribe_link($subscriber['confirmkey']);
		}else{
			$member = array('display_name' => $subscriber, 'user_email' => $subscriber);
			$unsubscribe_link = '#';
		}
		
		$owner = wpforo_member( $post );
		if($owner['user_email'] == $member['user_email']) continue;
		if( in_array($member['user_email'], WPF()->sbscrb->already_sent_emails) ) continue;
		
		if( isset($topic['forumid']) && $topic['forumid'] && isset($subscriber['userid']) ){
			
			$subscriber_groupid = ( isset($member['groupid']) && $member['groupid'] ) ? $member['groupid'] : WPF()->usergroup->get_groupid_by_userid($subscriber['userid']);
			
			if( isset($topic['private']) && $topic['private']
                && ( ( $topic['userid'] && $subscriber['userid'] && $topic['userid'] != $subscriber['userid'] )
                    || ( $topic['email'] && $subscriber['user_email'] && $topic['email'] != $subscriber['user_email'] ) ) ){
				if( !WPF()->perm->forum_can('vp', $topic['forumid'], $subscriber_groupid) ){
					continue;
				}
			}
			if( (isset($topic['status']) && $topic['status'] == 1) || (isset($post['status']) && $post['status'] == 1) ){
				if( !WPF()->perm->forum_can('au', $topic['forumid'], $subscriber_groupid) ){
					continue;
				}
			}
		}
		
		############### Sending Email  ##################
			
			if( isset($post['status']) && $post['status'] ){
				$subject_prefix = __('Please Moderate: ', 'wpforo');
				$mod_text = '<br /><br /><p style="color:#DD0000">' . __('This post is currently unapproved. You can approve posts in Dashboard &raquo; Forums &raquo; Moderation admin page.', 'wpforo') . '</p>';
			}
			else{
				$subject_prefix = '';
				$mod_text = '';
			}
			
			$subject = WPF()->sbscrb->options['new_post_notification_email_subject'];
		 	$message = WPF()->sbscrb->options['new_post_notification_email_message'];
			
			$from_tags = array( "[member_name]", "[topic]", "[unsubscribe_link]", "[reply_title]", "[reply_desc]");
			$to_words  = array( sanitize_text_field($member['display_name']), 
									'<a href="' . esc_url($post['posturl']) . '">' . sanitize_text_field($topic['title']) . '</a>', 
										'<br><a target="_blank" href="' . esc_url($unsubscribe_link) . '">'.wpforo_phrase('Unsubscribe', false).'</a>' , 
											'<a target="_blank" href="' . esc_url($post['posturl']) . '">' . sanitize_text_field($post['title']) . '</a>' , 
												wpforo_text( wpforo_kses($post['body'], 'email'), 70, FALSE ) );
			
			$subject = stripslashes(strip_tags(str_replace($from_tags, $to_words, $subject)));
			$message = stripslashes(str_replace($from_tags, $to_words, $message));
			
			add_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
			$headers = wpforo_mail_headers();
			$subject = $subject_prefix . $subject;
			$message = $message . $mod_text;
	 		$email_status = wp_mail( $member['user_email'] , $subject, $message, $headers );
	 		remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
	 		
	 	############### Sending Email end  ##############
		
	}
}
add_action( 'wpforo_after_add_post', 'wpforo_topic_subscribers_mail_sender', 13, 1 );


function wpforo_add_default_attachment($args){
	if( !empty($_FILES['attachfile']) && !empty($_FILES['attachfile']['name']) ){
		if( WPF()->perm->can_attach() ){
			$name = sanitize_file_name($_FILES['attachfile']['name']); //myimg.png
			$type = sanitize_mime_type($_FILES['attachfile']['type']); //image/png
			$tmp_name = sanitize_text_field($_FILES['attachfile']['tmp_name']); //D:\wamp\tmp\php986B.tmp
			$error = intval($_FILES['attachfile']['error']); //0
			$size = intval($_FILES['attachfile']['size']); //6112
			
			$phpFileUploadErrors = array(
				0 => 'There is no error, the file uploaded with success',
				1 => 'The uploaded file size is too big',
				2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
				3 => 'The uploaded file was only partially uploaded',
				4 => 'No file was uploaded',
				6 => 'Missing a temporary folder',
				7 => 'Failed to write file to disk.',
				8 => 'A PHP extension stopped the file upload.',
			);
			
			if( $error ){
				WPF()->notice->add($phpFileUploadErrors[$error], 'error');
				return $args;
			}elseif( $size > WPF()->post->options['max_upload_size'] ){
				WPF()->notice->add('The uploaded file size is too big', 'error');
				return $args;
			}
			
			if(function_exists('pathinfo')){
				$ext = pathinfo($name, PATHINFO_EXTENSION);
			}
			else{
				$ext = substr(strrchr($name, '.'), 1);
			}
			$ext = strtolower($ext);
			$mime_types = get_allowed_mime_types();
			$mime_types = array_flip($mime_types);
			if(!empty($mime_types)){
				$allowed_types = implode('|', $mime_types);
				$expld = explode('|', $allowed_types);
				if( !in_array($ext, $expld) ){
					WPF()->notice->add('File type is not allowed', 'error');
					return $args;
				}
				if( !WPF()->perm->can_attach_file_type($ext) ){
					 WPF()->notice->add('You are not allowed to attach this file type', 'error');
					 return $args;
				}
			}
			
			$wp_upload_dir = wp_upload_dir();
			$uplds_dir = $wp_upload_dir['basedir']."/wpforo";
			$attach_dir = $wp_upload_dir['basedir']."/wpforo/default_attachments";
			$attach_url = preg_replace('#^https?\:#is', '', $wp_upload_dir['baseurl'])."/wpforo/default_attachments";
			if(!is_dir($uplds_dir)) wp_mkdir_p($uplds_dir);
			if(!is_dir($attach_dir)) wp_mkdir_p($attach_dir);
			
			$fnm = pathinfo($name, PATHINFO_FILENAME);
			$fnm = str_replace(' ', '-', $fnm);
			while(strpos($fnm, '--') !== FALSE) $fnm = str_replace('--', '-', $fnm);
			$fnm = preg_replace("/[^-a-zA-Z0-9]/", "", $fnm);
			$fnm = trim($fnm, "-");
			$fnm_empty = ( $fnm ? FALSE : TRUE );
			
			$file_name = $fnm . "." . $ext;
			
			$attach_fname = current_time( 'timestamp', 1 ).( !$fnm_empty ? '-' : '' ) . $file_name;
			$attach_path = $attach_dir . "/" . $attach_fname;
			
			if( is_dir($attach_dir) && move_uploaded_file($tmp_name, $attach_path) ){
				$attach_id = wpforo_insert_to_media_library( $attach_path, $fnm );
				$args['body'] .= "\r\n" . '<div id="wpfa-' . $attach_id . '" class="wpforo-attached-file"><a class="wpforo-default-attachment" href="' . esc_url($attach_url.'/'.$attach_fname) . '" target="_blank"><i class="fa fa-paperclip"></i>' . esc_html(basename($name)) . '</a></div>';
				$args['has_attach'] = 1;
			}else{
				WPF()->notice->add('Can`t upload file', 'error');
				return $args;
			}
		}
	}
	return $args;
}

function wpforo_delete_attachment( $attach_post_id ){
	global $wpdb;
	if(!$attach_post_id) return;
	$posts = $wpdb->get_results("SELECT `postid`, `body` FROM `" . $wpdb->prefix . "wpforo_posts` WHERE `body` LIKE '%wpfa-" . intval( $attach_post_id ) . "%'", ARRAY_A );
	if(!empty($posts) || is_array($posts)){
		foreach( $posts as $post ){
			$body = preg_replace('|<div[^><]*id=[\'\"]+wpfa-' . $attach_post_id . '[\'\"]+[^><]*>.+?</div>|is', '<div class="wpforo-attached-file wpfa-deleted">' . wpforo_phrase('Attachment removed', FALSE) . '</div>', $post['body'] );
			if( $body ) $wpdb->query("UPDATE `" . $wpdb->prefix . "wpforo_posts` SET `body` = '" . esc_sql( $body ) . "' WHERE `postid` = " . intval($post['postid']));
		}
	}
}

function wpforo_default_attachments_filter($text){
	if( preg_match_all('#<a[^<>]*class=[\'"]wpforo-default-attachment[\'"][^<>]*href=[\'"]([^\'"]+)[\'"][^<>]*>[\r\n\t\s\0]*(?:<i[^<>]*>[\r\n\t\s\0]*</i>[\r\n\t\s\0]*)?([^<>]*)</a>#isu', $text, $matches, PREG_SET_ORDER) ){
		foreach( $matches as $match ){
			$attach_html = '';
			$fileurl = preg_replace('#^https?\:#is', '', $match[1]);
			$filename = $match[2];
			
			$upload_array = wp_upload_dir();
			$filedir = preg_replace('#^https?\:#is', '', str_replace( preg_replace('#^https?\:#is', '', $upload_array['baseurl']), $upload_array['basedir'], $fileurl ) );
			$filedir = str_replace( basename($filedir), urldecode( basename($filedir) ), $filedir );
			
			if(file_exists($filedir)){
				if(!WPF()->perm->forum_can('va')){
					$attach_html .= '<br/><div class="wpfa-item wpfa-file"><a class="attach_cant_view" style="cursor:pointer;"><span style="color:#666;">' . wpforo_phrase('Attachment', FALSE) . ':</span> ' . urldecode( basename($filename) ) . '</a></div>';
				}
			}
			
			if($attach_html){
				$attach_html .= '<br/>';
				$text = str_replace($match[0], $attach_html, $text);
			}
		}
	}
	
	return $text;
}

add_action( 'delete_attachment', 'wpforo_delete_attachment', 10 );
if( !defined('WPFOROATTACH_BASENAME') ){
	add_filter( 'wpforo_add_topic_data_filter', 'wpforo_add_default_attachment' );
	add_filter( 'wpforo_edit_topic_data_filter', 'wpforo_add_default_attachment' );
	add_filter( 'wpforo_add_post_data_filter', 'wpforo_add_default_attachment' );
	add_filter( 'wpforo_edit_post_data_filter', 'wpforo_add_default_attachment' );
	add_filter('wpforo_body_text_filter', 'wpforo_default_attachments_filter');
}

if( !class_exists('wpForoSmiles') ){
	add_filter('wpforo_body_text_filter', 'wp_encode_emoji', 9);
	add_filter('wpforo_body_text_filter', 'convert_smilies');
}


function wpforo_add_adminbar_links( $wp_admin_bar ) {
    $args = array(
        'id'    => 'new-forum',
        'title' => __('New Forum', 'wpforo'),
        'href'  => admin_url('admin.php?page=wpforo-forums&action=add'),
        'parent' => 'new-content'
    );
    $wp_admin_bar->add_node( $args );

    $args = array(
        'id'    => 'new-ugroup',
        'title' => __('New User Group', 'wpforo'),
        'href'  => admin_url('admin.php?page=wpforo-usergroups&action=add'),
        'parent' => 'new-content'
    );
    $wp_admin_bar->add_node( $args );

    $args = array(
        'id'    => 'new-phrase',
        'title' => __('New Phrase', 'wpforo'),
        'href'  => admin_url('admin.php?page=wpforo-phrases&action=add'),
        'parent' => 'new-content'
    );
    $wp_admin_bar->add_node( $args );

	if( WPF()->current_user_groupid == 1 ||
	    WPF()->current_user_groupid == 2 ||
	    WPF()->perm->usergroup_can('vm') ||
	    ( WPF()->perm->usergroup_can('cf') &&
	      WPF()->perm->usergroup_can('ef') &&
	      WPF()->perm->usergroup_can('df') )
	){
		$args = array(
			'id'    => 'wpf-community',
			'title' => __('Community', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-community')
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->perm->usergroup_can('cf') && WPF()->perm->usergroup_can('ef') && WPF()->perm->usergroup_can('df') ){
		$args = array(
			'id'    => 'wpf-forums',
			'title' => __('Forums', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-forums'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'    => 'wpf-new-forum',
			'title' => __('New Forum', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-forums&action=add'),
			'parent' => 'wpf-forums'
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->current_user_groupid == 1 ){
		$args = array(
			'id'    => 'wpf-settings',
			'title' => __('Settings', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-settings'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->current_user_groupid == 1 ){
		$args = array(
			'id'    => 'wpf-tools',
			'title' => __('Tools', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-tools'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->perm->usergroup_can('aum') ){
		$args = array(
			'id'    => 'wpf-moderation',
			'title' => __('Moderation', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-moderations'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->perm->usergroup_can('vm') ){
		$args = array(
			'id'    => 'wpf-members',
			'title' => __('Members', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-members'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->current_user_groupid == 1 ){
		$args = array(
			'id'    => 'wpf-usergroups',
			'title' => __('Usergroups', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-usergroups'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'    => 'wpf-new-ugroup',
			'title' => __('New UserGroup', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-usergroups&action=add'),
			'parent' => 'wpf-usergroups'
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->current_user_groupid == 1 ){
		$args = array(
			'id'    => 'wpf-phrases',
			'title' => __('Phrases', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-phrases'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
		$args = array(
			'id'    => 'wpf-new-phrase',
			'title' => __('New Phrase', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-phrases&action=add'),
			'parent' => 'wpf-phrases'
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->current_user_groupid == 1 ){
		$args = array(
			'id'    => 'wpf-themes',
			'title' => __('Themes', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-themes'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
    }
	if( WPF()->current_user_groupid == 1 ){
		$args = array(
			'id'    => 'wpf-addons',
			'title' => __('Addons', 'wpforo'),
			'href'  => admin_url('admin.php?page=wpforo-addons'),
			'parent' => 'wpf-community'
		);
		$wp_admin_bar->add_node( $args );
    }

}
add_action( 'admin_bar_menu', 'wpforo_add_adminbar_links', 999 );

function wpforo_create_cache(){
	WPF()->cache->create();
}
add_action( 'wp_footer', 'wpforo_create_cache', 10 );

function wpforo_redirect_to_custom_lostpassword() {
    if( !wpforo_feature('resetpass-url') ) return;

    if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
        if ( is_user_logged_in() ) {
            wp_redirect( wpforo_home_url() );
            exit;
        }

        wp_redirect( wpforo_home_url( '?wpforo=lostpassword' ) );
        exit;
    }
}
add_action('login_form_lostpassword', 'wpforo_redirect_to_custom_lostpassword');

function wpforo_redirect_to_custom_password_reset(){
    if( !wpforo_feature('resetpass-url') ) return;

    if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
        // Verify key / login combo
        $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
        if ( ! $user || is_wp_error( $user ) ) {
            if ( $user && $user->get_error_code() === 'expired_key' ) {
                WPF()->notice->add('The key is expired', 'error');
            } else {
                WPF()->notice->add('The key is invalid', 'error');
            }
            wp_redirect( wpforo_login_url() );
            exit;
        }

        $redirect_url = wpforo_home_url( '?wpforo=resetpassword&rp_key='.esc_attr( $_REQUEST['key'] ).'&rp_login='.esc_attr( $_REQUEST['login'] ) );

        wp_redirect( $redirect_url );
        exit;
    }
}
add_action( 'login_form_rp', 'wpforo_redirect_to_custom_password_reset' );
add_action( 'login_form_resetpass', 'wpforo_redirect_to_custom_password_reset' );

function wpforo_do_lostpass(){
    if( !wpforo_feature('resetpass-url') ) return;

    if( isset($_POST['user_login']) && $_POST['user_login'] ){
        $errors = retrieve_password();
        if ( is_wp_error( $errors ) ) {
            // Errors found
            $redirect_url = wpforo_home_url( '?wpforo=lostpassword' );
            WPF()->notice->add(join( ',', $errors->get_error_codes()), 'error');
        } else {
            // Email sent
            $redirect_url = wpforo_login_url();
            WPF()->notice->add('Email has been sent', 'success');
        }

        wp_safe_redirect( $redirect_url );
        exit();
    }
}
add_action('login_form_lostpassword', 'wpforo_do_lostpass');

function wpforo_do_password_reset() {
    if( !wpforo_feature('resetpass-url') ) return;

    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $rp_key = $_REQUEST['rp_key'];
        $rp_login = $_REQUEST['rp_login'];

        $user = check_password_reset_key( $rp_key, $rp_login );

        if ( ! $user || is_wp_error( $user ) ) {
            if ( $user && $user->get_error_code() === 'expired_key' ) {
                WPF()->notice->add('The key is expired', 'error');
            } else {
                WPF()->notice->add('The key is invalid', 'error');
            }
            wp_redirect( wpforo_login_url() );
            exit();
        }

        if ( isset( $_POST['pass1'] ) ) {
            if ( $_POST['pass1'] != $_POST['pass2'] ) {
                // Passwords don't match
                WPF()->notice->add('The password reset mismatch', 'error');
                $redirect_url = wpforo_home_url( '?wpforo=resetpassword&rp_key='.esc_attr( $rp_key ).'&rp_login='.esc_attr( $rp_login ) );;

                wp_redirect( $redirect_url );
                exit();
            }

            if ( empty( $_POST['pass1'] ) ) {
                // Password is empty
                WPF()->notice->add('The password reset empty', 'error');
                $redirect_url = wpforo_home_url( '?wpforo=resetpassword&rp_key='.esc_attr( $rp_key ).'&rp_login='.esc_attr( $rp_login ) );

                wp_redirect( $redirect_url );
                exit();
            }

            // Parameter checks OK, reset password
            reset_password( $user, $_POST['pass1'] );

            $creds = array('user_login' => sanitize_user($rp_login), 'user_password' => $_POST['pass1'] );
            wp_signon($creds);

            WPF()->notice->add('The password has been changed', 'success');
            wp_redirect( wpforo_login_url() );
            exit();
        }

        WPF()->notice->add('Invalid request.', 'error');
        wp_redirect( wpforo_login_url() );
        exit();

    }
}
add_action( 'login_form_rp', 'wpforo_do_password_reset' );
add_action( 'login_form_resetpass', 'wpforo_do_password_reset' );

function wpforo_replace_retrieve_password_message( $message, $key, $user_login, $user_data ) {
    if( wpforo_feature('resetpass-url') ){
		$reset_password_url = wpforo_home_url( '?wpforo=resetpassword&rp_key='.esc_attr( $key ).'&rp_login='.esc_attr( $user_login ) );
		if( empty(WPF()->sbscrb->options['reset_password_email_message']) ){
            $message = preg_replace('#<?http[^\r\n\t\s]+wp-login\.php[^\r\n\t\s]+#isu', "<$reset_password_url>", $message);
        }else{
            $message = str_replace(array('[user_login]', '[reset_password_url]'), array($user_login, "<$reset_password_url>"), WPF()->sbscrb->options['reset_password_email_message']);
        }
	}

    return $message;
}
add_filter( 'retrieve_password_message', 'wpforo_replace_retrieve_password_message', 10, 4 );