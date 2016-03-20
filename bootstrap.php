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

require_once 'compiler.php';

if( !function_exists('wp_dynamic_css_enqueue') )
{
    /**
     * Enqueue a dynamic stylesheet
     * 
     * This will print the compiled version of the stylesheet to the document's
     * <head> section.
     * 
     * @param string $path The absolute path to the dynamic CSS file
     */
    function wp_dynamic_css_enqueue( $path )
    {
        $dcss = DynamicCSSCompiler::get_instance();
        $dcss->enqueue_style( $path );
    }
}

if( !function_exists('wp_dynamic_css_set_callback') )
{
    /**
     * Set the value retrieval callback function
     * 
     * Set a callback function that will be used to get the values of the 
     * variables when the dynamic CSS file is compiled. The function accepts 1 
     * parameter which is the name of the variable, without the $ sign
     * 
     * @param string|array $callback A callback (or "callable" as of PHP 5.4) 
     * can either be a reference to a function name or method within an 
     * class/object.
     */
    function wp_dynamic_css_set_callback( $callback )
    {
        add_filter( 'wp_dynamic_css_get_variable_value', $callback );
    }
}