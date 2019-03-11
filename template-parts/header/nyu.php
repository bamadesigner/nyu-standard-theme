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
 *  - Make sure works with AMP?
 */

?>
<header id="masthead" class="site-header">
	<div class="site-inner">
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
			<nav id="site-navigation" class="main-navigation nav--toggle-sub nav--toggle-small nav--toggle-white" aria-label="<?php esc_attr_e( 'Main menu', 'wp-rig' ); ?>"
				<?php
				if ( wp_rig()->is_amp() ) {

					?>
					[class]=" siteNavigationMenu.expanded ? 'main-navigation nav--toggle-sub nav--toggle-small nav--toggle-white nav--toggled-on' : 'main-navigation nav--toggle-sub nav--toggle-small nav--toggle-white' "
					<?php
				}
				?>
			>
				<?php
				if ( wp_rig()->is_amp() ) {
					?>
					<amp-state id="siteNavigationMenu">
						<script type="application/json">
							{
								"expanded": false
							}
						</script>
					</amp-state>
					<?php
				}
				?>
				<button class="menu-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'wp-rig' ); ?>" aria-controls="primary-menu" aria-expanded="false"
					<?php
					if ( wp_rig()->is_amp() ) {
						?>
						on="tap:AMP.setState( { siteNavigationMenu: { expanded: ! siteNavigationMenu.expanded } } )"
						[aria-expanded]="siteNavigationMenu.expanded ? 'true' : 'false'"
						<?php
					}
					?>
				>
					<?php esc_html_e( 'Menu', 'wp-rig' ); ?>
				</button>
				<?php wp_rig()->display_primary_nav_menu( array( 'menu_id' => 'primary-menu' ) ); ?>
			</nav>
			<?php
		endif;

		?>
	</div>
</header>
<?php

if ( wp_rig()->is_secondary_nav_menu_active() ) :
	?>
	<nav id="navigation-secondary" class="secondary-navigation nav--toggle-sub nav--toggle-small" aria-label="<?php esc_attr_e( 'Secondary menu', 'wp-rig' ); ?>">
		<button class="menu-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'wp-rig' ); ?>" aria-controls="secondary-menu" aria-expanded="false">
			<?php esc_html_e( 'Menu', 'wp-rig' ); ?>
		</button>
		<?php wp_rig()->display_secondary_nav_menu( array( 'menu_id' => 'secondary-menu' ) ); ?>
	</nav>
	<?php
endif;
