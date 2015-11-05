<?php
load_theme_textdomain('ipin', get_template_directory() . '/languages');

register_nav_menus(array('top_nav' => __('Top Navigation', 'ipin')));
register_sidebar(array('id' => 'sidebar-l', 'name' => 'sidebar-left', 'before_widget' => '', 'after_widget' => '', 'before_title' => '<h4>', 'after_title' => '</h4>'));
register_sidebar(array('id' => 'sidebar-r', 'name' => 'sidebar-right', 'before_widget' => '', 'after_widget' => '', 'before_title' => '<h4>', 'after_title' => '</h4>'));

add_theme_support('automatic-feed-links');
add_theme_support('post-thumbnails');
add_theme_support('custom-background', array('default-color' => 'f2f2f2'));
add_editor_style();

show_admin_bar(false);

if (!isset($content_width))
	$content_width = 518;


//Theme options
if (!function_exists( 'optionsframework_init')) {
	require_once(get_template_directory() . '/inc/options-framework.php');
}


//Clean up wp head
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);


//Opengraph
function ipin_opengraph() {
	if (is_single()) {
		global $post;
		setup_postdata($post);		
		$output = '<meta property="og:type" content="article" />' . "\n";
		$output .= '<meta property="og:title" content="' . preg_replace('/[\n\r]/', ' ', the_title_attribute('echo=0')) . '" />' . "\n";
		$output .= '<meta property="og:url" content="' . get_permalink() . '" />' . "\n";
		$output .= '<meta property="og:description" content="' . esc_attr(get_the_excerpt()) . '" />' . "\n";
		if (has_post_thumbnail()) {
			$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
			$output .= '<meta property="og:image" content="' . $imgsrc[0] . '" />' . "\n";
		}
		echo $output;
	}
	
	if (is_tax('board')) {
		global $post, $wp_query;
		setup_postdata($post);		
		$output = '<meta property="og:type" content="article" />' . "\n";
		$output .= '<meta property="og:title" content="' . $wp_query->queried_object->name . '" />' . "\n";
		$output .= '<meta property="og:url" content="' . home_url('/board/') . $wp_query->queried_object->term_id . '/" />' . "\n";
		$output .= '<meta property="og:description" content="" />' . "\n";
		if (has_post_thumbnail()) {
			$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
			$output .= '<meta property="og:image" content="' . $imgsrc[0] . '" />' . "\n";
		}
		echo $output;
	}
}
add_action( 'wp_head', 'ipin_opengraph' );


//Rewrite slug from /author/ to /user/
add_filter('init', create_function(
	'$a',
	'global $wp_rewrite;
	$wp_rewrite->author_base = "user";
    $wp_rewrite->author_structure = "/" . $wp_rewrite->author_base . "/%author%/";
	'
    )
);


//Rewrite source page template slug from /source/?domain=google.com to /source/google.com/
function add_query_vars($aVars) {
	$aVars[] = 'domain';
	return $aVars;
}
add_filter('query_vars', 'add_query_vars');

function add_rewrite_rules($aRules) {
	$aNewRules = array('source/([^/]+)/?$' => 'index.php?pagename=source&domain=$matches[1]');
	$aRules = $aNewRules + $aRules;
	return $aRules;
}
add_filter('rewrite_rules_array', 'add_rewrite_rules');

//Remove canonical links for source page
function ipin_wp() {
	if (is_page('source'))
		remove_action( 'wp_head', 'rel_canonical');
}
add_action('wp', 'ipin_wp', 0);


//Rewrite titles
function ipin_wp_title( $title, $sep ) {
	if (is_tax('board')) {
		global $post;
		$user_info = get_user_by('id', $post->post_author);
		return $title . ' ' . __('Board by', 'ipin') . ' ' . $user_info->display_name;
	}
	
	if (is_page('source')) {
		global $wp_query;
		return __('Pins from', 'ipin') . ' ' . $wp_query->query_vars['domain'] . str_replace('Source ', ' ', $title);
	}
	
	if (is_single()) {
		if (mb_strlen($title) > 70) {
			$title = mb_strimwidth(strip_tags($title), 0, 70, ' ...');
		}
	}
	
	if (is_author()) {
		global $wp_query;
		$title = $title . '(' . $wp_query->queried_object->data->user_nicename . ')';
	}
	
	if (is_tag()) {
		$title = __('Tag:', 'ipin') . ' ' .$title;
	}
	
	if (is_category()) {
		$title = __('Category:', 'ipin') . ' ' .$title;
	}
	
	if (is_search()) {
		return __('Search results for', 'ipin') . ' ' . get_search_query();
	}
	
	return $title;
}
add_filter('wp_title', 'ipin_wp_title', 10, 2);


//Restrict /wp-admin/ to administrators & editors
function ipin_restrict_admin() {
	if ((!defined('DOING_AJAX') || !DOING_AJAX) && !current_user_can('administrator') && !current_user_can('editor')) {
		wp_redirect(home_url());
		exit;
    }
}
add_action('admin_init', 'ipin_restrict_admin', 1);


//Restrict acess to wp-login.php
function ipin_restrict_login() {
	if ($_GET['action'] != 'logout' && !wp_verify_nonce($_POST['nonce'], 'login')) {
		wp_redirect(home_url());
		exit;
    }
}
add_action('login_init', 'ipin_restrict_login', 1);


//Redirect login page from wp-login.php to /login/
function ipin_login_url($login_url, $redirect){
	$login_url = home_url('/login/');

	if (!empty($redirect)) {
		//prevent duplicate redirect_to parameters
		$duplicate_redirect = substr_count($redirect, 'redirect_to');
		if ($duplicate_redirect >= 1) {
			$redirect = substr($redirect, 0, (strrpos($redirect, '?')));
		}
		
		$login_url = add_query_arg('redirect_to', rawurlencode($redirect), $login_url);
	} else {
		$login_url = add_query_arg('redirect_to', rawurlencode(home_url('/')), $login_url);
	}

	if ($force_reauth)
		$login_url = add_query_arg('reauth', '1', $login_url);

	return $login_url;
}
add_filter('login_url', 'ipin_login_url', 10, 2);


//Redirect login page if login failed
function ipin_login_fail($username) {
	$referrer = $_SERVER['HTTP_REFERER'];
	
	if ($referrer == home_url() . '/login/') $referrer = $referrer . '?redirect_to=' . home_url(); // in rare case where user access /login/ page directly
	
	if (!empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') && (!defined('DOING_AJAX') || !DOING_AJAX)) {
		//notify unverified users to activate their account
		$userdata = get_user_by('login', $username);
		$verify = get_user_meta($userdata->ID, '_Verify Email', true);
		//user with verified email do not have this usermeta field
		if ($verify != '') {
			$verify = '&email=unverified';
		}

		if (strpos($referrer, '&login=failed')) {
			wp_redirect($referrer . $verify);
		} else {
			wp_redirect($referrer . $verify . '&login=failed');
		}
		exit;
	}
}
add_action('wp_login_failed', 'ipin_login_fail');


//Ajax login
function ipin_ajax_login() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
	
    $valid_user = wp_authenticate(sanitize_text_field($_POST['log']), sanitize_text_field($_POST['pwd']));
	
    if (is_wp_error($valid_user) ){
        echo 'error';
    } else {
        wp_set_auth_cookie($valid_user->ID, true);
    }
	
	exit;
}
add_action('wp_ajax_nopriv_ipin-ajax-login', 'ipin_ajax_login');


//Check whether user verified their email
function ipin_verify_email($userdata) {
	$verify = get_user_meta($userdata->ID, '_Verify Email', true);
	//user with verified email do not have this usermeta field
	if ($verify != '') {
		return new WP_Error('email_unverified', __('Email not verified. Please check your email for verification link.', 'ipin'));
	}
	return $userdata;
}
add_filter('wp_authenticate_user', 'ipin_verify_email', 1);


//Add user data after successful registration
function ipin_user_register($user_id) {
	$user_info = get_userdata($user_id);
	
	//create a parent board
	$board_id = wp_insert_term (
		$user_id,
		'board'
	);
	update_user_meta($user_id, '_Board Parent ID', $board_id['term_id']);
	
	//auto create boards
	if (of_get_option('auto_create_boards_name')) {
		$boards_name = explode(',', of_get_option('auto_create_boards_name'));
		$category_id = explode(',', of_get_option('auto_create_boards_cat'));
		
		$count = 0;
		foreach($boards_name as $board_name) {
			$board_name = sanitize_text_field($board_name);
			wp_insert_term (
				$board_name,
				'board',
				array(
					'description' => sanitize_text_field($category_id[$count]),
					'parent' => $board_id['term_id'],
					'slug' => $board_name . '__ipinboard'
				)
			);
			$count++;
		}
		
		delete_option("board_children");
	}
	
	//auto add follows
	/* if (of_get_option('auto_default_follows')) {
		$default_follows = explode(',', of_get_option('auto_default_follows'));	
		$user_ID = $user_id;
		$board_parent_id = '0';
		
		foreach ($default_follows as $default_follow) {
			$author_id = intval($default_follow);
			$board_id = get_user_meta($author_id, '_Board Parent ID', true);

			//if ($_POST['ipin_follow'] == 'follow') {		
				//update usermeta following for current user
				$usermeta_following_count = get_user_meta($user_ID, '_Following Count', true);
				$usermeta_following_user_id = get_user_meta($user_ID, '_Following User ID');
				$following_user_id = $usermeta_following_user_id[0];
				$usermeta_following_board_id = get_user_meta($user_ID, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
		
				if (!is_array($following_user_id))
					$following_user_id = array();
		
				if (!is_array($following_board_id))
					$following_board_id = array();
		
				if ($board_parent_id == '0') {
					//insert all sub-boards from author
					$author_boards = get_term_children($board_id, 'board');
					
					foreach ($author_boards as $author_board) {
						if (!in_array($author_board, $following_board_id)) {
							array_unshift($following_board_id, $author_board);
						}
					}
		
					//track followers who fully follow user to update them when user create a new board
					$usermeta_followers_id_allboards = get_user_meta($author_id, '_Followers User ID All Boards');
					$followers_id_allboards = $usermeta_followers_id_allboards[0];
		
					if (!is_array($followers_id_allboards))
						$followers_id_allboards = array();
		
					if (!in_array($user_ID, $followers_id_allboards)) {
						array_unshift($followers_id_allboards, $user_ID);
						update_user_meta($author_id, '_Followers User ID All Boards', $followers_id_allboards);
					}
				}
				array_unshift($following_board_id, $board_id);
				update_user_meta($user_ID, '_Following Board ID', $following_board_id);
		
				if (!in_array($author_id, $following_user_id)) {
					array_unshift($following_user_id, $author_id);
					update_user_meta($user_ID, '_Following User ID', $following_user_id);
					update_user_meta($user_ID, '_Following Count', ++$usermeta_following_count);
				}
		
				//update usermeta followers for author
				$usermeta_followers_count = get_user_meta($author_id, '_Followers Count', true);
				$usermeta_followers_id = get_user_meta($author_id, '_Followers User ID');
				$followers_id = $usermeta_followers_id[0];
		
				if (!is_array($followers_id))
					$followers_id = array();
		
				if (!in_array($user_ID, $followers_id)) {
					array_unshift($followers_id, $user_ID);
					update_user_meta($author_id, '_Followers User ID', $followers_id);
					update_user_meta($author_id, '_Followers Count', ++$usermeta_followers_count);
				}
			//}
		}
	} */
	
	//set email notifications
	if (stripos($user_info->user_email, '@example.com') === false) {
		update_user_meta($user_id, 'ipin_user_notify_likes', '1');
		update_user_meta($user_id, 'ipin_user_notify_repins', '1');
		update_user_meta($user_id, 'ipin_user_notify_follows', '1');
		update_user_meta($user_id, 'ipin_user_notify_comments', '1');
	} else {
		update_user_meta($user_id, 'ipin_user_notify_likes', '0');
		update_user_meta($user_id, 'ipin_user_notify_repins', '0');
		update_user_meta($user_id, 'ipin_user_notify_follows', '0');
		update_user_meta($user_id, 'ipin_user_notify_comments', '0');
	}
	
	//remove url if register via WP Social Login plugin
	if (function_exists('wsl_activate')) {
		wp_update_user(array('ID' => $user_id, 'user_url' => '')) ;
	}
}
add_action('user_register', 'ipin_user_register');


//Check and add parent board upon login (in case user did not register through ipin pro theme register page
function ipin_wp_login($user_login, $user) {
	$board_parent_id = get_user_meta($user->ID, '_Board Parent ID', true);
	//create a parent board if not exists
	if ($board_parent_id == '') {
		$board_id = wp_insert_term (
			$user->ID,
			'board'
		);
		update_user_meta($user->ID, '_Board Parent ID', $board_id['term_id']);
	}
}
add_action('wp_login', 'ipin_wp_login', 10, 2);


//Exclude blog entries from homepage
function ipin_exclude_category($query) {
	if (!is_admin()) {
		$blog_cat_id = of_get_option('blog_cat_id');
		
		if ($blog_cat_id) {
			$blog_cats = array($blog_cat_id);

			if (get_option('ipin_blog_subcats')) {
				$blog_cats = array_merge($blog_cats, get_option('ipin_blog_subcats'));
			}

			if (!$query->is_category($blog_cats)) {
				$query->set('cat', '-' . implode(' -', $blog_cats));
			}
		}
		
		//exclude pages from search
		if ($query->is_search) {
			$query->set('post_type', 'post');	
		}
	}
	return $query;
}
add_action('pre_get_posts', 'ipin_exclude_category');


//Save/cache blog sub-categories to options
function ipin_blog_subcats($term_id, $tt_id, $taxonomy) {
	if ($taxonomy == 'category') {
		$blog_cat_id = of_get_option('blog_cat_id');
		
		if ($blog_cat_id) {
			$blog_subcategories = get_categories('hide_empty=0&child_of=' . $blog_cat_id);
			$blog_subcats= array();
			foreach ($blog_subcategories as $blog_subcategory) {
				array_push($blog_subcats, $blog_subcategory->cat_ID);
			}
			
			if (!empty($blog_subcats)) {
				update_option('ipin_blog_subcats', $blog_subcats);
			} else {
				update_option('ipin_blog_subcats', '');
			}
		}
	}
}

add_action("created_term", 'ipin_blog_subcats', 10, 3);
add_action("delete_term", 'ipin_blog_subcats', 10, 3);


//Add boards taxonomy
function ipin_add_custom_taxonomies() {
	register_taxonomy('board', 'post', array(
		'hierarchical' => true,
		'public' => false,
		'labels' => array(
			'name' => 'Boards',
			'singular_name' => 'Board',
			'search_items' =>  'Search Boards',
			'all_items' => 'All Boards',
			'parent_item' => 'Parent Board',
			'parent_item_colon' => 'Parent Board:',
			'edit_item' => 'Edit Board',
			'update_item' => 'Update Board',
			'add_new_item' => 'Add New Board',
			'new_item_name' => 'New Board Name',
			'menu_name' => 'Boards'
		),
		'rewrite' => array(
			'slug' => 'board',
			'with_front' => false,
			'hierarchical' => true
		)
	));
}
add_action('init', 'ipin_add_custom_taxonomies', 0);


function ipin_board_permalink ($termlink, $term, $taxonomy) {
	if ($taxonomy == 'board')
		return home_url('/board/') . $term->term_id . '/';
	return $termlink;
}
add_filter('term_link', 'ipin_board_permalink', 10, 3);

function ipin_board_query($query) {
	if(isset($query->query_vars['board'])):
		if ($board = get_term_by('id', $query->query_vars['board'], 'board'))
			$query->query_vars['board'] = $board->slug;
	endif;
}
add_action('parse_query', 'ipin_board_query');


//Javascripts
function ipin_scripts() {
	global $current_user, $wp_rewrite;

	get_currentuserinfo();
	
	if (is_singular() && comments_open() && get_option('thread_comments') && is_user_logged_in()) {
		wp_enqueue_script('comment-reply');
	}

	wp_enqueue_script('ipin_masonry', get_template_directory_uri() . '/js/jquery.masonry.min.js', array('jquery'), null, true);
	wp_enqueue_script('ipin_infinitescroll', get_template_directory_uri() . '/js/jquery.infinitescroll.min.js', array('jquery'), null, true);
	wp_enqueue_script('ipin_bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), null, true);
	wp_enqueue_script('ipin_bootstrap_lightbox', get_template_directory_uri() . '/js/bootstrap-lightbox.min.js', array('jquery'), null, true);
	wp_enqueue_script('ipin_custom', get_template_directory_uri() . '/js/ipin.custom.js', array('jquery'), null, true);

	//for infinite scroll
	if (function_exists('wp_pagenavi')) {
		$nextSelector = '#navigation a:nth-child(3)';
	} else {
		$nextSelector = '#navigation #navigation-next a';
	}
		
	$description_instructions = '';
	$tags_html = '';
	$price_html= '';
	$dropdown_categories = '';
	
	if (is_user_logged_in()) {
		if (of_get_option('form_title_desc') != 'separate') {
			$description_fields = '<textarea id="pin-title" placeholder="' . __('Describe your pin...', 'ipin') .'"></textarea>';
		} else {
			$description_fields = '<textarea id="pin-title" placeholder="' . __('Title...', 'ipin') . '"></textarea><textarea id="pin-content" placeholder="' . __('Description...', 'ipin') . '"></textarea>';
		}
		
		if (of_get_option('htmltags') == 'enable') {
			$description_instructions = '<div class="description_instructions">' . __('Allowed HTML tags:', 'ipin') . ' &amp;lt;strong&gt; &amp;lt;em&gt; &amp;lt;a&gt; &amp;lt;blockquote&gt;</div>';
		}
		
		if (of_get_option('posttags') == 'enable') {
			$tags_html = '<div class="input-prepend"><span class="add-on pull-left"><i class="icon-tags"> </i></span><input type="text" name="tags" id="tags" value="" placeholder="' . __('Tags e.g. comma, separated', 'ipin') . '" /></div>';
		}
		
		if (of_get_option('price_currency') != '') {
			if (of_get_option('price_currency_position') == 'right') {
				$price_html = '<div class="input-append"><input class="pull-left" type="text" name="price" id="price" value="" placeholder="' . __('Price e.g. 23.45', 'ipin') . '" /><span class="add-on">' . of_get_option('price_currency') . '</span></div>';
			} else {
				$price_html = '<div class="input-prepend"><span class="add-on pull-left">' . of_get_option('price_currency') . '</span><input type="text" name="price" id="price" value="" placeholder="' . __('Price e.g. 23.45', 'ipin') . '" /></div>';
				}
		}
		
		if (of_get_option('blog_cat_id')) {
			$dropdown_categories = wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => __('Category for New Board', 'ipin'), 'exclude_tree' => of_get_option('blog_cat_id') . ',1', 'hide_empty' => 0, 'name' => 'board-add-new-category', 'orderby' => 'name', 'echo' => 0));
		} else {
			$dropdown_categories = wp_dropdown_categories(array('hierarchical' => true, 'show_option_none' => __('Category for New Board', 'ipin'), 'exclude' => '1', 'hide_empty' => 0, 'name' => 'board-add-new-category', 'orderby' => 'name', 'echo' => 0));
		}
	}
	
	$translation_array = array(
		'__allitemsloaded' => __('All items loaded', 'ipin'),
		'__addanotherpin' => __('Add Another Pin', 'ipin'),
		'__addnewboard' => __('Add new board...', 'ipin'),
		'__boardalreadyexists' => __('Board already exists. Please try another title.', 'ipin'),
		'__errorpleasetryagain' => __('Error. Please try again.', 'ipin'),
		'__cancel' => __('Cancel', 'ipin'),
		'__close' => __('Close', 'ipin'),
		'__comment' => __('comment', 'ipin'),
		'__comments' => __('comments', 'ipin'),
		'__enternewboardtitle' => __('Enter new board title', 'ipin'),
		'__Forgot' => __('Forgot?', 'ipin'),
		'__incorrectusernamepassword' => __('Incorrect Username or Password', 'ipin'),
		'__invalidimagefile' => __('Invalid image file. Please choose a JPG/GIF/PNG file.', 'ipin'),
		'__like' => __('like', 'ipin'),
		'__likes' => __('likes', 'ipin'),
		'__Likes' => __('Likes', 'ipin'),
		'__loading' => __('Loading...', 'ipin'),
		'__Login' => __('Login', 'ipin'),
		'__onto' => __('onto', 'ipin'),
		'__or' => __('or', 'ipin'),
		'__Password' => __('Password', 'ipin'),
		'__pinit' => __('Pin It', 'ipin'),
		'__pinnedto' => __('Pinned to', 'ipin'),
		'__pleaseenteratitle' => __('Please enter a title', 'ipin'),
		'__pleaseenterbothusernameandpassword' => __('Please enter both username and password.', 'ipin'),
		'__pleaseenterurl' => __('Please enter url', 'ipin'),
		'__RegisterAccount' => __('Register Account', 'ipin'),
		'__repin' => __('repin', 'ipin'),
		'__repins' => __('repins', 'ipin'),
		'__Repins' => __('Repins', 'ipin'),
		'__repinnedto' => __('Repinned to', 'ipin'),
		'__seethispin' => __('See This Pin', 'ipin'),
		'__shareitwithyourfriends' => __('Share it with your friends', 'ipin'),
		'__sorryunbaletofindanypinnableitems' => __('Sorry, unable to find any pinnable items.', 'ipin'),
		'__Username' => __('Username', 'ipin'),
		'__Video' => __('Video', 'ipin'),
		'__Welcome' => __('Welcome', 'ipin'),
		'__yourpinispendingreview' => __('Your pin is pending review', 'ipin'),

		'ajaxurl' => admin_url('admin-ajax.php'),
		'avatar30' => get_avatar($current_user->ID, '30'),
		'avatar48' => get_avatar($current_user->ID, '48'),
		'blogname' => get_bloginfo('name'),
		'categories' => $dropdown_categories,
		'current_date' => date('j M Y g:ia', current_time('timestamp')),
		'description_fields' => $description_fields,
		'description_instructions' => $description_instructions,
		'home_url' => home_url(),
		'infinitescroll' => of_get_option('infinitescroll'),
		'lightbox' => of_get_option('lightbox'),
		'login_url' => wp_login_url($_SERVER['REQUEST_URI']),
		'nextselector' => $nextSelector,
		'nonce' => wp_create_nonce('ajax-nonce'),
		'price_html' => $price_html,
		'site_url' => site_url(),
		'stylesheet_directory_uri' => get_template_directory_uri(),
		'tags_html' => $tags_html,
		'u' => $current_user->ID,
		'ui' => $current_user->display_name,
		'ul' => $current_user->user_nicename,
		'user_rewrite' => $wp_rewrite->author_base
	);
	
	wp_localize_script('ipin_custom', 'obj_ipin', $translation_array);
	
	wp_enqueue_script('twitter', 'http://platform.twitter.com/widgets.js', array(), null, true);
	wp_enqueue_script('pinterest', 'http://assets.pinterest.com/js/pinit.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'ipin_scripts');


// Make Twitter Bootstrap menu work with Wordpress Custom Menu
// From Roots Theme 6.4 http://rootstheme.com
function is_element_empty($element) {
  $element = trim($element);
  return empty($element) ? false : true;
}

class Roots_Nav_Walker extends Walker_Nav_Menu {
  function check_current($classes) {
    return preg_match('/(current[-_])|active|dropdown/', $classes);
  }

  function start_lvl(&$output, $depth = 0, $args = array()) {
    $output .= "\n<ul class=\"dropdown-menu\">\n";
  }

  function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
    $item_html = '';
    parent::start_el($item_html, $item, $depth, $args);

    if ($item->is_dropdown && ($depth === 0)) {
      $item_html = str_replace('<a', '<a class="dropdown-toggle" data-toggle="dropdown" data-target="#"', $item_html);
      $item_html = str_replace('</a>', ' <b class="caret"></b></a>', $item_html);
    }
    elseif (stristr($item_html, 'li class="divider')) {
      $item_html = preg_replace('/<a[^>]*>.*?<\/a>/iU', '', $item_html);
    }
    elseif (stristr($item_html, 'li class="nav-header')) {
      $item_html = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html);
    }

	$item_html = apply_filters('roots_wp_nav_menu_item', $item_html);
    $output .= $item_html;
  }

  function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output) {
    $element->is_dropdown = ((!empty($children_elements[$element->ID]) && (($depth + 1) < $max_depth || ($max_depth === 0))));

    if ($element->is_dropdown) {
      if ($depth === 0) {
        $element->classes[] = 'dropdown';
      } elseif ($depth === 1) {
        $element->classes[] = 'dropdown-submenu';
      }
    }

    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
}

/**
 * Remove the id="" on nav menu items
 * Return 'menu-slug' for nav menu classes
 */
function roots_nav_menu_css_class($classes, $item) {
  $slug = sanitize_title($item->title);
  $classes = preg_replace('/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', 'active', $classes);
  $classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);

  $classes[] = 'menu-' . $slug;

  $classes = array_unique($classes);

  return array_filter($classes, 'is_element_empty');
}
add_filter('nav_menu_css_class', 'roots_nav_menu_css_class', 10, 2);
add_filter('nav_menu_item_id', '__return_null');

/**
 * Clean up wp_nav_menu_args
 *
 * Remove the container
 * Use Roots_Nav_Walker() by default
 */
function roots_nav_menu_args($args = '') {
  $roots_nav_menu_args['container'] = false;

  if (!$args['items_wrap']) {
    $roots_nav_menu_args['items_wrap'] = '<ul class="%2$s">%3$s</ul>';
  }

  if (current_theme_supports('bootstrap-top-navbar')) {
    $roots_nav_menu_args['depth'] = 3;
  }

  if (!$args['walker']) {
    $roots_nav_menu_args['walker'] = new Roots_Nav_Walker();
  }

  return array_merge($args, $roots_nav_menu_args);
}
add_filter('wp_nav_menu_args', 'roots_nav_menu_args');


//Relative date modified from wp-includes/formatting.php
function ipin_human_time_diff( $from, $to = '' ) {
	if ( empty($to) )
		$to = time();
	$diff = (int) abs($to - $from);
	if ($diff <= 3600) {
		$mins = round($diff / 60);
		if ($mins <= 1) {
			$mins = 1;
		}

		if ($mins == 1) {
			$since = sprintf(__('%s min ago', 'ipin'), $mins);
		} else {
			$since = sprintf(__('%s mins ago', 'ipin'), $mins);
		}
	} else if (($diff <= 86400) && ($diff > 3600)) {
		$hours = round($diff / 3600);
		if ($hours <= 1) {
			$hours = 1;
		}
		
		if ($hours == 1) {
			$since = sprintf(__('%s hour ago', 'ipin'), $hours);
		} else {
			$since = sprintf(__('%s hours ago', 'ipin'), $hours);
		}
	} else if ($diff >= 86400 && $diff <= 31536000) {
		$days = round($diff / 86400);
		if ($days <= 1) {
			$days = 1;
		}

		if ($days == 1) {
			$since = sprintf(__('%s day ago', 'ipin'), $days);
		} else {
			$since = sprintf(__('%s days ago', 'ipin'), $days);
		}
	} else {
		$since = get_the_date();
	}
	return $since;
}


//Feed content for pins
function ipin_feed_content($content) {
	global $post;
	
	$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
	if ($imgsrc[0] != '') {
		$content_before = '<p><a href="' . get_permalink($post->ID) . '"><img src="' . $imgsrc[0] . '" alt="" /></a></p>';
	}

	$boards = get_the_terms($post->ID, 'board');

	if ($boards) {
		foreach ($boards as $board) {
			$board_name = $board->name;
			$board_slug = $board->slug;
		}
		
		$board_link = get_term_link($board_slug, 'board');
		$content_before .= '<p>' . __('Pinned onto', 'ipin') . ' <a href="' . $board_link . '">' . $board_name . '</a></p>';
	}
	
	return ($content_before . $content);
}
add_filter('the_excerpt_rss', 'ipin_feed_content');
add_filter('the_content_feed', 'ipin_feed_content');


//Nofollow links
//http://stackoverflow.com/questions/9571210/how-to-set-nofollow-rel-attribute-to-all-outbound-links-in-wordpress-any-plugin
function ipin_nofollow_callback( $matches ) {
	$link = $matches[0];
	$exclude = '('. home_url() .')';
	if ( preg_match( '#href=\S('. $exclude .')#i', $link ) )
		return $link;

	if ( strpos( $link, 'rel=' ) === false ) {
		$link = preg_replace( '/(?<=<a\s)/', 'rel="nofollow" ', $link );
	} elseif ( preg_match( '#rel=\S(?!nofollow)#i', $link ) ) {
		$link = preg_replace( '#(?<=rel=.)#', 'nofollow ', $link );
	}
	
	return $link;   
}


//Comments
function ipin_list_comments($comment, $args, $depth) {
	global $wp_rewrite;
	$GLOBALS['comment'] = $comment;
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">

		<?php $comment_author = get_user_by('id', $comment->user_id); ?>
		<div class="comment-avatar">
			<?php if ($comment_author) { ?>
			<a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $comment_author->user_nicename; ?>/">
			<?php } ?>
				<?php echo get_avatar($comment->user_id, '48'); ?>
			<?php if ($comment_author) { ?>
			</a>
			<?php } ?>
		</div>

		<div class="pull-right"><?php comment_reply_link(array('reply_text' => __('Reply', 'ipin'), 'login_text' => __('Reply', 'ipin'), 'depth' => $depth, 'max_depth'=> $args['max_depth'])); ?></div>

		<div class="comment-content">

			<strong><span <?php comment_class(); ?>>
			<?php if ($comment_author) { ?>
			<a class="url" href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $comment_author->user_nicename; ?>/">
			<?php } ?>
				<?php echo $comment->comment_author; ?>
			<?php if ($comment_author) { ?>
			</a>
			<?php } ?>
			
			</span></strong> / <?php comment_date('j M Y g:ia'); ?> <a href="#comment-<?php comment_ID() ?>" title="<?php esc_attr_e('Comment Permalink', 'ipin'); ?>">#</a> <?php edit_comment_link('e','',''); ?>
			<?php if ($comment->comment_approved == '0') : ?>
			<br /><em><?php _e('Your comment is awaiting moderation.', 'ipin'); ?></em>
			<?php endif; ?>
	
			<?php comment_text(); ?>
		</div>
	<?php
}


//Repins
function ipin_repin() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	global $user_ID, $user_identity;
	$original_id  = $_POST['repin_post_id'];
	$duplicate = get_post($original_id, 'ARRAY_A');
	$original_post_author = $duplicate['post_author']; //store original author for use later
	$duplicate['post_author'] = $user_ID;

	$allowed_html = array(
		'a' => array(
			'href' => array()
		),
		'b' => array(),
		'strong' => array(),
		'em' => array(),
		'i' => array(),
		'blockquote' => array()
	);
	
	if (of_get_option('htmltags') != 'enable') {
		unset($allowed_html);
		$allowed_html = array();
	}
	
	if (of_get_option('form_title_desc') != 'separate') {
		$duplicate['post_title'] = wp_kses($_POST['repin_title'], $allowed_html);
	} else {
		$duplicate['post_title'] = sanitize_text_field($_POST['repin_title']);
	}

	$duplicate['post_content'] = wp_kses($_POST['repin_content'], $allowed_html);

	unset($duplicate['ID']);
	unset($duplicate['post_date']);
	unset($duplicate['post_date_gmt']);
	unset($duplicate['post_modified']);
	unset($duplicate['post_modified_gmt']);
	unset($duplicate['post_name']);
	unset($duplicate['guid']);
	unset($duplicate['comment_count']);

	remove_action('save_post', 'ipin_save_post', 50, 2);
	$duplicate_id = wp_insert_post($duplicate);

	//set board
	$board_add_new = sanitize_text_field($_POST['repin_board_add_new']);
	$board_add_new_category = $_POST['repin_board_add_new_category'];
	$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
	if ($board_add_new !== '') {
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';

		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if (stripslashes(htmlspecialchars($board_add_new, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
				$found = '1';
				$found_board_id = $board_child_term->term_id;
				break;
			}
		}

		if ($found == '0') {
			$slug = wp_unique_term_slug($board_add_new . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
			if ($board_add_new_category == '-1')
				$board_add_new_category = '1';

			$new_board_id = wp_insert_term (
				$board_add_new,
				'board',
				array(
					'description' => $board_add_new_category,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			
			$repin_board = $new_board_id['term_id'];
		} else {
			$repin_board = $found_board_id;
		}
	} else {
		$repin_board = $_POST['repin_board'];		
	}
	wp_set_post_terms($duplicate_id, array($repin_board), 'board');

	//set category
	$category_id = get_term_by('id', $repin_board, 'board');
	wp_set_post_terms($duplicate_id, array($category_id->description), 'category');

	//update postmeta for new post
	if ('' == $repin_of_repin = get_post_meta($original_id, '_Original Post ID', true)) { //check if is a simple repin or a repin of a repin
		add_post_meta($duplicate_id, '_Original Post ID', $original_id);
	} else {
		add_post_meta($duplicate_id, '_Original Post ID', $original_id);
		add_post_meta($duplicate_id, '_Earliest Post ID', $repin_of_repin); //the very first post/pin		
	}
	add_post_meta($duplicate_id, '_Photo Source', get_post_meta($original_id, '_Photo Source', true));
	add_post_meta($duplicate_id, '_Photo Source Domain', get_post_meta($original_id, '_Photo Source Domain', true));
	add_post_meta($duplicate_id, '_thumbnail_id', get_post_meta($original_id, '_thumbnail_id', true));
	
	//add tags
	wp_set_post_tags($duplicate_id, sanitize_text_field($_POST['repin_tags']));

	//add price	
	if ($_POST['repin_price']) {
		add_post_meta($duplicate_id, '_Price', round(sanitize_text_field($_POST['repin_price']), 3));
	}

	//update postmeta for original post
	$postmeta_repin_count = get_post_meta($original_id, '_Repin Count', true);
	$postmeta_repin_post_id = get_post_meta($original_id, '_Repin Post ID');
	$repin_post_id = $postmeta_repin_post_id[0];

	if (!is_array($repin_post_id))
		$repin_post_id = array();

	array_push($repin_post_id, $duplicate_id);
	update_post_meta($original_id, '_Repin Post ID', $repin_post_id);
	update_post_meta($original_id, '_Repin Count', ++$postmeta_repin_count);

	//email author
	if (get_user_meta($original_post_author, 'ipin_user_notify_repins', true) != '' && $user_ID != $original_post_author) {
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$message = sprintf(__('%s repinned your "%s" pin at %s', 'ipin'), $user_identity, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($original_id)), ENT_QUOTES, 'UTF-8')), get_permalink($duplicate_id)) . "\r\n\r\n";
		$message .= "-------------------------------------------\r\n";
		$message .= sprintf(__('To change your notification settings, visit %s', 'ipin'), home_url('/settings/'));
		wp_mail(get_the_author_meta('user_email', $original_post_author), sprintf(__('[%s] Someone repinned your pin', 'ipin'), $blogname), $message);
	}
	
	//add new board to followers who fully follow user
	if ($new_board_id && !is_wp_error($new_board_id)) {
		$usermeta_followers_id_allboards = get_user_meta($user_ID, '_Followers User ID All Boards');
		$followers_id_allboards = $usermeta_followers_id_allboards[0];
		
		if (!empty($followers_id_allboards)) {
			foreach ($followers_id_allboards as $followers_id_allboard) {
				$usermeta_following_board_id = get_user_meta($followers_id_allboard, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				array_unshift($following_board_id, $new_board_id['term_id']);
				update_user_meta($followers_id_allboard, '_Following Board ID', $following_board_id);
			}
		}
	}
	
	echo get_permalink($duplicate_id);

	exit;
}
add_action('wp_ajax_ipin-repin', 'ipin_repin');

function ipin_repin_board_populate() {
	global $user_ID;

	$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
	$board_children_count = wp_count_terms('board', array('parent' => $board_parent_id));
	if (is_array($board_children_count) || $board_children_count == 0) {
		echo '<span id="noboard">' . wp_dropdown_categories(array('echo' => 0, 'show_option_none' => __('Add a new board first...', 'ipin'), 'taxonomy' => 'board', 'parent' => $board_parent_id, 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true));
		echo '</span>';
	} else {
		echo wp_dropdown_categories(array('echo' => 0, 'taxonomy' => 'board', 'parent' => $board_parent_id, 'hide_empty' => 0, 'name' => 'board', 'hierarchical' => true, 'order' => 'DESC'));
	}
	exit;
}
add_action('wp_ajax_ipin-repin-board-populate', 'ipin_repin_board_populate');


//Likes
function ipin_like() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	global $user_ID, $user_identity;
	$post_id = $_POST['post_id'];

	if ($_POST['ipin_like'] == 'like') {
		$postmeta_count = get_post_meta($post_id, '_Likes Count', true);
		$postmeta_user_id = get_post_meta($post_id, '_Likes User ID');
		$likes_user_id = $postmeta_user_id[0];

		if (!is_array($likes_user_id))
			$likes_user_id = array();

		//update postmeta
		array_push($likes_user_id, $user_ID);
		update_post_meta($post_id, '_Likes User ID', $likes_user_id);
		update_post_meta($post_id, '_Likes Count', ++$postmeta_count);

		//update usermeta
		$usermeta_count = get_user_meta($user_ID, '_Likes Count', true);
		$usermeta_post_id = get_user_meta($user_ID, '_Likes Post ID');
		$likes_post_id = $usermeta_post_id[0];

		if (!is_array($likes_post_id))
			$likes_post_id = array();

		array_unshift($likes_post_id, $post_id);

		update_user_meta($user_ID, '_Likes Post ID', $likes_post_id);
		update_user_meta($user_ID, '_Likes Count', ++$usermeta_count);

		//email author
		if (get_user_meta($_POST['post_author'], 'ipin_user_notify_likes', true) != '') {
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			$message = sprintf(__('%s likes your "%s" pin at %s', 'ipin'), $user_identity, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($post_id)), ENT_QUOTES, 'UTF-8')), get_permalink($post_id)) . "\r\n\r\n";
			$message .= "-------------------------------------------\r\n";
			$message .= sprintf(__('To change your notification settings, visit %s', 'ipin'), home_url('/settings/'));
			wp_mail(get_the_author_meta('user_email', $_POST['post_author']), sprintf(__('[%s] Someone likes your pin', 'ipin'), $blogname), $message);
		}

		echo $postmeta_count;

	} else if ($_POST['ipin_like'] == 'unlike') {
		//update postmeta
		$postmeta_count = get_post_meta($post_id, '_Likes Count', true);
		$postmeta_user_id = get_post_meta($post_id, '_Likes User ID');
		$likes_user_id = $postmeta_user_id[0];
		unset($likes_user_id[array_search($user_ID, $likes_user_id)]);
		$likes_user_id = array_values($likes_user_id);
		update_post_meta($post_id, '_Likes User ID', $likes_user_id);
		update_post_meta($post_id, '_Likes Count', --$postmeta_count);

		//update usermeta
		$usermeta_count = get_user_meta($user_ID, '_Likes Count', true);
		$usermeta_post_id = get_user_meta($user_ID, '_Likes Post ID');
		$likes_post_id = $usermeta_post_id[0];

		unset($likes_post_id[array_search($post_id, $likes_post_id)]);
		$likes_post_id = array_values($likes_post_id);

		update_user_meta($user_ID, '_Likes Post ID', $likes_post_id);
		update_user_meta($user_ID, '_Likes Count', --$usermeta_count);

		echo $postmeta_count;
	}
	
	exit;
}
add_action('wp_ajax_ipin-like', 'ipin_like');

function ipin_liked($post_id) {
	global $user_ID;
	$postmeta_user_id = get_post_meta($post_id, '_Likes User ID');
	$likes_user_id = $postmeta_user_id[0];

	if (!is_array($likes_user_id))
		$likes_user_id = array();

	if (in_array($user_ID, $likes_user_id)) {
		return true;
	}
	return false;
}


//Follows
function ipin_follow() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
	
	global $user_ID, $user_identity;
	$board_parent_id = $_POST['board_parent_id'];
	$board_id = $_POST['board_id'];
	$author_id = $_POST['author_id'];

	if ($_POST['ipin_follow'] == 'follow') {
		//update usermeta following for current user
		$usermeta_following_count = get_user_meta($user_ID, '_Following Count', true);
		$usermeta_following_user_id = get_user_meta($user_ID, '_Following User ID');
		$following_user_id = $usermeta_following_user_id[0];
		$usermeta_following_board_id = get_user_meta($user_ID, '_Following Board ID');
		$following_board_id = $usermeta_following_board_id[0];

		if (!is_array($following_user_id))
			$following_user_id = array();

		if (!is_array($following_board_id))
			$following_board_id = array();

		if ($board_parent_id == '0') {
			//insert all sub-boards from author
			$author_boards = get_term_children($board_id, 'board');

			foreach ($author_boards as $author_board) {
				if (!in_array($author_board, $following_board_id)) {
					array_unshift($following_board_id, $author_board);
				}
			}

			//track followers who fully follow user to update them when user create a new board
			$usermeta_followers_id_allboards = get_user_meta($author_id, '_Followers User ID All Boards');
			$followers_id_allboards = $usermeta_followers_id_allboards[0];

			if (!is_array($followers_id_allboards))
				$followers_id_allboards = array();

			if (!in_array($user_ID, $followers_id_allboards)) {
				array_unshift($followers_id_allboards, $user_ID);
				update_user_meta($author_id, '_Followers User ID All Boards', $followers_id_allboards);
			}
		}
		array_unshift($following_board_id, $board_id);
		update_user_meta($user_ID, '_Following Board ID', $following_board_id);

		if (!in_array($author_id, $following_user_id)) {
			array_unshift($following_user_id, $author_id);
			update_user_meta($user_ID, '_Following User ID', $following_user_id);
			update_user_meta($user_ID, '_Following Count', ++$usermeta_following_count);
		}

		//update usermeta followers for author
		$usermeta_followers_count = get_user_meta($author_id, '_Followers Count', true);
		$usermeta_followers_id = get_user_meta($author_id, '_Followers User ID');
		$followers_id = $usermeta_followers_id[0];

		if (!is_array($followers_id))
			$followers_id = array();

		if (!in_array($user_ID, $followers_id)) {
			array_unshift($followers_id, $user_ID);
			update_user_meta($author_id, '_Followers User ID', $followers_id);
			update_user_meta($author_id, '_Followers Count', ++$usermeta_followers_count);
		}

		//email author
		if (get_user_meta($author_id, 'ipin_user_notify_follows', true) != '') {
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			$message = sprintf(__('%s is now following you. View %s\'s profile at %s', 'ipin'), $user_identity, $user_identity, get_author_posts_url($user_ID)) . "\r\n\r\n";
			$message .= "-------------------------------------------\r\n";
			$message .= sprintf(__('To change your notification settings, visit %s', 'ipin'), home_url('/settings/'));
			wp_mail(get_the_author_meta('user_email', $author_id), sprintf(__('[%s] Someone is following you', 'ipin'), $blogname), $message);
		}
	} else if ($_POST['ipin_follow'] == 'unfollow') {		
		//update usermeta following for current user
		$usermeta_following_count = get_user_meta($user_ID, '_Following Count', true);
		$usermeta_following_user_id = get_user_meta($user_ID, '_Following User ID');
		$following_user_id = $usermeta_following_user_id[0];
		$usermeta_following_board_id = get_user_meta($user_ID, '_Following Board ID');
		$following_board_id = $usermeta_following_board_id[0];

		if ($board_parent_id == '0') {
			$author_boards = get_term_children($board_id, 'board');

			//prepare to remove all boards from author
			foreach ($author_boards as $author_board) {
				if (in_array($author_board, $following_board_id)) {
					unset($following_board_id[array_search($author_board, $following_board_id)]);
					$following_board_id = array_values($following_board_id);
				}
			}

			//remove parent board as well
			unset($following_board_id[array_search($board_id, $following_board_id)]);
			$following_board_id = array_values($following_board_id);

			unset($following_user_id[array_search($author_id, $following_user_id)]);
			$following_user_id = array_values($following_user_id);

			update_user_meta($user_ID, '_Following Board ID', $following_board_id);
			update_user_meta($user_ID, '_Following User ID', $following_user_id);
			update_user_meta($user_ID, '_Following Count', --$usermeta_following_count);

			//update usermeta followers for author
			$usermeta_followers_count = get_user_meta($author_id, '_Followers Count', true);

			$usermeta_followers_id = get_user_meta($author_id, '_Followers User ID');
			$followers_id = $usermeta_followers_id[0];
			unset($followers_id[array_search($user_ID, $followers_id)]);
			$followers_id = array_values($followers_id);

			$usermeta_followers_id_allboards = get_user_meta($author_id, '_Followers User ID All Boards');
			$followers_id_allboards = $usermeta_followers_id_allboards[0];
			unset($followers_id_allboards[array_search($user_ID, $followers_id_allboards)]);
			$followers_id_allboards = array_values($followers_id_allboards);

			update_user_meta($author_id, '_Followers User ID', $followers_id);
			update_user_meta($author_id, '_Followers User ID All Boards', $followers_id_allboards);
			update_user_meta($author_id, '_Followers Count', --$usermeta_followers_count);
			
			echo 'unfollow_all';
		} else {
			unset($following_board_id[array_search($board_id, $following_board_id)]);
			$following_board_id = array_values($following_board_id);

			$author_boards = get_term_children($board_parent_id, 'board');
			$board_following_others = 'no';

			//check if current user is following other boards from author
			//if no longer following other boards, also unfollow user
			foreach ($following_board_id as $following_board) {
				if (in_array($following_board, $author_boards)) {
					$board_following_others = 'yes';
					break;
				}
			}

			if ($board_following_others == 'no') {
				//remove parent board
				unset($following_board_id[array_search($board_parent_id, $following_board_id)]);
				$following_board_id = array_values($following_board_id);

				unset($following_user_id[array_search($author_id, $following_user_id)]);
				$following_user_id = array_values($following_user_id);

				update_user_meta($user_ID, '_Following User ID', $following_user_id);
				update_user_meta($user_ID, '_Following Count', --$usermeta_following_count);

				//update usermeta followers for author
				$usermeta_followers_count = get_user_meta($author_id, '_Followers Count', true);

				$usermeta_followers_id = get_user_meta($author_id, '_Followers User ID');
				$followers_id = $usermeta_followers_id[0];
				unset($followers_id[array_search($user_ID, $followers_id)]);
				$followers_id = array_values($followers_id);

				$usermeta_followers_id_allboards = get_user_meta($author_id, '_Followers User ID All Boards');
				$followers_id_allboards = $usermeta_followers_id_allboards[0];
				unset($followers_id_allboards[array_search($user_ID, $followers_id_allboards)]);
				$followers_id_allboards = array_values($followers_id_allboards);

				update_user_meta($author_id, '_Followers User ID', $followers_id);
				update_user_meta($author_id, '_Followers User ID All Boards', $followers_id_allboards);
				update_user_meta($author_id, '_Followers Count', --$usermeta_followers_count);

				echo 'unfollow_all';
			}
			update_user_meta($user_ID, '_Following Board ID', $following_board_id);
		}
	}
	
	exit;
}
add_action('wp_ajax_ipin-follow', 'ipin_follow');

function ipin_followed($board_id) {
	global $user_ID;
	$usermeta_board_id = get_user_meta($user_ID, '_Following Board ID');
	$follow_board_id = $usermeta_board_id[0];

	if (!is_array($follow_board_id))
		$follow_board_id = array();
	
	if (in_array($board_id, $follow_board_id)) {
		return true;
	}
	return false;
}


//Ajax comments
function ipin_ajaxify_comments($comment_ID, $comment_status) {
	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		if ('spam' !== $comment_status) {
			if ('0' == $comment_status) {
				wp_notify_moderator($comment_ID);
			} else if ('1' == $comment_status) {
				//email author
				global $user_ID, $user_identity;
				$commentdata = get_comment($comment_ID, 'ARRAY_A');
				$postdata = get_post($commentdata['comment_post_ID'], 'ARRAY_A');
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

				if (get_user_meta($postdata['post_author'], 'ipin_user_notify_comments', true) != '' && $user_ID != $postdata['post_author']) {
					$message = sprintf(__('%s commented on your "%s" pin at %s', 'ipin'), $user_identity, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field($postdata['post_title']), ENT_QUOTES, 'UTF-8')), get_permalink($postdata['ID'])) . "\r\n\r\n";
					$message .= "-------------------------------------------\r\n";
					$message .= sprintf(__('To change your notification settings, visit %s', 'ipin'), home_url('/settings/'));
					wp_mail(get_the_author_meta('user_email', $postdata['post_author']), sprintf(__('[%s] Someone commented on your pin', 'ipin'), $blogname), $message);
				}
				
				$comment_author_domain = @gethostbyaddr($commentdata['comment_author_IP']);
				
				//email admin
				if (get_option('comments_notify') && $user_ID != $postdata['post_author']) {
					$admin_message  = sprintf(__('New comment on the pin "%s"', 'ipin'), preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field($postdata['post_title']), ENT_QUOTES, 'UTF-8'))) . "\r\n";
					$admin_message .= sprintf(__('Author : %1$s (IP: %2$s , %3$s)', 'ipin'), $commentdata['comment_author'], $commentdata['comment_author_IP'], $comment_author_domain) . "\r\n";
					$admin_message .= sprintf(__('E-mail : %s', 'ipin'), $commentdata['comment_author_email']) . "\r\n";
					$admin_message .= sprintf(__('URL    : %s', 'ipin'), $commentdata['comment_author_url']) . "\r\n";
					$admin_message .= sprintf(__('Whois  : http://whois.arin.net/rest/ip/%s', 'ipin'), $commentdata['comment_author_IP']) . "\r\n";
					$admin_message .= __('Comment: ', 'ipin') . "\r\n" . $commentdata['comment_content'] . "\r\n\r\n";
					$admin_message .= __('You can see all comments on this pin here: ', 'ipin') . "\r\n";
					$admin_message .= get_permalink($postdata['ID']) . "#comments\r\n\r\n";
					$admin_message .= sprintf(__('Permalink: %s', 'ipin'), get_permalink($postdata['ID']) . '#comment-' . $comment_ID) . "\r\n";
					$admin_message .= sprintf(__('Delete it: %s', 'ipin'), admin_url("comment.php?action=delete&c=$comment_ID")) . "\r\n";
					$admin_message .= sprintf(__('Spam it: %s', 'ipin'), admin_url("comment.php?action=spam&c=$comment_ID")) . "\r\n";
					$admin_subject = sprintf(__('[%1$s] Comment: "%2$s"', 'ipin'), $blogname, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field($postdata['post_title']), ENT_QUOTES, 'UTF-8')));
					wp_mail(get_option('admin_email'), $admin_subject, $admin_message);
				}

				echo 'success';
			}
		}
		exit;
	}
}
add_action('comment_post', 'ipin_ajaxify_comments', 20, 2);



//Auto assign board if no board e.g adding post from backend
function ipin_save_post($post_id, $post) {
	if ($post->post_type != 'post' || $post->post_status != 'publish')
		return;

	$blog_cat_id = of_get_option('blog_cat_id');
	if ($blog_cat_id) {
		$blog_cats = array($blog_cat_id);
	
		if (get_option('ipin_blog_subcats')) {
			$blog_cats = array_merge($blog_cats, get_option('ipin_blog_subcats'));
		}
	}
	
	//Exclude blog category
	$boards = get_the_terms($post_id, 'board');
	if (!$boards && !in_category($blog_cats, $post_id)) {
		$board_parent_id = get_user_meta($post->post_author, '_Board Parent ID', true);
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';
		
		$post_category = get_the_category($post_id);	
		
		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if ($board_child_term->name == $post_category[0]->cat_name) {
				$found = '1';
				$found_board_id = $board_child_term->term_id;
				break;
			}
		}
		
		if ($found == '0') {
			$slug = wp_unique_term_slug($post_category[0]->cat_name . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title

			$new_board_id = wp_insert_term (
				$post_category[0]->cat_name,
				'board',
				array(
					'description' => $post_category[0]->cat_ID,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			$postdata_board = $new_board_id['term_id'];
		} else {
			$postdata_board = $found_board_id;
		}
		
		//set board
		wp_set_post_terms($post_id, array($postdata_board), 'board');
		
		//category ID is stored in the board description field
		$category_id = get_term_by('id', $postdata_board, 'board');
		
		//set category
		wp_set_object_terms($post_id, array(intval($category_id->description)), 'category');
	}
}
add_action('save_post', 'ipin_save_post', 50, 2);


//Clean up postmeta & usermeta when delete post
function ipin_delete_post_clean($post_id) {
	global $wpdb;

	$original_id = get_post_meta($post_id, '_Original Post ID', true);

	if ($original_id == '') { //this is an original post
		//remove instances from repinned postmeta
		$wpdb->query(
			$wpdb->prepare("UPDATE $wpdb->postmeta
						SET meta_value = 'deleted'
						WHERE meta_key = '_Original Post ID'
						AND meta_value = %s
						"
						,$post_id)
		);

		//remove instances from repinned of repinned postmeta
		$wpdb->query(
			$wpdb->prepare("UPDATE $wpdb->postmeta
						SET meta_value = 'deleted'
						WHERE meta_key = '_Earliest Post ID'
						AND meta_value = %s
						"
						,$post_id)
		);

		//remove instances from usermeta
		$postmeta_likes_user_ids = get_post_meta($post_id, '_Likes User ID');
		$likes_user_ids = $postmeta_likes_user_ids[0];

		if (is_array($likes_user_ids)) {
			foreach ($likes_user_ids as $likes_user_id) {
				$usermeta_count = get_user_meta($likes_user_id, '_Likes Count', true);
				$usermeta_post_id = get_user_meta($likes_user_id, '_Likes Post ID');
				$likes_post_id = $usermeta_post_id[0];
		
				unset($likes_post_id[array_search($post_id, $likes_post_id)]);
				$likes_post_id = array_values($likes_post_id);
		
				update_user_meta($likes_user_id, '_Likes Post ID', $likes_post_id);
				update_user_meta($likes_user_id, '_Likes Count', --$usermeta_count);
			}
		}
	} else { //this is a repinned post
		//remove instances from repinned postmeta
		$wpdb->query(
			$wpdb->prepare("UPDATE $wpdb->postmeta
						SET meta_value = 'deleted'
						WHERE meta_key = '_Original Post ID'
						AND meta_value = %s
						"
						,$post_id)
		);

		//remove instances from original postmeta
		$postmeta_repin_count = get_post_meta($original_id, '_Repin Count', true);
		$postmeta_repin_post_id = get_post_meta($original_id, '_Repin Post ID');
		$repin_post_id = $postmeta_repin_post_id[0];
		unset($repin_post_id[array_search($post_id, $repin_post_id)]);
		$repin_post_id = array_values($repin_post_id);

		update_post_meta($original_id, '_Repin Post ID', $repin_post_id);
		update_post_meta($original_id, '_Repin Count', --$postmeta_repin_count);

		//remove instances from usermeta
		$postmeta_likes_user_ids = get_post_meta($post_id, '_Likes User ID');
		$likes_user_ids = $postmeta_likes_user_ids[0];

		if (is_array($likes_user_ids)) {
			foreach ($likes_user_ids as $likes_user_id) {
				$usermeta_count = get_user_meta($likes_user_id, '_Likes Count', true);
				$usermeta_post_id = get_user_meta($likes_user_id, '_Likes Post ID');
				$likes_post_id = $usermeta_post_id[0];
		
				unset($likes_post_id[array_search($post_id, $likes_post_id)]);
				$likes_post_id = array_values($likes_post_id);
		
				update_user_meta($likes_user_id, '_Likes Post ID', $likes_post_id);
				update_user_meta($likes_user_id, '_Likes Count', --$usermeta_count);
			}
		}
	}
}
add_action('before_delete_post', 'ipin_delete_post_clean');


//Format slug for tags when name is same as board
function ipin_created_term($term_id, $tt_id, $taxonomy) {
	if ($taxonomy == 'post_tag') {
		$term = get_term($term_id, $taxonomy);
		if (strpos($term->slug, '__ipinboard') !== false){
			$slug = str_replace('__ipinboard', '', $term->slug);
			wp_update_term($term_id, $taxonomy, array('slug' => $slug));
		}
	}
}
add_action('created_term', 'ipin_created_term', 10, 3);


//Clean up usermeta & boards when delete user
function ipin_delete_user_clean($id) {
	global $wpdb;

	//user_id is name of parent board
	$board_parent_id = get_user_meta($id, '_Board Parent ID', true);
	$child_boards = get_term_children($board_parent_id, 'board');
	array_push($child_boards, $board_parent_id);

	//remove likes from postmeta
	$usermeta_likes_post_ids = get_user_meta($id, '_Likes Post ID');

	if (!empty($usermeta_likes_post_ids[0])) {
		foreach ($usermeta_likes_post_ids[0] as $likes_post_id) {
			$postmeta_likes_count = get_post_meta($likes_post_id, '_Likes Count', true);
			$postmeta_likes_user_id = get_post_meta($likes_post_id, '_Likes User ID');
			$likes_user_id = $postmeta_likes_user_id[0];
	
			unset($likes_user_id[array_search($id, $likes_user_id)]);
			$likes_user_id = array_values($likes_user_id);
	
			update_post_meta($likes_post_id, '_Likes User ID', $likes_user_id);
			update_post_meta($likes_post_id, '_Likes Count', --$postmeta_likes_count);
		}
	}

	//remove instances from followers
	$followers = get_user_meta($id, '_Followers User ID');
	
	if(!empty($followers[0])) {
		foreach ($followers[0] as $follower) {
			$usermeta_following_count = get_user_meta($follower, '_Following Count', true);
			$usermeta_following_user_id = get_user_meta($follower, '_Following User ID');
			$following_user_id = $usermeta_following_user_id[0];

			unset($following_user_id[array_search($id, $following_user_id)]);
			$following_user_id = array_values($following_user_id);

			update_user_meta($follower, '_Following User ID', $following_user_id);
			update_user_meta($follower, '_Following Count', --$usermeta_following_count);

			//delete board from followers usermeta
			foreach ($child_boards as $child_board) {
				$usermeta_following_board_id = get_user_meta($follower, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				
				unset($following_board_id[array_search($child_board, $following_board_id)]);
				$following_board_id = array_values($following_board_id);
				update_user_meta($follower, '_Following Board ID', $following_board_id);	
			}
		}
	}
	
	//remove instances from following users
	$following = get_user_meta($id, '_Following User ID');
	
	if(!empty($following[0])) {
		foreach ($following[0] as $following) {
			$usermeta_followers_count = get_user_meta($following, '_Followers Count', true);
			$usermeta_followers_user_id = get_user_meta($following, '_Followers User ID');
			$followers_user_id = $usermeta_followers_user_id[0];
			$usermeta_followers_user_id_all_boards = get_user_meta($following, '_Followers User ID All Boards');
			$followers_user_id_all_boards = $usermeta_followers_user_id_all_boards[0];

			unset($followers_user_id[array_search($id, $followers_user_id)]);
			$followers_user_id = array_values($followers_user_id);
			
			unset($followers_user_id_all_boards[array_search($id, $followers_user_id_all_boards)]);
			$followers_user_id_all_boards = array_values($followers_user_id_all_boards);

			update_user_meta($following, '_Followers User ID', $followers_user_id);
			update_user_meta($following, '_Followers Count', --$usermeta_followers_count);
			update_user_meta($following, '_Followers User ID All Boards', $followers_user_id_all_boards);
		}
	}

	//finally delete the boards
	foreach ($child_boards as $child_board) {
		wp_delete_term($child_board, 'board');
	}
	
}
add_action('delete_user', 'ipin_delete_user_clean');


//Prune posts
function ipin_add_cron_schedule($schedules) {
	$prune_duration = of_get_option('prune_duration') * 60;
	
    $schedules['ipin_prune'] = array(
        'interval' => $prune_duration,
        'display'  => 'Prune Duration'
    );
 
    return $schedules;
}
add_filter('cron_schedules', 'ipin_add_cron_schedule');

if (!wp_next_scheduled( 'ipin_cron_action' )) {
    wp_schedule_event(time(), 'ipin_prune', 'ipin_cron_action');
}
 
function ipin_cron_function() {
	global $wpdb;
	
	$prune_postnumber = of_get_option('prune_postnumber');
	
	$posts = $wpdb->get_results("
		SELECT ID FROM $wpdb->posts
		WHERE post_status = 'ipin_prune'
		LIMIT $prune_postnumber
	");
	
	if ($posts) {
		foreach ($posts as $post) {
			$thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
			
			wp_delete_post($post->ID, true);

			//look for other posts using the same featured image e.g thru repin
			$post_same_thumbnail = $wpdb->get_var("
				SELECT post_id FROM $wpdb->postmeta
				WHERE meta_key = '_thumbnail_id'
				AND meta_value = $thumbnail_id
				LIMIT 1
			");

			if ($post_same_thumbnail) {
				$wpdb->query(
					"
					UPDATE $wpdb->posts
					SET post_parent = $post_same_thumbnail
					WHERE ID = $thumbnail_id
					"
				);
			} else {
				wp_delete_attachment($thumbnail_id, true);
			}
		}
	}
}
add_action('ipin_cron_action', 'ipin_cron_function');


//delete or assign attachment when deleting pins
function ipin_delete_post($postid) {
	global $wpdb;

	$boards = get_the_terms($postid, 'board');
	if ($boards && !is_wp_error($boards)) {
		$thumbnail_id = get_post_meta($postid, '_thumbnail_id', true);
		
		//look for other posts using the same featured image e.g thru repin
		$post_same_thumbnail = $wpdb->get_var("
			SELECT post_id FROM $wpdb->postmeta
			WHERE meta_key = '_thumbnail_id'
			AND meta_value = $thumbnail_id
			AND post_id != $postid
			LIMIT 1
		");
	
		if ($post_same_thumbnail) {
			$wpdb->query(
				"
				UPDATE $wpdb->posts
				SET post_parent = $post_same_thumbnail
				WHERE ID = $thumbnail_id
				"
			);
		} else {
			wp_delete_attachment($thumbnail_id, true);
		}
	}
}
add_action('before_delete_post', 'ipin_delete_post');


//Change default email
function ipin_mail_from($email)
{
	if ('' != $outgoing_email = of_get_option('outgoing_email')) {
		return $outgoing_email;
	} else {
		return $email;
	}
}
add_filter('wp_mail_from', 'ipin_mail_from');

function ipin_mail_from_name($name)
{
	if ('' != $outgoing_email_name = of_get_option('outgoing_email_name')) {
		return $outgoing_email_name;
	} else {
		return $name;
	}
}
add_filter('wp_mail_from_name', 'ipin_mail_from_name');


//Local avatar
function ipin_local_avatar($avatar, $id_or_email, $size, $default, $alt) {
	$avatar_id = get_user_meta($id_or_email, 'ipin_user_avatar', true);

	if ($avatar_id != '') {
		if (intval($size) <= 48) {
			$imgsrc = wp_get_attachment_image_src($avatar_id, 'avatar48');
			return '<img alt="avatar" src="' . $imgsrc[0] . '" class="avatar" height="' . $size . '" width="' . $size . '" />';
		} else {
			$imgsrc = wp_get_attachment_image_src($avatar_id, 'thumbnail');
			return '<img alt="avatar" src="' . $imgsrc[0] . '" class="avatar" height="' . $size . '" width="' . $size . '" />';
		}
	} else {
		if (of_get_option('default_avatar') == '') { 
			$upload_dir = wp_upload_dir();
			if ($size <= 64){
				$default = get_template_directory_uri() . '/img/avatar-48x48.png';
			} else {
				$default = get_template_directory_uri() . '/img/avatar-96x96.png';
			}
		} else {			
			if ($size <= 64){
				$default = get_option('ipin_avatar_48');
			} else {
				$default = get_option('ipin_avatar_96');
			}
		}
		$avatar = '<img alt="avatar" src="' . $default . '" class="avatar" height="' . $size . '" width="' . $size . '" />';
	}

	return $avatar;
}
add_filter('get_avatar', 'ipin_local_avatar', 10, 5);


//Ajax upload avatar
function ipin_upload_avatar(){
    check_ajax_referer('upload_avatar', 'ajax-nonce');

	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');

	if ($_FILES) {
		foreach ($_FILES as $file => $array) {							
			$imageTypes = array (
				1, //IMAGETYPE_GIF
				2, //IMAGETYPE_JPEG
				3 //IMAGETYPE_PNG
			);

			$imageinfo = getimagesize($_FILES[$file]['tmp_name']);
			$width = @$imageinfo[0];
			$height = @$imageinfo [1];
			$type = @$imageinfo [2];
			$bits = @$imageinfo ['bits'];
			$mime = @$imageinfo ['mime'];

			if (!in_array($type, $imageTypes)) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			if ($width <= 1 && $height <= 1) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			}

			$filename = time() . substr(str_shuffle("genki02468"), 0, 5);
			
			switch($type) {
				case 1:
					$ext = '.gif';											
					break;
				case 2:
					$ext = '.jpg';
					break;
				case 3:
					$ext = '.png';
					break;
			}
			$_FILES[$file]['name'] = 'avatar-' . $filename . $ext;
			
			add_image_size('avatar48', 48, 48, true);
			$attach_id = media_handle_upload($file, $post_id, array('post_title' => 'Avatar for UserID ' . $_POST['avatar-userid']));			

			if (is_wp_error($attach_id)) {
				@unlink($_FILES[$file]['tmp_name']);
				echo 'error';
				die();
			} else {
				$user_avatar = get_user_meta($_POST['avatar-userid'], 'ipin_user_avatar', true);
				if ($user_avatar != '')
					wp_delete_attachment($user_avatar, true);

				update_user_meta($_POST['avatar-userid'], 'ipin_user_avatar', $attach_id);

				//attach the avatar to the user settings page so that it's not orpahned in the media library
				$settings_page = get_page_by_path('settings');
			
				global $wpdb;
				$wpdb->query(
					"
					UPDATE $wpdb->posts 
					SET post_parent = $settings_page->ID
					WHERE ID = $attach_id
					"
				);
			}
		}
	}
	
	$return = array();

	$thumbnail = wp_get_attachment_image_src($attach_id, 'thumbnail');
	$return['thumbnail'] = $thumbnail[0];
	$return['id'] = $attach_id;
	echo json_encode($return);
		
	exit;
}
add_action('wp_ajax_ipin-upload-avatar', 'ipin_upload_avatar');


//Delete avatar
function ipin_delete_avatar() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	$user_avatar = get_user_meta($_POST['id'], 'ipin_user_avatar', true);
	
	$upload_dir = wp_upload_dir();
	$avatar48_img = wp_get_attachment_image_src($user_avatar, 'avatar48');
	$avatar48_img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $avatar48_img[0]);

	if (file_exists($avatar48_img_path))
		unlink($avatar48_img_path);
	
	wp_delete_attachment($user_avatar, true);
	update_user_meta($_POST['id'], 'ipin_user_avatar', '');
	exit;
}
add_action('wp_ajax_ipin-delete-avatar', 'ipin_delete_avatar');


//**User Control Panel**//

//Add Board/Edit Board
function ipin_add_board() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	global $wpdb, $user_ID;
	$mode = $_POST['mode'];
	$term_id = $_POST['term_id'];
	$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
	$board_title  = sanitize_text_field($_POST['board_title']);
	$category_id  = $_POST['category_id'];
	
	if ($category_id == '-1')
		$category_id = '1';

	if ($mode == 'add') {
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';

		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if (stripslashes(htmlspecialchars($board_title, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
				$found = '1';
				break;
			}
		}
		
		if ($found == '0') {
			$slug = wp_unique_term_slug($board_title . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
			
			$new_board_id = wp_insert_term (
				$board_title,
				'board',
				array(
					'description' => $category_id,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			echo get_term_link($new_board_id['term_id'], 'board');
		} else {
			echo 'error';
		}

		//add new board to followers who fully follow user
		$usermeta_followers_id_allboards = get_user_meta($user_ID, '_Followers User ID All Boards');
		$followers_id_allboards = $usermeta_followers_id_allboards[0];

		if (!empty($followers_id_allboards)) {
			foreach ($followers_id_allboards as $followers_id_allboard) {
				$usermeta_following_board_id = get_user_meta($followers_id_allboard, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				array_unshift($following_board_id, $new_board_id['term_id']);
				update_user_meta($followers_id_allboard, '_Following Board ID', $following_board_id);
			}
		}
	} else if ($mode == 'edit') {
		$board_info = get_term_by('id', $term_id, 'board', ARRAY_A);
		
		if (stripslashes(htmlspecialchars($board_title, ENT_NOQUOTES, 'UTF-8')) == $board_info['name']) {
			wp_update_term(
				$term_id,
				'board',
				array(
					'description' => $category_id
				)
			);
			echo get_term_link(intval($term_id), 'board');
		} else {
			$board_children = get_term_children($board_info['parent'], 'board');
			$found = '0';

			foreach ($board_children as $board_child) {
				$board_child_term = get_term_by('id', $board_child, 'board');
				if (stripslashes(htmlspecialchars($board_title, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
					$found = '1';
					break;
				}
			}

			if ($found == '0') {
				$slug = wp_unique_term_slug($board_title . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
				wp_update_term(
					$term_id,
					'board',
					array(
						'name' => $board_title,
						'slug' => $slug,
						'description' => $category_id
					)
				);
				echo get_term_link(intval($term_id), 'board');
			} else {
				echo 'error';				
			}
		}

		//change the category of all posts in this board only if category is changed in the form
		$original_board_cat_id = get_term_by('id', $board_info['term_id'], 'board');
		if ($category_id != $original_board_cat_id) {		
			$posts = $wpdb->get_results(
				$wpdb->prepare("SELECT object_id FROM $wpdb->term_relationships
							WHERE term_taxonomy_id = %d
							"
							,intval($board_info['term_taxonomy_id']))
			);
			
			if ($posts) {
				foreach ($posts as $post) {
					wp_set_object_terms($post->object_id, array(intval($category_id)), 'category');
				}
			}
		}
	}
	exit;
}
add_action('wp_ajax_ipin-add-board', 'ipin_add_board');


//Delete board
function ipin_delete_board() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	global $wpdb;

	$board_id = $_POST['board_id'];
	$board_info = get_term_by('id', $board_id, 'board');

	//user_id is name of parent board
	$board_parent_info = get_term_by('id', $board_info->parent, 'board');
	$user_id = $board_parent_info->name;

	//get all posts in this board
	$posts = $wpdb->get_results(
		$wpdb->prepare("SELECT object_id FROM $wpdb->term_relationships
					WHERE term_taxonomy_id = %d
					"
					,intval($board_info->term_taxonomy_id))
	);

	if ($posts) {
		$post_ids = array();

		foreach ($posts as $post) {
			array_push($post_ids, $post->object_id);
		}

		$post_ids = implode(',', $post_ids);

		//set status to prune
		$wpdb->query("UPDATE $wpdb->posts
					SET post_status = 'ipin_prune'
					WHERE ID IN ($post_ids)
		");
	}

	//delete board from followers usermeta
	$followers = get_user_meta($user_id, '_Followers User ID');

	if(!empty($followers[0])) {
		foreach ($followers[0] as $follower) {
			$usermeta_following_board_id = get_user_meta($follower, '_Following Board ID');
			$following_board_id = $usermeta_following_board_id[0];

			unset($following_board_id[array_search($board_info->term_id, $following_board_id)]);
			$following_board_id = array_values($following_board_id);
			update_user_meta($follower, '_Following Board ID', $following_board_id);
		}
	}

	wp_delete_term($board_info->term_id, 'board');

	echo get_author_posts_url($user_id);
	exit;
}
add_action('wp_ajax_ipin-delete-board', 'ipin_delete_board');


//Add pin
function ipin_upload_pin(){
    check_ajax_referer('upload_pin', 'ajax-nonce');

	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');

	if ($_POST['mode'] == 'computer') {
		if ($_FILES) {
			foreach ($_FILES as $file => $array) {							
				$imageTypes = array (
					1, //IMAGETYPE_GIF
					2, //IMAGETYPE_JPEG
					3 //IMAGETYPE_PNG
				);

				$imageinfo = getimagesize($_FILES[$file]['tmp_name']);
				$width = @$imageinfo[0];
				$height = @$imageinfo [1];
				$type = @$imageinfo [2];
				$bits = @$imageinfo ['bits'];
				$mime = @$imageinfo ['mime'];

				if (!in_array($type, $imageTypes)) {
					@unlink($_FILES[$file]['tmp_name']);
					echo 'error';
					die();
				}

				if ($width <= 1 && $height <= 1) {
					@unlink($_FILES[$file]['tmp_name']);
					echo 'error';
					die();
				}

				if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
					@unlink($_FILES[$file]['tmp_name']);
					echo 'error';
					die();
				}

				$filename = time() . str_shuffle("gnk48");

				$frames = 0;
				
				switch($type) {
					case 1:
						$ext = '.gif';
						
						//check if is animated gif
						if(($fh = @fopen($_FILES[$file]['tmp_name'], 'rb')) && $error != 'error') {
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
				$original_filename = preg_replace('/[^(\x20|\x61-\x7A)]*/', '', strtolower(str_ireplace($ext, '', $_FILES[$file]['name']))); //preg_replace('/[^(\x48-\x7A)]*/' strips non-utf character. Ref: http://www.ssec.wisc.edu/~tomw/java/unicode.html#x0000
                $_FILES[$file]['name'] = strtolower(substr($original_filename, 0, 100)) . '-' . $filename . $ext;

				$attach_id = media_handle_upload($file, $post_id);

				if ($frames > 1) {
					update_post_meta($attach_id, 'a_gif', 'yes');
				}

				if (is_wp_error($attach_id)) {
					@unlink($_FILES[$file]['tmp_name']);
					echo 'error';
					die();
				}
			}   
		}
		
		$return = array();

		$thumbnail = wp_get_attachment_image_src($attach_id, 'medium');
		$return['thumbnail'] = $thumbnail[0];
		$return['id'] = $attach_id;
		echo json_encode($return);
	}

	if ($_POST['mode'] == 'web') {
		$url = esc_url_raw($_POST['pin_upload_web']);
		
		if (function_exists("curl_init")) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$image = curl_exec($ch);
			curl_close($ch);
		} elseif (ini_get("allow_url_fopen")) {
			$image = file_get_contents($url, false, $context);
		}

		if (!$image) {
			echo 'error';
			die();
		}

		$filename = time() . str_shuffle("gnk48");
		$file_array['tmp_name'] = WP_CONTENT_DIR . "/" . $filename . '.tmp';
		$filetmp = file_put_contents($file_array['tmp_name'], $image);
		
		if (!$filetmp) {
			@unlink($file_array['tmp_name']);
			echo 'error';
			die();
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
			echo 'error';
			die();
		}

		if ($width <= 1 && $height <= 1) {
			@unlink($file_array['tmp_name']);
			echo 'error';
			die();
		}

		if($mime != 'image/gif' && $mime != 'image/jpeg' && $mime != 'image/png') {
			@unlink($file_array['tmp_name']);
			echo 'error';
			die();
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
        $original_filename = preg_replace('/[^(\x20|\x61-\x7A)]*/', '', strtolower(str_ireplace($ext, '', basename($url)))); //preg_replace('/[^(\x48-\x7A)]*/' strips non-utf character. Ref: http://www.ssec.wisc.edu/~tomw/java/unicode.html#x0000
        $file_array['name'] = strtolower(substr($original_filename, 0, 100)) . '-' . $filename . $ext;

		$attach_id = media_handle_sideload($file_array, $post_id);
		
		if ($frames > 1) {
			update_post_meta($attach_id, 'a_gif', 'yes');
		}

		if (is_wp_error($attach_id)) {
			@unlink($file_array['tmp_name']);
			echo 'error';
			die();
		}

		$return = array();
		$thumbnail = wp_get_attachment_image_src($attach_id, 'medium');
		$return['thumbnail'] = $thumbnail[0];
		$return['id'] = $attach_id;
		echo json_encode($return);
	}
	exit;
}
add_action('wp_ajax_ipin-upload-pin', 'ipin_upload_pin');

//Remove %20 from filenames
function ipin_clean_filename($filename, $filename_raw) {
	$filename = str_replace('%20', '-', $filename);
	return $filename;
}
add_filter('sanitize_file_name', 'ipin_clean_filename', 1, 2);

//Add pin as a wp post
function ipin_postdata() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	global $user_ID;

	//get board info
	$board_add_new = sanitize_text_field($_POST['postdata_board_add_new']);
	$board_add_new_category = $_POST['postdata_board_add_new_category'];
	$board_parent_id = get_user_meta($user_ID, '_Board Parent ID', true);
	if ($board_add_new !== '') {
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';

		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if (stripslashes(htmlspecialchars($board_add_new, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
				$found = '1';
				$found_board_id = $board_child_term->term_id;
				break;
			}
		}

		if ($found == '0') {
			$slug = wp_unique_term_slug($board_add_new . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
			if ($board_add_new_category == '-1')
				$board_add_new_category = '1';

			$new_board_id = wp_insert_term (
				$board_add_new,
				'board',
				array(
					'description' => $board_add_new_category,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			
			$postdata_board = $new_board_id['term_id'];
		} else {
			$postdata_board = $found_board_id;
		}
	} else {
		$postdata_board = $_POST['postdata_board'];
	}

	//category ID is stored in the board description field
	$category_id = get_term_by('id', $postdata_board, 'board');

	$post_status = 'publish';
	
	if (!current_user_can('publish_posts')) {
		$post_status = 'pending';
	}
	
	$allowed_html = array(
		'a' => array(
			'href' => array()
		),
		'b' => array(),
		'strong' => array(),
		'em' => array(),
		'i' => array(),
		'blockquote' => array()
	);
	
	if (of_get_option('htmltags') != 'enable') {
		unset($allowed_html);
		$allowed_html = array();
	}
	
	if (of_get_option('form_title_desc') != 'separate') {
		$post_title = wp_kses($_POST['postdata_title'], $allowed_html);
	} else {
		$post_title = sanitize_text_field($_POST['postdata_title']);
	}
	
	$post_content = wp_kses($_POST['postdata_content'], $allowed_html);

	$post_array = array(
	  'post_title'    => $post_title,
	  'post_content'    => $post_content,
	  'post_status'   => $post_status,
	  'post_category' => array($category_id->description)
	);
	
	remove_action('save_post', 'ipin_save_post', 50, 2);
	$post_id = wp_insert_post($post_array);
		
	wp_set_post_terms($post_id, array($postdata_board), 'board');

	//update postmeta for new post
	if ($_POST['postdata_photo_source'] != '') {
		add_post_meta($post_id, '_Photo Source', esc_url($_POST['postdata_photo_source']));
		add_post_meta($post_id, '_Photo Source Domain', parse_url(esc_url($_POST['postdata_photo_source']), PHP_URL_HOST));
		
	}
	
	//add tags
	if ($_POST['postdata_tags']) {	
		wp_set_post_tags($post_id, sanitize_text_field($_POST['postdata_tags']));
	}
	
	//add price
	if ($_POST['postdata_price']) {
		add_post_meta($post_id, '_Price', round(sanitize_text_field($_POST['postdata_price']), 3));
	}

	$attachment_id = $_POST['postdata_attachment_id'];
	add_post_meta($post_id, '_thumbnail_id', $attachment_id);

	global $wpdb;
	$wpdb->query(
		"
		UPDATE $wpdb->posts 
		SET post_parent = $post_id
		WHERE ID = $attachment_id
		"
	);
	
	//add new board to followers who fully follow user
	if ($new_board_id && !is_wp_error($new_board_id)) {
		$usermeta_followers_id_allboards = get_user_meta($user_ID, '_Followers User ID All Boards');
		$followers_id_allboards = $usermeta_followers_id_allboards[0];
		
		if (!empty($followers_id_allboards)) {
			foreach ($followers_id_allboards as $followers_id_allboard) {
				$usermeta_following_board_id = get_user_meta($followers_id_allboard, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				array_unshift($following_board_id, $new_board_id['term_id']);
				update_user_meta($followers_id_allboard, '_Following Board ID', $following_board_id);
			}
		}
	}

	echo get_permalink($post_id);
	exit;
}
add_action('wp_ajax_ipin-postdata', 'ipin_postdata');


//Edit pin
function ipin_edit() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();

	$postinfo = get_post(intval($_POST['postdata_pid']), ARRAY_A);
	$user_id = $postinfo['post_author'];
		
	//Get board info
	$board_add_new = sanitize_text_field($_POST['postdata_board_add_new']);
	$board_add_new_category = $_POST['postdata_board_add_new_category'];
	$board_parent_id = get_user_meta($user_id, '_Board Parent ID', true);
	if ($board_add_new !== '') {
		$board_children = get_term_children($board_parent_id, 'board');
		$found = '0';

		foreach ($board_children as $board_child) {
			$board_child_term = get_term_by('id', $board_child, 'board');
			if (stripslashes(htmlspecialchars($board_add_new, ENT_NOQUOTES, 'UTF-8')) == $board_child_term->name) {
				$found = '1';
				$found_board_id = $board_child_term->term_id;
				break;
			}
		}

		if ($found == '0') {
			$slug = wp_unique_term_slug($board_add_new . '__ipinboard', 'board'); //append __ipinboard to solve slug conflict with category and 0 in title
			if ($board_add_new_category == '-1')
				$board_add_new_category = '1';

			$new_board_id = wp_insert_term (
				$board_add_new,
				'board',
				array(
					'description' => $board_add_new_category,
					'parent' => $board_parent_id,
					'slug' => $slug
				)
			);
			
			$postdata_board = $new_board_id['term_id'];
		} else {
			$postdata_board = $found_board_id;
		}
	} else {
		$postdata_board = $_POST['postdata_board'];		
	}

	//category ID is stored in the board description field
	$category_id = get_term_by( 'id', $postdata_board, 'board');

	$post_id = intval($_POST['postdata_pid']);
	$edit_post = array();
	$edit_post['ID'] = $post_id;
	$edit_post['post_category'] = array($category_id->description);
	$edit_post['post_name'] = '';
	
	$allowed_html = array(
		'a' => array(
			'href' => array()
		),
		'b' => array(),
		'strong' => array(),
		'em' => array(),
		'i' => array(),
		'blockquote' => array()
	);
	
	if (of_get_option('htmltags') != 'enable') {
		unset($allowed_html);
		$allowed_html = array();
	}

	if (of_get_option('form_title_desc') != 'separate') {
		$edit_post['post_title'] = wp_kses($_POST['postdata_title'], $allowed_html);
	} else {
		$edit_post['post_title'] = sanitize_text_field($_POST['postdata_title']);
	}
	
	$edit_post['post_content'] = wp_kses($_POST['postdata_content'], $allowed_html);

	remove_action('save_post', 'ipin_save_post', 50, 2);
	wp_update_post($edit_post);
	
	wp_set_post_terms($post_id, array($postdata_board), 'board');
	
	//update postmeta for new post
	if ($_POST['postdata_source'] != '') {
		update_post_meta($post_id, '_Photo Source', esc_url($_POST['postdata_source']));
		update_post_meta($post_id, '_Photo Source Domain', parse_url(esc_url($_POST['postdata_source']), PHP_URL_HOST));
	} else {
		delete_post_meta($post_id, '_Photo Source');
		delete_post_meta($post_id, '_Photo Source Domain');
	}
	
	//add tags
	wp_set_post_tags($post_id, sanitize_text_field($_POST['postdata_tags']));
	
	//add price
	if ($_POST['postdata_price']) {
		update_post_meta($post_id, '_Price', round(sanitize_text_field($_POST['postdata_price']), 3));
	}
	else {
		if (get_post_meta($post_id, '_Price', true) !== '') {
			delete_post_meta($post_id, '_Price');
		}
	}
	
	//add new board to followers who fully follow user
	if ($new_board_id && !is_wp_error($new_board_id)) {
		$usermeta_followers_id_allboards = get_user_meta($user_id, '_Followers User ID All Boards');
		$followers_id_allboards = $usermeta_followers_id_allboards[0];
		
		if (!empty($followers_id_allboards)) {
			foreach ($followers_id_allboards as $followers_id_allboard) {
				$usermeta_following_board_id = get_user_meta($followers_id_allboard, '_Following Board ID');
				$following_board_id = $usermeta_following_board_id[0];
				array_unshift($following_board_id, $new_board_id['term_id']);
				update_user_meta($followers_id_allboard, '_Following Board ID', $following_board_id);
			}
		}
	}
	
	echo get_permalink($post_id);
	exit;
}
add_action('wp_ajax_ipin-pin-edit', 'ipin_edit');


//Delete pin
function ipin_delete_pin() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
		
	global $wpdb;
	$post_id = $_POST['pin_id'];
	$post_author = intval($_POST['pin_author']);
	
	//set status to prune
	$wpdb->query("UPDATE $wpdb->posts
				SET post_status = 'ipin_prune'
				WHERE ID = $post_id
	");

	echo get_author_posts_url($post_author) . '?view=pins';
	exit;
}
add_action('wp_ajax_ipin-delete-pin', 'ipin_delete_pin');


//Email friend
function ipin_post_email() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
	
	global $user_ID, $user_identity;
	$post_id = $_POST['email_post_id'];
	$board_id = $_POST['email_board_id'];
	$recipient_name = sanitize_text_field($_POST['recipient_name']);
	$recipient_email = sanitize_text_field($_POST['recipient_email']);
	$recipient_message = sanitize_text_field($_POST['recipient_message']);
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
	
	if ($post_id) { //from single-pin.php
		$message = sprintf(__('Hi %s', 'ipin'), $recipient_name) . "\r\n\r\n";
		$message .= sprintf(__('%s wants to share "%s" with you.', 'ipin'), $user_identity, preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($post_id)), ENT_QUOTES, 'UTF-8'))) . "\r\n\r\n";
		if ($recipient_message !='') {
			$message .= sprintf(__('%s said, "%s".', 'ipin'), $user_identity, $recipient_message) . "\r\n\r\n";
		}
		$message .= sprintf(__('View pin at %s', 'ipin'), get_permalink($post_id)) . "\r\n\r\n";

		wp_mail($recipient_email, sprintf(__('%s wants to share a pin with you from %s', 'ipin'), $user_identity, $blogname), $message);
	}
	
	if ($board_id) { //from taxonomy-board.php
		$board_info = get_term_by('id', $board_id, 'board');
		$message = sprintf(__('Hi %s', 'ipin'), $recipient_name) . "\r\n\r\n";
		$message .= sprintf(__('%s wants to share "%s" with you.', 'ipin'), $user_identity, sanitize_text_field($board_info->name)) . "\r\n\r\n";
		if ($recipient_message !='') {
			$message .= sprintf(__('%s said, "%s".', 'ipin'), $user_identity, $recipient_message) . "\r\n\r\n";
		}
		$message .= sprintf(__('View board at %s', 'ipin'), home_url('/board/') . $board_info->term_id) . '/' . "\r\n\r\n";
		
		wp_mail($recipient_email, sprintf(__('%s wants to share a board with you from %s', 'ipin'), $user_identity, $blogname), $message);
	}
	exit;
}
add_action('wp_ajax_ipin-post-email', 'ipin_post_email');


//Report pin
function ipin_post_report() {
	$nonce = $_POST['nonce'];

	if (!wp_verify_nonce($nonce, 'ajax-nonce'))
		die();
	
	//global $user_ID, $user_identity;
	$post_id = $_POST['report_post_id'];
	$report_message = sanitize_text_field($_POST['report_message']);
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
	$message = sprintf(__('Someone reported the "%s" pin.', 'ipin'), preg_replace('/[\n\r]/', ' ', html_entity_decode(sanitize_text_field(get_the_title($post_id)), ENT_QUOTES, 'UTF-8'))) . "\r\n";
	$message .= sprintf(__('Message: %s', 'ipin'), $report_message) . "\r\n";
	$message .= sprintf(__('View pin at %s', 'ipin'), get_permalink($post_id)) . "\r\n\r\n";
	wp_mail(get_option('admin_email'), sprintf(__('[%s] Someone reported a pin', 'ipin'), $blogname), $message);
	exit;
}
add_action('wp_ajax_ipin-post-report', 'ipin_post_report');


//Custom usermeta - allow admin to override email verification
function ipin_profile_fields($user) {
	if ('' != $verify_email = get_the_author_meta( '_Verify Email', $user->ID)) {
	?>
	<table class="form-table">
		<tr>
			<th><label for="emailverify">Email Verification Link</label></th>
			<td>
				<?php $verification_link .= sprintf('%s?email=verify&login=%s&key=%s', home_url('/login/'), rawurlencode($user->user_login), $verify_email); ?>
				<input type="text" name="_Verify_Email" id="_Verify_Email" value="<?php echo $verification_link; ?>" class="regular-text" /><br />
				<span class="description">Leave blank to allow user to login without email verification.</span>
			</td>
		</tr>
	</table>
<?php
	}
}
add_action('edit_user_profile', 'ipin_profile_fields');

function ipin_save_profile_fields($user_id) {
	if (!$_POST['_Verify_Email']) {
		delete_user_meta($user_id, '_Verify Email');
	}
}
add_action('edit_user_profile_update', 'ipin_save_profile_fields');


//Setup theme for first time
function ipin_setup() {
	global $wpdb;
	$ipin_version = get_option('ipin_version');
	if (!$ipin_version) {
		//setup pages
		$page= array(
			'post_title' => __('Boards Settings', 'ipin'),
			'post_name' => 'boards-settings',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_boards.php');
		
		$page = array(
			'post_title' => __('Login', 'ipin'),
			'post_name' => 'login',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_login.php');
		
		$page = array(
			'post_title' => __('Lost Your Password?', 'ipin'),
			'post_name' => 'login-lpw',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_login_lpw.php');
	
		$page = array(
			'post_title' => __('Pins Settings', 'ipin'),
			'post_name' => 'pins-settings',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_pins.php');
	
		$page = array(
			'post_title' => __('Register', 'ipin'),
			'post_name' => 'register',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_register.php');
		
		$page = array(
			'post_title' => __('Settings', 'ipin'),
			'post_name' => 'settings',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_cp_settings.php');
		
		$page = array(
			'post_title' => __('Everything', 'ipin'),
			'post_name' => 'everything',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_everything.php');
		
		$page = array(
			'post_title' => __('Following', 'ipin'),
			'post_name' => 'following',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_following.php');
	
		$page = array(
			'post_title' => __('Popular', 'ipin'),
			'post_name' => 'popular',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_popular.php');
		
		$page = array(
			'post_title' => __('Source', 'ipin'),
			'post_name' => 'source',
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed'
		);
		$pageid = wp_insert_post($page);
		add_post_meta($pageid, '_wp_page_template', 'page_source.php');

		//setup top menu
		$menuname = 'Top Menu';
		$menulocation = 'top_nav';
		$menu_exists = wp_get_nav_menu_object($menuname);

		if( !$menu_exists){
			$menu_id = wp_create_nav_menu($menuname);
		
			$category_menu_id = wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' =>  __('Categories', 'ipin'),
				'menu-item-url' => '#', 
				'menu-item-status' => 'publish'));
				
			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' =>  __('Popular', 'ipin'),
				'menu-item-url' => home_url('/popular/'), 
				'menu-item-status' => 'publish',
				'menu-item-parent-id' => $category_menu_id));
		
			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' =>  __('Everything', 'ipin'),
				'menu-item-url' => home_url('/everything/'), 
				'menu-item-status' => 'publish',
				'menu-item-parent-id' => $category_menu_id));

			if(!has_nav_menu($bpmenulocation)){
				$locations = get_theme_mod('nav_menu_locations');
				$locations[$menulocation] = $menu_id;
				set_theme_mod('nav_menu_locations', $locations);
			}
		}
		
		//remove default sidebar widgets
		update_option('sidebars_widgets', array());

		//setup user accounts
		$ipin_users = get_users('orderby=ID');
		foreach ($ipin_users as $user) {
			$board_parent_id = get_user_meta($user->ID, '_Board Parent ID', true);

			if ($board_parent_id == '') {
				$board_id = wp_insert_term (
					$user->ID,
					'board'
				);
				update_user_meta($user->ID, '_Board Parent ID', $board_id['term_id']);
				update_user_meta($user->ID, 'ipin_user_notify_likes', '1');
				update_user_meta($user->ID, 'ipin_user_notify_repins', '1');
				update_user_meta($user->ID, 'ipin_user_notify_follows', '1');
				update_user_meta($user->ID, 'ipin_user_notify_comments', '1');
			}
		}
	
		update_option('ipin_version', '1.0');
		add_action('admin_notices', 'ipin_setup_notice');

	}
}
add_action('admin_init', 'ipin_setup');

if (EMPTY_TRASH_DAYS != 0) {
	add_action('admin_notices', 'ipin_setup_notice');
}

function ipin_setup_notice() {
	echo '<div class="error fade"><p><strong>Important! Please read the <a href="'
		 . admin_url('themes.php?page=theme_installation') . '">'
		 . 'Theme Installation</a> to finish installation.' . '</strong></div>';
}

//Setup Guide
function ipin_setup_guide() {
	if (function_exists('add_options_page'))
		add_theme_page('Theme Installation', 'Theme Installation', 'edit_theme_options', 'theme_installation', 'ipin_setup_guide_page');
}

function ipin_setup_guide_page() {
?>
<style type="text/css">
.wrap ol li { margin-bottom:30px; width: 520px; }
.wrap ul li { margin:3px 0 0 15px;list-style-type:disc; }
.wrap hr { border:none;border-top:1px dashed #aaa;height:0;margin:10px 0 0 0; }
</style>
<div class="wrap">
	<?php screen_icon(); ?>
    <h2>Theme Installation</h2>
	<hr />
    <table class="form-table"><tr><th>
		<div style="background: #fcfcfc; border: 1px solid #eee; padding: 15px; max-width: 550px;">
			<strong>Server Checklist</strong>
			<ul>
				<li>PHP Extension: Curl 
				<?php if (extension_loaded('curl')) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">enabled</span>
				<?php } else { $error_extension = true; ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not enabled!</span>
				<?php } ?>
				</li>
				
				<li>PHP Extension: Dom 
				<?php if (extension_loaded('dom')) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">enabled</span>
				<?php } else { $error_extension = true; ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not enabled!</span>
				<?php } ?>
				</li>
				
				<li>PHP Extension: Mbstring 
				<?php if (extension_loaded('mbstring')) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">enabled</span>
				<?php } else { $error_extension = true; ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not enabled!</span>
				<?php } ?>
				</li>
				
				<li>PHP Extension: GD/Imagemagick 
				<?php if (extension_loaded('gd') || extension_loaded('imagemagick')) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">enabled</span>
				<?php } else { $error_extension = true; ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not enabled!</span>
				<?php } ?>
				</li>
				
				<li>WP-Content Directory Permission
				<?php if (is_writable(WP_CONTENT_DIR)) { ?>
				<span style="color: green; font-weight: bold; font-style:italic;">writable</span>
				<?php } else { ?>
				<span style="color: red; font-weight: bold; font-style:italic;">not writable</span>
				<?php } ?>
				</li>
				
				<?php if ($error_extension ) { ?>
				<p><span style="color: red; font-weight: bold; font-style:italic;">Alert:</span> Required php extension not enabled. Please check with your host to enable them.</p>
				<?php } ?>
				
				<?php if (!is_writable(WP_CONTENT_DIR)) { ?>
				<p><span style="color: red; font-weight: bold; font-style:italic;">Alert:</span> WP-Content directory (<?php echo WP_CONTENT_DIR; ?>) not writeable. Please change directory permission to 755 or 777. If 777 works, check with your host if it's possible to work with 755, which is safer.</p>
				<?php } ?>
				
				<?php if (!$error_extension && is_writable(WP_CONTENT_DIR)) { ?>
				<p>Server checklist passed. Please proceed below.</p>
				<?php } ?>
			</ul>
		</div>

		<ol>
			<li>
				Go to <strong><a href="<?php echo admin_url('options-general.php'); ?>" target="_blank">Settings > General</a></strong> and set
				<ul>
					<li>Membership = Anyone can register (ticked)</li>
					<li>New User Default Role = Author</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('options-reading.php'); ?>" target="_blank">Settings > Reading</a></strong> and set
				<ul>
					<li>Blog pages show at most = 20 (or as you like. 20 for a good start)</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('options-media.php'); ?>" target="_blank">Settings > Media</a></strong> and set
				<ul>
					<li>Medium size: Max Width = 200, Max Height = 4096</li>
					<li>Large size: Max Width = 520, Max Height = 4096</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('options-permalink.php'); ?>" target="_blank">Settings > Permalinks</a></strong> and set
				<ul>
					<li>Custom Structure = /pin/%post_id%/</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>" target="_blank">Posts > Categories</a></strong>
				<ul>
					<li>Add your categories e.g. Celebrities, Food, Technology</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('nav-menus.php'); ?>" target="_blank">Appearance > Menus</a></strong>
				<ul>
					<li>From the “Categories” box, select the categories you created earlier and click “Add to Menu”. Drag the newly added items slightly to the right, such that they are aligned with the "Everything" menu.</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('themes.php?page=options-framework'); ?>" target="_blank">Appearance > Theme Options</a></strong>
				<ul>
					<li>Tweak to your liking</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php global $current_user, $wp_rewrite; echo home_url('/' . $wp_rewrite->author_base . '/' . $current_user->data->user_nicename . '/'); ?>" target="_blank"><?php echo home_url('/' . $wp_rewrite->author_base . '/' . $current_user->data->user_nicename . '/'); ?></a></strong>
				<ul>
					<li>If you see a 404 error, go to <a href="<?php echo admin_url('options-permalink.php'); ?>" target="_blank">Settings > Permalinks</a> and simply click "Save Changes" again</li>
				</ul>
			</li>
			
			<li>
				Edit <strong>wp-config.php</strong>
				<ul>
					<li>Find the wp-config.php file on your wordpress server directory and below "define('WPLANG', '');" add <em>define('EMPTY_TRASH_DAYS', 0);</em></li>
				</ul>
			</li>
			
			<li>
				<strong>Enjoy your theme! Or continue below to setup a sideblog (optional).</strong>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>" target="_blank">Posts > Categories</a></strong>
				<ul>
					<li>Add a new category e.g Blog</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('themes.php?page=options-framework'); ?>" target="_blank">Appearance > Theme Options</a></strong>
				<ul>
					<li>Under "Category For Blog", select the blog category you just created</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('nav-menus.php'); ?>" target="_blank">Appearance > Menus</a></strong>
				<ul>
					<li>From the “Categories” box, select the blog category you just created and click “Add to Menu”</li>
				</ul>
			</li>
			
			<li>
				Go to <strong><a href="<?php echo admin_url('post-new.php'); ?>" target="_blank">Posts > Add New</a></strong>
				<ul>
					<li>Create your post and make sure to select "Blog" under "Categories"</li>
				</ul>
			</li>
		</ol>
    </th></tr></table>
</div>
<?php
}
add_action('admin_menu', 'ipin_setup_guide');
?>