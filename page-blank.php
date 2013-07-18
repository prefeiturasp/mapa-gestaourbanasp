<?php
/*
Template Name: Blank Template
*/
?>

<style type="text/css">
	.page-blank {
		font-size:90%;
		font-family: Helvetica,Arial,sans-serif;
		color: #6B6B6B;
		line-height: 1.4em
	}
</style>
<div class="page-blank">

<?php 
	if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				the_content();

				//
				// Post Content here
				//
			} // end while
		} // end if
?>
</div>