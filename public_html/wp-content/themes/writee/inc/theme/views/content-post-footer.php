<?php 
/****************************************/
## Blog post header content.
/***************************************/

?>

	<div class="entry-footer">
		<?php if(!is_single()):  ?>
		<a href="<?php echo get_the_permalink()?>" title="<?php the_title_attribute() ?>" class="entry-button"><?php echo __('Continue Reading', 'writee'); ?></a>
		<?php endif; ?>
		
		<?php if (is_single()) : ?>
		<div class="entry-comments">
			
			<a href="<?php comments_link(); ?>"><span class="fa fa-comment"></span>
			<span class="comments-counting"><?php comments_number('0','1','%'); ?></span>
			</a>
			
		</div>
		<?php elseif(is_archive()):  ?>
		<div class="entry-comments">
			
			<a href="<?php comments_link(); ?>"><span class="fa fa-comment"></span>
			<span class="comments-counting"><?php comments_number('0','1','%'); ?></span>
			</a>
			
		</div>
		<?php elseif(is_front_page() || is_home()):  ?>
		<div class="entry-comments">
			
			<a href="<?php comments_link(); ?>"><span class="fa fa-comment"></span>
			<span class="comments-counting"><?php comments_number('0','1','%'); ?></span>
			</a>
			
		</div>
		
		<?php else: ?>
		<div class="entry-comments">
			
			<a href="<?php comments_link(); ?>"><span class="fa fa-comment"></span>
			<span class="comments-counting"><?php comments_number('0','1','%'); ?></span>
			</a>
			
		</div>
		
		<?php endif; ?>
		<div class="entry-share">
			<?php echo writee_social_sharing_buttons() ?>
		</div>
		
	</div>
