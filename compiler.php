<?php
/**
 * @package   WordPress Dynamic CSS
 * @version   1.0.5
 * @author    Askupa Software <contact@askupasoftware.com>
 * @link      https://github.com/askupasoftware/wp-dynamic-css
 * @copyright 2016 Askupa Software
 */

/**
 * Dynamic CSS Compiler Utility Class
 * 
 * 
 * Dynamic CSS Syntax
 * ------------------
 * <pre>
 * body {color: $body_color;} 
 * </pre>
 * In the above example, the variable $body_color is replaced by a value 
 * retrieved by the value callback function. The function is passed the variable 
 * name without the dollar sign, which can be used with get_option() or 
 * get_theme_mod() etc.
 */
class DynamicCSSCompiler
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;
    
    /**
     * @var array The list of dynamic styles paths to compile
     */
    private $stylesheets = array();
    
    /**
     * @var array 
     */
    private $callbacks = array();
    
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function get_instance()
    {
        if (null === static::$instance) 
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    /**
     * Enqueue the PHP script used for compiling dynamic stylesheets that are 
     * loaded externally
     */
    public function wp_enqueue_style()
    {
        // Only enqueue if there is at least one dynamic stylesheet that is
        // set to be loaded externally
        if( 0 < count( array_filter($this->stylesheets, array( $this, 'filter_external' ) ) ) )
        {
            wp_enqueue_style( 'wp-dynamic-css', admin_url( 'admin-ajax.php?action=wp_dynamic_css' ) );
        }
    }
    
    /**
     * Parse all styles in $this->stylesheets and print them if the flag 'print'
     * is set to true. Used for printing styles to the document head.
     */
    public function compile_printed_styles()
    {        
        // Compile only if there are styles to be printed
        if( 0 < count( array_filter($this->stylesheets, array( $this, 'filter_print' ) ) ) )
        {
            $compiled_css = $this->get_compiled_styles( true );
        
            echo "<style id=\"wp-dynamic-css\">\n";
            include 'style.phtml';
            echo "</style>";
        }
    }
    
    /**
     * Parse all styles in $this->stylesheets and print them if the flag 'print'
     * is not set to true. Used for loading styles externally via an http request.
     */
    public function compile_external_styles()
    {
        header( "Content-type: text/css; charset: UTF-8" );
        
        $compiled_css = $this->get_compiled_styles( false );
        
        include 'style.phtml';
        wp_die();
    }
    
    /**
     * Add a style path to the pool of styles to be compiled
     * 
     * @param string $handle The stylesheet's name/id
     * @param string $path The absolute path to the dynamic style
     * @param boolean $print Whether to print the compiled CSS to the document
     * head, or include it as an external CSS file
     * @param boolean $minify Whether to minify the CSS output
     * @param boolean $cache Whether to store the compiled version of this 
     * stylesheet in cache to avoid compilation on every page load.
     */
    public function enqueue_style( $handle, $path, $print, $minify, $cache )
    {
        $this->stylesheets[] = array(
            'handle'=> $handle,
            'path'  => $path,
            'print' => $print,
            'minify'=> $minify,
            'cache' => $cache
        );
    }
    
    /**
     * Register a value retrieval function and associate it with the given handle
     * 
     * @param type $handle The stylesheet's name/id
     * @param type $callback
     */
    public function register_callback( $handle, $callback )
    {
        $this->callbacks[$handle] = $callback;
    }

    /**
     * Compile multiple dynamic stylesheets
     * 
     * @param boolean $printed
     * @return string Compiled CSS
     */
    protected function get_compiled_styles( $printed )
    {
        $compiled_css = '';
        foreach( $this->stylesheets as $style ) 
        {
            if( !array_key_exists( $style['handle'], $this->callbacks ) )
            {
                trigger_error( 'There is no callback function associated with the handle "'.$style['handle'].'". Use <b>wp_dynamic_css_set_callback()</b> to register a callback function for this handle.' );
                continue;
            }
            
            if( $style['print'] === $printed )
            {
                $compiled_css .= $this->get_compiled_style( $style )."\n";
            }
        }
        return $compiled_css;
    }
    
    /**
     * Get the compiled CSS for the given style. Skips compilation if the compiled
     * version can be found in cache.
     * 
     * @param array $style List of styles with the same structure as they are 
     * stored in $this->stylesheets
     * @return type
     */
    protected function get_compiled_style( $style )
    {
        $cache = DynamicCSSCache::get_instance();
        
        if( $style['cache'] )
        {
            $cached_css = $cache->get( $style['handle'] );
            if( false !== $cached_css )
            {
                return $cached_css;
            }
        }

        $css = file_get_contents( $style['path'] );
        if( $style['minify'] ) $css = $this->minify_css( $css );
        $compiled_css = $this->compile_css( $css, $this->callbacks[$style['handle']] );
        $cache->update( $style['handle'], $compiled_css );
        return $compiled_css;
    }
    
    /**
     * Minify a given CSS string by removing comments, whitespaces and newlines
     * 
     * @see http://stackoverflow.com/a/6630103/1096470
     * @param string $css CSS style to minify
     * @return string Minified CSS
     */
    protected function minify_css( $css )
    {
        return preg_replace( '@({)\s+|(\;)\s+|/\*.+?\*\/|\R@is', '$1$2 ', $css );
    }

    /**
     * This filter is used to return only the styles that are set to be printed
     * in the document head
     * 
     * @param array $style
     * @return boolean
     */
    protected function filter_print( $style )
    {
        return true === $style['print'];
    }
    
    /**
     * This filter is used to return only the styles that are set to be loaded
     * externally
     * 
     * @param array $style
     * @return boolean
     */
    protected function filter_external( $style )
    {
        return true !== $style['print'];
    }
    
    /**
     * Parse the given CSS string by converting the variables to their 
     * corresponding values retrieved by applying the callback function
     * 
     * @param callable $callback A function that replaces the variables with 
     * their values. The function accepts the variable's name as a parameter
     * @param string $css A string containing dynamic CSS (pre-compiled CSS with 
     * variables)
     * @return string The compiled CSS after converting the variables to their 
     * corresponding values
     */
    protected function compile_css( $css, $callback )
    {
        return preg_replace_callback( "#\\$([\\w-]+)((?:\\['?[\\w-]+'?\\])*)#", function( $matches ) use ( $callback ) {
            // If this variable is an array, get the subscripts
            if( '' !== $matches[2] )
            {
                preg_match_all('/[\w-]+/i', $matches[2], $subscripts);
            }
            return call_user_func_array( $callback, array($matches[1],@$subscripts[0]) );
        }, $css);
    }
}
