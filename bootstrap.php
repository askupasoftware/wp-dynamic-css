<?php
/**
 * WordPress Dynamic CSS
 *
 * Dynamic CSS compiler for WordPress
 *
 * @package   wp-dynamic-css
 * @author    Askupa Software <contact@askupasoftware.com>
 * @link      https://github.com/askupasoftware/wp-dynamic-css
 * @copyright 2016 Askupa Software
 *
 * @wordpress-plugin
 * Plugin Name:     WordPress Dynamic CSS
 * Plugin URI:      https://github.com/askupasoftware/wp-dynamic-css
 * Description:     Dynamic CSS compiler for WordPress
 * Version:         1.0.1
 * Author:          Askupa Software
 * Author URI:      http://www.askupasoftware.com
 * Text Domain:     wp-dynamic-css
 * Domain Path:     /languages
 */

require_once 'compiler.php';
require_once 'functions.php';

/**
 * The following actions are used for printing or loading the compiled 
 * stylesheets externally.
 */
$dcss = DynamicCSSCompiler::get_instance();
add_action( 'wp_print_styles', array( $dcss, 'compile_printed_styles' ) );
add_action( 'wp_enqueue_scripts', array( $dcss, 'wp_enqueue_style' ) );
add_action( 'wp_ajax_wp_dynamic_css', array( $dcss, 'compile_external_styles' ) );
add_action( 'wp_ajax_nopriv_wp_dynamic_css', array( $dcss, 'compile_external_styles' ) );