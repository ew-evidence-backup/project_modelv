<!DOCTYPE html>
<html <?php language_attributes(); ?> prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php

        if (is_home()) {
            echo 'fashion model, fashion models, fashion photographers, fashion, fashion modelling';
        } else {
            echo get_the_title();
        }



        //wp_title( '', true, 'right' ); echo ' '.get_the_title().' '; ' | '; bloginfo( 'name' ); $site_description = get_bloginfo( 'description', 'display' ); if ($site_description && (is_home() || is_front_page())) echo ' | ' . $site_description;
        ?></title>
    <?php
    global $post;
    if (is_single() && $post->post_content == '') {
        $meta_boards = get_the_terms($post->ID, 'board');
        $meta_categories = get_the_category($post->ID);

        if ($meta_boards) {
            foreach ($meta_boards as $meta_board) {
                $meta_board_name = $meta_board->name;
            }
        } else {
            $meta_board_name = __('Untitled', ipin);
        }

        foreach ($meta_categories as $meta_category) {
            $meta_category_name = $meta_category->name;
        }
        ?>
        <meta name="description"
              content="<?php _e('Pinned onto', 'ipin'); ?> <?php echo $meta_board_name; ?> <?php _e('Board in', 'ipin') ?> <?php echo $meta_category_name; ?> <?php _e('Category', 'ipin'); ?>"/>
    <?php
    }
    ?>

    <style type="text/css">

        div.transbox {
            z-index: 200;
            width: 400px;
            height: 180px;
            margin: 30px 50px;
            background-color: #ffffff;
            border: 1px solid black;
            opacity: 0.6;
            filter: alpha(opacity=60); /* For IE8 and earlier */
        }

        div.transbox p {
            margin: 30px 40px;
            font-weight: bold;
            color: #000000;
        }

    </style>
    <link rel="profile" href="http://gmpg.org/xfn/11"/>
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
    <link href="<?php echo get_template_directory_uri(); ?>/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo get_template_directory_uri(); ?>/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo get_stylesheet_directory_uri(); ?>/style.css" rel="stylesheet">
    <?php if (of_get_option('color_scheme') == 'dark') { ?>
        <link href="<?php echo get_stylesheet_directory_uri(); ?>/style-dark.css" rel="stylesheet">
    <?php } ?>

    <!--[if lt IE 9]>
    <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!--[if IE 7]>
    <link href="<?php echo get_template_directory_uri(); ?>/css/font-awesome-ie7.css" rel="stylesheet">
    <![endif]-->

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div align="center">
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <!-- MV_Footer -->
    <ins class="adsbygoogle"
         style="display:inline-block;width:970px;height:90px"
         data-ad-client="ca-pub-6488826557497129"
         data-ad-slot="8906955154"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>

<?php // print_r($_SERVER);?>

<?php if (!$_SERVER['REDIRECT_URL'] == '/register/' && !is_user_logged_in()) { ?>

    <div style="padding-bottom:2000px;" align="center">
        <a href="http://modelvariety.com/register/"><img src="<?php echo get_template_directory_uri(); ?>/img/join.jpg"
                                                         alt=""/></a></div>

<?php } ?>




<noscript>
    <div class="alert alert-error text-align-center">
        <h3>You need to enable Javascript.</h3>
    </div>

    <style>
        #masonry {
            visibility: visible !important;
        }
    </style>
</noscript>

<div id="topmenu"
     class="navbar<?php if (of_get_option('color_scheme') == 'dark') echo ' navbar-inverse'; ?> navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <i class="icon-bar"></i>
                <i class="icon-bar"></i>
                <i class="icon-bar"></i>

            </a>

            <?php $logo = of_get_option('logo'); ?>
            <a class="brand<?php if ($logo != '') {
                echo ' logo';
            } ?>" href="<?php echo esc_url(home_url('/')); ?>">
                <?php if ($logo != '') { ?>
                    <img src="<?php echo $logo ?>" alt="fashion model"/>
                <?php
                } else {
                    bloginfo('name');
                }
                ?>
            </a>

            <nav id="nav-main" class="nav-collapse" role="navigation">
                <ul id="menu-top-right" class="nav pull-right">

                    <?php if (is_user_logged_in()) {
                        global $user_ID, $user_identity; ?>
                        <?php if (current_user_can('edit_posts')) { ?>
                            <li class="dropdown"><a class="dropdown-toggle"
                                                    data-toggle="dropdown"
                                                    data-target=""
                                                    href=""><?php _e('Add', 'ipin'); ?> <i class="icon-caret-down"></i></a>
                                <ul class="dropdown-menu dropdown-menu-add">
                                    <li>
                                        <a href="<?php echo home_url('/pins-settings/'); ?>"><?php _e('Pin', 'ipin'); ?></a>

                                    </li>

                                    <li>
                                        <a href="<?php echo home_url('/boards-settings/'); ?>"><?php _e('Board', 'ipin'); ?></a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                        <li class="dropdown"><a class="dropdown-toggle"
                                                data-toggle="dropdown"
                                                data-target=""
                                                href=""><?php if (strlen($user_identity) > 12) {
                                    echo substr($user_identity, 0, 12) . '..';
                                } else {
                                    echo $user_identity;
                                } ?> <i class="icon-caret-down"></i></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="<?php echo get_author_posts_url($user_ID); ?>"><?php _e('Boards', 'ipin'); ?></a>
                                </li>
                                <li>
                                    <a href="<?php echo get_author_posts_url($user_ID); ?>?view=pins"><?php _e('Pins', 'ipin'); ?></a>
                                </li>
                                <li>
                                    <a href="<?php echo get_author_posts_url($user_ID); ?>?view=likes"><?php _e('Likes', 'ipin'); ?></a>
                                </li>
                                <li><a href="<?php echo home_url('/settings/'); ?>"><?php _e('Settings', 'ipin'); ?></a>
                                </li>
                                <?php if (current_user_can('administrator') || current_user_can('editor')) { ?>
                                    <li>
                                        <a href="<?php echo home_url('/wp-admin/'); ?>"><?php _e('WP Admin', 'ipin'); ?></a>
                                    </li>
                                <?php } ?>
                                <li>
                                    <a href="<?php echo home_url('/login/?action=logout&nonce=' . wp_create_nonce('logout')); ?>"><?php _e('Logout', 'ipin'); ?></a>
                                </li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="hidden-desktop">
                            <a href="<?php echo home_url('/register/'); ?>"><?php _e('Register', 'ipin'); ?></a></li>
                        <li class="hidden-desktop">
                            <a href="<?php echo wp_login_url($_SERVER['REQUEST_URI']); ?>"><?php _e('Login', 'ipin'); ?></a>
                        </li>
                        <li class="visible-desktop" id="loginbox-wrapper"><a id="loginbox"
                                                                             data-content='<?php if (function_exists('wsl_activate')) {
                                                                                 do_action('wordpress_social_login');
                                                                                 echo '<hr />';
                                                                             } ?>'
                                                                             aria-hidden="true"><i class="icon-signin"></i> <?php _e('Register / Login', 'ipin'); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <?php

                $remove = array("/","-","category","pin");
                $keyword = str_replace($remove, " ", $_SERVER['REQUEST_URI']);

                //echo'sd';
                if (has_nav_menu('top_nav')) {
                    $topmenu = wp_nav_menu(array('theme_location' => 'top_nav', 'menu_class' => 'nav', 'echo' => false));
                    if (!is_user_logged_in()) {
                        echo $topmenu;
                    } else {
                        $following_active = '';
                        if (is_page('following')) $following_active = ' active';
                        $following_menu = '<li>'.$keyword.'</li><li class="menu-following' . $following_active . '"><a href="' . home_url('/') . 'following/">' . __('Following', 'ipin') . '</a></li>';
                        $pos = stripos($topmenu, '<li');
                        echo substr($topmenu, 0, $pos) . $following_menu . substr($topmenu, $pos);
                    }
                } else {
                    echo '<ul id="menu-top" class="nav">';
                    //echo '<li>test</li>';
                    wp_list_pages('title_li=&depth=0&sort_column=menu_order');
                    echo '</ul>';
                }
                ?>
                <?php if ('' != $facebook_icon_url = of_get_option('facebook_icon_url')) { ?>
                    <a href="<?php echo $facebook_icon_url; ?>"
                       title="<?php _e('Find us on Facebook', 'ipin'); ?>"
                       class="topmenu-social"><i class="icon-facebook"></i></a>
                <?php } ?>

                <?php if ('' != $twitter_icon_url = of_get_option('twitter_icon_url')) { ?>
                    <a href="<?php echo $twitter_icon_url; ?>"
                       title="<?php _e('Follow us on Twitter', 'ipin'); ?>"
                       class="topmenu-social"><i class="icon-twitter"></i></a>
                <?php } ?>

                <a href="<?php bloginfo('rss2_url'); ?>"
                   title="<?php _e('Subscribe to our RSS Feed', 'ipin'); ?>"
                   class="topmenu-social"><i class="icon-rss"></i></a>

                <form class="navbar-search" method="get" id="searchform" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="text"
                           class="search-query"
                           placeholder="<?php _e('Search', 'ipin'); ?>"
                           name="s"
                           id="s"
                           value="<?php the_search_query(); ?>">
                    <?php if ($_GET['q']) { ?>
                        <input type="hidden" name="q" value="<?php echo $_GET['q']; ?>"/>
                    <?php } ?>
                </form>
            </nav>
        </div>
    </div>
</div>

<?php if (!$user_ID && of_get_option('top_message') != '') { ?>
    <div id="top-message-wrapper" class="container">
        <div class="row">
            <div class="span3 hidden-phone"></div>
            <div id="top-message" class="span6">
                <p class="pull-right">
                    <a class="btn btn-small btn-primary"
                       href="<?php echo home_url('/register/'); ?>"><?php _e('Join Now', 'ipin'); ?></a>
                    <a class="btn btn-small" href="<?php echo home_url('/login/'); ?>"><?php _e('Login', 'ipin'); ?></a>
                </p>

                <p class="top-message-p"><?php echo of_get_option('top_message'); ?></p>
            </div>
            <div class="span3"></div>
        </div>
    </div>
<?php } ?>

<?php if (of_get_option('header_ad') != '' && !is_page('pins-settings')) { ?>
    <div id="header-ad" class="container-fluid">
        <div class="row-fluid">
            <div class="span12"><?php eval('?>' . of_get_option('header_ad')); ?></div>
        </div>
    </div>
<?php } ?>

<?php if (is_search() || is_category() || is_tag() || is_page_template('page_everything.php') || is_page_template('page_following.php') || is_page_template('page_popular.php')) { ?>
<div class="subpage-title container-fluid">
    <div class="row-fluid">
        <div class="span4 hidden-phone"></div>
        <div class="span4">
            <?php if (is_search()) { ?>
                <h1><?php _e('Search results for', 'ipin'); ?> "<?php the_search_query(); ?>"</h1>
                <?php if (category_description()) { ?>
                    <?php echo category_description(); ?>
                <?php } ?>
            <?php } ?>

            <?php if (is_category()) { ?>
                <h1><?php single_cat_title(); ?></h1>
                <?php if (category_description()) { ?>
                    <?php echo category_description(); ?>
                <?php } ?>
            <?php } ?>

            <?php if (is_tag()) { ?>
                <h1><?php _e('Tag:', 'ipin'); ?> <?php single_tag_title(); ?></h1>
                <?php if (tag_description()) { ?>
                    <?php echo tag_description(); ?>
                <?php } ?>
            <?php } ?>

            <?php if (is_page_template('page_everything.php') || is_page_template('page_following.php') || is_page_template('page_popular.php')) { ?>
                <h1><?php the_title(); ?></h1>
            <?php } ?>
        </div>
        <div class="span4"></div>
    </div>
</div>
<?php } ?>