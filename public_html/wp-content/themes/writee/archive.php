<?php 
/******************************************************/
## Writty theme archive.php
## display blog taxonomies and categories post list.
/*****************************************************/
global $wp_query;

$layout = 'standard';
$layout_css_class = 'compact-view';
$sidebar_layout   = esc_attr(get_theme_mod('wrt_sidebar_position', '2cr'));

get_header();

?>
	<section class="site-main <?php if($sidebar_layout == '2cl') echo 'with-left-sidebar'; elseif($sidebar_layout == '2cr') echo 'with-right-sidebar'; else echo '';?> ">
	
        <div class="site-container">
            <div class="site-row">
				
				<div class="site-content <?php echo $layout_css_class; if($sidebar_layout == '2cl'  || $sidebar_layout == '2cr') echo ' with-sidebar '; else { if($layout == 'standard')echo ' site-col-center ';  echo ' with-no-sidebar ';} ?>" id="site-content" role="main">
                    
					<div class="site-content-header">
						
						<h6 class="site-header-title">
							<?php if ( is_day() ) : ?>
								<?php printf( __( 'Daily Archives: <span>%s</span>', 'writee' ), get_the_date() ); ?>
							<?php elseif ( is_month() ) : ?>
								<?php printf( __( 'Monthly Archives: <span>%s</span>', 'writee' ), get_the_date( 'F Y' ) ); ?>
							<?php elseif ( is_year() ) : ?>
								<?php printf( __( 'Yearly Archives: <span>%s</span>', 'writee' ), get_the_date( 'Y' ) ); ?>
							<?php else : ?>
								<?php _e( 'Blog Archives', 'writee' ); ?>
							<?php endif; ?>
						</h6>
						
					</div>
	
					<?php 
						get_template_part('inc/theme/layouts/'.$layout);
					?>
					
					<?php if ($wp_query->max_num_pages > 1) writee_pagenavi(); ?>
				</div>
				
				<?php 
				if($sidebar_layout == '2cl' || $sidebar_layout == '2cr'): ?>
				<div class="site-sidebar" id="sidebar" role="complementary">
					<?php get_sidebar(); ?>
				</div>
				<?php endif; ?>
				
				
			</div>
		</div>
	</section>
<?php 
get_footer();
?>
