<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!--[if lt IE 9]>
    <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/html5.js"></script>
    <![endif]-->
    <?php wp_head(); ?>
</head>
 
<body <?php body_class(); ?>>

<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container">
    <a class="navbar-brand" href="<?= get_home_url() ?>">
      <img src="<?= get_site_icon_url() ?>" width="85" height="85" alt="<?= get_bloginfo("name") ?>">  
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <ul class="navbar-nav">        
        <?php
            $menu_items = wp_get_nav_menu_items('Primary Menu');
            
            foreach($menu_items as $menu_item)
            {
              // Don't display menu items that are featured. These get displayed later on the right side of menu.
              $classes = $menu_item->classes;
              if(!in_array('nav-item-feature', $classes))
              {
                echo '<li class="nav-item"><a class="nav-link" href="'. $menu_item->url .'">'.$menu_item->title.'</a></li>';
              }
            }
        ?>       
      </ul>
      <div class="d-flex ms-auto">
        <?php
            foreach($menu_items as $menu_item)
            {
              $classes = $menu_item->classes;
              if(in_array('nav-item-feature', $classes))
              {
                echo '<a class="btn btn-nav-feature" href="'. $menu_item->url .'">'.$menu_item->title.'</a>';
              }
            }
        ?>
    </div>
  </div>
</nav>

<div class="container mt-2">