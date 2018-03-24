jQuery(window).load(function(e) {
				jQuery('.wfmrs').delay( 10000 ).slideDown('slow');
			});
   jQuery(document).ready(function() {
				jQuery('#wp_file_manager').elfinder({
					url : ajaxurl,
					customData : {action: 'mk_file_folder_manager', _wpnonce: security_key },
					uploadMaxChunkSize : 1048576000000,
					defaultView : 'list',
					height: 500,
					lang : fmlang,
				});				
				jQuery('.close_fm_help').on('click', function(e) {
					var what_to_do = jQuery(this).data('ct');
					 jQuery.ajax({
						 type : "post",
						 url : ajaxurl,
						 data : {action: "mk_fm_close_fm_help", what_to_do : what_to_do},
						 success: function(response) {
							jQuery('.wfmrs').slideUp('slow');
						 }
						});	});
						
   jQuery('#fm_lang').change(function(e) {
    var fm_lang = jQuery(this).val();
	window.location.href = 'admin.php?page=wp_file_manager&lang='+fm_lang;
});	
jQuery('#fm_theme').change(function(e) {
    var fm_theme = jQuery(this).val();
	window.location.href = 'admin.php?page=wp_file_manager&theme='+fm_theme;
});								
						
});