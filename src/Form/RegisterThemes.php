<?php

/**
 * Handle Form Themes
 *
 * @package Give
 * @since   2.7.0
 */

namespace Give\Form;

use Give\Views\Form\Themes\Legacy\Legacy;
use Give\Views\Form\Themes\Sequoia\Sequoia;

defined( 'ABSPATH' ) || exit;

/**
 * Themes class
 *
 * @since 2.7.0
 */
class RegisterThemes {
	/**
	 * Themes
	 *
	 * @var Theme[]
	 */
	private $themes;

	/**
	 * Load themes
	 *
	 * @since 2.7.0
	 */
	public function load() {
		/**
		 * Filter list of form theme
		 *
		 * @since 2.7.0
		 *
		 * @param Theme[]
		 */
		$this->themes = apply_filters(
			'give_register_form_theme',
			[
				new Sequoia(),
				new Legacy(),
			]
		);

		// Validate: Only Give\Form\Theme class object is valid and Store themes.
		$this->themes = array_filter( $this->themes, array( $this, 'isValidTheme' ) );
	}


	/**
	 * Get Registered themes
	 *
	 * @return array
	 * @since 2.7.0
	 */
	public function get() {
		return $this->themes;
	}

	/**
	 * Get Registered theme
	 *
	 * @param string $themeID
	 *
	 * @return Theme|null
	 * @since 2.7.0
	 */
	public function getTheme( $themeID ) {
		foreach ( $this->themes as $theme ) {
			if ( $themeID === $theme->getID() ) {
				return $theme;
			}
		}

		return null;
	}

	/**
	 * Check if theme is valid or not
	 *
	 * @since 2.7.0
	 * @param $theme
	 *
	 * @return bool
	 */
	private function isValidTheme( $theme ) {
		return $theme instanceof Theme;
	}
}
