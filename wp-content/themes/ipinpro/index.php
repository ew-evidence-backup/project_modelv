<?php get_header(); global $user_ID; ?>



<div class="container-fluid">
	<?php
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

	function filter_where( $where = '' ) {
		$duration = '-' . of_get_option('frontpage_popularity_duration') . ' days';
		$where .= " AND post_date > '" . date('Y-m-d', strtotime($duration)) . "'";
		return $where;
	}
	
	if (is_home()) {
		if ('likes' == $popularity = of_get_option('frontpage_popularity')) {
			add_filter('posts_where', 'filter_where');
		
			$args = array(
				'meta_key' => '_Likes Count',
				'meta_compare' => '>',
				'meta_value' => '0',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'paged' => $paged
			);
			query_posts($args);
		} else if ($popularity == 'repins') {
			add_filter('posts_where', 'filter_where');
		
			$args = array(
				'meta_key' => '_Repin Count',
				'meta_compare' => '>',
				'meta_value' => '0',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'paged' => $paged
			);
			query_posts($args);
		} else if ($popularity == 'comments') {
			add_filter('posts_where', 'filter_where');
	
			$args = array(
				'orderby' => 'comment_count',
				'paged' => $paged
			);
			query_posts($args);
		}
	}

	get_template_part('index', 'masonry');
	get_footer();
?>