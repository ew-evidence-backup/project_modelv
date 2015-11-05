<?php
define('WP_USE_THEMES', false); 
require('../../../wp-load.php');
?>
<html>
<head>
<meta charset="UTF-8" />
<title>Add Board for Existing Users</title>
<link href="<?php echo get_template_directory_uri(); ?>/css/bootstrap.css" rel="stylesheet">
<style>
body {
padding: 30px;
}
input {
height: 2em !important;
}
</style>
</head>
<body>
<div class="hero-unit">
<?php
if (current_user_can('manage_options')) {
	global $wpdb;
	
	if ($_POST['catname']) {
		$ipin_users = get_users('orderby=ID');
		foreach ($ipin_users as $user) {
			$board_parent_id = get_user_meta($user->ID, '_Board Parent ID', true);
			$boards_name = explode(',', $_POST['catname']);
			$category_id = explode(',', $_POST['catid']);
			
			$count = 0;
				
			foreach($boards_name as $board_name) {
				$board_name = sanitize_text_field($board_name);
				
				$board_id = wp_insert_term (
					$board_name,
					'board',
					array(
						'description' => sanitize_text_field($category_id[$count]),
						'parent' => $board_parent_id,
						'slug' => $board_name . '__ipinboard'
					)
				);
				echo 'Board added for User ID: ' . $user->ID . '<br />';
				$count++;
			}
			
			delete_option("board_children");
		}
		echo '<br /><span class="alert alert-success">Completed! Please re-enable user registration by checking "Anyone can register" <a href="' . admin_url('options-general.php') . '" target="_blank">here</a>.</span>';
		echo '<br /><br /><span class="alert alert-success">Also remember to update "Auto Create These Boards for New Users" theme option <a href="' . admin_url('themes.php?page=options-framework') . '" target="_blank">here</a>.</span>';
		echo '<br /><br /><span class="alert alert-success">To add more boards, <a href="' . $_SERVER['REQUEST_URI'] . '">click here</a>.</span>';
	} else {
	?>
		<h3>Add Board for Existing Users</h3>
		<p>Please temporarily disable user registration by unchecking "Anyone can register" <a href="<?php echo admin_url('options-general.php'); ?>" target="_blank">here</a></p>
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<label>Board Name:</label> <input type="text" name="catname" id="catename" value="" /><br/>
			<label>Category ID:</label> <input type="text" name="catid" id="cateid" value="" /><br />
			<input class="btn" type="submit" value="Submit" />
		</form>
	<?php
	}
} else {
	echo '<span class="alert alert-warning">Please login as Administrator first...</span>';	
}
?>
</div>
</body>
</html>