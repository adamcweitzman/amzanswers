<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;
 
class wpForoUsergroup{
    public $default;
    public $default_groupid;
    public $cans;

	static $cache = array( 'usergroup' => array(), 'user' => array() );
	
	function __construct(){
        $this->init_defaults();
        $this->init_options();
	}

    private function init_defaults(){
        $this->default = new stdClass;

        $this->default->default_groupid = 3;

        $this->default->cans = array (
            'cf'   => 'Dashboard - Can create forum',
            'ef'   => 'Dashboard - Can edit forum',
            'df'   => 'Dashboard - Can delete forum',
            'vm'   => 'Dashboard - Members Menu',
            'aum'  => 'Dashboard - Moderation Menu',
            'em'   => 'Dashboard - Can edit member',
            'bm'   => 'Dashboard - Can ban member',
            'dm'   => 'Dashboard - Can delete member',
            'vmg'  => 'Dashboard - Usergroup Menu',
            'aup'  => 'Front - Can pass moderation',
            'vmem' => 'Front - Can view members',
            'vprf' => 'Front - Can view profiles',
			'vpra' => 'Front - Can view member activity',
			'vprs' => 'Front - Can view member subscriptions',
			
            'upa'  => 'Front - Can upload avatar',
            'ups'  => 'Front - Can have signature',
            'va'   => 'Front - Can view avatars',
			
            'vmu'  => 'Front - Can view member username',
            'vmm'  => 'Front - Can view member email',
            'vmt'  => 'Front - Can view member title',
            'vmct' => 'Front - Can view member custom title',
            'vmr'  => 'Front - Can view member reputation',
            'vmw'  => 'Front - Can view member website',
            'vmsn' => 'Front - Can view member social networks',
            'vmrd' => 'Front - Can view member reg. date',
            'vmlad'=> 'Front - Can view member last active date',
            'vip'  => 'Front - Can view member ip address',
            'vml'  => 'Front - Can view member location',
            'vmo'  => 'Front - Can view member occupation',
            'vms'  => 'Front - Can view member signature',
            'vmam' => 'Front - Can view member about me',
            'vmpn' => 'Front - Can view member phone number',
            'vwpm' => 'Front - Can write PM'
        );
    }

    private function init_options(){
        $this->default_groupid = get_wpf_option('wpforo_default_groupid', $this->default->default_groupid);
        $this->cans = apply_filters('wpforo_usergroup_cans', $this->default->cans);
    }
	
	function usergroup_list_data(){
		$ugdata = array();
		$ugroups = WPF()->db->get_results('SELECT `groupid`, `name`, `access`, `color` FROM '.WPF()->db->prefix.'wpforo_usergroups ORDER BY `name` ', ARRAY_A);
		foreach($ugroups as $ugroup){
			$user_count = WPF()->db->get_var('SELECT COUNT(userid) FROM '.WPF()->db->prefix.'wpforo_profiles WHERE `groupid` = ' . intval($ugroup['groupid']));
			$ugdata[$ugroup['groupid']]['groupid'] = $ugroup['groupid'];
			$ugdata[$ugroup['groupid']]['name'] = wpforo_phrase($ugroup['name'], FALSE);
			$ugdata[$ugroup['groupid']]['count'] = intval($user_count);
			$ugdata[$ugroup['groupid']]['access'] = $ugroup['access'];
			$ugdata[$ugroup['groupid']]['color'] = $ugroup['color'];
		}
		return $ugdata;
	}
	
	function add($title, $cans = array(), $description = '', $role = 'subscriber', $access = 'standard', $color = '', $visible = 1 ){
		$i = 2;
		$real_title = $title;
		while( WPF()->db->get_var(
						WPF()->db->prepare(
								"SELECT `groupid` FROM `".WPF()->db->prefix."wpforo_usergroups` 
									WHERE `name` = '%s'", sanitize_text_field($title) )))
		{
			$title = $title . '-' . $i;
			$i++;
		}

		$cans = wpforo_parse_args( $cans, array_map('wpforo_return_zero', $this->cans) );

		if(	WPF()->db->insert(
			WPF()->db->prefix . 'wpforo_usergroups',
				array( 
					'name'		=> sanitize_text_field($title), 
					'cans' 	    => serialize( $cans ), 
					'description' => $description,
					'utitle' => sanitize_text_field($real_title), 
					'role' => $role,
					'access' => $access,
					'color' => $color,
					'visible' => $visible
				),
				array( 
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d'
				)
			)
		){
			$ugid = WPF()->db->insert_id;
			$forums = WPF()->forum->get_forums();
			if(!empty($forums) && $ugid){
				foreach($forums as $forum){
					if(isset($forum['permissions'])){
						$permissions = unserialize($forum['permissions']);
						if(!empty($permissions)){
							$permissions[$ugid] = $access;
							$permissions = serialize($permissions);
							WPF()->db->update( WPF()->db->prefix . 'wpforo_forums', array('permissions' => $permissions), array('forumid' => $forum['forumid']), array('%s'), array('%d') );
						}
					}
				}
			}
			WPF()->notice->add('User group successfully added', 'success');
			return WPF()->db->insert_id;
		}
		
		WPF()->notice->add('User group add error', 'error');
		return FALSE;
	}
	
	function edit( $groupid, $title, $cans, $description = '', $role = NULL, $access = NULL, $color = '', $visible = 1 ){
		
		if( $groupid == 1 ) return false;
		if( !current_user_can('administrator') ){
			WPF()->notice->add('Permission denied', 'error');
			return FALSE;	
		}
		
		$cans = wpforo_parse_args( $cans, array_map('wpforo_return_zero', $this->cans) );
		$usergroup = $this->get_usergroup( $groupid );
		$role = is_null($role) ? $usergroup['role'] : $role;
		$access = is_null($access) ? $usergroup['access'] : $access;
		
		if( FALSE !== WPF()->db->update(
				WPF()->db->prefix . 'wpforo_usergroups',
				array( 
					'name' => sanitize_text_field($title), 
					'cans' => serialize( $cans ), 
					'description' => $description,
					'utitle' => $usergroup['utitle'],
					'role' => $role,
					'access' => $access,
					'color' => $color,
					'visible' => $visible
				),
				array( 'groupid' => intval($groupid) ),
				array( 
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d'
				),
				array( '%d' ))
		){
			WPF()->notice->add('User group successfully edited', 'success');
			return $groupid;
		}
		
		WPF()->notice->add('User group edit error', 'error');
		return FALSE;
	}
	
	function delete(){
		
		if( !current_user_can('administrator') ){
			WPF()->notice->add('Permission denied', 'error');
			return FALSE;	
		}
		
		if( isset($_GET['action']) && $_GET['action'] == 'del' && isset($_GET['gid']) && $_GET['gid'] != 1 && $_GET['gid'] != 4 ){
			$status = FALSE;
			extract($_POST['usergroup'], EXTR_OVERWRITE);
			$mergeid = intval($mergeid);
			$insert_gid = $_GET['gid'];
			#################################################### USERS
			if(isset($mergeid)){
				$status = WPF()->db->query("UPDATE `".WPF()->db->prefix ."wpforo_profiles` SET `groupid` = " . intval($mergeid) . " WHERE `groupid` = " . intval($insert_gid) );
				$notice = wpforo_phrase('Usergroup has been successfully deleted. All users of this usergroup have been moved to the usergroup you\'ve chosen', false);
			}else{
				$status = WPF()->db->query("UPDATE `".WPF()->db->prefix ."wpforo_profiles` SET `status` = 'trashed' WHERE `groupid` = " . intval($insert_gid) );
				$notice = wpforo_phrase('Usergroup has been successfully deleted.');
			}
			#################################################### END USERS
			if( $status !== FALSE ){
				if( WPF()->db->query("DELETE FROM `".WPF()->db->prefix ."wpforo_usergroups` WHERE `groupid` = " . intval($insert_gid) ) ){
					WPF()->notice->add($notice, 'success');
					return TRUE;
				}
			}
		}
		WPF()->notice->add('Can\'t delete this Usergroup', 'error');
		return FALSE;
	}
	
	function get_usergroup( $groupid = 4 ){
		// Guest UsergroupID = 4
		$cache = WPF()->cache->on('memory_cashe');
		if( $cache && isset(self::$cache['usergroup'][$groupid]) ){
			return self::$cache['usergroup'][$groupid];
		}
		$usergroup = WPF()->db->get_row("SELECT * FROM `".WPF()->db->prefix."wpforo_usergroups` WHERE `groupid` = ".intval($groupid), ARRAY_A);
		if($cache && isset($groupid)){
			self::$cache['usergroup'][$groupid] = $usergroup;
		}
		return $usergroup;
	}
	
	function get_usergroups( $field = 'full' ){
        $cache = WPF()->cache->on('memory_cashe');
        if( $cache && isset(self::$cache['usergroups'][$field])  ) return self::$cache['usergroups'][$field];

		if( $field == 'full' ){
            $results = WPF()->db->get_results("SELECT * FROM `".WPF()->db->prefix."wpforo_usergroups`", ARRAY_A);
		}else{
            $results = WPF()->db->get_col("SELECT `$field` FROM `".WPF()->db->prefix."wpforo_usergroups`");
		}

        if( $cache ) self::$cache['usergroups'][$field] = $results;
        return $results;
	}
	
	function get_groupid_by_userid( $userid ){
		$cache = WPF()->cache->on('memory_cashe');
		if( $cache && isset(self::$cache['user'][$userid]) ){
			return self::$cache['user'][$userid];
		}
		$groupid = WPF()->db->get_var("SELECT `groupid` FROM `".WPF()->db->prefix ."wpforo_profiles` WHERE `userid` = " . intval($userid));
		if($cache && isset($groupid)){
			self::$cache['user'][$userid] = $groupid;
		}
		return $groupid;
	}
	
	function show_selectbox( $groupid = 0, $exclude = array() ){
		if( !$groupid = intval($groupid) ) $groupid = (isset($_POST['usergroup']['groupid'])) ? intval($_POST['usergroup']['groupid']) : 0;
		if( !$groupid ) $groupid = $this->default_groupid;
		if( empty($exclude) && isset($_GET['gid']) && intval($_GET['gid']) ) $exclude[] = intval($_GET['gid']);
		$ugroups = $this->usergroup_list_data();
		foreach($ugroups as $ugroup){
			if( in_array($ugroup['groupid'], $exclude) ) continue;
			echo '<option value="'.esc_attr($ugroup['groupid']).'" '.($groupid == $ugroup['groupid'] ? 'selected' : '').'>' . esc_html( __($ugroup['name'], 'wpforo') ) . '</option>';
		}
	}
	
	function get_visible_usergroup_ids(){
		return $results = WPF()->db->get_col("SELECT `groupid` FROM `".WPF()->db->prefix."wpforo_usergroups` WHERE `visible` = 1");
	
	}
	
}