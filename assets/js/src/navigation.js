/**
 * File navigation.js.
 *
 * Handles toggling the navigation menu for small screens and enables TAB key
 * navigation support for dropdown menus.
 */

const KEYMAP = {
		TAB: 9,
		ESC: 27
	};

if ( 'loading' === document.readyState ) {

	// The DOM has not yet been loaded.
	document.addEventListener( 'DOMContentLoaded', initNavigation );
} else {

	// The DOM has already been loaded.
	initNavigation();
}

// Initiate the menus when the DOM loads.
function initNavigation() {
	initNavToggleSubmenus();
	initMainNavToggleSmall();
}

/**
 * Initiate the script to process all
 * navigation menus with submenu toggle enabled.
 */
function initNavToggleSubmenus() {

	const navTOGGLE = document.querySelectorAll( '.nav--toggle-sub' );

	// No point if no navs.
	if ( ! navTOGGLE.length ) {
		return;
	}

	navTOGGLE.forEach( function( nav ) {
		initEachNavToggleSubmenu( nav );
	});
}

/**
 * Initiate the script to process submenu
 * navigation toggle for a specific navigation menu.
 */
function initEachNavToggleSubmenu( nav ) {

	// Get the submenus.
	const SUBMENUS = nav.querySelectorAll( '.menu ul' );

	// No point if no submenus.
	if ( ! SUBMENUS.length ) {
		return;
	}

	// Create the dropdown button.
	const dropdownButton = getDropdownButton();

	SUBMENUS.forEach( function( submenu ) {
		const parentMenuItem = submenu.parentNode;
		var dropdown = parentMenuItem.querySelector( '.dropdown' );

		// If no dropdown, create one.
		if ( ! dropdown ) {

			// Create dropdown.
			dropdown = document.createElement( 'span' );
			dropdown.classList.add( 'dropdown' );

			const dropdownSymbol = document.createElement( 'i' );
			dropdownSymbol.classList.add( 'dropdown-symbol' );
			dropdown.appendChild( dropdownSymbol );

			// Add before submenu.
			submenu.parentNode.insertBefore( dropdown, submenu );

		}

		// Convert dropdown to button.
		const thisDropdownButton = dropdownButton.cloneNode( true );

		// Copy contents of dropdown into button.
		thisDropdownButton.innerHTML = dropdown.innerHTML;

		// Replace dropdown with toggle button.
		dropdown.parentNode.replaceChild( thisDropdownButton, dropdown );

		// Toggle the submenu when we click the dropdown button.
		thisDropdownButton.addEventListener( 'click', function( event ) {
			toggleSubMenu( this.parentNode );
		});

		// Clean up the toggle if a mouse takes over from keyboard.
		parentMenuItem.addEventListener( 'mouseleave', function( event ) {
			toggleSubMenu( this, false );
		});

		// When we focus on a menu link, make sure all siblings are closed.
		parentMenuItem.querySelector( 'a' ).addEventListener( 'focus', function( event ) {
			this.parentNode.parentNode.querySelectorAll( 'li.menu-item--toggled-on' ).forEach( function( item ) {
				toggleSubMenu( item, false );
			});
		});

		// Handle keyboard accessibility for traversing menu.
		submenu.addEventListener( 'keydown', function( event ) {

			// These specific selectors help us only select items that are visible.
			const focusSelector = 'ul.toggle-show > li > a, ul.toggle-show > li > button';

			if ( KEYMAP.TAB === event.keyCode ) {
				if ( event.shiftKey ) {

					// Means we're tabbing out of the beginning of the submenu.
					if ( isfirstFocusableElement( this, document.activeElement, focusSelector ) ) {
						toggleSubMenu( this.parentNode, false );
					}
				} else {

					// Means we're tabbing out of the end of the submenu.
					if ( islastFocusableElement( this, document.activeElement, focusSelector ) ) {
						toggleSubMenu( this.parentNode, false );
					}
				}
			} else if ( KEYMAP.ESC === event.keyCode ) {

				toggleSubMenu( this.parentNode, false );

				// Give focus to first element.
				this.parentNode.querySelector( '* > a' ).focus();

			}
		});

		submenu.parentNode.classList.add( 'menu-item--has-toggle' );

	});
}

/**
 * Initiate opening the small toggle for the main nav.
 */
function openMainNavToggleSmall() {
	const siteHEADER = document.querySelector( '.site-header' );
	const navTOGGLE = document.querySelector( '.main-navigation__toggle--small' );
	siteHEADER.addEventListener( 'keydown', processOpenMainNavFocus );
	siteHEADER.addEventListener( 'keydown', processCloseMainNavToggleSmall );
	document.body.classList.add( 'nav-main--toggled-on' );
	siteHEADER.classList.add( 'nav--toggled-on' );
	navTOGGLE.setAttribute( 'aria-expanded', 'true' );
}

/**
 * Initiate closing the small toggle for the main nav.
 */
function closeMainNavToggleSmall() {
	const siteHEADER = document.querySelector( '.site-header' );
	const navTOGGLE = document.querySelector( '.main-navigation__toggle--small' );
	siteHEADER.removeEventListener( 'keydown', processOpenMainNavFocus );
	siteHEADER.removeEventListener( 'keydown', processCloseMainNavToggleSmall );
	document.body.classList.remove( 'nav-main--toggled-on' );
	siteHEADER.classList.remove( 'nav--toggled-on' );
	navTOGGLE.setAttribute( 'aria-expanded', 'false' );
	navTOGGLE.focus();
}

/**
 * Process closing the small toggle for the main nav
 */
function processCloseMainNavToggleSmall( event ) {
	if ( KEYMAP.ESC !== event.keyCode ) {
		return;
	}
	closeMainNavToggleSmall();
}

/**
 * Process to keep the focus inside the main nav.
 */
function processOpenMainNavFocus( event ) {
	if ( KEYMAP.TAB !== event.keyCode ) {
		return;
	}

	const siteHEADER = document.querySelector( '.site-header' );
	const focusSelector = 'a, button';

	if ( event.shiftKey ) {

		if ( isfirstFocusableElement( siteHEADER, event.target, focusSelector ) ) {

			var lastElement = getLastFocusableElement( siteHEADER, focusSelector );
			if ( lastElement ) {
				lastElement.focus();
				event.preventDefault();
			}
		}
	} else {

		if ( islastFocusableElement( siteHEADER, event.target, focusSelector ) ) {

			var firstElement = getFirstFocusableElement( siteHEADER, focusSelector );
			if ( firstElement ) {
				firstElement.focus();
				event.preventDefault();
			}
		}
	}
}

/**
 * Initiate the script to process the main nav small toggle.
 */
function initMainNavToggleSmall() {

	const navTOGGLE = document.querySelector( '.main-navigation__toggle--small' );

	if ( ! navTOGGLE ) {
		return;
	}

	const siteHEADER = document.querySelector( '.site-header' );

	if ( ! siteHEADER ) {
		return;
	}

	// Add an initial values for the attribute.
	navTOGGLE.setAttribute( 'aria-expanded', 'false' );

	navTOGGLE.addEventListener( 'click', function() {

		if ( siteHEADER.classList.contains( 'nav--toggled-on' ) ) {
			closeMainNavToggleSmall();
		} else {
			openMainNavToggleSmall();
		}
	}, false );
}

/**
 * Toggle submenus open and closed, and tell screen readers what's going on.
 */
function toggleSubMenu( parentMenuItem, forceToggle ) {
	const toggleButton = parentMenuItem.querySelector( '.dropdown-toggle' ),
		subMenu = parentMenuItem.querySelector( 'ul' );
	var parentMenuItemToggled = parentMenuItem.classList.contains( 'menu-item--toggled-on' );

	// Will be true if we want to force the toggle on, false if force toggle close.
	if ( undefined !== forceToggle && 'boolean' == ( typeof forceToggle ) ) {
		parentMenuItemToggled = ! forceToggle;
	}

	// Toggle aria-expanded status.
	toggleButton.setAttribute( 'aria-expanded', ( ! parentMenuItemToggled ).toString() );

	/*
	 * Steps to handle during toggle:
	 * - Let the parent menu item know we're toggled on/off.
	 * - Toggle the ARIA label to let screen readers know will expand or collapse.
	 */
	if ( parentMenuItemToggled ) {

		// Toggle "off" the submenu.
		parentMenuItem.classList.remove( 'menu-item--toggled-on' );
		subMenu.classList.remove( 'toggle-show' );
		toggleButton.setAttribute( 'aria-label', wpRigScreenReaderText.expand );

		// Make sure all children are closed.
		parentMenuItem.querySelectorAll( '.menu-item--toggled-on' ).forEach( function( item ) {
			toggleSubMenu( item, false );
        });

	} else {

		// Make sure siblings are closed.
		parentMenuItem.parentNode.querySelectorAll( 'li.menu-item--toggled-on' ).forEach( function( item ) {
			toggleSubMenu( item, false );
		});

		// Toggle "on" the submenu.
		parentMenuItem.classList.add( 'menu-item--toggled-on' );
		subMenu.classList.add( 'toggle-show' );
		toggleButton.setAttribute( 'aria-label', wpRigScreenReaderText.collapse );

		// Give focus to first element.
		subMenu.querySelector( '* > li > a' ).focus();

	}
}

/**
 * Returns the dropdown button
 * element needed for the menu.
 */
function getDropdownButton() {
	const dropdownButton = document.createElement( 'button' );
	dropdownButton.classList.add( 'dropdown-toggle' );
	dropdownButton.setAttribute( 'aria-expanded', 'false' );
	dropdownButton.setAttribute( 'aria-label', wpRigScreenReaderText.expand );
	return dropdownButton;
}

/**
 * Returns first focusable element inside a container.
 */
function getFirstFocusableElement( container, focusSelector ) {
	const focusableElements = container.querySelectorAll( focusSelector );
	if ( 0 < focusableElements.length ) {
		return focusableElements[0];
	}
	return null;
}

/**
 * Returns last focusable element inside a container.
 */
function getLastFocusableElement( container, focusSelector ) {
	const focusableElements = container.querySelectorAll( focusSelector );
	if ( 0 < focusableElements.length ) {
		return focusableElements[focusableElements.length - 1];
	}
	return null;
}

/**
 * Returns true if element is the first focusable element in the container.
 */
function isfirstFocusableElement( container, element, focusSelector ) {
	const firstFocusableElement = getFirstFocusableElement( container, focusSelector );
	if ( firstFocusableElement ) {
		return element === firstFocusableElement;
	}
	return false;
}

/**
 * Returns true if element is the last focusable element in the container.
 */
function islastFocusableElement( container, element, focusSelector ) {
	const lastFocusableElement = getLastFocusableElement( container, focusSelector );
	if ( lastFocusableElement ) {
		return element === lastFocusableElement;
	}
	return false;
}
