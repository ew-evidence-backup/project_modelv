<?php get_header();
global $user_ID, $wp_rewrite; ?>
    <script type="text/javascript">
        $(document).ready(function () {

            $.post('server.php', $('#form').serialize())

        });
    </script>
<?php //print_r($_SERVER); ?>
<div class="container-fluid">
    <div class="row-fluid">


        <div class="span4 hidden-phone"></div>
        <div class="span4 user-wrapper">
            <div class="post-content">
                <div class="user-avatar">
                    <div id="success">


                    </div>
                    <?php $user_info = get_user_by('id', $wp_query->query_vars['author']);
                    echo get_avatar($user_info->ID, '96');


                    include 'lib/Router.php';
                    $username = new Router();
                    $name = $username->getLastPath($_SERVER['REQUEST_URI']);
                    //echo $name;


                    $type = mysql_query('SELECT user_type FROM mv_users WHERE user_nicename = "'.$name.'"');
                    $data = mysql_fetch_array($type);
                    //$data = '';
                    //print_r($data);

                    ?>
                </div>

                <div class="user-profile">


                    <h1><?php echo $user_info->display_name; ?></h1>

                    <p>

                        <?php
                        //print_r('<pre>');
                        //echo is_user_logged_in();

                        $user_obj = wp_get_current_user();
                        $logged_in_user = $user_obj->user_nicename;

                        //print_r(wp_get_current_user());

                        if ($data[0] !== NULL) {


                            if ($data[0] == 1) {

                                $type = 'Model';

                            }

                            if ($data[0] == 2) {

                                $type = 'Photographer';

                            }

                            if ($data[0] == 3) {

                                $type = 'Agency';

                            }

                            if ($data[0] == 4) {

                                $type = 'Makeup Artist';

                            }


                            echo '<b style=text-transform:uppercase>' . $type . '</b>';

                        } else {


                            if (is_user_logged_in() == 1) {

                                if ($logged_in_user == $name) {


                                    echo '<b style="color:red">What are you?</b>';

                                    echo '<form id="form" method="post" action="/add_type.php">';
                                    echo '

                                   <select name="type" id="type">

                                        <option value="1">model</option>
                                        <option value="2">photographer</option>
                                        <option value="3">agency</option>
                                        <option value="4">makeup-artist</option>


                                    </select>


                            <input type="hidden" value="' . $user_ID . '" name="ID">
                                  <br/> <input class="btn" type="submit" value="submit" id="submit">';
                                    echo "<form/>";
                                } else {
                                    $type = '';
                                }


                            } else {
                                $type = '';

                            }


                        }

                        ?>
                        <?php echo $user_info->description; ?></p>

                    <?php if ($user_info->user_url) { ?>
                        <a href="<?php echo esc_url($user_info->user_url); ?>"
                           target="_blank"><i class="icon-globe"></i></a>
                    <?php } ?>

                    <?php if ($user_info->ipin_user_facebook) { ?>
                        <a href="http://www.facebook.com/<?php echo esc_attr($user_info->ipin_user_facebook); ?>"
                           target="_blank"><i class="icon-facebook-sign"></i></a>
                    <?php } ?>

                    <?php if ($user_info->ipin_user_twitter) { ?>
                        <a href="http://twitter.com/<?php echo esc_attr($user_info->ipin_user_twitter); ?>"
                           target="_blank"><i class="icon-twitter"></i></a>
                    <?php } ?>

                    <?php if ($user_info->ipin_user_pinterest) { ?>
                        <a href="http://pinterest.com/<?php echo esc_attr($user_info->ipin_user_pinterest); ?>"
                           target="_blank"><i class="icon-pinterest"></i></a>
                    <?php } ?>

                    <?php if ($user_info->ipin_user_googleplus) { ?>
                        <a href="http://plus.google.com/<?php echo esc_attr($user_info->ipin_user_googleplus); ?>"
                           target="_blank"><i class="icon-google-plus"></i></a>
                    <?php } ?>

                    <?php if ($user_info->ipin_user_location) { ?>
                        <a href="http://maps.google.com/?q=<?php echo esc_attr($user_info->ipin_user_location); ?>"
                           target="_blank"><i class="icon-map-marker"></i>
                            <small><?php echo esc_attr($user_info->ipin_user_location); ?></small>
                        </a>
                    <?php } ?>
                </div>

                <div class="clearfix"></div>
            </div>
        </div>

        <div class="span4"></div>
    </div>

    <div class="row-fluid">
        <div id="userbar" class="navbar">
            <div class="navbar-inner">
                <ul class="nav">
                    <?php
                    $parent_board_id = get_user_meta($user_info->ID, '_Board Parent ID', true);
                    $parent_board = get_term_by('id', $parent_board_id, 'board', ARRAY_A);
                    $boards = get_terms('board', array('parent' => $parent_board_id, 'hide_empty' => false, 'orderby' => 'id', 'order' => 'DESC'));
                    $boards_count = count($boards);

                    $blog_cat_id = of_get_option('blog_cat_id');
                    if ($blog_cat_id) {
                        $blog_post_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts
						LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
						LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
						WHERE $wpdb->term_taxonomy.term_id = $blog_cat_id
						AND $wpdb->term_taxonomy.taxonomy = 'category'
						AND $wpdb->posts.post_status = 'publish'
						AND post_author = $user_info->ID");
                    }
                    $pins_count = count_user_posts($user_info->ID) - $blog_post_count;

                    $likes_count = get_user_meta($user_info->ID, '_Likes Count', true);
                    $likes_count = $likes_count ? $likes_count : 0;
                    $followers_count = get_user_meta($user_info->ID, '_Followers Count', true);
                    $followers_count = $followers_count ? $followers_count : 0;
                    $following_count = get_user_meta($user_info->ID, '_Following Count', true);
                    $following_count = $following_count ? $following_count : 0;
                    ?>
                    <li<?php if (!isset($_GET['view'])) {
                        echo ' class="active"';
                    } ?>>
                        <a href="<?php echo get_author_posts_url($user_info->ID); ?>"><strong><?php echo $boards_count; ?></strong> <?php if ($boards_count == 1) {
                                _e('Board', 'ipin');
                            } else {
                                _e('Boards', 'ipin');
                            } ?></a></li>
                    <li<?php if ($_GET['view'] == 'pins') {
                        echo ' class="active"';
                    } ?>>
                        <a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=pins"><strong><?php echo $pins_count; ?></strong> <?php if ($pins_count == 1) {
                                _e('Pin', 'ipin');
                            } else {
                                _e('Pins', 'ipin');
                            } ?></a></li>
                    <li<?php if ($_GET['view'] == 'likes') {
                        echo ' class="active"';
                    } ?>>
                        <a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=likes"><strong><?php echo $likes_count; ?></strong> <?php if ($likes_count == 1) {
                                _e('Like', 'ipin');
                            } else {
                                _e('Likes', 'ipin');
                            } ?></a></li>
                    <li<?php if ($_GET['view'] == 'followers') {
                        echo ' class="active"';
                    } ?>>
                        <a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=followers"><strong id="ajax-follower-count"><?php echo $followers_count; ?></strong> <?php if ($followers_count == 1) {
                                _e('Follower', 'ipin');
                            } else {
                                _e('Followers', 'ipin');
                            } ?></a></li>
                    <li<?php if ($_GET['view'] == 'following') {
                        echo ' class="active"';
                    } ?>>
                        <a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=following"><strong><?php echo $following_count; ?></strong> <?php _e('Following', 'ipin'); ?>
                        </a></li>
                    <li>
                        <?php if ($user_info->ID != $user_ID) { ?>
                            <button class="btn follow ipin-follow<?php if ($followed = ipin_followed($parent_board['term_id'])) {
                                echo ' disabled';
                            } ?>"
                                    data-author_id="<?php echo $user_info->ID ?>"
                                    data-board_id="<?php echo $parent_board['term_id']; ?>"
                                    data-board_parent_id="<?php echo $parent_board['parent']; ?>"
                                    data-disable_others="no"
                                    type="button"><?php if (!$followed) {
                                    _e('Follow', 'ipin');
                                } else {
                                    _e('Unfollow', 'ipin');
                                } ?></button>
                        <?php } else { ?>
                            <a class="btn follow"
                               href="<?php echo home_url('/settings/'); ?>"><strong><?php _e('Edit Profile', 'ipin'); ?></strong></a>
                        <?php } ?>
                    </li>
                    <?php if ((current_user_can('administrator') || current_user_can('editor')) && $user_info->ID != $user_ID) { ?>
                        <li><a class="btn follow"
                               href="<?php echo home_url('/settings/?user=') . $user_info->ID; ?>"><strong><?php _e('Edit User', 'ipin'); ?></strong></a>
                        </li>
                    <?php } ?>
                </ul>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

<?php
if ($_GET['view'] == 'pins') {
    get_template_part('index', 'masonry');


} else if ($_GET['view'] == 'likes') {
    $post_likes = get_user_meta($user_info->ID, '_Likes Post ID');

    if (!empty($post_likes[0])) {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'post__in' => $post_likes[0],
            'orderby' => 'post__in',
            'paged' => $paged
        );

        query_posts($args);
        get_template_part('index', 'masonry');
    } else {
        ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="bigmsg">
                    <h2><?php _e('Nothing yet.', 'ipin'); ?></h2>
                </div>
            </div>
        </div>
        </div>
    <?php
    }


} else if ($_GET['view'] == 'followers' || $_GET['view'] == 'following') {
    if ($_GET['view'] == 'followers') {
        $followers = get_user_meta($user_info->ID, '_Followers User ID');
    } else if ($_GET['view'] == 'following') {
        $followers = get_user_meta($user_info->ID, '_Following User ID');
    }

    if (!empty($followers[0])) {
        $pnum = $_GET['pnum'] ? intval($_GET['pnum']) : 1;
        $followers_per_page = get_option('posts_per_page');
        $maxpage = ceil(count($followers[0]) / $followers_per_page);
        $followers[0] = array_slice($followers[0], ($followers_per_page * ($pnum - 1)), $followers_per_page);
        echo '<div id="user-profile-follow" class="row-fluid">';
        foreach ($followers[0] as $follower) {
            $follower_info = get_user_by('id', $follower);
            ?>
            <div class="follow-wrapper">
                <div class="post-content">
                    <?php
                    if ($follower != $user_ID) {
                        ?>
                        <button class="btn follow ipin-follow<?php $parent_board = get_user_meta($follower, '_Board Parent ID', true);
                        if ($followed = ipin_followed($parent_board)) {
                            echo ' disabled';
                        } ?>"
                                data-author_id="<?php echo $follower; ?>"
                                data-board_id="<?php echo $parent_board; ?>"
                                data-board_parent_id="0"
                                data-disable_others="no"
                                type="button"><?php if (!$followed) {
                                _e('Follow', 'ipin');
                            } else {
                                _e('Unfollow', 'ipin');
                            } ?></button>
                    <?php } else { ?>
                        <a class="btn follow disabled"><?php _e('Myself!', 'ipin'); ?></a>
                    <?php } ?>
                    <div class="user-avatar">
                        <a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $follower_info->user_nicename; ?>/"><?php echo get_avatar($follower_info->ID, '32'); ?></a>
                    </div>

                    <div class="user-name">
                        <h4>
                            <a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $follower_info->user_nicename; ?>/"><?php echo $follower_info->display_name; ?></a>
                        </h4>
                    </div>
                </div>
            </div>
        <?php
        }

        if ($maxpage != 0) {
            ?>
            <div id="navigation">
                <ul class="pager">
                    <?php if ($pnum != 1 && $maxpage >= $pnum) { ?>
                        <li id="navigation-previous">
                            <?php if ($_GET['view'] == 'followers') { ?>
                                <a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=followers&pnum=<?php echo $pnum - 1; ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
                            <?php } else if ($_GET['view'] == 'following') { ?>
                                <a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=following&pnum=<?php echo $pnum - 1; ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
                            <?php } ?>
                        </li>
                    <?php } ?>

                    <?php if ($maxpage != 1 && $maxpage != $pnum) { ?>
                        <li id="navigation-next">
                            <?php if ($_GET['view'] == 'followers') { ?>
                                <a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=followers&pnum=<?php echo $pnum + 1; ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
                            <?php } else if ($_GET['view'] == 'following') { ?>
                                <a href="<?php echo get_author_posts_url($user_info->ID); ?>?view=following&pnum=<?php echo $pnum + 1; ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php
        }
        echo '</div><div class="clearfix"></div></div>';
    } else {
        ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="bigmsg">
                    <?php if ($_GET['view'] == 'followers') { ?>
                        <h2><?php _e('No one following yet.', 'ipin'); ?></h2>
                    <?php } else if ($_GET['view'] == 'following') { ?>
                        <h2><?php _e('Not following anyone yet.', 'ipin'); ?></h2>
                    <?php } ?>
                </div>
            </div>
        </div>
        </div>
    <?php
    }
} else { //default to boards page 
    if ($boards_count > 0) {
        ?>
        <div id="user-profile-boards">
            <?php
            $pnum = intval($_GET['pnum']) ? $_GET['pnum'] : 1;
            $boards_per_page = 24;
            $maxpage = ceil($boards_count / $boards_per_page);
            $boards_paginated = get_terms('board', array('parent' => $parent_board_id, 'hide_empty' => false, 'orderby' => 'id', 'order' => 'DESC', 'number' => $boards_per_page, 'offset' => ($pnum - 1) * $boards_per_page));

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
                        </a>
                    </h4>

                    <p><?php echo $board_count ?> <?php if ($board_count == 1) {
                            _e('pin', 'ipin');
                        } else {
                            _e('pins', 'ipin');
                        } ?></p>

                    <div class="board-photo-frame">
                        <a href="<?php echo home_url('/board/' . $board_id . '/'); ?>">
                            <?php
                            $count = 1;
                            $post_array = array();
                            while ($loop_board->have_posts()) : $loop_board->the_post();
                                if ($count == 1) {
                                    $imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($loop_board->ID), 'medium');
                                    $imgsrc = $imgsrc[0];
                                    array_unshift($post_array, $imgsrc);
                                } else {
                                    $imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($loop_board->ID), 'thumbnail');
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
                                    if ($post_final !== '') {
                                        ?>
                                        <div class="board-main-photo-wrapper">
                                            <img src="<?php echo $post_final; ?>" class="board-main-photo" alt=""/>
                                        </div>
                                    <?php
                                    } else {
                                        ?>
                                        <div class="board-main-photo-wrapper">
                                        </div>
                                    <?php
                                    }
                                } else if ($post_final !== '') {
                                    ?>
                                    <div class="board-photo-wrapper">
                                        <img src="<?php echo $post_final; ?>" class="board-photo" alt=""/>
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
                            <button class="btn follow ipin-follow<?php if ($followed = ipin_followed($board_id)) {
                                echo ' disabled';
                            } ?>"
                                    data-author_id="<?php echo $user_info->ID; ?>"
                                    data-board_id="<?php echo $board_id; ?>"
                                    data-board_parent_id="<?php echo $board_parent_id; ?>"
                                    type="button"><?php if (!$followed) {
                                    _e('Follow', 'ipin');
                                } else {
                                    _e('Unfollow', 'ipin');
                                } ?></button>
                        <?php } else { ?>
                            <a class="btn edit-board"
                               href="<?php echo home_url('/boards-settings/?i=') . $board_id; ?>"><?php _e('Edit Board', 'ipin'); ?></a>
                        <?php } ?>
                    </div>
                </div>
            <?php } //end foreach	?>

            <?php if ($maxpage != 0) { ?>
                <div id="navigation">
                    <ul class="pager">
                        <?php if ($pnum != 1 && $maxpage >= $pnum) { ?>
                            <li id="navigation-previous">
                                <a href="<?php echo get_author_posts_url($user_info->ID); ?>?pnum=<?php echo $pnum - 1; ?>"><?php _e('&laquo; Previous', 'ipin') ?></a>
                            </li>
                        <?php } ?>

                        <?php if ($maxpage != 1 && $maxpage != $pnum) { ?>
                            <li id="navigation-next">
                                <a href="<?php echo get_author_posts_url($user_info->ID); ?>?pnum=<?php echo $pnum + 1; ?>"><?php _e('Next &raquo;', 'ipin') ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <div class="clearfix"></div>
            <div id="scrolltotop"><a href="#"><i class="icon-chevron-up"></i><br/><?php _e('Top', 'ipin'); ?></a></div>
        </div></div>


    <?php } else { ?>
        <div class="bigmsg">
            <h2><?php _e('Nothing yet.', 'ipin'); ?></h2>
        </div>
        </div>
    <?php
    }
}
get_footer();
?>