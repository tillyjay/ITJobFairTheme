
</div><!-- .container -->

<footer class="mt-5 pb-4 pt-4">
    <div class="container">
        <div class="d-flex align-items-center">
            <img src="<?= bloginfo('template_directory') ?>/assets/images/bird_logo_black.png" class="footer-logo">
            <div>
                <?= get_bloginfo("description") ?>
            </div>
            <div style="margin-left:auto">
                <ul class="nav">
                    <?php 
                    // Get secordary menu items
                    $menu_items = wp_get_nav_menu_items('Secondary Menu');
                    foreach($menu_items as $menu_item)
                    {
                        echo '<li class="nav-item"><a class="nav-link" href="'. $menu_item->url .'">'.$menu_item->title.'</a></li>';
                    }
                    ?>      
                </ul>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>