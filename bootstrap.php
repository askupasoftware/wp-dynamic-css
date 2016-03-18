<?php
/**
 * WordPress Dynamic CSS
 *
 * Render CSS using dynamic data from WordPress
 *
 * @package   wp-dynamic-css
 * @author    Askupa Software <contact@askupasoftware.com>
 * @link      https://github.com/askupasoftware/wp-dynamic-css
 * @copyright 2016 Askupa Software
 *
 * @wordpress-plugin
 * Plugin Name:     WordPress Dynamic CSS
 * Plugin URI:      https://github.com/askupasoftware/wp-dynamic-css
 * Description:     Render CSS using dynamic data from WordPress
 * Version:         1.0.0
 * Author:          Askupa Software
 * Author URI:      http://www.askupasoftware.com
 * Text Domain:     wp-dynamic-css
 * Domain Path:     /languages
 */

require_once 'renderer.php';

if( !function_exists('wp_dynamic_css_enqueue') )
{
    function wp_dynamic_css_enqueue( $path )
    {
        $dcss = DynamicCSSRenderer::get_instance();
        $dcss->enqueue_style( $path );
    }
}

if( !function_exists('wp_dynamic_css_set_callback') )
{
    function wp_dynamic_css_set_callback( $callback )
    {
        add_filter( 'wp_dynamic_css_get_variable_value', $callback );
    }
}