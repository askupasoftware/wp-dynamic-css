<?php
/**
 * @package   WordPress Dynamic CSS
 * @version   1.0.5
 * @author    Askupa Software <contact@askupasoftware.com>
 * @link      https://github.com/askupasoftware/wp-dynamic-css
 * @copyright 2016 Askupa Software
 */

if( !function_exists('wp_dynamic_css_enqueue') )
{
    /**
     * Enqueue a dynamic stylesheet
     * 
     * This will either print the compiled version of the stylesheet to the 
     * document's <head> section, or load it as an external stylesheet if $print 
     * is set to false
     * 
     * @param string $handle The stylesheet's name/id
     * @param string $path The absolute path to the dynamic CSS file
     * @paran boolean $print Whether to print the compiled CSS to the document 
     * head, or include it as an external CSS file
     * @param boolean $minify Whether to minify the CSS output
     * @param boolean $cache Whether to store the compiled version of this 
     * stylesheet in cache to avoid compilation on every page load.
     */
    function wp_dynamic_css_enqueue( $handle, $path, $print = true, $minify = false, $cache = false )
    {
        $dcss = DynamicCSSCompiler::get_instance();
        $dcss->enqueue_style( $handle, $path, $print, $minify, $cache );
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
     * @param string $handle The name of the stylesheet to be associated with this
     * callback function
     * @param string|array $callback A callback (or "callable" as of PHP 5.4) 
     * can either be a reference to a function name or method within an 
     * class/object.
     */
    function wp_dynamic_css_set_callback( $handle, $callback )
    {
        $dcss = DynamicCSSCompiler::get_instance();
        $dcss->register_callback( $handle, $callback );
    }
}

if( !function_exists('wp_dynamic_css_clear_cache') )
{
    /**
     * Clear the cached compiled CSS for the given handle.
     * 
     * Initially, registered dynamic stylesheets are compiled and stored in cache.
     * Subsequesnt requests are served statically from cache until 
     * wp_dynamic_css_clear_cache() is called and clears it, forcing the compiler
     * to recompile the CSS.
     * 
     * @param string $handle The name of the stylesheet to be cleared from cache
     */
    function wp_dynamic_css_clear_cache( $handle )
    {
        $cache = DynamicCSSCache::get_instance();
        $cache->clear( $handle );
    }
}
