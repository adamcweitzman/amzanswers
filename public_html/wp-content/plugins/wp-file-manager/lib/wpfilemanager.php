<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>     
<div class="wrap">
<?php $fm_nonce = wp_create_nonce( 'wp-file-manager' ); 
$wp_fm_lang = get_transient( 'wp_fm_lang' );
$wp_fm_theme = get_transient( 'wp_fm_theme' );
?>
<script>
var security_key = "<?php echo $fm_nonce;?>";
var fmlang = "<?php echo isset($_GET['lang']) ? $_GET['lang'] : ($wp_fm_lang !== false) ? $wp_fm_lang : 'en';?>";
</script>
<?php
$this->load_custom_assets();
$this->load_help_desk();
?>
<div class="wp_fm_lang" style="float:left">
<h2><img src="<?php echo plugins_url( 'images/wp_file_manager.png', dirname(__FILE__) ); ?>"><?php  _e('WP File Manager', 'wp-file-manager'); ?> <a href="http://filemanager.webdesi9.com/product/file-manager" class="button button-primary" target="_blank" title="Click to Buy PRO"><?php  _e('Buy PRO', 'wp-file-manager'); ?></a></h2>
</div>

<div class="wp_fm_lang" style="float:right">
<h2>
<select name="theme" id="fm_theme">
<option value="light" <?php echo (isset($_GET['theme']) && $_GET['theme'] == 'light') ? 'selected="selected"' : ($wp_fm_theme !== false) && $wp_fm_theme == 'light' ? 'selected="selected"' : '';?>><?php  _e('Light - Default', 'wp-file-manager'); ?></option>
<?php foreach($this->get_themes() as $theme) { ?>
<option value="<?php echo $theme;?>" <?php echo (isset($_GET['theme']) && $_GET['theme'] == $theme) ? 'selected="selected"' : ($wp_fm_theme !== false) && $wp_fm_theme == $theme ? 'selected="selected"' : '';?>><?php echo ucfirst($theme);?></option>
<?php } ?>
</select><select name="lang" id="fm_lang">
<?php foreach($this->fm_languages() as $name => $lang) { ?>
<option value="<?php echo $lang;?>" <?php echo (isset($_GET['lang']) && $_GET['lang'] == $lang) ? 'selected="selected"' : ($wp_fm_lang !== false) && $wp_fm_lang == $lang ? 'selected="selected"' : '';?>><?php echo $name;?></option>
<?php } ?>
</select></h2>
</div><div style="clear:both"></div>
<div id="wp_file_manager"><center><img src="<?php echo plugins_url( 'images/loading.gif', dirname(__FILE__) ); ?>" class="wp_fm_loader" /></center></div>
</div>