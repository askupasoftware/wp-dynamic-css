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
     * Enqueue the stylesheets that are registered to be loaded externally
     */
    public function enqueue_styles()
    {
        foreach( $this->stylesheets as $stylesheet )
        {
            if( !$stylesheet['print'] && $this->callback_exists( $stylesheet['handle'] ) )
            {
                wp_enqueue_style( 
                    'wp-dynamic-css-'.$stylesheet['handle'],
                    esc_url_raw( add_query_arg(array(
                        'action' => 'wp_dynamic_css',
                        'handle' => $stylesheet['handle']
                    ), admin_url( 'admin-ajax.php')))
                );
            }
        }
    }
    
    /**
     * Print the stylesheets that are registered to be printed to the document head
     */
    public function print_styles()
    {        
        foreach( $this->stylesheets as $stylesheet )
        {
            if( $stylesheet['print'] && $this->callback_exists( $stylesheet['handle'] ) )
            {
                $compiled_css = $this->get_compiled_style( $stylesheet );
        
                echo "<style id=\"wp-dynamic-css-".$stylesheet['handle']."\">\n";
                include 'style.phtml';
                echo "\n</style>\n";
            }
        }
    }
    
    /**
     * This is the AJAX callback used for loading styles externally via an http 
     * request.
     */
    public function ajax_callback()
    {
        header( "Content-type: text/css; charset: UTF-8" );
        $handle = filter_input( INPUT_GET, 'handle' );
        
        foreach( $this->stylesheets as $stylesheet )
        {
            if( $handle === $stylesheet['handle'] )
            {
                $compiled_css = $this->get_compiled_style( $stylesheet );
                include 'style.phtml';
            }
        }
        
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
     * Check if a callback function has been register for the given handle.
     * 
     * @param string $handle 
     * @return boolean
     */
    protected function callback_exists( $handle )
    {
        if( array_key_exists( $handle, $this->callbacks ) )
        {
            return true;
        }
        trigger_error( 
            "There is no callback function associated with the handle '$handle'. ".
            "Use <b>wp_dynamic_css_set_callback()</b> to register a callback function for this handle." 
        );
        return false;
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
