<?php
/*
Template Name: _pins_settings
*/

if (!is_user_logged_in()) { wp_redirect(wp_login_url($_SERVER['REQUEST_URI'])); exit; }

if (!current_user_can('edit_posts')) { wp_redirect(home_url('/')); exit; }

error_reporting(0); get_header(); global $user_ID;

if ($_GET['i']) {  //edit pin
	$pin_id = intval($_GET['i']);
	$pin_info = get_post($pin_id);
	$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($pin_info->ID),'medium');
	$terms = get_the_terms($pin_info->ID, 'board');
	
	if ($terms) {
		foreach ($terms as $term) {
			$board_id = $term->term_id;
		}
	}
	
	if (($pin_info->post_author == $user_ID || current_user_can('edit_others_posts')) && $pin_info->post_type == 'post') {
	?>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span4 hidden-phone"></div>
	
			<div class="span4 usercp-wrapper usercp-pins">
				<h1><?php _e('Edit Pin', 'ipin') ?></h1>
				
				<div class="error-msg"></div>
				
				<br />
				
				<div id="pin-upload-postdata-wrapper">
					<div class="postdata-box-photo"><img id="thumbnail" src="<?php echo $imgsrc[0]; ?>" /></div>
					<form id="pin-edit-form">
						<?php if (of_get_option('form_title_desc') != 'separate') { ?>
						<textarea id="pin-title" placeholder="<?php _e('Describe your pin...', 'ipin'); ?>"><?php echo $pin_info->post_title; ?></textarea>
						<?php } else { ?>
						<textarea id="pin-title" placeholder="<?php _e('Title...', 'ipin'); ?>"><?php echo $pin_info->post_title; ?></textarea>
						<textarea id="pin-content" placeholder="<?php _e('Description...', 'ipin'); ?>"><?php echo $pin_info->post_content; ?></textarea>
						<?php } ?>
						
						<?php if (of_get_option('htmltags') == 'enable') { ?>
							<div class="description_instructions">
								<?php _e('Allowed HTML tags:', 'ipin'); ?> &lt;strong> &lt;em> &lt;a> &lt;blockquote>
							</div>
						<?php } ?>

						<?php
						if (of_get_option('posttags') == 'enable') {
							$the_tags = get_the_tags($pin_info->ID);
							if ($the_tags) {
								foreach($the_tags as $the_tags) {
									$tags .= $the_tags->name . ', ';
								}
							}
						?>
						<div class="input-prepend">
							<span class="add-on pull-left"><i class="icon-tags"> </i></span>
							<input type="text" name="tags" id="tags" value="<?php echo substr($tags, 0, -2); ?>" placeholder="<?php _e('Tags e.g. comma, separated', 'ipin'); ?>" />
						</div>
						<?php } ?>
						
						<?php if (of_get_option('price_currency') != '') { ?>
							<?php if (of_get_option('price_currency_position') == 'right') { ?>
							<div class="input-append">
								<input class="pull-left" type="text" name="price" id="price" value="<?php echo get_post_meta($pin_info->ID, '_Price', true); ?>" placeholder="<?php _e('Price e.g. 23.45', 'ipin'); ?>" />
								<span class="add-on"><?php echo of_get_option('price_currency'); ?></span>
							</div>
							<?php } else { ?>
							<div class="input-prepend">
								<span class="add-on pull-left"><?php echo of_get_option('price_currency'); ?></span>
								<input type="text" name="price" id="price" value="<?php echo get_post_meta($pin_info->ID, '_Price', true); ?>" placeholder="<?php _e('Price e.g. 23.45', 'ipin'); ?>" />
							</div>
							<?php } ?>
						<?php } ?>
						
						<div class="input-prepend<?php if (of_get_option('source_input') != 'enable') echo ' hide'; ?>">
							<span class="add-on pull-left"><i class="icon-link"> </i></span>
							<input type="text" name="source" id="source" value="<?php echo get_post_meta($pin_info->ID, '_Photo Source', true); ?>" placeholder="<?php _e('Source e.g. http://domain.com/link', 'ipin'); ?>" />
						</div>

						<?php wp_dropdown_categories(array('taxonomy' => 'board', 'parent' => get_user_meta($pin_info->post_author, '_Board Parent ID', true), 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true, 'selected' => $board_id, 'order' => 'DESC')); ?>
						<input type="text" class="board-add-new" id="board-add-new" placeholder="<?php _e('Enter new board title', 'ipin'); ?>" />
						<?php
						if (of_get_option('blog_cat_id')) {
							wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => __('Category for New Board', 'ipin'), 'exclude_tree' => of_get_option('blog_cat_id') . ',1', 'hide_empty' => 0, 'name' => 'board-add-new-category', 'orderby' => 'name'));
						} else {
							wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => __('Category for New Board', 'ipin'), 'exclude' => '1', 'hide_empty' => 0, 'name' => 'board-add-new-category', 'orderby' => 'name'));
						}
						?>
						<a id="pin-postdata-add-new-board" class="btn btn-mini pull-right"><?php _e('Add new board...', 'ipin'); ?></a>
						<input type="hidden" name="pid" id="pid" value="<?php echo $pin_id; ?>" />
						<div class="clearfix"></div>
						<input class="btn btn-primary btn-large" type="submit" name="pinit" id="pinit" value="<?php _e('Save Pin', 'ipin'); ?>" /> 
						<div class="ajax-loader-add-pin ajax-loader hide"></div>
					</form>
				</div>
				<hr style="border-top: 1px solid #ccc" />
				<button class="btn ipin-delete-pin" type="button"><?php _e('Delete Pin', 'ipin') ?></button>
			</div>
	
			<div class="span4"></div>
		</div>
	
		<div id="scrolltotop"><a href="#"><i class="icon-chevron-up"></i><br /><?php _e('Top', 'ipin'); ?></a></div>
	</div>
	<?php } else { ?>
	<div class="row-fluid">			
		<div class="span12">
			<div class="bigmsg">
				<h2><?php _e('No pins found.', 'ipin'); ?></h2>
			</div>
		</div>
	</div>

<?php }
} else if ($_GET['m'] == 'bm') {  //bookmarklet
	$imgsrc = 'http' . $_GET['imgsrc'];
	$source = esc_url_raw(urldecode('http' . $_GET['source']));
	$title = esc_textarea(html_entity_decode(rawurldecode(stripslashes($_GET['title'])), ENT_QUOTES, 'UTF-8'));
	
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
		
	if (function_exists("curl_init")) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $imgsrc);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$image = curl_exec($ch);
		curl_close($ch);
	} elseif (ini_get("allow_url_fopen")) {
		$image = file_get_contents($imgsrc, false, $context);
	}

	if (!$image) {
		$error = 'error';
	}

	$filename = time() . str_shuffle("gnk48");
	$file_array['tmp_name'] = WP_CONTENT_DIR . "/" . $filename . '.tmp';
	$filetmp = file_put_contents($file_array['tmp_name'], $image);
	
	if (!$filetmp) {
		@unlink($file_array['tmp_name']);
		$error = 'error';
	}

	$imageTypes = array (
		1, //IMAGETYPE_GIF
		2, //IMAGETYPE_JPEG
		3 //IMAGETYPE_PNG
	);

	$imageinfo = getimagesize($file_array['tmp_name']);
	$width = @$imageinfo[0];
	$height = @$imageinfo[1];
	$type = @$imageinfo[2];
	$mime = @$imageinfo ['mime'];

	if (!in_array ( $type, $imageTypes)) {
		@unlink($file_array['tmp_name']);
		$error = 'error';
	}

	if ($width <= 1 && $height <= 1) {
		@unlink($file_array['tmp_name']);
		$error = 'error';
	}

	if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
		@unlink($file_array['tmp_name']);
		$error = 'error';
	}
	
	switch($type) {
		case 1:
			$ext = '.gif';
					
			//check if is animated gif
			if(($fh = @fopen($file_array['tmp_name'], 'rb')) && $error != 'error') {
				while(!feof($fh) && $frames < 2) {
					$chunk = fread($fh, 1024 * 100); //read 100kb at a time
					$frames += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
			   }
			}
			fclose($fh);

			break;
		case 2:
			$ext = '.jpg';
			break;
		case 3:
			$ext = '.png';
			break;
	}
	$original_filename = preg_replace('/[^(\x20|\x61-\x7A)]*/', '', strtolower(str_ireplace($ext, '', $title))); //preg_replace('/[^(\x48-\x7A)]*/' strips non-utf character. Ref: http://www.ssec.wisc.edu/~tomw/java/unicode.html#x0000
	$file_array['name'] = strtolower(substr($original_filename, 0, 100)) . '-' . $filename . $ext;

	$attach_id = media_handle_sideload($file_array, 'none'); //use none for $post_id so that image is uploaded to current month/year directory. Else $post_id = this pins page id, which will point to older month/year directory
	
	if ($frames > 1) {
		update_post_meta($attach_id, 'a_gif', 'yes');
	}

	if (is_wp_error($attach_id)) {
		@unlink($file_array['tmp_name']);
		$error = 'error';
	}
		
	if ($error == 'error') {
	?>
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span4 hidden-phone"></div>
				<div class="span4 usercp-wrapper usercp-pins">
					<div class="error-msg">
						<div class="alert">
						<strong><?php _e('Invalid image file.', 'ipin'); ?></strong>
						</div>
					</div>
				</div>
				<div class="span4"></div>
			</div>
		</div>
	<?php
	} else {
			$thumbnail = wp_get_attachment_image_src($attach_id, 'medium');
			?>
			<div class="container-fluid">
				<div class="row-fluid">
					<div class="span4 hidden-phone"></div>
			
					<div class="span4 usercp-wrapper usercp-pins">
						<h1><?php _e('Add Pin', 'ipin') ?></h1>
						
						<div class="error-msg"></div>
						
						<br />
						
						<div id="pin-upload-postdata-wrapper">
						<div class="postdata-box-photo"><img id="thumbnail" src="<?php echo $thumbnail[0]; ?>" /></div>
						<form id="pin-postdata-form">
							<?php if (of_get_option('form_title_desc') != 'separate') { ?>
							<textarea id="pin-title" placeholder="<?php _e('Describe your pin...', 'ipin'); ?>"><?php echo $title; ?></textarea>
							<?php } else { ?>
							<textarea id="pin-title" placeholder="<?php _e('Title...', 'ipin'); ?>"><?php echo $title; ?></textarea>
							<textarea id="pin-content" placeholder="<?php _e('Description...', 'ipin'); ?>"></textarea>
							<?php } ?>

							<?php if (of_get_option('htmltags') == 'enable') { ?>
								<div class="description_instructions">
									<?php _e('Allowed HTML tags:', 'ipin'); ?> &lt;strong> &lt;em> &lt;a> &lt;blockquote>
								</div>
							<?php } ?>
							
							<?php if (of_get_option('posttags') == 'enable') { ?>
								<div class="input-prepend">
									<span class="add-on pull-left"><i class="icon-tags"> </i></span>
									<input type="text" name="tags" id="tags" value="" placeholder="<?php _e('Tags e.g. comma, separated', 'ipin'); ?>" />
								</div>
							<?php } ?>
							
							<?php if (of_get_option('price_currency') != '') { ?>
								<?php if (of_get_option('price_currency_position') == 'right') { ?>
								<div class="input-append">
									<input class="pull-left" type="text" name="price" id="price" value="" placeholder="<?php _e('Price e.g. 23.45', 'ipin'); ?>" />
									<span class="add-on"><?php echo of_get_option('price_currency'); ?></span>
								</div>
								<?php } else { ?>
								<div class="input-prepend">
									<span class="add-on pull-left"><?php echo of_get_option('price_currency'); ?></span>
									<input type="text" name="price" id="price" value="" placeholder="<?php _e('Price e.g. 23.45', 'ipin'); ?>" />
								</div>
								<?php } ?>
							<?php } ?>
							
							
							<div class="input-prepend<?php if (of_get_option('source_input') != 'enable') echo ' hide'; ?>">
								<span class="add-on pull-left"><i class="icon-link"> </i></span>
								<input type="text" name="photo_data_source" id="photo_data_source" value="<?php echo $source; ?>" placeholder="<?php _e('Source e.g. http://domain.com/link', 'ipin'); ?>" />
							</div>
							
							<?php  
							$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
							$board_children_count = wp_count_terms('board', array('parent' => $board_parent_id));
							if (is_array($board_children_count) || $board_children_count == 0) {
								echo '<span id="noboard">';
								wp_dropdown_categories(array('show_option_none' => __('Add a new board first...', 'ipin'), 'taxonomy' => 'board', 'parent' => $board_parent_id, 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true));
								echo '</span>';
								$noboard = 'yes';
							} else {
								wp_dropdown_categories(array('taxonomy' => 'board', 'parent' => $board_parent_id, 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true, 'order' => 'DESC'));
							}
							?>
							<input type="text" class="board-add-new" id="board-add-new" placeholder="<?php _e('Enter new board title', 'ipin'); ?>" />
							<?php
							if (of_get_option('blog_cat_id')) {
								wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => __('Category for New Board', 'ipin'), 'exclude_tree' => of_get_option('blog_cat_id') . ',1', 'hide_empty' => 0, 'name' => 'board-add-new-category', 'orderby' => 'name'));
							} else {
								wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => __('Category for New Board', 'ipin'), 'exclude' => '1', 'hide_empty' => 0, 'name' => 'board-add-new-category', 'orderby' => 'name'));
							}
							?>
							<a id="pin-postdata-add-new-board" class="btn btn-mini pull-right"><?php _e('Add new board...', 'ipin'); ?></a>
							<input type="hidden" value="<?php echo $attach_id; ?>" name="attachment-id" id="attachment-id" />
							<div class="clearfix"></div>
							<input <?php if ($noboard == 'yes' || $title == '') { echo ' disabled="disabled"'; } ?> class="btn btn-primary btn-large" type="submit" name="pinit" id="pinit" value="<?php _e('Pin It', 'ipin'); ?>" /> 
							<div class="ajax-loader-add-pin ajax-loader hide"></div>
						</form>
					</div>
					</div>
			
					<div class="span4"></div>
				</div>
			
				<div id="scrolltotop"><a href="#"><i class="icon-chevron-up"></i><br /><?php _e('Top', 'ipin'); ?></a></div>
			</div>
		<?php
	}
} else { ?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="span4 hidden-phone"></div>

		<div class="span4 usercp-wrapper usercp-pins">
			<h1><?php _e('Add Pin', 'ipin') ?></h1>
			
			<div class="error-msg hide"></div>

			<br />

			<div id="pin-upload-from-computer-wrapper" class="hero-unit">
				<h4><?php _e('From Computer', 'ipin'); ?></h4>
				<form id="pin_upload_form" method="post" action="#" enctype="multipart/form-data">
					<input type="file" name="pin_upload_file" id="pin_upload_file" accept="image/*" /> 
					<input type="hidden" name="ajax-nonce" id="ajax-nonce" value="<?php echo wp_create_nonce('upload_pin'); ?>" />
					<input type="hidden" name="mode" id="mode" value="computer" />
					<input type="hidden" name="action" id="action" value="ipin-upload-pin" />
					<div class="ajax-loader-add-pin ajax-loader hide"></div>
				</form>
			</div>
			
			<div id="pin-upload-from-web-wrapper" class="hero-unit">
				<h4><?php _e('From Web', 'ipin'); ?></h4>
				<form id="pin_upload_web_form" method="post" action="#">
					<input type="text" name="pin_upload_web" id="pin_upload_web" style="margin:0;" placeholder="http://" />
					<input type="hidden" name="ajax-nonce" id="ajax-nonce" value="<?php echo wp_create_nonce('upload_pin'); ?>" />
					<input type="hidden" name="mode" id="mode" value="web" />
					<input type="hidden" name="action" id="action" value="ipin-upload-pin" />
					<input class="fetch-pin" type="submit" name="fetch" id="fetch" value="Fetch" />
					<div class="ajax-loader-add-pin ajax-loader hide"></div>
				</form>
			</div>
			
			<div id="bookmarklet" class="hero-unit">
				<h4><?php _e('Pin It Bookmarklet', 'ipin'); ?></h4>
				<span class="badge badge-warning"><a onClick='javascript:return false' href="javascript:var ipinsite='<?php echo rawurlencode(get_bloginfo('name')); ?>',ipinsiteurl='<?php echo site_url('/'); ?>';(function(){if(window.ipinit!==undefined){ipinit();}else{document.body.appendChild(document.createElement('script')).src='<?php echo get_template_directory_uri(); ?>/js/ipinit.js';}})();"><?php bloginfo('name'); ?></a></span>
				<p><small><?php _e('Drag the orange button to your bookmarks/favorites toolbar. Then click to pin an image from any website. You can also pin videos from youtube.com and vimeo.com.', 'ipin'); ?></small></p>
				<!--
				<br />
				<?php //if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false) {  ?>
					<iframe width="560" height="315" src="http://www.youtube.com/embed/813ddZ_3aIA?rel=0" frameborder="0" allowfullscreen></iframe>
				<?php //} else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {  ?>
					<iframe width="560" height="315" src="http://www.youtube.com/embed/CZGFfxMhHAQ?rel=0" frameborder="0" allowfullscreen></iframe>
				<?php //} else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) {  ?>
					<iframe width="560" height="315" src="http://www.youtube.com/embed/D0C7WWkW8n4?rel=0" frameborder="0" allowfullscreen></iframe>
				<?php //} else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {  ?>
					<iframe width="560" height="315" src="http://www.youtube.com/embed/aBHblmHjy_Q?rel=0" frameborder="0" allowfullscreen></iframe>				
				<?php //} else {  ?>
					<iframe width="560" height="315" src="http://www.youtube.com/embed/CZGFfxMhHAQ?rel=0" frameborder="0" allowfullscreen></iframe>
				<?php //} ?>
				-->
			</div>
			
			<div id="pinitbutton" class="hero-unit">
				<h4><?php _e('Pin It Button', 'ipin'); ?></h4>
				<?php if (of_get_option('pinit_button')) { ?>
					<a href="javascript:var ipinsite='<?php echo rawurlencode(get_bloginfo('name')); ?>',ipinsiteurl='<?php echo site_url('/'); ?>';(function(){if(window.ipinit!==undefined){ipinit();}else{document.body.appendChild(document.createElement('script')).src='<?php echo get_template_directory_uri(); ?>/js/ipinit.js';}})();"><img src="<?php echo of_get_option('pinit_button'); ?>" /></a>
					<p><small><?php _e('Make it easy for people to pin from your site. Copy the code below and paste it where you want the button to appear on your website.', 'ipin'); ?></small></p>
					<textarea><a href="javascript:var ipinsite='<?php echo rawurlencode(get_bloginfo('name')); ?>',ipinsiteurl='<?php echo site_url('/'); ?>';(function(){if(window.ipinit!==undefined){ipinit();}else{document.body.appendChild(document.createElement('script')).src='<?php echo get_template_directory_uri(); ?>/js/ipinit.js';}})();"><img src="<?php echo of_get_option('pinit_button'); ?>" /></a></textarea>
				<?php } else { ?>
					<a style="cursor:pointer; border: 1px solid #d7e0f3; padding: 2px 6px; background: #eceef5; font-size: 11px; color: #3b5998; border-radius: 3px;" href="javascript:var ipinsite='<?php echo rawurlencode(get_bloginfo('name')); ?>',ipinsiteurl='<?php echo site_url('/'); ?>';(function(){if(window.ipinit!==undefined){ipinit();}else{document.body.appendChild(document.createElement('script')).src='<?php echo get_template_directory_uri(); ?>/js/ipinit.js';}})();"><?php bloginfo('name'); ?></a>
					<p><small><?php _e('Make it easy for people to pin from your site. Copy the code below and paste it where you want the button to appear on your website.', 'ipin'); ?></small></p>
					<textarea><a style="cursor:pointer; border: 1px solid #d7e0f3; padding: 2px 6px; background: #eceef5; font-size: 11px; color: #3b5998; border-radius: 3px;" href="javascript:var ipinsite='<?php echo rawurlencode(get_bloginfo('name')); ?>',ipinsiteurl='<?php echo site_url('/'); ?>';(function(){if(window.ipinit!==undefined){ipinit();}else{document.body.appendChild(document.createElement('script')).src='<?php echo get_template_directory_uri(); ?>/js/ipinit.js';}})();"><?php bloginfo('name'); ?></a></textarea>
				<?php }
				?>
			</div>
			
			<div id="pin-upload-postdata-wrapper" class="hide">
				<div class="postdata-box-photo"><img id="thumbnail" /></div>
				<form id="pin-postdata-form">
					<?php if (of_get_option('form_title_desc') != 'separate') { ?>
					<textarea id="pin-title" placeholder="<?php _e('Describe your pin...', 'ipin'); ?>"></textarea>
					<?php } else { ?>
					<textarea id="pin-title" placeholder="<?php _e('Title...', 'ipin'); ?>"></textarea>
					<textarea id="pin-content" placeholder="<?php _e('Description...', 'ipin'); ?>"></textarea>
					<?php } ?>

					<?php if (of_get_option('htmltags') == 'enable') { ?>
						<div class="description_instructions">
							<?php _e('Allowed HTML tags:', 'ipin'); ?> &lt;strong> &lt;em> &lt;a> &lt;blockquote>
						</div>
					<?php } ?>
					
					<?php if (of_get_option('posttags') == 'enable') { ?>
						<div class="input-prepend">
							<span class="add-on pull-left"><i class="icon-tags"> </i></span>
							<input type="text" name="tags" id="tags" value="" placeholder="<?php _e('Tags e.g. comma, separated', 'ipin'); ?>" />
						</div>
					<?php } ?>
					
					<?php if (of_get_option('price_currency') != '') { ?>
						<?php if (of_get_option('price_currency_position') == 'right') { ?>
						<div class="input-append">
							<input class="pull-left" type="text" name="price" id="price" value="" placeholder="<?php _e('Price e.g. 23.45', 'ipin'); ?>" />
							<span class="add-on"><?php echo of_get_option('price_currency'); ?></span>
						</div>
						<?php } else { ?>
						<div class="input-prepend">
							<span class="add-on pull-left"><?php echo of_get_option('price_currency'); ?></span>
							<input type="text" name="price" id="price" value="" placeholder="<?php _e('Price e.g. 23.45', 'ipin'); ?>" />
						</div>
						<?php } ?>
					<?php } ?>
					
					<div class="input-prepend<?php if (of_get_option('source_input') != 'enable') echo ' hide'; ?>">
						<span class="add-on pull-left"><i class="icon-link"> </i></span>
						<input type="text" name="photo_data_source" id="photo_data_source" value="" placeholder="<?php _e('Source e.g. http://domain.com/link', 'ipin'); ?>" />
					</div>
					
					<?php  
					$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
					$board_children_count = wp_count_terms('board', array('parent' => $board_parent_id));
					if (is_array($board_children_count) || $board_children_count == 0) {
						echo '<span id="noboard">';
						wp_dropdown_categories(array('show_option_none' => __('Add a new board first...', 'ipin'), 'taxonomy' => 'board', 'parent' => $board_parent_id, 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true));
						echo '</span>';
					} else {
						wp_dropdown_categories(array('taxonomy' => 'board', 'parent' => $board_parent_id, 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true, 'order' => 'DESC'));
					}
					?>
					<input type="text" class="board-add-new" id="board-add-new" placeholder="<?php _e('Enter new board title', 'ipin'); ?>" />
					<?php
					if (of_get_option('blog_cat_id')) {
						wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => __('Category for New Board', 'ipin'), 'exclude_tree' => of_get_option('blog_cat_id') . ',1', 'hide_empty' => 0, 'name' => 'board-add-new-category', 'orderby' => 'name'));
					} else {
						wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => __('Category for New Board', 'ipin'), 'exclude' => '1', 'hide_empty' => 0, 'name' => 'board-add-new-category', 'orderby' => 'name'));
					}
					?>
					<a id="pin-postdata-add-new-board" class="btn btn-mini pull-right"><?php _e('Add new board...', 'ipin'); ?></a>
					<input type="hidden" value="" name="attachment-id" id="attachment-id" />
					<div class="clearfix"></div>
					<input disabled="disabled" class="btn btn-primary btn-large" type="submit" name="pinit" id="pinit" value="<?php _e('Pin It', 'ipin'); ?>" /> 
					<div class="ajax-loader-add-pin ajax-loader hide"></div>
				</form>
			</div>
		</div>

		<div class="span4"></div>
	</div>

	<div id="scrolltotop"><a href="#"><i class="icon-chevron-up"></i><br /><?php _e('Top', 'ipin'); ?></a></div>
</div>
<?php } ?>

<div class="modal hide" id="delete-pin-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-body">
		<h4><?php _e('Are you sure you want to permanently delete this pin?', 'ipin'); ?></h4>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal"><strong><?php _e('Cancel', 'ipin'); ?></strong></a>
		<a href="#" id="ipin-delete-pin-confirmed" class="btn btn-danger" data-pin_id="<?php echo $pin_id; ?>" data-pin_author="<?php echo $pin_info->post_author; ?>"><strong><?php _e('Delete Pin', 'ipin'); ?></strong></a> 
		<div class="ajax-loader-delete-pin ajax-loader hide"></div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#pin-edit-form textarea#pin-title').focus();
	$('#pin-postdata-form textarea#pin-title').focus().select();
});
</script>

<?php 
wp_enqueue_script('jquery-form', array('jquery'), false, true);
get_footer();
?>