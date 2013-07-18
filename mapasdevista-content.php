<section id="entry-content" class="clearfix">

    <?php 
        the_content();
        //global $withcomments;
        //$withcomments = 1;
        //comments_template( 'comments.php', true );
    ?>
</section>

<footer class="entry-meta">
</footer>

<?php comments_template('comments.php', true ); ?>