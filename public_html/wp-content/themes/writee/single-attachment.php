<?php 
/******************************************/
## Writty theme single-attachment.php
## attachment object detail page.
/******************************************/
global $post;


$sidebar_layout = esc_attr(get_theme_mod('wrt_sidebar_position', '2cr'));


get_header();
?>
	<section class="site-main <?php if($sidebar_layout == '2cl') echo 'with-left-sidebar'; elseif($sidebar_layout == '2cr') echo 'with-right-sidebar'; else echo '';?> ">
	
        <div class="site-container">
            <div class="site-row">
			
				<div class="site-content <?php if($sidebar_layout == '2cl'  || $sidebar_layout == '2cr') echo ' with-sidebar '; else echo ' with-no-sidebar '; ?>" id="site-content" role="main">
				<?php 
					if(have_posts()):
						while(have_posts()): the_post();
				?>
					<article id="entry-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-row">
							<div class="entry-header" style="text-align:center;">
								<div class="entry-meta">
									<h2 class="entry-title"><?php the_title() ?></h2>
								</div>
							</div>								
							<div class="entry-full-center">
								<div class="entry-content">
									<?php the_content();?>
								</div>
							</div>
						</div>
					<article>
					<?php
						endwhile;
					else:
						get_template_part('inc/theme/views/content-none'); 
					endif; 
					?>
					<?php 
						if ( comments_open() || get_comments_number() ) :
							comments_template('', true );
						endif;
					?>
					
				</div>
				
				<?php if($sidebar_layout == '2cl' || $sidebar_layout == '2cr'): ?>
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
