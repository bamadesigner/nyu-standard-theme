<?php
/**
 * Template part for displaying the NYU header.
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

$is_primary_nav_active = wp_rig()->is_primary_nav_menu_active();

wp_rig()->display_secondary_nav();

?>
<header id="masthead" class="site-header">
	<div class="site-header-container">
		<?php

		if ( $is_primary_nav_active ) :
			?>
			<button class="main-navigation__toggle--small nav-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'wp-rig' ); ?>" aria-controls="primary-menu" aria-expanded="false">
				<div class="nav-toggle__icon">
					<div class="nav-toggle__bar"></div>
					<div class="nav-toggle__bar"></div>
					<div class="nav-toggle__bar"></div>
				</div>
			</button>
			<?php

		endif;

		?>
		<div class="site-branding">
			<?php

			if ( is_front_page() && is_home() ) :
				?>
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<?php
			else :
				?>
				<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
				<?php
			endif;

			if ( wp_rig()->display_tagline_header() ) :

				$site_description = wp_rig()->get_site_description();

				if ( ! empty( $site_description ) ) :
					printf( '<p class="site-description">%s</p>', $site_description ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				endif;

			endif;

			?>
		</div>
		<?php

		if ( $is_primary_nav_active ) :
			?>
			<nav id="site-navigation" class="main-navigation nav--toggle-sub" aria-label="<?php esc_attr_e( 'Main menu', 'wp-rig' ); ?>">
				<?php wp_rig()->display_primary_nav_menu( array( 'menu_id' => 'primary-menu' ) ); ?>
			</nav>
			<?php
		endif;

		wp_rig()->display_secondary_nav();

		?>
	</div>
</header>
