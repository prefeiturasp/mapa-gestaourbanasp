<?php header('Access-Control-Allow-Origin: *'); ?>

<?php
/*
Template Name: Mapa Login
*/
?>
<?php global $current_user;
    get_currentuserinfo();

	if ($current_user->ID)
	{
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

<form>
	
</form>

<?php
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