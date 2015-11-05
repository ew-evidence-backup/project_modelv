<div class="clearfix">
</div>
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
<div align="center">©2013 ModelVariety. All Rights Reserved.
</div>
<?php wp_footer(); ?>
<script>
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-46652489-1', 'modelvariety.com');
    ga('send', 'pageview');
</script>
<div align="center">
    <?php
    $remove = array("/","-","category","pin");
    $keyword = str_replace($remove, " ", $_SERVER['REQUEST_URI']);
    echo $keyword;
    ?>

</div>
</body>
</html>