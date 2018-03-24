<?php 
/*************************************************************/
## Theme header style . It's header style, part of the theme.
/*************************************************************/

?>
	<header id="site-header" class="site-header">
		<div class="site-header-top">
			<div class="site-container">
				<div class="site-row">
					<?php writee_get_social(); ?>
					
					<div class="site-header-top-left site-column-9">
					
						<?php if ( has_nav_menu( 'primary' ) ) : ?>
						<nav class="primary-navigation" id="primary-navigation">
							
							<?php 
							$writtyNav = array(
								'theme_location'  => 'primary',
								'menu'            => '',
								'container'       => false,
								'container_class' => '',
								'container_id'    => '',
								'menu_class'      => '',
								'menu_id'         => '',
								'echo'            => true,
								'fallback_cb'     => '',
								'before'          => '',
								'after'           => '',
								'link_before'     => '',
								'link_after'      => '',
								'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
								'depth'           => 0,
								
							); 
							
							wp_nav_menu($writtyNav);
							
							?>
						</nav>
						<a href="<?php echo esc_url( home_url() ) ; ?>" class="mobile-navigation-toggle"></a>
						<?php else: ?>
						<nav class="primary-navigation" id="primary-navigation">
							<ul>
								<li><a href="<?php echo esc_url( home_url() ) ; ?>"> 
								<?php esc_html_e('HOME', 'writee');?> </a></li>
								<li><a href="<?php echo esc_url( 'https://wp-themes.com/?page_id=2' ) ; ?>"> 
								<?php esc_html_e('ABOUT US', 'writee');?> </a></li>
								<li class="menu-item-has-children"><a href="<?php echo esc_url( 'https://wp-themes.com/?page_id=46' ) ; ?>"> 
								<?php esc_html_e('PARENT PAGE', 'writee');?> </a>
								<span class="menu-item-arrow"></span>
								<ul class="sub-menu">
									<li><a href="<?php echo esc_url( 'https://wp-themes.com/?page_id=49' ) ; ?>"> 
								<?php esc_html_e('SUB PAGE', 'writee');?> </a></li>
								</ul>
								</li>
							</ul>
						</nav>
						<a href="<?php echo esc_url( home_url() ) ; ?>" class="mobile-navigation-toggle"></a>
						<?php endif ?>
					</div>
					
					
				</div>
			</div>
		</div>
		
		<div class="site-header-bottom">
			<div class="site-container">
				<div class="site-row">
					<div class="site-header-middle-center">
						<div  class="site-logo">
						
							
							<?php 
								$writee_header_text = esc_attr(get_theme_mod('header_text'));
								if ($writee_header_text): ?>
								<h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?>
								</a></h1>
							<?php 
								elseif(has_custom_logo()): 
							?>
								<h1><?php the_custom_logo(); ?></h1>
								
							<?php
								else:
							?>
								<h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?>
								</a></h1>
								<p class="tagline"><?php bloginfo( 'description' ); ?></p>

							<?php endif;  ?>
						
							<?php if($writee_header_text): ?>
								<p class="tagline"><?php bloginfo( 'description' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
    
    </header>
    <!-- / header -->
