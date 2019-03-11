<?php

/*
 * Only display the "hero" for posts and pages.
 * 
 * @TODO:
 *  - Do we want a checkbox to be able to turn this
 *      off on a post by post basis?
 * - Do we want to use a separate image for the header?
 */

namespace WP_Rig\WP_Rig;

if ( ! wp_rig()->can_display_hero() ) {
    return;
}

wp_rig()->display_hero();
