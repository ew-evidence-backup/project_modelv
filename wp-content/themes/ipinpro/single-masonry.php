<div id="post-masonry" class="container-fluid">
	<?php
	$pnum = intval($_GET['pnum']) ? $_GET['pnum'] : 1;
	$tags_related = get_the_tags();
	
	if (!empty($tags_related)) {
		$tag_in = array();
		foreach ($tags_related as $tag_related) {
			array_push($tag_in, $tag_related->term_id);
		}

		$args = array(
			'tag__in' => $tag_in,
			'post__not_in' => array(get_the_ID()),
			'paged' => $pnum
		);
	} else {
		$category_in = array();
		foreach (get_the_category() as $category) {
			array_push($category_in, $category->cat_ID);
		}
		
		$args = array(
			'category__in' => $category_in,
			'post__not_in' => array(get_the_ID()),
			'paged' => $pnum
		);
	}

	query_posts($args);
	$maxpage = $wp_query->max_num_pages;
	?>

	<?php if (have_posts()) { ?>
		<div id="ajax-loader-masonry" class="ajax-loader"></div>
	
		<h3 class="text-align-center"><?php _e('Related Pins', 'ipin'); ?></h3>
	<?php } ?>

	<div id="masonry">
		<?php $count_ad = 1; if (have_posts()) : while (have_posts()) : the_post(); ?>
		
		<?php if (of_get_option('frontpage1_ad') == $count_ad && of_get_option('frontpage1_ad_code') != '' && ($pnum == 1  || of_get_option('infinitescroll') == 'disable')) { ?>
		<div class="thumb">
			<div class="thumb-ad">				
				<?php eval('?>' . of_get_option('frontpage1_ad_code')); ?>
			</div>	 
		</div>
		<?php } ?>
		
		<?php if (of_get_option('frontpage2_ad') == $count_ad && of_get_option('frontpage2_ad_code') != '' && ($pnum == 1  || of_get_option('infinitescroll') == 'disable')) { ?>
		<div class="thumb">
			<div class="thumb-ad">				
				<?php eval('?>' . of_get_option('frontpage2_ad_code')); ?>
			</div>	 
		</div>
		<?php } ?>
		
		<?php if (of_get_option('frontpage3_ad') == $count_ad && of_get_option('frontpage3_ad_code') != '' && ($pnum == 1  || of_get_option('infinitescroll') == 'disable')) { ?>
		<div class="thumb">
			<div class="thumb-ad">				
				<?php eval('?>' . of_get_option('frontpage3_ad_code')); ?>
			</div>	 
		</div>
		<?php } ?>
		
		<?php if (of_get_option('frontpage4_ad') == $count_ad && of_get_option('frontpage4_ad_code') != '' && ($paged == 0 || $paged == 1 || of_get_option('infinitescroll') == 'disable')) { ?>
		<div class="thumb">
			<div class="thumb-ad">				
				<?php eval('?>' . of_get_option('frontpage4_ad_code')); ?>
			</div>	 
		</div>
		<?php } ?>
		
		<?php if (of_get_option('frontpage5_ad') == $count_ad && of_get_option('frontpage5_ad_code') != '' && ($paged == 0 || $paged == 1 || of_get_option('infinitescroll') == 'disable')) { ?>
		<div class="thumb">
			<div class="thumb-ad">				
				<?php eval('?>' . of_get_option('frontpage5_ad_code')); ?>
			</div>	 
		</div>
		<?php } ?>
		
		<div id="post-<?php the_ID(); ?>" <?php post_class('thumb'); ?>>
			<div class="thumb-holder">
				<div class="masonry-actionbar">
					<?php if (current_user_can('administrator') || current_user_can('editor') || current_user_can('author') || !is_user_logged_in()) { ?>
					<a class="ipin-repin btn btn-mini" data-post_id="<?php echo $post->ID ?>" href="#"><i class="icon-pushpin"></i> <?php _e('Repin', 'ipin'); ?></a>
					<?php } ?>
					<?php if ($post->post_author != $user_ID) { ?> 
					<button class="ipin-like btn btn-mini<?php if(ipin_liked($post->ID)) { echo ' disabled'; } ?>" data-post_id="<?php echo $post->ID ?>" data-post_author="<?php echo $post->post_author; ?>" type="button"><i class="icon-heart"></i> <?php _e('Like', 'ipin'); ?></button>
					<?php } else { ?>
					<a class="btn btn-mini" href="<?php echo home_url('/pins-settings/'); ?>?i=<?php the_ID(); ?>"><?php _e('Edit', 'ipin'); ?></a>
					<?php } ?>
					<a class="ipin-comment btn btn-mini" href="<?php the_permalink(); ?>#respond" data-post_id="<?php echo $post->ID ?>"><i class="icon-comment"></i> <?php _e('Comment', 'ipin'); ?></a>
				</div>
				
				<a class="featured-thumb-link" href="<?php the_permalink(); ?>">
					<?php
					if (of_get_option('price_currency') != '') {
						$post_price = get_post_meta($post->ID, '_Price', true);
						if ($post_price) {
							if (of_get_option('price_currency_position') == 'left') {
								$post_price_tag = of_get_option('price_currency') . $post_price;
							} else {
								$post_price_tag = $post_price . of_get_option('price_currency');
							}
					?>
						<div class="pricewrapper"><div class="pricewrapper-inner"><?php echo $post_price_tag; ?></div></div>
					<?php
						}
					}
					?>
						
					<?php
					//if is youtube or vimeo video
					$photo_source = get_post_meta($post->ID, "_Photo Source", true);
					if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', str_replace('&#038;', '&', $photo_source), $match) || (strpos(parse_url($photo_source, PHP_URL_HOST), 'vimeo.com') !== FALSE && sscanf(parse_url($photo_source, PHP_URL_PATH), '/%d', $video_id))) {
					?>
					<div class="featured-thumb-video"></div>
					<?php } ?>
					
					<?php
					$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),'medium');
					if ($imgsrc[0] == '') {
						$imgsrc[0] = get_template_directory_uri() . '/img/blank.gif';
					}
					
					//if is animted gif
					$animated_gif = false;
					if (substr($imgsrc[0], -4) == '.gif' && get_post_meta(get_post_thumbnail_id($post->ID), 'a_gif', true) == 'yes') {
							$animated_gif = true;
							$animated_gif_imgsrc_full = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
							echo '<div class="featured-thumb-gif"></div>';

					}
					?>
					<img class="featured-thumb<?php if ($animated_gif) echo ' featured-thumb-gif-class" data-animated-gif-src-medium="' . $imgsrc[0] .  '" data-animated-gif-src-full="' . $animated_gif_imgsrc_full[0]; ?>" src="<?php echo $imgsrc[0]; ?>" alt="<?php the_title_attribute(); ?>" style="width:<?php echo $imgsrc[1] ?>px;height:<?php echo $imgsrc[2] ?>px" />
				</a>
				
				<?php
				$tags = '';
				if (of_get_option('posttags') == 'enable') {
					$the_tags = get_the_tags();
					if ($the_tags) {
						foreach($the_tags as $the_tags) {
							$tags .= $the_tags->name . ', ';
						}
					}
				}
				?>

				<div class="post-title" data-title="<?php esc_attr_e($post->post_title); ?>" data-tags="<?php esc_attr_e(substr($tags, 0, -2)); ?>" data-price="<?php esc_attr_e($post_price); ?>" data-content="<?php esc_attr_e($post->post_content); ?>">
					<?php
					echo mb_strimwidth(strip_tags(get_the_title()), 0, 255, ' ...');

					$posttags = get_the_tags();
					if ($posttags) {
						echo '<div id="thetags">';
						
						foreach($posttags as $tag) {
							echo '<a href="' . get_tag_link($tag->term_id). '">' . $tag->name . '</a> '; 
						}
						
						echo '</div>';
					}
					?>
				</div>
			</div>
			
			<?php 
			$likes_number = get_post_meta($post->ID, '_Likes Count', true);
			$repins_number = get_post_meta($post->ID, '_Repin Count', true);
			$comments_number = get_comments_number();
			?>
			<div class="masonry-meta masonry-meta-comment-likes text-align-center">
			<?php
				if ($likes_number == '' || $likes_number == '0') {
					echo '<span id="likes-count-' . $post->ID . '" class="likes-count hide"></span>';
				} elseif ($likes_number == '1') {
					echo '<span id="likes-count-' . $post->ID . '" class="likes-count">1 ' . __('like', 'ipin') . '</span>';
				} else {
					echo '<span id="likes-count-' . $post->ID . '" class="likes-count">' . $likes_number . ' ' . __('likes', 'ipin') . '</span>';
				}

				if ($comments_number == '0') {
					echo '<span id="comments-count-' . $post->ID . '" class="comments-count hide"></span>';
				} elseif ($comments_number == '1') {
					echo '<span id="comments-count-' . $post->ID . '" class="comments-count">1 ' . __('comment', 'ipin') . '</span>';
				} else {
					echo '<span id="comments-count-' . $post->ID . '" class="comments-count">' . $comments_number . ' ' . __('comments', 'ipin') . '</span>';
				}
				
				if ($repins_number == '' || $repins_number == '0') {
					echo '<span id="repins-count-' . $post->ID . '" class="repins-count hide"></span>';
				} elseif ($repins_number == '1') {
					echo '<span id="repins-count-' . $post->ID . '" class="repins-count">1 ' . __('repin', 'ipin') . '</span>';
				} else {
					echo '<span id="repins-count-' . $post->ID . '" class="repins-count">' . $repins_number . ' ' . __('repins', 'ipin') . '</span>';	
				}
			?>
			</div>
			
			<div class="masonry-actionbar-mobile">
				<?php if (current_user_can('administrator') || current_user_can('editor') || current_user_can('author') || !is_user_logged_in()) { ?>
				<a class="ipin-repin btn btn-small" data-post_id="<?php echo $post->ID ?>" href="#"><?php _e('Repin', 'ipin'); ?></a>
				<?php } ?>
				<?php if ($post->post_author != $user_ID) { ?> 
				<button class="ipin-like btn btn-small<?php if(ipin_liked($post->ID)) { echo ' disabled'; } ?>" data-post_id="<?php echo $post->ID ?>" data-post_author="<?php echo $post->post_author; ?>" type="button"><?php _e('Like', 'ipin'); ?></button>
				<?php } else { ?>
				<a class="btn btn-small" href="<?php echo home_url('/pins-settings/'); ?>?i=<?php the_ID(); ?>"><?php _e('Edit', 'ipin'); ?></a>
				<?php } ?>
				<a class="ipin-comment btn btn-small" href="<?php the_permalink(); ?>#respond" data-post_id="<?php echo $post->ID ?>"><?php _e('Comment', 'ipin'); ?></a>
			</div>

			<div class="masonry-meta">
				<div class="masonry-meta-avatar"><a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . get_the_author_meta('user_nicename'); ?>/"><?php echo get_avatar(get_the_author_meta('ID'), '30'); ?></a></div>
				<div class="masonry-meta-comment">
					<span class="masonry-meta-author"><?php the_author_posts_link(); ?></span> 
					<?php 
						$original_post_id = get_post_meta($post->ID, "_Original Post ID", true);
						if ($original_post_id != '' && $original_post_id != 'deleted') {
							$original_postdata = get_post($original_post_id, 'ARRAY_A');
							$original_author = get_user_by('id', $original_postdata['post_author']);
						?>
						<?php _e('via', 'ipin'); ?> 
						<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $original_author->user_nicename; ?>/"><strong><?php echo $original_author->display_name; ?></strong></a>
						<?php
						}
						if ($board_info = get_the_terms($post->ID, 'board')) {
							_e('onto', 'ipin');
							foreach ($board_info as $board) {
							?> 
							<span class="masonry-meta-content"><strong><a href="<?php echo home_url('/board/'); ?><?php echo $board->term_id ?>/"><?php echo $board->name; ?></a></strong></span>
							<?php
							}
						}
					?>
				</div>
			</div>
				
			<?php
			if ('0' != $frontpage_comments_number = of_get_option('frontpage_comments_number')) {
			?> 
			<div id="masonry-meta-comment-wrapper-<?php echo $post->ID; ?>"> 
			<?php
				if ($comments_number >  $frontpage_comments_number) {
					$offset = $comments_number - $frontpage_comments_number;
				} else {
					$offset = 0;	
				}
				
				$args = array(
					'number' => $frontpage_comments_number,
					'post_id' => $post->ID,
					'order' => 'asc',
					'offset' => $offset,
					'status' => 'approve'
				);
				$comments = get_comments($args);
				foreach($comments as $comment) {
				?>
				<div class="masonry-meta">
					<?php $comment_author = get_user_by('id', $comment->user_id); ?>
					<div class="masonry-meta-avatar">
						<?php if ($comment_author) { ?>
						<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $comment_author->user_nicename; ?>/">
						<?php } ?>
							<?php echo get_avatar($comment->user_id, '30'); ?>
						<?php if ($comment_author) { ?>
						</a>
						<?php } ?>
					</div>
					<div class="masonry-meta-comment">
						<span class="masonry-meta-author">
							<?php if ($comment_author) { ?><a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $comment_author->user_nicename; ?>/"><?php } ?><?php echo $comment->comment_author; ?><?php if ($comment_author) { ?></a><?php } ?>
						</span>
						<span class="masonry-meta-comment-content"><?php echo $comment->comment_content; ?></span>
					</div>
				</div>
				<?php 
				}
				?>
				</div>
			<?php
			} 
				
			if (is_user_logged_in()) {
			?>
			<div id="masonry-meta-commentform-<?php echo $post->ID ?>" class="masonry-meta hide">
				<div class="masonry-meta-avatar"><?php echo get_avatar($user_ID, '30'); ?></div>
				<div class="masonry-meta-comment">
				<?php 
				$id_form = 'commentform-' . $post->ID;
				$id_submit = 'submit-' . $post->ID;
				
				comment_form(array(
					'id_form' => $id_form,
					'id_submit' => $id_submit,
					'title_reply' => '',
					'cancel_reply_link' => __('X Cancel reply', 'ipin'),
					'comment_notes_before' => '',
					'comment_notes_after' => '',
					'logged_in_as' => '',
					'label_submit' => __('Post Comment', 'ipin'),
					'comment_field' => '<textarea placeholder="' . __('Add a comment...', 'ipin') . '" id="comment" name="comment" aria-required="true"></textarea>'
				));
				?>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php
		$count_ad++;
		endwhile; 
		else :
		?>

		<?php 
		endif;
		wp_reset_query(); 
		?>
	</div>
	
	<?php if ($maxpage != 0) { ?>
	<div id="navigation">
		<ul class="pager">			
			<?php if ($pnum != 1 && $maxpage >= $pnum) { ?>
			<li id="navigation-previous">
				<a href="<?php $permalink = get_permalink(); if (substr($permalink,-1) == '/') { $permalink = substr($permalink,0,-1); } echo $permalink . '/' . $source . '?pnum=' . ($pnum-1); ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
			</li>
			<?php } ?>
			
			<?php if ($maxpage != 1 && $maxpage != $pnum) { ?>
			<li id="navigation-next">
				<a href="<?php $permalink = get_permalink(); if (substr($permalink,-1) == '/') { $permalink = substr($permalink,0,-1); } echo $permalink . '/' . $source . '?pnum=' . ($pnum+1); ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>

	<div id="scrolltotop"><a href="#"><i class="icon-chevron-up"></i><br /><?php _e('Top', 'ipin'); ?></a></div>
</div>