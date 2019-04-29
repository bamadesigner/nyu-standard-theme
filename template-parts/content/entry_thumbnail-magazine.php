<?php
/**
 * Template part for displaying a post's featured image
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

if ( post_password_required() || ! post_type_supports( 'post', 'thumbnail' ) || ! has_post_thumbnail() ) {
	return;
}

?>
<div class="post-thumbnail">
	<a class="post-thumbnail--link" href="<?php the_permalink(); ?>" aria-hidden="true">
		<?php

		the_post_thumbnail(
			'wp-rig-magazine',
			array(
				'alt' => the_title_attribute(
					array(
						'echo' => false,
					)
				),
			)
		);

		?>
	</a><!-- .post-thumbnail -->
</div>
<?php
