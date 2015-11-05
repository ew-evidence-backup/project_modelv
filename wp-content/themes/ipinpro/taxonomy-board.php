<?php get_header(); global $user_ID, $wp_rewrite; ?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="span4 hidden-phone"></div>
		<div class="span4 grand-title-wrapper">
			<?php 
			$board_info = $wp_query->get_queried_object();
			$board_user = $post->post_author;
			if (!isset($board_user)) {
				$board_user = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key ='_Board Parent ID' AND meta_value = %s LIMIT 1", $board_info->parent));
			}
			$user_info = get_user_by('id', $board_user);
			?>
			<h1>
			<?php 
				if ($board_info->parent == 0) {
					echo __('Pins From All', 'ipin') . ' ' . $user_info->display_name . '&#39;s ' . __('Boards', 'ipin');
				} else {
					echo $board_info->name;
				}
			?>
			</h1>

			<div class="grand-title-subheader">
				<div class="pull-right">
					<?php 
					if ($board_user != $user_ID) {
					?>
					<button class="btn follow ipin-follow<?php if ($followed = ipin_followed($board_info->term_id)) { echo ' disabled'; } ?>" data-author_id="<?php echo $board_user; ?>" data-board_id="<?php echo $board_info->term_id;  ?>" data-board_parent_id="<?php echo $board_info->parent; ?>" type="button"><?php if (!$followed) { _e('Follow', 'ipin'); } else { _e('Unfollow', 'ipin'); } ?></button>
					<?php 
					} 
					if ($board_info->parent && ($board_user == $user_ID || current_user_can('edit_others_posts'))) { ?>
					<a class="btn edit-board" href="<?php echo home_url('/boards-settings/?i=') . $board_info->term_id; ?>"><?php _e('Edit Board' , 'ipin'); ?></a>
					<?php } ?>
				</div>
			
				<div class="pull-left">
					<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $user_info->user_nicename; ?>/"><?php echo get_avatar($user_info->ID, '32'); ?></a> 
					<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $user_info->user_nicename; ?>/"><?php echo $user_info->display_name; ?></a>
				</div>
				
				<div class="clearfix"></div>
			</div>
			
			<div class="post-share-horizontal" style="background: none;">
				<?php $imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large'); ?>
				<iframe src="//www.facebook.com/plugins/like.php?href=<?php echo rawurlencode(home_url('/board/') . $wp_query->query['board']. '/'); ?>&amp;send=false&amp;layout=button_count&amp;width=75&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:75px; height:21px;" allowTransparency="true"></iframe>

				<a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo home_url('/board/') . $wp_query->query['board'] . '/'; ?>" data-text="<?php echo $board_info->name; ?>">Tweet</a>

				<div class="g-plusone" data-size="small" data-href="<?php echo home_url('/board/') . $wp_query->query['board'] . '/'; ?>"></div>
				<script>(function() {var po=document.createElement('script');po.type='text/javascript';po.async=true;po.src='https://apis.google.com/js/plusone.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(po,s);})();</script>

				<a class="pinterest" data-pin-config="beside" href="//pinterest.com/pin/create/button/?url=<?php echo rawurlencode(home_url('/board/') . $wp_query->query['board']. '/'); ?>&media=<?php echo rawurlencode($imgsrc[0]); ?>&description=<?php echo $board_info->name; ?>" data-pin-do="buttonPin"><img border="0" src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>
			
				<a id="post-email-board" class="btn btn-mini"><strong>@&nbsp; <?php _e('Email', 'ipin'); ?></strong></a>
			</div>

		</div>

		<div class="span4"></div>
	</div>
	
	<div id="post-email-board-overlay"></div>
	
	<div class="modal hide" id="post-email-board-box" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button id="post-email-board-close" type="button" class="close" aria-hidden="true">x</button>
			<h3><?php _e('Email This Board', 'ipin'); ?></h3>
		</div>
		
		<div class="modal-footer">
			<input type="text" id="recipient-name" /><span class="help-inline"> <?php _e('Recipient Name', 'ipin'); ?></span>
			<input type="email" id="recipient-email" /><span class="help-inline"> <?php _e('Recipient Email', 'ipin'); ?></span>
			<input type="hidden" id="email-board-id" value="<?php echo $board_info->term_id; ?>" />
			<textarea placeholder="<?php _e('Message (optional)', 'ipin'); ?>"></textarea>
			<input class="btn btn-primary" type="submit" disabled="disabled" value="<?php _e('Send Email', 'ipin'); ?>" id="post-email-board-submit" name="post-email-board-submit">
			<div class="ajax-loader-email-pin ajax-loader hide"></div>
		</div>
	</div>
	
<?php 
get_template_part('index', 'masonry');
get_footer();
?>