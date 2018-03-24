<?php if ( ! defined( 'ABSPATH' ) ) exit; 
 if(isset($_POST['submit'])) { 
		   $save = update_option('wp_file_manager_settings', $_POST);
		  if($save) {
			  echo '<script>';
			  echo 'window.location.href="?page=wp_file_manager_root&status=1"';
			  echo '</script>';
		  } else {
			  echo '<script>';
			  echo 'window.location.href="?page=wp_file_manager_root&status=2"';
			  echo '</script>';
		  }
	   }
$settings = get_option('wp_file_manager_settings');	 
?>
<div class="wrap">
<h3><?php _e('Settings - General', 'wp-file-manager');?></h3>
<?php $path = str_replace('\\','/', ABSPATH); ?>
<form action="" method="post">
<table class="form-table">
<tr>
<th><?php _e('Public Root Path','file-manager-advanced')?></th>
<td>
<input name="public_path" type="text" id="public_path" value="<?php echo isset($settings['public_path']) && !empty($settings['public_path']) ? $settings['public_path'] : $path;?>" class="regular-text">
<p class="description"><?php _e('File Manager Root Path, you can change according to your choice.','wp-file-manager')?></p>
<p>Default: <code><?php echo $path ?></code></p>
<p style="color:#F00" class="description"><?php _e('Please change this carefully, Wrong path can lead file manager plugin to go down.','file-manager-advanced')?></p>
</td>
</tr>
</table>
<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
</form>
</div>