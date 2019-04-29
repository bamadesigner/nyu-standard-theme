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

$thumb_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'wp-rig-magazine', true );
$thumb_url = array_shift( $thumb_url );

if ( empty( $thumb_url ) ) {
	return;
}

?>
<div class="post-thumbnail" aria-hidden="true" style="background-image:url('<?php echo esc_attr( $thumb_url ); ?>');"></div>
<?php
