<?php
/**
 * Template part for displaying the NYU header.
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

/*
 * @TODO:
 *  - Optimize printing menus.
 */

?>
<header id="masthead" class="site-header">
	<div class="site-header-container">
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

			?>
		</div>
		<?php

		if ( wp_rig()->is_primary_nav_menu_active() ) :
			?>
			<nav id="site-navigation" class="main-navigation nav--toggle-sub nav--toggle-small nav--toggle-white" aria-label="<?php esc_attr_e( 'Main menu', 'wp-rig' ); ?>">
				<button class="menu-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'wp-rig' ); ?>" aria-controls="primary-menu" aria-expanded="false">
					<div class="menu-toggle-icon">
						<div class="menu-toggle-bar"></div>
						<div class="menu-toggle-bar"></div>
						<div class="menu-toggle-bar"></div>
					</div>
				</button>
				<?php wp_rig()->display_primary_nav_menu( array( 'menu_id' => 'primary-menu' ) ); ?>
			</nav>
			<?php
		endif;

		if ( wp_rig()->is_secondary_nav_menu_active() ) :
			?>
			<nav id="navigation-secondary" class="secondary-navigation nav--toggle-sub nav--toggle-small" aria-label="<?php esc_attr_e( 'Secondary menu', 'wp-rig' ); ?>">
				<?php wp_rig()->display_secondary_nav_menu( array( 'menu_id' => 'secondary-menu' ) ); ?>
			</nav>
			<?php
		endif;
		

		?>
	</div>
</header>
