<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;

class wpForoCache{
	public $object;
	public $dir;
	public $lang;
	
	function __construct(){
		$this->init();
	}
	
	private function init(){
		$wp_upload_dir = wp_upload_dir();
		$uplds_dir = $wp_upload_dir['basedir']."/wpforo";
		$cache_dir = $uplds_dir . "/cache";
		if(!is_dir($uplds_dir)) wp_mkdir_p($uplds_dir);
		if(!is_dir($cache_dir)) $this->dir($cache_dir);
		$this->dir = $cache_dir;
		$this->lang = get_locale();
	}
	
	public function get_key( $type = 'html' ){	
		if($type == 'html'){ 
			$ug = WPF()->current_user_groupid;
			return md5( preg_replace('|(.+)\#.+?$|is', '$1', $_SERVER['REQUEST_URI']) . $ug );
		}
	}
	
	private function dir( $cache_dir ){

		$dirs = array(  $cache_dir, 
						$cache_dir . '/forum', 
						$cache_dir . '/topic', 
						$cache_dir . '/post', 
						$cache_dir . '/item', 
						$cache_dir . '/item/forum', 
						$cache_dir . '/item/topic', 
						$cache_dir . '/item/post');
		
		$this->mkdir( $dirs );
	}
	
	private function mkdir( $dirs ){
		foreach( $dirs as $dir ){
			wp_mkdir_p($dir);
			wpforo_write_file( $dir . '/index.html' , '' );
			wpforo_write_file( $dir . '/.htaccess' , 'deny from all' );
		}
	}
	
	public function on( $type = 'object_cashe' ){
		if( $type == 'html_cashe' ){
			if( wpforo_feature('output-buffer') && function_exists('ob_start') ){
				return wpforo_feature( 'html_cashe');
			}
			else{
				return false;
			}
		}
		else{
			return wpforo_feature($type);
		}
	}
	
	public function get( $key, $type = 'loop' ){
		
		$template = WPF()->current_object['template'];
		$loop_templates = array('forum', 'topic', 'post');
		if( $type == 'loop' && $template ){
			if( $this->exists($key, $template) ){
				if( in_array( $template, $loop_templates) ){
					$cache_file = $this->dir . '/' . $template . '/' . $key;
					$array = wpforo_get_file_content( $cache_file );
					return @unserialize( $array );
				}
			}
		}
	}
	
	public function get_item( $id, $type = 'post' ){
		if( $id ){ $key = $id . '_' . $this->lang;
			if( $this->exists( $key, 'item', $type )){
				$cache_file = $this->dir . '/item/' . $type . '/' . $key;
				$array = wpforo_get_file_content( $cache_file );
				return @unserialize( $array );
			}
		}
	}
	
	public function get_html(){
		$template = WPF()->current_object['template'];
		if( $template == 'forum' ){
			$key = $this->get_key();
			if( $this->exists($key, $template) ){
				$cache_file = $this->dir . '/' . $template . '/' . $key;
				$html = wpforo_get_file_content( $cache_file );
				return $this->filter($html);
			}
		}
		return false;
	}
	
	public function html( $content ){
		if(!$this->on('html_cashe')) return false;
		$template = WPF()->current_object['template'];
		if( $template == 'forum' ){
			$key = $this->get_key();
			$this->create_html( $content, $template, $key );
		}
	}
	
	public function create( $mode = 'loop', $cache = array(), $type = 'post' ){
	
		if(!$this->on('object_cashe')) return false;
		$template = WPF()->current_object['template'];
		if( $template == 'forum' ) { $this->check( $this->dir . '/item/post' ); }
		
		if( $mode == 'loop' && $template ){
			if( $template == 'forum' || $template == 'topic' || $template == 'post'){
				$cache = WPF()->forum->get_cache('forums');
				$this->create_files( $cache, $template );
				$cache = WPF()->topic->get_cache('topics');
				$this->create_files( $cache, $template );
				$cache = WPF()->post->get_cache('posts');
				$this->create_files( $cache, $template );
			}
		}
		elseif( $mode == 'item' && !empty($cache) ){
			$this->create_files( $cache, 'item', $type );
		}
	}
	
	public function create_files( $cache = array(), $template = '', $type = '' ){
		if( !empty($cache) ){
			$type = ( $type ) ? $type . '/' : '' ;
			foreach( $cache as $key => $object ){
				if($template == 'item') $key = $key . '_' . $this->lang;
				if( !$this->exists($key, $template) ){
					$object = serialize($object);
					wpforo_write_file( $this->dir . '/' . $template . '/' . $type . $key , $object );
				}
			}	
		}
	}
	
	public function create_html( $content, $template = '', $key = '' ){
		if( $content ){
			if( !$this->exists($key, $template) ){
				wpforo_write_file( $this->dir . '/' . $template . '/' . $key , $content );
			}
		}
	}
	
	public function filter( $html = '' ){
		//exit();
		$html = preg_replace('|<div[\s\t]*id=\"wpf\-msg\-box\"|is', '<div style="display:none;"', $html);
		return $html;
	}
	
	#################################################################################
	/**
	 * Cleans forum cache
	 *
	 * @since 1.2.1
	 *
	 * @param	integer		Item ID		(e.g.: $topicid or $postid) | (!) ID is 0 on dome actions (e.g.: delete actions)
	 * @param	string		Item Type	(e.g.: 'forum', 'topic', 'post', 'user', 'widget', etc...)
	 * @param   array		Item data as array		
	 *
	 * @return	NULL	
	 */
	 
	public function clean( $id, $template, $item = array() ){
		
		$dirs = array();
		$userid = (isset($item['userid']) && $item['userid']) ? $item['userid'] : 0;
		$postid = (isset($item['postid']) && $item['postid']) ? $item['postid'] : 0;
		$topicid = (isset($item['topicid']) && $item['topicid']) ? $item['topicid'] : 0;
		$forumid = (isset($item['forumid']) && $item['forumid']) ? $item['forumid'] : 0;
		
		if( $template == 'forum' || $template == 'forum-soft' ){
			$id = isset($id) ? $id : $forumid;
			if( $template == 'forum'){
				$dirs = array( $this->dir . '/forum' );
			}
			if( $id ){
				$file = $this->dir . '/item/forum/' . $id . '_' . $this->lang; $this->clean_file( $file );
			}
		}
		elseif( $template == 'topic' || $template == 'topic-soft' ){
			$id = isset($id) ? $id : $topicid;
			if( $template == 'topic'){
				$dirs = array( $this->dir . '/forum', $this->dir . '/topic', $this->dir . '/post' );
			}
			if( $forumid ){
				$file = $this->dir . '/item/forum/' . $forumid . '_' . $this->lang; $this->clean_file( $file );
			}
			if( $id ){
				$file = $this->dir . '/item/topic/' . $id . '_' . $this->lang; $this->clean_file( $file );
				$postid = (isset($item['first_postid']) && $item['first_postid']) ? $item['first_postid'] : 0;
				if( $postid ) $file = $this->dir . '/item/post/' . $postid . '_' . $this->lang; $this->clean_file( $file );
			}
		}
		elseif( $template == 'post' || $template == 'post-soft' ){
			$id = isset($id) ? $id : $postid;
			if( $template == 'post'){
				$dirs = array( $this->dir . '/forum', $this->dir . '/topic', $this->dir . '/post' );
			}
			if( $forumid ){
				$file = $this->dir . '/item/forum/' . $forumid . '_' . $this->lang; $this->clean_file( $file );
			}
			if( $topicid ){
				$file = $this->dir . '/item/topic/' . $topicid . '_' . $this->lang; $this->clean_file( $file );
			}
			if( $id ){
				$file = $this->dir . '/item/post/' . $id . '_' . $this->lang; $this->clean_file( $file );
			}
		}
		elseif( $template == 'user' ){
			//no cache//
		}
		elseif( $template == 'loop' ){
			$dirs = array( $this->dir . '/forum', $this->dir . '/topic', $this->dir . '/post' );
		}
		elseif( $template == 'item' ){
			$dirs = array( $this->dir . '/item/post', $this->dir . '/item/topic', $this->dir . '/item/forum' );
		}
		else{
			$dirs = array( $this->dir . '/forum', $this->dir . '/topic', $this->dir . '/post', $this->dir . '/item/post', $this->dir . '/item/topic', $this->dir . '/item/forum' );
		}
		
		if(!empty($dirs)){
			foreach( $dirs as $dir ){ 
				$this->clean_files( $dir ); 
			}
		}
		
	}
	
	public function clean_files( $directory ) {
		$directory_ns = trim( $directory, '/') . '/*';
		$directory_ws = '/' . trim( $directory, '/') . '/*';
		$glob = glob( $directory_ns ); if( empty($glob) ) $glob = glob( $directory_ws );
		foreach( $glob as $item ) {
			if( strpos($item, 'index.html') !== FALSE || strpos($item, '.htaccess') !== FALSE ) continue;
			if( !is_dir($item) ) unlink( $item );
		}
	}
	
	public function clean_file( $file ) {
		if( !is_dir($file) && file_exists($file) ) unlink( $file );
	}
	
	public function exists( $key, $template, $type = '' ){
		$type = ( $type ) ? $type . '/' : '' ;
		if( file_exists( $this->dir . '/' . $template . '/' . $type . $key ) ){
			return true;
		}
		else{
			return false;
		}
	}
	
	public function check( $directory ){
		$filecount = 0;
		if( class_exists('FilesystemIterator') && is_dir($directory) ){
			$fi = new FilesystemIterator( $directory, FilesystemIterator::SKIP_DOTS );
			$filecount = iterator_count($fi);
		}
		if( !$filecount ){
			$directory_ns = trim( $directory, '/') . '/*';
			$directory_ws = '/' . trim( $directory, '/') . '/*';
			$files = glob( $directory_ns ); 
			if( empty($files) ) $files = glob( $directory_ws );
			$filecount = count($files);
		}
		if( $filecount > 1000 ) {
			$this->clean_files( $directory );
		}
	}
	
}