<?php get_header();
global $user_ID, $wp_rewrite; ?>

    <div class="container" id="single-pin">
    <div class="row">
    <div class="span9">
    <div class="row">
    <div id="double-left-column" class="span6 pull-right">
    <?php while (have_posts()) : the_post(); ?>
        <div id="post-<?php the_ID(); ?>" <?php post_class('post-wrapper'); ?>>
        <div class="post-top-wrapper">
            <div class="pull-left">
                <a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . get_the_author_meta('user_nicename'); ?>/">
                    <?php echo get_avatar($post->post_author, '48'); ?>
                </a>
            </div>

            <div class="post-top-wrapper-header">
                <?php if ($post->post_author != $user_ID) { ?>
                    <button class="btn pull-right follow ipin-follow<?php $data_boards = get_the_terms($post->ID, 'board');
                    foreach ($data_boards as $data_board) {
                        $board_parent_id = $data_board->parent;
                        $board_id = $data_board->term_id;
                    }
                    if ($followed = ipin_followed($board_parent_id)) {
                        echo ' disabled';
                    } ?>"
                            data-board_parent_id="0"
                            data-author_id="<?php echo $post->post_author; ?>"
                            data-board_id="<?php echo $board_parent_id; ?>"
                            type="button"><?php if (!$followed) {
                            _e('Follow', 'ipin');
                        } else {
                            _e('Unfollow', 'ipin');
                        } ?></button>
                <?php } ?>
                <a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . get_the_author_meta('user_nicename'); ?>/">
                    <div class="post-top-wrapper-author"><?php echo get_the_author_meta('display_name'); ?></div>
                </a>
                <?php
                $original_post_id = get_post_meta($post->ID, "_Original Post ID", true);
                if ($original_post_id != '' && $original_post_id != 'deleted') {
                    _e('Repinned', 'ipin');

                } else {
                    _e('Pinned', 'ipin');
                }
                echo ' ' . ipin_human_time_diff(get_post_time('U', true));
                ?>
            </div>
        </div>

        <?php
        $imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
        $imgsrc_full = $imgsrc[0];

        //exclude animated gif
        if (substr($imgsrc[0], -3) != 'gif' && intval($imgsrc[1]) > 520) {
            $imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
        }

        if ($imgsrc[0] == '') {
            $imgsrc[0] = get_template_directory_uri() . '/img/blank.gif';
        }
        ?>

        <div class="post-share">
            <p>
                <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo rawurlencode(get_permalink()); ?>&amp;send=false&amp;layout=button_count&amp;width=75&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21"
                        scrolling="no"
                        frameborder="0"
                        style="border:none; overflow:hidden; width:75px; height:21px;"
                        allowTransparency="true"></iframe>
            </p>

            <p><a href="https://twitter.com/share"
                  class="twitter-share-button"
                  data-url="<?php the_permalink(); ?>"
                  data-text="<?php echo preg_replace('/[\n\r]/', ' ', the_title_attribute('echo=0')); ?>">Tweet</a></p>

            <p>

            <div class="g-plusone" data-size="small" data-href="<?php the_permalink(); ?>"></div>
            </p>

            <p><a data-pin-config="beside"
                  href="//pinterest.com/pin/create/button/?url=<?php echo rawurlencode(get_permalink()); ?>&media=<?php echo rawurlencode($imgsrc[0]); ?>&description=<?php echo preg_replace('/[\n\r]/', ' ', the_title_attribute('echo=0')); ?>"
                  data-pin-do="buttonPin"><img border="0"
                                               src="//assets.pinterest.com/images/pidgets/pin_it_button.png"/></a></p>

            <p><a id="post-embed" class="btn btn-mini"><strong>&lt;&gt; <?php _e('Embed', 'ipin'); ?></strong></a></p>

            <p><a id="post-email" class="btn btn-mini"><strong>@&nbsp; <?php _e('Email', 'ipin'); ?></strong></a></p>

            <p><a id="post-report" class="btn btn-mini"><strong><i class="icon-flag"></i> <?php _e('Report', 'ipin'); ?>
                    </strong></a></p>
        </div>

        <div class="post-top-meta">
            <div class="pull-left">
                <div class="post-actionbar">
                    <?php if (current_user_can('administrator') || current_user_can('editor') || current_user_can('author') || !is_user_logged_in()) { ?>
                        <a class="ipin-repin btn"
                           data-post_id="<?php echo $post->ID ?>"
                           href="#"><i class="icon-pushpin"></i> <?php _e('Repin', 'ipin'); ?></a>
                    <?php } ?>
                    <?php if ($post->post_author != $user_ID) { ?>
                        <button class="ipin-like btn <?php if (ipin_liked($post->ID)) {
                            echo ' disabled';
                        } ?>"
                                data-post_id="<?php echo $post->ID ?>"
                                data-post_author="<?php echo $post->post_author; ?>"
                                type="button"><i class="icon-heart"></i> <?php _e('Like', 'ipin'); ?></button>
                    <?php }
                    if ($post->post_author == $user_ID || current_user_can('edit_others_posts')) { ?>
                        <a class="ipin-edit btn"
                           href="<?php echo home_url('/pins-settings/'); ?>?i=<?php the_ID(); ?>"><?php _e('Edit', 'ipin'); ?></a>
                    <?php } ?>

                    <?php
                    //check if is video
                    //if is youtube video
                    $photo_source = get_post_meta($post->ID, "_Photo Source", true);
                    $is_video = 'no';
                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', str_replace('&#038;', '&', $photo_source), $videomatch)) {
                        $is_video = 'youtube';
                    }
                    //if is vimeo video
                    if (strpos(parse_url($photo_source, PHP_URL_HOST), 'vimeo.com') !== FALSE && sscanf(parse_url($photo_source, PHP_URL_PATH), '/%d', $video_id)) {
                        $is_video = 'vimeo';
                    }

                    if ($is_video == 'no') {
                        ?>
                        <a class="ipin-zoom btn"
                           href="<?php echo $imgsrc_full; ?>"><i class="icon-zoom-in"></i> <?php _e('Zoom', 'ipin'); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <div class="pull-right">
                <?php if ($photo_source == '') { ?>
                    <strong><?php _e('Uploaded by user', 'ipin'); ?></strong>
                <?php
                } else {
                    $photo_source_domain = parse_url($photo_source, PHP_URL_HOST);
                    _e('From', 'ipin'); ?>
                    <a href="<?php echo $photo_source; ?>" target="_blank"><?php echo $photo_source_domain; ?></a>
                <?php } ?>
            </div>
        </div>

        <div class="clearfix"></div>

        <?php if (of_get_option('single_pin_above_ad') != '') { ?>
            <div id="single-pin-above-ad">
                <?php eval('?>' . of_get_option('single_pin_above_ad')); ?>
            </div>
        <?php } ?>

        <div class="post-featured-photo">
            <?php
            $blog_cat_id = of_get_option('blog_cat_id');
            $blog_cats = array();

            if ($blog_cat_id) {
                $blog_cats = array($blog_cat_id);

                if (get_option('ipin_blog_subcats')) {
                    $blog_cats = array_merge($blog_cats, get_option('ipin_blog_subcats'));
                }
            }
            ?>
            <div class="post-nav-next"><?php echo previous_post_link('%link', '<i class="icon-chevron-right"></i>', false, $blog_cats); ?></div>
            <div class="post-nav-prev"><?php echo next_post_link('%link', '<i class="icon-chevron-left"></i>', false, $blog_cats); ?></div>

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
                    <div class="pricewrapper">
                        <div class="pricewrapper-inner"><?php echo $post_price_tag; ?></div>
                    </div>
                <?php
                }
            }
            ?>

            <?php
            //if is youtube video
            if ($is_video == 'youtube') {
                ?>
                <embed id="video-embed"
                       src="http://www.youtube.com/v/<?php echo $videomatch[1]; ?>?rel=0&autoplay=1"
                       type="application/x-shockwave-flash"
                       width="520"
                       height="292"
                       allowscriptaccess="always"
                       allowfullscreen="true"
                       wmode="opaque"></embed>
                <img class="featured-thumb hide" src="<?php echo $imgsrc[0]; ?>" alt="<?php the_title_attribute(); ?>"/>
                <?php
                //if is vimeo video
            } else if ($is_video == 'vimeo') {
                ?>
                <iframe id="video-embed"
                        src="http://player.vimeo.com/video/<?php echo $video_id; ?>?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;color=ffffff&amp;autoplay=1"
                        width="520"
                        height="292"
                        frameborder="0"
                        webkitAllowFullScreen
                        mozallowfullscreen
                        allowFullScreen></iframe>
                <img class="featured-thumb hide" src="<?php echo $imgsrc[0]; ?>" alt="<?php the_title_attribute(); ?>"/>
            <?php
            } else {
                ?>
                <img class="featured-thumb" src="<?php echo $imgsrc[0]; ?>" alt="<?php the_title_attribute(); ?>"/>
            <?php } ?>

            <div class="post-share-horizontal visible-phone">
                <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo rawurlencode(get_permalink()); ?>&amp;send=false&amp;layout=button_count&amp;width=75&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21"
                        scrolling="no"
                        frameborder="0"
                        style="border:none; overflow:hidden; width:75px; height:21px;"
                        allowTransparency="true"></iframe>

                <a href="https://twitter.com/share"
                   class="twitter-share-button"
                   data-url="<?php the_permalink(); ?>"
                   data-text="<?php echo preg_replace('/[\n\r]/', ' ', the_title_attribute('echo=0')); ?>">Tweet</a>

                <div class="g-plusone" data-size="small" data-href="<?php the_permalink(); ?>"></div>
                <script>(function () {
                        var po = document.createElement('script');
                        po.type = 'text/javascript';
                        po.async = true;
                        po.src = 'https://apis.google.com/js/plusone.js';
                        var s = document.getElementsByTagName('script')[0];
                        s.parentNode.insertBefore(po, s);
                    })();</script>

                <a class="pinterest"
                   data-pin-config="beside"
                   href="//pinterest.com/pin/create/button/?url=<?php echo rawurlencode(get_permalink()); ?>&media=<?php echo rawurlencode($imgsrc[0]); ?>&description=<?php echo preg_replace('/[\n\r]/', ' ', the_title_attribute('echo=0')); ?>"
                   data-pin-do="buttonPin"><img border="0"
                                                src="//assets.pinterest.com/images/pidgets/pin_it_button.png"/></a>

                <a id="post-embed" class="btn btn-mini"><strong>&lt;&gt; <?php _e('Embed', 'ipin'); ?></strong></a>

                <a id="post-email" class="btn btn-mini"><strong>@&nbsp; <?php _e('Email', 'ipin'); ?></strong></a>

                <a id="post-report"
                   class="btn btn-mini"><strong><i class="icon-flag"></i> <?php _e('Report', 'ipin'); ?></strong></a>
            </div>
        </div>

        <?php if (of_get_option('single_pin_below_ad') != '') { ?>
            <div id="single-pin-below-ad">
                <?php eval('?>' . of_get_option('single_pin_below_ad')); ?>
            </div>
        <?php } ?>

        <?php
        $tags = '';
        if (of_get_option('posttags') == 'enable') {
            $the_tags = get_the_tags();
            if ($the_tags) {
                foreach ($the_tags as $the_tags) {
                    $tags .= $the_tags->name . ', ';
                }
            }
        }
        ?>

        <div class="post-content">
            <?php if (of_get_option('form_title_desc') != 'separate') { ?>
                <?php if (mb_strlen(get_the_title()) < 120) { ?>
                    <h1 class="post-title"
                        data-title="<?php esc_attr_e($post->post_title); ?>"
                        data-tags="<?php esc_attr_e(substr($tags, 0, -2)); ?>"
                        data-price="<?php esc_attr_e($post_price); ?>"
                        data-content="<?php esc_attr_e($post->post_content); ?>"><?php echo wpautop(preg_replace_callback('/<a[^>]+/', 'ipin_nofollow_callback', get_the_title())); ?></h1>
                <?php } else { ?>
                    <div class="post-title"
                         data-title="<?php esc_attr_e($post->post_title); ?>"
                         data-tags="<?php esc_attr_e(substr($tags, 0, -2)); ?>"
                         data-price="<?php esc_attr_e($post_price); ?>"
                         data-content="<?php esc_attr_e($post->post_content); ?>"><?php echo wpautop(preg_replace_callback('/<a[^>]+/', 'ipin_nofollow_callback', get_the_title())); ?></div>
                <?php } ?>
            <?php } else { ?>
                <h1 class="post-title post-title-large"
                    data-title="<?php esc_attr_e($post->post_title); ?>"
                    data-tags="<?php esc_attr_e(substr($tags, 0, -2)); ?>"
                    data-price="<?php esc_attr_e($post_price); ?>"
                    data-content="<?php esc_attr_e($post->post_content); ?>"><?php the_title(); ?></h1>
            <?php } ?>

            <?php
            echo '<div class="thecontent">' . preg_replace_callback('/<a[^>]+/', 'ipin_nofollow_callback', apply_filters('the_content', get_the_content())) . '</div>';

            $posttags = get_the_tags();
            if ($posttags) {
                echo '<div id="thetags">';

                foreach ($posttags as $tag) {
                    echo '<a href="' . get_tag_link($tag->term_id) . '">' . $tag->name . '</a> ';
                }

                echo '</div>';
            }
            wp_link_pages(array('before' => '<p><strong>' . __('Pages:', 'ipin') . '</strong>', 'after' => '</p>'));
            ?>

            <?php if ($original_post_id != '' && $original_post_id != 'deleted') { ?>
                <p class="post-original-author">
                    <?php
                    $original_postdata = get_post($original_post_id, 'ARRAY_A');
                    $original_author = get_user_by('id', $original_postdata['post_author']);
                    $board = wp_get_post_terms($original_post_id, 'board', array("fields" => "all"));
                    ?>
                    <?php
                    if ($board) {
                        _e('Repinned from', 'ipin');
                        ?>
                        <a href="<?php echo get_term_link($board[0]->slug, 'board'); ?>"><?php echo $board[0]->name; ?></a>
                        <?php _e('by', 'ipin'); ?>
                        <a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $original_author->user_nicename; ?>/"><?php echo $original_author->display_name; ?></a>
                    <?php } ?>
                </p>
            <?php } ?>

            <?php
            $earliest_post_id = get_post_meta($post->ID, "_Earliest Post ID", true);
            if ($earliest_post_id != '' && $earliest_post_id != 'deleted') {
                ?>
                <p class="post-original-author">
                    <?php
                    $earliest_postdata = get_post($earliest_post_id, 'ARRAY_A');
                    $earliest_author = get_user_by('id', $earliest_postdata['post_author']);
                    $earliest_board = wp_get_post_terms($earliest_post_id, 'board', array("fields" => "all"));
                    ?>
                    <?php
                    if ($earliest_board) {
                        _e('Originally pinned onto', 'ipin'); ?>
                        <a href="<?php echo get_term_link($earliest_board[0]->slug, 'board'); ?>"><?php echo $earliest_board[0]->name; ?></a>
                        <?php _e('by', 'ipin'); ?>
                        <a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $earliest_author->user_nicename; ?>/"><?php echo $earliest_author->display_name; ?></a>
                    <?php } ?>
                </p>
            <?php } ?>
        </div>

        <div class="post-comments">
            <div class="post-comments-wrapper">
                <?php comments_template(); ?>
            </div>
        </div>

        <?php
        $boards = get_the_terms($post->ID, 'board');
        if ($boards) {
            ?>
            <div class="post-board hide">
                <div class="post-board-wrapper">
                    <?php if ($post->post_author != $user_ID) { ?>
                        <button class="btn btn-mini pull-right follow ipin-follow<?php if ($followed = ipin_followed($board_id)) {
                            echo ' disabled';
                        } ?>"
                                data-author_id="<?php echo $post->post_author; ?>"
                                data-board_id="<?php echo $board_id; ?>"
                                data-board_parent_id="<?php echo $board_parent_id; ?>"
                                type="button"><?php if (!$followed) {
                                _e('Follow', 'ipin');
                            } else {
                                _e('Unfollow', 'ipin');
                            } ?></button>
                    <?php } ?>
                    <h4><?php _e('Pinned onto', 'ipin') ?> <?php the_terms($post->ID, 'board', '<span>', ', ', '</span>'); ?></h4>
                    <?php
                    foreach ($boards as $board) {
                        $board_id = $board->term_id;
                        $board_name = $board->name;
                        $board_count = $board->count;
                        $board_slug = $board->slug;
                    }

                    $loop_board_args = array(
                        'posts_per_page' => 10,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'board',
                                'field' => 'id',
                                'terms' => $board_id
                            )
                        )
                    );

                    $loop_board = new WP_Query($loop_board_args);
                    $board_link = get_term_link($board_slug, 'board');
                    ?>
                    <a href="<?php echo $board_link; ?>">
                        <?php
                        $post_array = array();
                        while ($loop_board->have_posts()) : $loop_board->the_post();
                            $board_imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($loop_board->ID), 'thumbnail');
                            $board_imgsrc = $board_imgsrc[0];
                            array_unshift($post_array, $board_imgsrc);
                        endwhile;

                        wp_reset_query();

                        $post_array_final = array_fill(0, 10, '');

                        foreach ($post_array as $post_imgsrc) {
                            array_unshift($post_array_final, $post_imgsrc);
                            array_pop($post_array_final);
                        }

                        foreach ($post_array_final as $post_final) {
                            if ($post_final !== '') {
                                ?>
                                <div class="post-board-photo">
                                    <img src="<?php echo $post_final; ?>" alt=""/>
                                </div>
                            <?php
                            } else {
                                ?>
                                <div class="post-board-photo">
                                </div>
                            <?php
                            }
                        }
                        ?>
                    </a>
                </div>

                <div class="clearfix"></div>
            </div>
        <?php } ?>

        <?php
        if ($photo_source_domain != '') {
            $loop_domain_args = array(
                'posts_per_page' => 10,
                'meta_key' => '_Photo Source Domain',
                'meta_value' => $photo_source_domain
            );

            $loop_domain = new WP_Query($loop_domain_args);
            ?>
            <div id="post-board-source" class="post-board hide">
                <div class="post-board-wrapper">
                    <h4><?php _e('Also from', 'ipin'); ?>
                        <a href="<?php echo home_url('/source/') . $photo_source_domain; ?>/"><?php echo $photo_source_domain; ?></a>
                    </h4>
                    <a href="<?php echo home_url('/source/') . $photo_source_domain; ?>/">
                        <?php
                        $post_domain_array = array();
                        while ($loop_domain->have_posts()) : $loop_domain->the_post();
                            $domain_imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($loop_board->ID), 'thumbnail');
                            $domain_imgsrc = $domain_imgsrc[0];
                            array_unshift($post_domain_array, $domain_imgsrc);
                        endwhile;
                        wp_reset_query();

                        $post_domain_array_final = array_fill(0, 10, '');

                        foreach ($post_domain_array as $post_imgsrc) {
                            array_unshift($post_domain_array_final, $post_imgsrc);
                            array_pop($post_domain_array_final);
                        }

                        foreach ($post_domain_array_final as $post_final) {
                            if ($post_final !== '') {
                                ?>
                                <div class="post-board-photo">
                                    <img src="<?php echo $post_final; ?>" alt=""/>
                                </div>
                            <?php
                            } else {
                                ?>
                                <div class="post-board-photo">
                                </div>
                            <?php
                            }
                        }
                        ?>
                    </a>
                </div>
                <div class="clearfix"></div>
            </div>
        <?php
        }

        $post_likes = get_post_meta($post->ID, "_Likes User ID");
        $post_likes_count = count($post_likes[0]);
        if (!empty($post_likes[0])) {
            $post_likes[0] = array_slice($post_likes[0], -16);
            ?>
            <div class="post-likes">
                <div class="post-likes-wrapper">
                    <h4><?php _e('Likes', 'ipin'); ?></h4>

                    <div class="post-likes-avatar">
                        <?php
                        foreach ($post_likes[0] as $post_like) {
                            $like_author = get_user_by('id', $post_like);
                            ?>
                            <a id="likes-<?php echo $post_like; ?>"
                               href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $like_author->user_nicename; ?>/"
                               rel="tooltip"
                               title="<?php esc_attr_e($like_author->display_name); ?>">
                                <?php echo get_avatar($like_author->ID, '48'); ?>
                            </a>
                        <?php
                        }
                        if ($post_likes_count > 16) {
                            ?>
                            <p class="more-likes">
                                <strong>+<?php echo $post_likes_count - 16 ?></strong> <?php _e('more likes', 'ipin'); ?>
                            </p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php
        $post_repins = get_post_meta($post->ID, "_Repin Post ID");
        $post_repins_count = count($post_repins[0]);
        if (!empty($post_repins[0])) {
            $post_repins[0] = array_slice($post_repins[0], -10);
            ?>
            <div id="post-repins">
                <div class="post-repins-wrapper">
                    <h4><?php _e('Repins', 'ipin'); ?></h4>
                    <ul>
                        <?php
                        foreach ($post_repins[0] as $post_repin) {
                            $repin_postdata = get_post($post_repin, 'ARRAY_A');
                            $repin_author = get_user_by('id', $repin_postdata['post_author']);
                            ?>
                            <li id="repins-<?php echo $post_repin; ?>">
                                <a class="post-repins-avatar pull-left"
                                   href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $repin_author->user_nicename; ?>/">
                                    <?php echo get_avatar($repin_author->ID, '48'); ?>
                                </a>

                                <div class="post-repins-content">
                                    <a href="<?php echo home_url('/' . $wp_rewrite->author_base . '/') . $repin_author->user_nicename; ?>/">
                                        <?php echo $repin_author->display_name; ?>
                                    </a>
                                    <?php
                                    _e('onto', 'ipin');
                                    $board = wp_get_post_terms($post_repin, 'board', array("fields" => "all"));
                                    echo ' <a href="' . get_term_link($board[0]->slug, 'board') . '">' . $board[0]->name . '</a></div>';
                                    ?>
                            </li>
                        <?php
                        }
                        if ($post_repins_count > 10) {
                            ?>
                            <li class="more-repins">
                                <strong>+<?php echo $post_repins_count - 10; ?></strong> <?php _e('more repins', 'ipin'); ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>

        <div id="post-zoom-overlay"></div>
        <div id="post-embed-overlay"></div>
        <div id="post-email-overlay"></div>
        <div id="post-report-overlay"></div>

        <div id="post-fullsize" class="lightbox hide" tabindex="-1" role="dialog" aria-hidden="true">
            <div class='lightbox-header'>
                <button type="button" class="close" id="post-fullsize-close" aria-hidden="true">&times;</button>
            </div>
            <div class="lightbox-content">
                <img src="<?php echo $imgsrc_full; ?>"/>
            </div>
        </div>

        <div class="modal hide" id="post-embed-box" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-header">
                <button id="post-embed-close" type="button" class="close" aria-hidden="true">x</button>
                <h3><?php _e('Embed Pin on Your Blog', 'ipin'); ?></h3>
            </div>

            <div class="modal-footer">
                <?php $size = getimagesize(realpath(str_replace(home_url('/'), '', $imgsrc[0]))); ?>
                <input type="text"
                       id="embed-width"
                       value="<?php echo $size[0]; ?>"/><span class="help-inline"> <?php _e('px -Image Width', 'ipin'); ?></span>
                <input type="text"
                       id="embed-height"
                       value="<?php echo $size[1]; ?>"/><span class="help-inline"> <?php _e('px -Image Height', 'ipin'); ?></span>
                <textarea>
                    <div style='padding-bottom: 2px;line-height:0px;'><a href='<?php the_permalink(); ?>'
                                                                         target='_blank'><img src='<?php echo $imgsrc[0]; ?>'
                                                                                              border='0'
                                                                                              width='<?php echo $size[0]; ?>'
                                                                                              height='<?php echo $size[1]; ?>'/></a>
                    </div>
                    <div style='float:left;padding-top:0px;padding-bottom:0px;'>
                        <p style='font-size:10px;color:#76838b;'><?php _e('Source', 'ipin'); ?>:
                            <a style='text-decoration:underline;font-size:10px;color:#76838b;'
                               href='<?php echo $photo_source; ?>'><?php echo $photo_source_domain; ?></a> <?php _e('via', 'ipin'); ?>
                            <a style='text-decoration:underline;font-size:10px;color:#76838b;'
                               href='<?php echo home_url('/' . $wp_rewrite->author_base . '/') . get_the_author_meta('user_nicename'); ?>'
                               target='_blank'><?php echo get_the_author_meta('display_name'); ?></a> <?php _e('on', 'ipin'); ?>
                            <a style='text-decoration:underline;color:#76838b;'
                               href='<?php echo home_url('/'); ?>'
                               target='_blank'><?php bloginfo('name'); ?></a></p></div>
                </textarea>
            </div>
        </div>

        <div class="modal hide" id="post-email-box" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-header">
                <button id="post-email-close" type="button" class="close" aria-hidden="true">x</button>
                <h3><?php _e('Email This Pin', 'ipin'); ?></h3>
            </div>

            <div class="modal-footer">
                <input type="text"
                       id="recipient-name"/><span class="help-inline"> <?php _e('Recipient Name', 'ipin'); ?></span>
                <input type="email"
                       id="recipient-email"/><span class="help-inline"> <?php _e('Recipient Email', 'ipin'); ?></span>
                <input type="hidden" id="email-post-id" value="<?php echo $post->ID; ?>"/>
                <textarea placeholder="<?php _e('Message (optional)', 'ipin'); ?>"></textarea>
                <input class="btn btn-primary"
                       type="submit"
                       disabled="disabled"
                       value="<?php _e('Send Email', 'ipin'); ?>"
                       id="post-email-submit"
                       name="post-email-submit">

                <div class="ajax-loader-email-pin ajax-loader hide"></div>
            </div>
        </div>

        <div class="modal hide" id="post-report-box" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-header">
                <button id="post-report-close" type="button" class="close" aria-hidden="true">x</button>
                <h3><?php _e('Report This Pin', 'ipin'); ?></h3>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="report-post-id" value="<?php echo $post->ID; ?>"/>
                <textarea placeholder="<?php _e('Please write a little about why you want to report this pin.', 'ipin'); ?>"></textarea>
                <input class="btn btn-primary"
                       type="submit"
                       disabled="disabled"
                       value="<?php _e('Report Pin', 'ipin'); ?>"
                       id="post-report-submit"
                       name="post-report-submit">

                <div class="ajax-loader-report-pin ajax-loader hide"></div>
            </div>
        </div>
        </div>
    <?php endwhile; ?>
    </div>

    <div id="single-right-column" class="span3">
        <?php get_sidebar('left'); ?>
    </div>
    </div>
    </div>

    <div class="span3">
        <?php get_sidebar('right'); ?>
    </div>
    </div>
    </div>

<?php
get_template_part('single', 'masonry');
get_footer();
?>