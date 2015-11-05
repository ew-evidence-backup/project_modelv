<?php
define('WP_USE_THEMES', false);
require('../../../wp-load.php');
?>
<html>
<head>
<meta charset="UTF-8" />
<title>Strip Tags &amp; Price In Pin Description</title>
<link href="<?php echo get_template_directory_uri(); ?>/css/bootstrap.css" rel="stylesheet">
<style>
body {
padding: 30px;	
}
</style>
</head>
<body>
<div class="hero-unit">
<?php
if (current_user_can('manage_options')) {
	global $wpdb;
	
	echo '<h3>Purpose: Strip Tags &amp; Price In Pin Description</h3>';
	echo '<p>You should see a "Update Completed" message at the end. If you do not see the message, refresh this page again.</p>';
	
	$posts = $wpdb->get_results(
		"SELECT ID, post_title, post_name 
		FROM $wpdb->posts
		WHERE post_title LIKE '%tags[%]%'
		"
	);

	if (!empty($posts)) {
		echo '<p>Updating posts with tags...</p>';
		foreach ($posts as $post) {
			$new_title = substr($post->post_title, 0, stripos($post->post_title, ' tags['));
			$new_name = substr($post->post_name, 0, strpos($post->post_name, '-tags'));
			echo 'Post ID: ' . $post->ID .'<br>';
			$wpdb->query(
				"UPDATE $wpdb->posts SET post_title = '$new_title', post_name = '$new_name'
				WHERE ID = $post->ID
				"
			);
		}
	}
	
	$posts_price = $wpdb->get_results(
		"SELECT post_id, meta_value 
		FROM $wpdb->postmeta
		WHERE meta_key = '_Price'
		"
	);
	
	echo '<br /><p>Updating post with price...</p>';
	
	foreach ($posts_price as $post_price) {
		$new_title = trim(str_replace('$'. $post_price->meta_value, '', get_the_title($post_price->post_id)));
		echo 'Post ID: ' . $post_price->post_id .'<br>';
		$wpdb->query(
			"UPDATE $wpdb->posts SET post_title = '$new_title'
			WHERE ID = $post_price->post_id
			"
		);
	}
	
	echo '<br /><span class="alert alert-success">Update Completed!</span>';
} else {
	echo '<span class="alert alert-warning">Please login as Administrator first...</span>';	
}
?>
</div>
</body>
</html>