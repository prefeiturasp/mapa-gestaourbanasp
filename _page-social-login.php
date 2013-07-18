<?php header('Access-Control-Allow-Origin: *'); ?>

<?php
/*
Template Name: Social Login
*/
?>
<?php global $current_user;
    get_currentuserinfo();

	if ($current_user->ID)
	{
		$user_info = get_userdata($current_user->ID);
		echo '<div class="left"><img src="'.$user_info->user_avatar.'" height="27" />&nbsp;&nbsp;</div><div style="padding-top: 7px">' . $current_user->display_name .' | <a href="'.wp_logout_url( '/social-login/' ).'" title="Logout">sair</a></div>';
	} else {
		get_header();
		echo //'<p>Conecte-se via redes sociais:</p>';
		do_action( 'social_connect_form' );
		get_footer();
		?>
			<script>
				jQuery('hr').hide();
				jQuery('#header').hide();
				jQuery('#footer').hide();
				jQuery('label').hide();
			</script>
		<?php
	}
?>

<style>

/**
 * WebFonts
 */
 
@font-face {
    font-family: 'museoSlab';
    src: url('<?php echo bloginfo('template_url'); ?>/webfonts/museo_slab_500-webfont.eot');
    src: url('<?php echo bloginfo('template_url'); ?>/webfonts/museo_slab_500-webfont.eot?#iefix') format('embedded-opentype'),
         url('<?php echo bloginfo('template_url'); ?>/webfonts/museo_slab_500-webfont.woff') format('woff'),
         url('<?php echo bloginfo('template_url'); ?>/webfonts/museo_slab_500-webfont.ttf') format('truetype'),
         url('<?php echo bloginfo('template_url'); ?>/webfonts/museo_slab_500-webfont.svg#museo_slab500') format('svg');
    font-weight: normal;
    font-style: normal;

}

*{
	font-family: 'museoSlab', Arial, sans-serif;
	color: #6b6b6b;
	font-size: 12px;
}
body {
	background-image:url(<?php echo bloginfo('template_url'); ?>/img/bg-header-inner.png);
}
.left {
	float:left;
}
.right {
	float:right;
}

div.social_connect_ui img {
	width:27px;
	height:27px;
}
</style>