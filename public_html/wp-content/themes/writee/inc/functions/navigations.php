<?php 

/********************************************/
## Primary and other navigation register
/********************************************/

add_action( 'after_setup_theme', 'writee_register_primary_menu' );

function writee_register_primary_menu() {
 
  register_nav_menu( 'primary', __( 'Primary Menu', 'writee' ) );
  
}

?>