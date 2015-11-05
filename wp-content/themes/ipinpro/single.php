<?php
$blog_cat_id = of_get_option('blog_cat_id');

if ($blog_cat_id) {
	$blog_cats = array($blog_cat_id);

	if (get_option('ipin_blog_subcats')) {
		$blog_cats = array_merge($blog_cats, get_option('ipin_blog_subcats'));
	}
}

if (!empty($blog_cats) && in_category($blog_cats)) {
	get_template_part('single', 'blog');
} else {
	get_template_part('single', 'pin');
}
?>