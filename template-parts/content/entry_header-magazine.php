<?php
/**
 * Template part for displaying a post's header
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

?>

<header class="entry-header">
	<?php

	get_template_part( 'template-parts/content/entry_title', 'magazine' );

	get_template_part( 'template-parts/content/entry_meta', 'magazine' );

	?>
</header><!-- .entry-header -->
