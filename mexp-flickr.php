<?php
/**
 * Plugin Name: MEXP Flickr
 * Depends: Media Explorer
 * Plugin URI: https://github.com/gedex/mexp-flickr
 * Description: Flickr extension for the Media Explorer.
 * Version: 0.1.0
 * Author: Akeda Bagus
 * Author URI: http://gedex.web.id/
 * Text Domain: mexp-flickr
 * Domain Path: /languages
 * License: GPL v2 or later
 * Requires at least: 3.6
 * Tested up to: 3.8
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * Class that acts as plugin bootstrapper.
 *
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class MEXP_Flickr {

	/**
	 * Plugin version.
	 */
	const VERSION = '0.1.0';

	/**
	 * Constructor.
	 *
	 * - Defines constants used in this plugin.
	 * - Autoloader registration.
	 * - Loads the translation used in this plugin.
	 * - Loads Flickr service.
	 *
	 * @since 0.1.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->define_constants();

		// Autloader registration.
		spl_autoload_register( array( $this, 'loader' ) );

		$this->i18n();

		// Loads the Flickr service.
		add_filter( 'mexp_services', array( $this, 'load_flickr_service' ) );
	}

	/**
	 * Autoloader for this plugin. The convention for a class to be loaded:
	 *
	 * - Prefixed with this class name and '_'
	 * - Filename in lowercase without the prefix separated by '-'.
	 *
	 * @since 0.1.2
	 * @access public
	 * @param string $classname Class name
	 * @return void
	 */
	public function loader( $classname ) {
		if ( false === strpos( $classname, __CLASS__ . '_' ) )
			return;

		$classname = str_replace( __CLASS__ . '_', '', $classname );
		$filename  = str_replace( '_', '-', strtolower( $classname ) );

		require_once MEXP_FLICKR_INCLUDES_DIR . $filename . '.php';
	}

	/**
	 * Define constants used by the plugin.
	 *
	 * @since 0.1.0
	 * @access public
	 * @return void
	 */
	public function define_constants() {
		define( 'MEXP_FLICKR_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		define( 'MEXP_FLICKR_INCLUDES_DIR', MEXP_FLICKR_DIR . trailingslashit( 'includes' ) );

		define( 'MEXP_FLICKR_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	}

	/**
	 * Loads the translation files.
	 *
	 * @since 0.1.0
	 * @access public
	 * @return void
	 */
	public function i18n() {
		load_plugin_textdomain( 'mexp-flickr', false, 'mexp-flickr/languages' );
	}

	/**
	 * Loads Flickr service.
	 *
	 * @since 0.1.0
	 * @access public
	 * @filter mexp_services
	 * @param @param array $services Associative array of Media Explorer services to load; key is a string, value is a MEXP_Template object.
	 * @return array $services Associative array of Media Explorer services to load; key is a string, value is a MEXP_Template object.
	 */
	public function load_flickr_service( array $services ) {
		$services[ MEXP_Flickr_Service::NAME ] = new MEXP_Flickr_Service;
		return $services;
	}
}

add_action( 'plugins_loaded', function() {
	$GLOBALS['mexp_flickr'] = new MEXP_Flickr();
} );
