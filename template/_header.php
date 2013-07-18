<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        
        <meta name="google" value="notranslate"> <!--  this avoids problems with hash change and the google chrome translate bar -->
        
        <title>
            <?php
            global $page, $paged;
            wp_title( '|', true, 'right' );
            bloginfo( 'name' );
            $site_description = get_bloginfo( 'description', 'display' );
            ?>
        </title>    
        
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
        <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_url' ); ?>/css/jquery.fancybox.css" />
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

        <!-- Barra Prefeitura -->
        <script type="text/javascript" src="//misc.prefeitura.sp.gov.br/v2/startup.js"></script>
        <!--         
        <noscript>
            <link href="//misc.prefeitura.sp.gov.br/v2/css/min/header-common.css" rel="stylesheet" type="text/css" />
        </noscript> 
        -->
        <!-- Barra Prefeitura -->   

        <style type="text/css">
            <?php include( mapasdevista_get_template('template/style.css', null, false) ); ?>

            #pmsp-header-container a:link {
                  font-family: verdana, arial, helvetica, serif !important;
                  font-size: 11px !important;
                  line-height: 13px;
                }

            #pmsp-main-menu ul {padding:0;}
        </style>
        
        <?php wp_enqueue_script("jquery"); ?>
        
        <?php wp_head(); ?>

        <script src="<?php bloginfo( 'template_url' ); ?>/js/jquery.cookie.js"></script>
        
        <script type="text/javascript" src="<?php bloginfo( 'template_url' ); ?>/js/jquery.fancybox.js"></script>
    </head>

    <body <?php body_class(); ?>>

        <!-- header -->

        <div id="logo_bar">
        <!--     
        <noscript>
            <div id="pmsp-header-noscript">
                <img src="//misc.prefeitura.sp.gov.br/v2/img/logos/logo-noscript.png" alt="Prefeitura de SP" title="Prefeitura de SP" />
            </div>
        </noscript> 
        -->
        <div id="logo_bar_inner">

        <a href="//gestaourbana.prefeitura.sp.gov.br/" target="_blank"><img class="logo-title logo"src="<?php bloginfo( 'template_url' ); ?>/img/logo-gestao_urbana.png" widt="206" height="22"/></a>

        <img class="logo" src="<?php bloginfo( 'template_url' ); ?>/img/logo-prefeitura.png" width="131" height="48"/>

        <!-- Social login -->

        <?php global $current_user;
            get_currentuserinfo();

        	if ($current_user->ID)
        	{
        		$user_info = get_userdata($current_user->ID);
        		echo '<div class="social_connect_ui "><div class="left social_connect_form"><img src="'.$user_info->user_avatar.'" height="27" />&nbsp;&nbsp;</div><div class="social_connect_form" style="padding-top: 6px;width: 150px;color:black;"><a href="wp-admin/profile.php" title="Perfil" class="inline">'.$current_user->display_name.'</a> | <a href="'.wp_logout_url( '/' ).'" title="Logout">sair</a></div></div>';
        	} else {
                    echo '<span style="float: right; width: 160px;color: #999;text-align: center;margin-top: 10px;line-height: 15px;">
                    Selecione um das opções ao lado para contribuir
                    </span>';
        	}
        ?>
        
        <a id="send-prob" class="inline btn" href="/wp-admin/post-new.php?mycat=1">Enviar Problema</a>
        <a id="send-sol" class="inline btn" href="/wp-admin/post-new.php?mycat=50">Enviar Solução</a>
        <a id="ajuda" class="inline" href="/?page_id=1148" title="Ajuda" style="float:right; margin: 25px 15px 0 0">Ajuda</a>

        <div class="clear"></div>
        </div>
        </div>

        <div class="clear"></div>

        <!-- fim header -->

        <?php do_action('wsi_first_load_mode'); ?>
                
        <div id="post_overlay">
            <a id="close_post_overlay" title="Fechar"><?php mapasdevista_image("close.png", array("alt" => "Fechar")); ?></a>
            <div id="post_overlay_content">
            </div>
        </div>
                
        <div id="map">
                
        </div>

        <div id="blog-title">
            <a href="<?php echo get_bloginfo('siteurl'); ?>">
                <img src="<?php echo get_theme_option('header_image'); ?>" />
            </a>
        </div>
                
        <?php wp_nav_menu( array( 'container_class' => 'map-menu-top', 'theme_location' => 'mapasdevista_top', 'fallback_cb' => false ) ); ?>
                
                
        <?php $menu_positions = get_theme_mod('nav_menu_locations'); ?>
                
        <?php if (isset($menu_positions['mapasdevista_side']) && $menu_positions['mapasdevista_side'] != '0'): ?>
                
        <div id="toggle-side-menu">
            <?php mapasdevista_image("side-menu.png", array("id" => "toggle-side-menu-icon")); ?>
        </div>
                
        <?php endif; ?>

        <div id="posts-loader">
                    <span id="posts-loader-loaded">0</span>/<span id="posts-loader-total">0</span> <span><?php _e('items loaded', 'mapasdevista'); ?></span>
        </div>
                
        <?php wp_nav_menu( array( 'container_class' => 'map-menu-side', 'theme_location' => 'mapasdevista_side', 'fallback_cb' => false ) ); ?>
                
        <div id="toggle-results">
            <?php mapasdevista_image("show-results.png", array("id" => "hide-results", "alt" => "Esconder Resultados")); ?>
        </div>

        <script>
        jQuery(document).ready(function() {
            // Enviar Contribuição
            jQuery('.inline').fancybox({
               'width' : 600,
               'type' : 'iframe',
               'padding' : 35,
               'afterClose' : function () { // USE THIS IT IS YOUR ANSWER THE KEY WORD IS "afterClose"
                parent.location.reload(true);
            }
            });
        });
        </script>

        <!-- Open Ajuda only once, with jquery.cookie! -->
        <script type="text/javascript">      
            function openFancybox() {
                jQuery('a#ajuda').trigger('click');
            }

            jQuery(document).ready(function() {
                var visited = jQuery.cookie('visited');
                if (visited == 'yes') {
                    return false;
                } else {
                    openFancybox();
                }
                jQuery.cookie('visited', 'yes', { expires: 7 });
                jQuery('a#ajuda').fancybox();
            });
        </script>