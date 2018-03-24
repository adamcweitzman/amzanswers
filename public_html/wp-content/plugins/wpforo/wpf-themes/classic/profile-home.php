<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;

	$fields = wpforo_profile_fields();
?>

<div class="wpforo-profile-home">

    <div class="wpf-profile-section wpf-mi-section">
        <div class="wpf-table">
            <?php wpforo_fields( $fields ); ?>
        </div>
    </div>

	<?php if( WPF()->perm->usergroup_can('vmr') ): ?>
        <div class="wpf-profile-section wpf-ma-section">
            <div class="wpf-profile-section-head">
            	<i class="fa fa-bar-chart"></i>
				<?php wpforo_phrase('Member Activity'); ?>
            </div>
            <div class="wpf-profile-section-body">
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-pencil"></i></div>
                        <div class="wpf-statbox-value"><?php wpforo_print_number($posts, true) ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Forum Posts') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-file-text"></i></div>
                        <div class="wpf-statbox-value"><?php echo (isset($stat['topics'])) ? (int)wpforo_print_number($stat['topics']) : 0 ; ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Topics') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-question"></i></div>
                        <div class="wpf-statbox-value"><?php wpforo_print_number($questions, true) ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Questions') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-check"></i></div>
                        <div class="wpf-statbox-value"><?php wpforo_print_number($answers, true) ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Answers') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-comment"></i></div>
                        <div class="wpf-statbox-value"><?php wpforo_print_number($comments, true) ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Question Comments') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-thumbs-up"></i> </div>
                        <div class="wpf-statbox-value"><?php wpforo_print_number(WPF()->member->get_votes_and_likes_count( $userid ), true);  ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Liked') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-thumbs-up fa-flip-horizontal"></i></div>
                        <div class="wpf-statbox-value"><?php wpforo_print_number(WPF()->member->get_user_votes_and_likes_count( $userid ), true);  ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Received Likes') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-star"></i></div>
                        <div class="wpf-statbox-value"><?php echo WPF()->member->rating_level( $posts, FALSE ) ?>/10</div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Rating') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-pencil-square"></i></div>
                        <div class="wpf-statbox-value"><?php echo WPF()->member->blog_posts($userid, $user_email) ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Blog Posts') ?></div>
                    </div>
                </div>
                <div class="wpf-statbox wpfbg-9">
                    <div class="wpf-statbox-body">
                        <div class="wpf-statbox-icon wpfcl-5"><i class="fa fa-comments"></i></div>
                        <div class="wpf-statbox-value"><?php echo WPF()->member->blog_comments($userid, $user_email) ?></div>
                        <div class="wpf-statbox-title"><?php wpforo_phrase('Blog Comments') ?></div>
                    </div>
                </div>
            	<div class="wpf-clear"></div>
             </div>
        </div>
    <?php endif; ?>

</div>