<?php

$user = wp_get_current_user();

// Includes
include('admin/maps.php');
include('admin/pins.php');
include('admin/theme.php');
if ( $user->roles[0] == 'contributor' ) {
	include('admin/metabox-contributor.php');
} else {
	include('admin/metabox.php');
}
include('template/ajax.php');

add_action( 'after_setup_theme', 'mapasdevista_setup' );
if ( ! function_exists( 'mapasdevista_setup' ) ):

    function mapasdevista_setup() {

        // Post Format support. You can also use the legacy "gallery" or "asides" (note the plural) categories.
        add_theme_support( 'post-formats', array( 'gallery', 'image', 'video' /*, 'audio' */ ) );

        // This theme uses post thumbnails
        add_theme_support( 'post-thumbnails' );

        // Make theme available for translation
        // Translations can be filed in the /languages/ directory
        load_theme_textdomain( 'mapasdevista', TEMPLATEPATH . '/languages' );

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'mapasdevista_top' => __( 'Map Menu (top)', 'mapasdevista' ),
            'mapasdevista_side' => __( 'Map Menu (side)', 'mapasdevista' )
        ) );
        
        add_image_size('mapasdevista-thumbnail',270,203,true);

    }

endif;

add_action( 'admin_menu', 'mapasdevista_admin_menu' );

function mapasdevista_admin_menu() {

    add_submenu_page('mapasdevista_maps', __('Maps', 'mapasdevista'), __('Maps', 'mapasdevista'), 'manage_maps', 'mapasdevista_maps', 'mapasdevista_maps_page');
    add_menu_page(__('Maps of view', 'mapasdevista'), __('Maps of view', 'mapasdevista'), 'manage_maps', 'mapasdevista_maps', 'mapasdevista_maps_page',null,30);
    add_submenu_page('mapasdevista_maps', __('Pins', 'mapasdevista'), __('Pins', 'mapasdevista'), 'manage_maps', 'mapasdevista_pins_page', 'mapasdevista_pins_page');
    add_submenu_page('mapasdevista_maps', __('Settings', 'mapasdevista'), __('Settings', 'mapasdevista'), 'manage_maps', 'mapasdevista_theme_page', 'mapasdevista_theme_page');

}

add_action( 'init', 'mapasdevista_init' );

function mapasdevista_init() {
    global $pagenow;

    if ( is_admin() && isset($_GET['activated'] ) && $pagenow == 'themes.php' ) {
        mapasdevista_activate();
    }
}

function mapasdevista_activate() {
    $adm = get_role('administrator');
    $adm->add_cap( 'manage_maps' );
    $adm->add_cap( 'post_item_on_map' );
}


add_action( 'admin_init', 'mapasdevista_admin_init' );

function mapasdevista_admin_init() {
    global $pagenow, $user;
    
    if($pagenow === "post.php" || $pagenow === "post-new.php" || (isset($_GET['page']) && $_GET['page'] === "mapasdevista_maps")) {
        // api do google maps versao 3 direto 
        $googleapikey = get_theme_option('google_key');
        $googleapikey = $googleapikey ? "&key=$googleapikey" : '';
        wp_enqueue_script('google-maps-v3', 'http://maps.google.com/maps/api/js?sensor=false' . $googleapikey);

        wp_enqueue_script('openlayers', 'http://openlayers.org/api/OpenLayers.js');

        wp_enqueue_script('mapstraction', mapasdevista_get_baseurl() . '/js/mxn/mxn-min.js' );
        wp_enqueue_script('mapstraction-core', mapasdevista_get_baseurl() . '/js/mxn/mxn.core-min.js');
        wp_enqueue_script('mapstraction-googlev3', mapasdevista_get_baseurl() . '/js/mxn/mxn.googlev3.core-min.js');
        wp_enqueue_script('mapstraction-openlayers', mapasdevista_get_baseurl() . '/js/mxn/mxn.openlayers.core-min.js');
    }
    
    if (isset($_GET['page']) && $_GET['page'] === "mapasdevista_theme_page") {
        
        wp_enqueue_script('jcolorpicker', mapasdevista_get_baseurl() . '/admin/colorpicker/js/colorpicker.js', array('jquery') );
        wp_enqueue_style('colorpicker', mapasdevista_get_baseurl() . '/admin/colorpicker/css/colorpicker.css' );
        wp_enqueue_script('mapasdevista_theme_options', mapasdevista_get_baseurl() . '/admin/mapasdevista_theme_options.js', array('jquery', 'jcolorpicker') );
    
    }

    if($pagenow === "post.php" || $pagenow === "post-new.php") {
    	if ( $user->roles[0] == 'contributor' ) {
    		wp_enqueue_script('metabox', mapasdevista_get_baseurl() . '/admin/metabox-contributor.js' );
    	} else {
        wp_enqueue_script('metabox', mapasdevista_get_baseurl() . '/admin/metabox.js' );
    	}
    } elseif(isset($_GET['page']) && $_GET['page'] === 'mapasdevista_pins_page') {
        wp_enqueue_script('metabox', mapasdevista_get_baseurl() . '/admin/pins.js' );
    }

		
    if ( $user->roles[0] == 'contributor' ) {
    	wp_enqueue_style('mapasdevista-admin', mapasdevista_get_baseurl('template_directory') . '/admin/admin-hidden.css');
    } else {
    	wp_enqueue_style('mapasdevista-admin', mapasdevista_get_baseurl('template_directory') . '/admin/admin.css');
    }
}

/* Page Template redirect */
add_action('template_redirect', 'mapasdevista_page_template_redirect');

function mapasdevista_page_template_redirect() {

    if (is_page()) {
        $page = get_queried_object();
        if (get_post_meta($page->ID, '_mapasdevista', true)) {
            mapasdevista_get_template('template/main-template');
            exit;
        }
    }
}

/**************************/


function mapasdevista_get_template($file, $context = null, $load = true) {
    
    $templates = array();
	if ( !is_null($context) )
		$templates[] = "{$file}-{$context}.php";

	$templates[] = "{$file}.php";
    
    if (preg_match('|/wp-content/themes/|', __FILE__)) {
        $found = locate_template($templates, $load, false);
    } else {
        $f = is_null($context) || empty($context) || strlen($context) == 0 ? $file : $file . '-'. $context ;
        $file = $file . '.php';
        $f = $f . '.php';
        
        if (
            file_exists(TEMPLATEPATH . '/' . $f) ||
            file_exists(STYLESHEETPATH . '/' . $f) ||
            file_exists(TEMPLATEPATH . '/' . $file) ||
            file_exists(STYLESHEETPATH . '/' . $file) 
            ) {
            $found = locate_template($templates, $load, false);
        } else {
            $f = WP_CONTENT_DIR . '/plugins/' . plugin_basename( dirname(__FILE__)) . '/' . $f;
            if ($load)
                include $f;
            else
                $found = $f;
        }
            
    }
    
    return $found;
    
}

function mapasdevista_get_baseurl() {
    
    if (preg_match('|[\\\/]wp-content[\\\/]themes[\\\/]|', __FILE__))
        return get_bloginfo('template_directory') . '/';
    else
        return plugins_url('mapasdevista') . '/';
}

function mapasdevista_get_maps() {

    global $wpdb;
    $maps = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '_mapasdevista'");
    $r = array();
    foreach ($maps as $m) {

        if (!is_serialized($m->meta_value))
            continue;

        $mapinfo = unserialize($m->meta_value);
        $mapinfo['page_id'] = $m->post_id;
        $r[$m->post_id] = $mapinfo;

    }

    return $r;
}

// COMMENTS

if (!function_exists('mapasdevista_comment')): 

function mapasdevista_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;  
    ?>
    <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">        

	        <p class="comment-meta alignright bottom">
	          <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'mapasdevista'), '| ', ''); ?>          
	        </p>
	        <div class="comment-entry clearfix">
            <div class="alignleft"><?php echo get_avatar($comment, 66); ?></div>
            <p class="comment-meta bottom">
              <?php printf( __('By <strong>%s</strong> on <strong>%s</strong> at <strong>%s</strong>.', 'mapasdevista'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
              <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'mapasdevista'); ?></em><?php endif; ?>
            </p>
            <?php comment_text(); ?>
        </div>

    </li>
    <?php
}

endif; 

// IMAGES
function mapasdevista_get_image($name) {
    return mapasdevista_get_baseurl() . '/img/' . $name;
}

function mapasdevista_image($name, $params = null) {
    $extra = '';

    if(is_array($params)) {
        foreach($params as $param=>$value){
            $extra.= " $param=\"$value\" ";		
        }
    }

    echo '<img src="', mapasdevista_get_image($name), '" ', $extra ,' />';
}

function mapasdevista_create_homepage_map($args) {

    /*
    if (get_option('mapasdevista_created_homepage'))
        return __('You have done this before...', 'mapasdevista');
    */
    
    $params = wp_parse_args(
        $args,
        array(
            'name' => __('Home Page Map', 'mapasdevista'),
            'api' => 'openlayers',
            'type' => 'road',
            'coord' => array(
                'lat' => '-13.888513111069498',
                'lng' => '-56.42951505224626'
            ),
            'zoom' => '4',
            'post_types' => array('post'),
            'filters' => array('new'),
            'taxonomies' => array('category')
        )
    );
    
    $page = array(
        'post_title' => 'Home Page',
        'post_content' => __('Page automatically created by Mapas de Vista as a placeholder for your map.', 'mapasdevista'),
        'post_status' => 'publish',
        'post_type' => 'page'
    );
    
    $page_id = wp_insert_post($page);
    
    if ($page_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_id);
        update_option('page_for_posts', 0);
        
        update_post_meta($page_id, '_mapasdevista', $params);
        
        update_option('mapasdevista_created_homepage', true);
        
        return true;
        
    } else {
        return $page_id;
    }

}

add_action('comment_post_redirect', 'mapasdevista_handle_comments_ajax', 10, 2);

function mapasdevista_handle_comments_ajax($location, $comment) {
    
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        die(mapasdevista_get_post_ajax($comment->comment_post_ID));
    } else {
        return $location;   
    }
}

/**
 * 
 * @global WP_Query $MAPASDEVISTA_POSTS_RCACHE
 * @param int $page_id
 * @param array $mapinfo
 * @param array $postsArgs
 * @return WP_Query 
 */

function mapasdevista_get_posts($page_id, $mapinfo, $postsArgs = array()){
    global $MAPASDEVISTA_POSTS_RCACHE;
    
    if(is_object($MAPASDEVISTA_POSTS_RCACHE) && get_class($MAPASDEVISTA_POSTS_RCACHE) === 'WP_Query'){
        
        $MAPASDEVISTA_POSTS_RCACHE->rewind_posts();
        return $MAPASDEVISTA_POSTS_RCACHE;
    }else{
        
        if ($mapinfo['api'] == 'image') {
            
            $postsArgs += array(
                    'posts_per_page'     => -1,
                    'orderby'         => 'post_date',
                    'order'           => 'DESC',
                    'meta_key'        => '_mpv_in_img_map',
                    'meta_value'      => $page_id,
                    'post_type'       => $mapinfo['post_types'],
                    'ignore_sticky_posts' => true
                );


        } else {

            $postsArgs += array(
                        'posts_per_page'     => -1,
                        'orderby'         => 'post_date',
                        'order'           => 'DESC',
                        'meta_key'        => '_mpv_inmap',
                        'meta_value'      => $page_id,
                        'post_type'       => $mapinfo['post_types'],
                        'ignore_sticky_posts' => true
                    );
        }

        if (isset($_GET['mapasdevista_search']) && $_GET['mapasdevista_search'] != '')
            $postsArgs['s'] = $_GET['mapasdevista_search'];
        
        $MAPASDEVISTA_POSTS_RCACHE = new WP_Query($postsArgs); 
        
        return $MAPASDEVISTA_POSTS_RCACHE;
    }
}

add_filter('the_content', 'mapasdevista_gallery_filter');
function mapasdevista_gallery_filter($content){
    return str_replace('[gallery]', '[gallery link="file"]', $content);
}

/* ADMIN HACKS */

function disable_browser_upgrade_warning() {
    remove_meta_box( 'dashboard_browser_nag', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'disable_browser_upgrade_warning' );

// Remove Default Metabox from post edit form
add_action( 'admin_menu', 'remove_meta_boxes' );
function remove_meta_boxes() {
    remove_meta_box( 'commentsdiv', 'post', 'normal' ); // Comments meta box
    remove_meta_box( 'revisionsdiv', 'post', 'normal' ); // Revisions meta box
    remove_meta_box( 'authordiv', 'post', 'normal' ); // Author meta box
    remove_meta_box( 'slugdiv', 'post', 'normal' ); // Slug meta box
    remove_meta_box( 'postexcerpt', 'post', 'normal' ); // Excerpt meta box
    remove_meta_box( 'formatdiv', 'post', 'normal' ); // Post format meta box
    remove_meta_box( 'trackbacksdiv', 'post', 'normal' ); // Trackbacks meta box
    //remove_meta_box( 'commentstatusdiv', 'post', 'normal' ); // Comment status meta box
    remove_meta_box( 'postimagediv', 'post', 'side' ); // Featured image meta box   
    remove_meta_box( 'pageparentdiv', 'page', 'side' ); // Page attributes meta box
    //remove_meta_box( 'categorydiv', 'post', 'side' ); // Categories meta box
    remove_action('admin_notices', 'default_password_nag'); // Change password message

}

function so_screen_layout_columns( $columns ) {
    $columns['post'] = 1;
    return $columns;
}
add_filter( 'screen_layout_columns', 'so_screen_layout_columns' );

function so_screen_layout_post() {
    return 1;
}
add_filter( 'get_user_option_screen_layout_post', 'so_screen_layout_post' );

/* teste de ordenação */

add_action('admin_init', 'set_user_metaboxes'); //I want it to fire every time edit post screen comes up
// add_action('user_register', 'set_user_metaboxes');  //You can also have it only set when a new user is created

function set_user_metaboxes($user_id=NULL) {

  //These are the metakeys we will need to update  
  $meta_key['order'] = 'meta-box-order_events';
  $meta_key['hidden'] = 'metaboxhidden_events';

  //So this can be used without hooking into user_register
  if ( ! $user_id)
    $user_id = get_current_user_id(); 

  //Set the default order if it has not been set yet by the user. These are WP handles
  if ( ! get_user_meta( $user_id, $meta_key['order'], true) ) {
    $meta_value = array(
        'side' => '',
        'normal' => 'events_metabox,submitdiv, commentsdiv',
        'advanced' => '',
    );
    update_user_meta( $user_id, $meta_key['order'], $meta_value );
  }

// Set the default hidden boxes if it has not been set yet by the user
  if ( ! get_user_meta( $user_id, $meta_key['hidden'], true) ) {
    $meta_value = array('postcustom','trackbacksdiv','commentstatusdiv','commentsdiv','slugdiv','authordiv','revisionsdiv','postexcerpt','postimagediv','tagsdiv-post_tag');
    update_user_meta( $user_id, $meta_key['hidden'], $meta_value );
  }
}

// Hide admin 'Screen Options' tab
function remove_screen_options_tab()
    {
        return false;
    }    
add_filter('screen_options_show_screen', 'remove_screen_options_tab');

// Hide admin "Help" tab
add_filter( 'contextual_help', 'mytheme_remove_help_tabs', 999, 3 );
function mytheme_remove_help_tabs($old_help, $screen_id, $screen){
    $screen->remove_help_tabs();
    return $old_help;
}

// Hide admin footer message "Obrigado por criar com o Wordpress"
function wpse_remove_footer()
{
    add_filter( 'admin_footer_text',    '__return_false', 11 );
    add_filter( 'update_footer',        '__return_false', 11 );
}
add_action( 'admin_init', 'wpse_remove_footer' );

// Hide "Adicionar Nova"
function hide_buttons()
{
    global $current_screen;

    if($current_screen->id == 'edit-post' && !current_user_can('publish_posts'));
    {
    echo '<style>.add-new-h2{display: none;}</style>';
    }
}
add_action('admin_head','hide_buttons');

// Hide "Featured Image"
add_action('do_meta_boxes', 'remove_thumbnail_box');

function remove_thumbnail_box() {
    remove_meta_box( 'postimagediv','post','side' );
}

// Change Wordpress logo from login page
function my_login_logo() { ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(<?php echo get_bloginfo( 'template_directory' ) ?>/img/logo-gestao_urbana.png);
            padding-bottom: 30px;
            background-size: 206px 22px;
            height: 22px;
            display: none; /*remove logo*/
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

// Change WordPress Login page logo link
function change_login_page_url( $url ) {
    return '';
}
add_filter( 'login_headerurl', 'change_login_page_url' );

// Redirect user to message page
add_filter('redirect_post_location', 'redirect_to_post_on_publish_or_save');

function redirect_to_post_on_publish_or_save($location)
{
    global $post;

    if (current_user_can('contributor') &&
        (isset($_POST['publish']) || isset($_POST['save'])) &&
        preg_match("/post=([0-9]*)/", $location, $match) &&
        $post &&
        $post->ID == $match[1] &&
        (isset($_POST['publish']) || $post->post_status == 'publish') && // Publishing draft or updating published post
        $pl = get_permalink($post->ID)
    ) {
        if (isset($_POST['publish'])) {

            // Homepage for new posts only
            $location = home_url() . '/?page_id=1277';

        } elseif ($ref = wp_get_original_referer()) {

            // Referer for edited posts
            $ref = explode('#', $ref, 2);
            $location = $ref[0] . '#post-' . $post->ID;

        } else {

            // Post page as a last resort
            $location = $pl;

        }
    }

    return $location;
}

// Change Category Label

add_action('init', 'renameCategory');
function renameCategory() {
global $wp_taxonomies;

$cat = $wp_taxonomies['category'];
$cat->label = 'Tipo';
$cat->labels->singular_name = 'Tipo';
$cat->labels->name = $cat->label;
$cat->labels->menu_name = $cat->label;
}

// Change Category Label

add_action('init', 'renameTag');
function renameTag() {
global $wp_taxonomies;

$cat = $wp_taxonomies['post_tag'];
$cat->label = 'Áreas';
$cat->labels->singular_name = 'Área';
$cat->labels->name = $cat->label;
$cat->labels->menu_name = $cat->label;
}

//Comment status - do not remove it, just hide it.
add_filter( 'wp_insert_post_data', 'handle_comments_setting' );
function handle_comments_setting( $data ) {
  if ( current_user_can( 'contributor' )) {
    if ( $data['guid'] == '') {
      //Default new posts to allow comments
      $data['comment_status'] = "open";        
    } else {
      //Otherwise ignore comment setting for community_member role users
      unset($data['comment_status']);
    }
  }
  return $data;
}

//Remove commentstatusdiv only from contributor
if ( current_user_can( 'contributor' ) ) {
    add_action( 'add_meta_boxes', 'my_remove_wp_seo_meta_box', 100000 );
}

function my_remove_wp_seo_meta_box() {
    remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
}

// Remove Profile Fields
function add_twitter_contactmethod( $contactmethods ) {
  unset($contactmethods['aim']);
  unset($contactmethods['jabber']);
  unset($contactmethods['yim']);
  return $contactmethods;
}
add_filter('user_contactmethods','add_twitter_contactmethod',10,1);

// post-form validation

add_action('admin_footer', 'sb_post_validation');

function sb_post_validation() {

 if ($_SERVER['PHP_SELF'] != '/wp-admin/post-new.php') {
 return;
 }

 echo '<script>

     jQuery( "form#post #publish" ).hide();
     jQuery( "form#post #publish" ).after("<input type=\'button\' value=\'Enviar\' class=\'sb_publish button-primary\' /><span class=\'sb_js_errors\'></span>");

     jQuery( ".sb_publish" ).click(function() {
     var error = false

    //js validation here

    //jQuery("form#post #title")
    var title_form = document.getElementById("title");
    var content_form = document.getElementById("content");
    var mpv_lat = document.getElementById("mpv_lat");
    var mpv_search_address = document.getElementById("mpv_search_address");

    if (mpv_lat.value == "") 
    {
        error=true;
        mpv_search_address.focus();
        mpv_search_address.className += " error";
    }

    else {

        mpv_search_address.className += " sucess";
    }

    if (content_form.value=="") 
    {
        error=true;
        content_form.focus();
        content_form.className += " error";
        //content_form.value = "Preencha a descrição do Formulário";
    }

    else {

        content_form.className += " sucess";
    }

    if (title_form.value=="")
    {
        error=true;
        title_form.focus();
        title_form.className += " error";
        //title_form.value = "Preencha o título do Formulário";
    }

    else {

        title_form.className += " sucess";
    }

    if (!error) 
        {
            jQuery( "form#post #publish" ).click();
            } else {
            return false;
         }
    });

     </script>';
}

// Change max upload size

function custom_file_max_upload_size( $file ) {

    $size = $file['size'];
    if ( $size > 2000 * 1024 ) {
        //$file['error'] = __( 'ERROR: you cannot upload files larger than 2M', 'textdomain' );
        echo '<script language="javascript">window.history.back(-1);</script>';
    }
    return $file;

}
//add_filter ( 'wp_handle_upload_prefilter', 'custom_file_max_upload_size', 10, 1 );