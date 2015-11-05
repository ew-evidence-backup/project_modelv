<?php get_header(); global $user_ID; ?>

<div class="container-fluid">
	<div class="row-fluid">
		<div id="userbar" class="navbar">
			<div class="navbar-inner">
				<ul class="nav">
					<li<?php if (!isset($_GET['q'])) { echo ' class="active"'; } ?>><a href="<?php echo home_url('/?s=') . str_replace(' ','+',get_search_query()); ?>"><strong><?php _e('Pins', 'ipin'); ?></strong></a></li>
					<?php if ($user_ID) { ?>
					<li<?php if ($_GET['q'] == 'ownpins') { echo ' class="active"'; } ?>><a href="<?php echo home_url('/?s=') . str_replace(' ','+',get_search_query()); ?>&q=ownpins"><strong><?php _e('My Own Pins', 'ipin'); ?></strong></a></li>
					<?php } ?>
					<li<?php if ($_GET['q'] == 'boards') { echo ' class="active"'; } ?>><a href="<?php echo home_url('/?s=') . str_replace(' ','+',get_search_query()); ?>&q=boards"><strong><?php _e('Boards', 'ipin'); ?></strong></a></li>
					<li<?php if ($_GET['q'] == 'users') { echo ' class="active"'; } ?>><a href="<?php echo home_url('/?s=') . str_replace(' ','+',get_search_query()); ?>&q=users"><strong><?php _e('Users', 'ipin'); ?></strong></a></li>
					<li>
				</ul>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>

	<?php
	if ($_GET['q'] == 'boards') {
		//exclude the parent boards
	    $board_exclude = get_transient('search_board_exclude');
	    if ($board_exclude === false) {
			$board_exclude = $wpdb->get_col(
				"SELECT meta_value 
				FROM $wpdb->usermeta
				WHERE meta_key = '_Board Parent ID'
				"
			);

	        set_transient('search_board_exclude', $board_exclude, 10800);
		}
		
		$boards = get_terms('board', array(search => get_search_query(), 'hide_empty' => false, 'exclude' => $board_exclude));
		$boards_count = count($boards);
		
		if ($boards_count > 0) {
		?>
		<div id="user-profile-boards">
		<?php	
			$pnum = intval($_GET['pnum']) ? $_GET['pnum'] : 1;
			$boards_per_page = 24;
			$maxpage = ceil($boards_count/$boards_per_page);
			$boards_paginated = get_terms('board', array('search' => get_search_query(), 'hide_empty' => false, 'orderby' => 'name', 'exclude' => $board_exclude, 'number' => $boards_per_page, 'offset' => ($pnum - 1) * $boards_per_page));
			
			foreach ($boards_paginated as $board) {
				$board_id = $board->term_id;
				$board_parent_id = $board->parent;
				$board_name = $board->name;
				$board_count = $board->count;
				$board_slug = $board->slug;
				
				$loop_board_args = array(
					'posts_per_page' => 5,
					'tax_query' => array(
						array(
							'taxonomy' => 'board',
							'field' => 'id',
							'terms' => $board_id
						)
					)
				);
				
				$loop_board = new WP_Query($loop_board_args);
				?>
				<div class="board-mini">
					<h4>
						<a href="<?php echo home_url('/board/' . $board_id . '/'); ?>">
							<?php echo $board_name; ?>
						<?php if ($board_count > 0) { ?>
						</a>
						<?php } ?>
					</h4>
					<p><?php echo $board_count ?> <?php if ($board_count == 1) { _e('pin', 'ipin'); } else { _e('pins', 'ipin'); } ?></p>
					
					<div class="board-photo-frame">
						<?php if ($board_count > 0) { ?>
						<a href="<?php echo home_url('/board/' . $board_id . '/'); ?>">
						<?php } ?>
						<?php
						$count= 1;
						$post_array = array();
						while ($loop_board->have_posts()) : $loop_board->the_post();
							if ($count == 1) {
								$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($loop_board->ID),'medium');
								$imgsrc = $imgsrc[0];
								array_unshift($post_array, $imgsrc);
							} else {
								$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($loop_board->ID),'thumbnail');
								$imgsrc = $imgsrc[0];
								array_unshift($post_array, $imgsrc);
							}
							$count++;
						endwhile;
						wp_reset_query();
						
						$count = 1;
				
						$post_array_final = array_fill(0, 5, '');
						
						foreach ($post_array as $post_imgsrc) {
							array_unshift($post_array_final, $post_imgsrc);
							array_pop($post_array_final);
						}
						
						foreach ($post_array_final as $post_final) {
							if ($count == 1) {
								if ($post_final !=='') {
								?>
								<div class="board-main-photo-wrapper">
									<img src="<?php echo $post_final; ?>" class="board-main-photo" alt="" />
								</div>
								<?php
								} else {
								?>
								<div class="board-main-photo-wrapper">
								</div>
								<?php 
								}
							} else if ($post_final !=='') {
								?>
								<div class="board-photo-wrapper">
								<img src="<?php echo $post_final; ?>" class="board-photo" alt="" />
								</div>
								<?php
							} else {
								?>
								<div class="board-photo-wrapper">
								</div>
								<?php
							}
							$count++;
						}
						?>
						</a>
						
						<?php if ($user_info->ID != $user_ID) { ?>
							<button class="btn follow ipin-follow<?php if ($followed = ipin_followed($board_id)) { echo ' disabled'; } ?>" data-author_id="<?php echo $user_info->ID; ?>" data-board_id="<?php echo $board_id;  ?>" data-board_parent_id="<?php echo $board_parent_id; ?>" type="button"><?php if (!$followed) { _e('Follow', 'ipin'); } else { _e('Unfollow', 'ipin'); } ?></button>
						<?php } else { ?>
							<a class="btn edit-board" href="<?php echo home_url('/boards-settings/?i=') . $board_id; ?>"><?php _e('Edit Board', 'ipin'); ?></a>
						<?php } ?>
					</div>
				</div>
			<?php } //end foreach	?>
			
			<?php if ($maxpage != 0) { ?>
			<div id="navigation">
				<ul class="pager">				
					<?php if ($pnum != 1 && $maxpage >= $pnum) { ?>
					<li id="navigation-previous">
						<a href="<?php echo home_url('/?s=') . str_replace(' ','+',get_search_query()); ?>&q=boards&pnum=<?php echo $pnum-1; ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
					</li>
					<?php } ?>
					
					<?php if ($maxpage != 1 && $maxpage != $pnum) { ?>
					<li id="navigation-next">
						<a href="<?php echo home_url('/?s=') . str_replace(' ','+',get_search_query()); ?>&q=boards&pnum=<?php echo $pnum+1; ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
			
			<div class="clearfix"></div>
			<div id="scrolltotop"><a href="#"><i class="icon-chevron-up"></i><br /><?php _e('Top', 'ipin'); ?></a></div>
		</div></div>
	
	
		<?php } else { ?>
			<div class="bigmsg">
				<h2><?php _e('Nothing yet.', 'ipin'); ?></h2>
			</div>
		</div>
		<?php }
			
	} else if ($_GET['q'] == 'users') {
		$pnum = $_GET['pnum'] ? intval($_GET['pnum']) : 1;
		$args = array(
			'search' => '*' . get_search_query() . '*',
			'search_columns' => array('user_login'),
			'orderby' => 'display_name',
			'number' => get_option('posts_per_page'),
			'offset' => ($pnum-1) * get_option('posts_per_page')
		 );
	
		$search_user_query = new WP_User_Query($args);
		$maxpage = ceil($search_user_query->total_users/get_option('posts_per_page'));
		$user_info = get_user_by('id', $user_ID);
	
		if ($search_user_query->total_users > 0) {
			echo '<div id="user-profile-follow" class="row-fluid">';
			foreach ($search_user_query->results as $search_user) {
				?>
				<div class="follow-wrapper">
					<div class="post-content">
					<?php
					if ($search_user->ID != $user_info->ID) {
					?>
					<button class="btn follow ipin-follow<?php $parent_board = get_user_meta($search_user->ID, '_Board Parent ID', true); if ($followed = ipin_followed($parent_board)) { echo ' disabled'; } ?>" data-author_id="<?php echo $search_user->ID; ?>" data-board_id="<?php echo $parent_board; ?>" data-board_parent_id="0" data-disable_others="no" type="button"><?php if (!$followed) { _e('Follow', 'ipin'); } else { _e('Unfollow', 'ipin'); } ?></button>
					<?php } else { ?>
					<a class="btn follow disabled"><?php _e('Myself!', 'ipin'); ?></a>
					<?php } ?>
						<div class="user-avatar">
							<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $search_user->user_nicename; ?>/"><?php echo get_avatar($search_user->ID , '32'); ?></a>
						</div>
						
						<div class="user-name">
							<h4><a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $search_user->user_nicename; ?>/"><?php echo $search_user->display_name; ?></a></h4>
						</div>
					</div>
				</div>
			<?php 
			}
			
			if ($maxpage != 0) { ?>
			<div id="navigation">
				<ul class="pager">				
					<?php if ($pnum != 1 && $maxpage >= $pnum) { ?>
					<li id="navigation-previous">
						<a href="<?php echo home_url('/?s=') . str_replace(' ','+',get_search_query()); ?>&q=users&pnum=<?php echo $pnum-1; ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
					</li>
					<?php } ?>
					
					<?php if ($maxpage != 1 && $maxpage != $pnum) { ?>
					<li id="navigation-next">
						<a href="<?php echo home_url('/?s=') . str_replace(' ','+',get_search_query()); ?>&q=users&pnum=<?php echo $pnum+1; ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
			<?php }
			echo '</div><div class="clearfix"></div></div>';
		} else {
		?>
			<div class="row-fluid">		
				<div class="span12">
					<div class="bigmsg">
							<h2><?php _e('Nobody yet.', 'ipin'); ?></h2>
					</div>
				</div>
			</div>
		</div>
		<?php
		}
	} else if ($_GET['q'] == 'ownpins') {
		$args = array(
			'author' => $user_ID,
			's' => get_search_query()
		);
		query_posts($args);
		
		get_template_part('index', 'masonry');		
	} else {
		get_template_part('index', 'masonry');
	}
	?>

<?php get_footer(); ?>