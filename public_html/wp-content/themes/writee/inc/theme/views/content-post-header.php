<?php 
/****************************************/
## Blog post header content.
/***************************************/

global $post;

$date_format = 'l, F j, Y';

?>
	<div class="entry-header">
		
		<?php if (is_single()) : ?>
			
			<div class="entry-meta">
				<span class="entry-cat"><?php the_category(' ')?></span>
			</div>
			
		<?php  elseif(is_archive()):  ?>
		
			<div class="entry-meta">
				<span class="entry-cat"><?php the_category(' ')?></span>
			</div>
			
		<?php elseif(is_front_page() || is_home()):?>
		
			<div class="entry-meta">
				<span class="entry-cat"><?php the_category(' ')?></span>
			</div>
			
		<?php else: ?>
			
			<div class="entry-meta">
				<span class="entry-cat"><?php the_category(' ')?></span>
			</div>
			
		<?php endif; ?>
		
		<?php 
		if (! is_single()) :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );

		 else: 
			the_title( '<h2 class="entry-title">', '</h2>' );

		endif; 
		
	   ?>
		
		<?php if (is_single()) : ?>
			
			<div class="entry-meta">
				
				<span class="entry-author"><?php echo __('By', 'writee'); ?> <?php the_author_posts_link(); ?> </span>
				<span class="entry-date"><?php echo __('on', 'writee'); ?> <a href="<?php echo get_month_link(get_the_time('Y'), get_the_time('m')); ?>"><?php the_time($date_format); ?></a></span>
				
			</div>	
			
		<?php elseif(is_archive()): ?>
		
			
			<div class="entry-meta">
				
				<span class="entry-author"><?php echo __('By', 'writee'); ?> <?php the_author_posts_link(); ?> </span>
				
				<span class="entry-date"><?php echo __('on', 'writee'); ?> <a href="<?php echo get_month_link(get_the_time('Y'), get_the_time('m')); ?>"><?php the_time($date_format); ?></a></span>
				
			</div>
			
			
		<?php elseif(is_front_page() || is_home()): ?> 
		
			
			<div class="entry-meta">
				
				<span class="entry-author"><?php echo __('By', 'writee'); ?> <?php the_author_posts_link(); ?> </span>
				
				<span class="entry-date"><?php echo __('on', 'writee'); ?> <a href="<?php echo get_month_link(get_the_time('Y'), get_the_time('m')); ?>"><?php the_time($date_format); ?></a></span>
				
			</div>	
			 
			
		<?php else: ?>
			
			<div class="entry-meta">
				
				<span class="entry-author"><?php echo __('By', 'writee'); ?> <?php the_author_posts_link(); ?> </span>
				
				<span class="entry-date"><?php echo __('on', 'writee'); ?> <a href="<?php echo get_month_link(get_the_time('Y'), get_the_time('m')); ?>"><?php the_time($date_format); ?></a></span>
				
			</div>
			
			
		<?php endif; ?>
		
	</div>
