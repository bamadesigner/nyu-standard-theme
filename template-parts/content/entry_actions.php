<?php
/**
 * Template part for displaying a post's comment and edit links
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

$show_comments = ! is_singular( get_post_type() ) && ! post_password_required() && post_type_supports( get_post_type(), 'comments' ) && comments_open();

$edit_post_link = get_edit_post_link();

if ( ! $show_comments && ! $edit_post_link ) {
	return;
}

?>
<div class="entry-actions">
	<?php

	if ( $show_comments ) {
		?>
		<span class="comments-link">
			<?php
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'wp-rig' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)
			);
			?>
		</span>
		<?php
	}

	if ( $edit_post_link ) :
		?>
		<span class="edit-link">
			<a class="post-edit-link" href="<?php echo esc_url( $edit_post_link ); ?>">
				<?php

				printf(
					__( 'Edit <span class="screen-reader-text">%s</span>', 'wp-rig' ),
					get_the_title()
				);

				?>
			</a>
		</span>
		<?php
	endif;

	?>
</div><!-- .entry-actions -->
