<?php
/**
 * @package   WordPress Dynamic CSS
 * @version   1.0.2
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
     * document's <head> section, or load it as an external stylesheet if the 
     * second parameter is set to false
     * 
     * @param string $handle The stylesheet's name/id
     * @param string $path The absolute path to the dynamic CSS file
     * @paran boolean $print Whether to print the compiled CSS to the document 
     * head, or include it as an external CSS file
     */
    function wp_dynamic_css_enqueue( $handle, $path, $print = true )
    {
        $dcss = DynamicCSSCompiler::get_instance();
        $dcss->enqueue_style( $handle, $path, $print );
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