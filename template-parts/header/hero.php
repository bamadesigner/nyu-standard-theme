<?php

/*
 * Only display the "hero" for posts and pages.
 * 
 * @TODO:
 *  - Do we want a checkbox to be able to turn this
 *      off on a post by post basis?
 * - Do we want to use a separate image for the header?
 */
if ( ! is_singular() || ! has_post_thumbnail() ) {
    return;
}

?>
<aside class="site-hero">
    <?php the_post_thumbnail( array( 2000, 675 ) ); ?>
</aside>
