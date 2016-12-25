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
     * @var DynamicCSSCompiler The reference to *Singleton* instance of this class
     */
    private static $instance;
    
    /**
     * @var array The list of dynamic styles paths to compile
     */
    private $stylesheets = array();
    
    /**
     * @var array The list of registered callbacks
     */
    private $callbacks = array();
    
    /**
     * @var aray The list of registered filters
     */
    private $filters = array();
    
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return DynamicCSSCompiler The *Singleton* instance.
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
     * Enqueue all registered stylesheets.
     */
    public function enqueue_styles()
    {
        foreach( $this->stylesheets as $stylesheet )
        {
            if( $this->callback_exists( $stylesheet['handle'] ) )
            {
                $this->enqueue_style( $stylesheet );
            }
        }
    }
    
    /**
     * Enqueue a single registered stylesheet.
     * 
     * @param array $stylesheet
     */
    public function enqueue_style( $stylesheet )
    {
        $handle = 'wp-dynamic-css-'.$stylesheet['handle'];
        $print  = $stylesheet['print'];
        
        wp_register_style( 
            $handle,
            // Don't pass a URL if this style is to be printed
            $print ? false : $this->get_ajax_callback_url( $stylesheet['handle'] )
        );

        wp_enqueue_style( $handle );
        
        // Add inline styles for styles that are set to be printed
        if( $print )
        {
            // Inline styles only work if the handle has already been registered and enqueued
            wp_add_inline_style( $handle, $this->get_compiled_style( $stylesheet ) );
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
                echo $this->get_compiled_style( $stylesheet );
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
    public function register_style( $handle, $path, $print, $minify, $cache )
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
     * @param string $handle The stylesheet's name/id
     * @param callable $callback
     */
    public function register_callback( $handle, $callback )
    {
        $this->callbacks[$handle] = $callback;
    }
    
    /**
     * Register a filter function for a given stylesheet handle.
     */
    public function register_filter( $handle, $filter_name, $callback )
    {
        if( !array_key_exists( $handle, $this->filters ) )
        {
            $this->filters[$handle] = array();
        }
        $this->filters[$handle][$filter_name] = $callback;
    }
    
    /**
     * Get the compiled CSS for the given style. Skips compilation if the compiled
     * version can be found in cache.
     * 
     * @param array $style List of styles with the same structure as they are 
     * stored in $this->stylesheets
     * @return string The compiled CSS for this stylesheet
     */
    protected function get_compiled_style( $style )
    {
        $cache = DynamicCSSCache::get_instance();
        
        // Use cached compiled CSS if applicable
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
        
        // Compile the dynamic CSS
        $compiled_css = $this->compile_css( 
            $css, 
            $this->callbacks[$style['handle']], 
            (array) @$this->filters[$style['handle']]
        );
        
        $cache->update( $style['handle'], $compiled_css );
        return $this->add_meta_info( $compiled_css );
    }
    
    /**
     * Add meta information to the compiled CSS
     * 
     * @param string $compiled_css The compiled CSS
     * @return string The compiled CSS with the meta information added to it
     */
    protected function add_meta_info( $compiled_css )
    {
        return "/**\n".
               " * Compiled using wp-dynamic-css\n".
               " * https://github.com/askupasoftware/wp-dynamic-css\n".
               " */\n\n".
               $compiled_css;
    }
    
    /**
     * Get the callback URL for enqueuing the stylesheet extrnally
     * 
     * @param string $handle The stylesheet's handle
     * @return string The URL for the given handle
     */
    protected function get_ajax_callback_url( $handle )
    {
        return esc_url_raw( 
            add_query_arg(array(
                'action' => 'wp_dynamic_css',
                'handle' => $handle
            ), 
            admin_url( 'admin-ajax.php'))
        );
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
    protected function compile_css( $css, $callback, $filters )
    {
        return preg_replace_callback( 
                
            "#".                        // Begin
            "\\$".                      // Must start with $
            "([\\w-]+)".                // Match alphanumeric characters and dashes
            "((?:\\['?[\\w-]+'?\\])*)". // Optionally match array subscripts i.e. $myVar['index']
            "((?:".                     // Optionally match pipe filters i.e. $myVar|myFilter
                "\\|[\\w-]+".           // Starting with the | character
                "(\([\w\.,']+\))?".     // Filters can have strings and numbers i.e myFilter('string',1,2.5)
            ")*)".                      // Allow for 0 or more piped filters
            "#",                        // End
            
            function( $matches ) use ( $callback, $filters ) 
            {
                $subscripts = array();
                
                // If this variable is an array, get the subscripts
                if( '' !== $matches[2] )
                {
                    preg_match_all('/[\w-]+/i', $matches[2], $subscripts);
                }
                
                $val = call_user_func_array( $callback, array( $matches[1],@$subscripts[0] ) );
                
                // If there are filters, apply them
                if( '' !== $matches[3] )
                {
                    $val = $this->apply_filters( substr( $matches[3], 1 ), $val, $filters );
                }
                
                return $val;
            }, $css
        );
    }
    
    /**
     * Apply the filters specified in $filters_string to the given $value.
     * 
     * @param string $filters_string 
     * @param string $value
     * @param array $filters Array of callback functions
     * @return string The value after all filters have been applied
     */
    protected function apply_filters( $filters_string, $value, $filters = array() )
    {
        foreach( explode( '|', $filters_string) as $filter )
        {
            $args = array( $value );
            
            if( false !== strrpos( $filters_string, "(" ) )
            {
                $pieces = explode( '(', $filter );
                $filter = $pieces[0];
                $params = explode( ',', str_replace( ')', '', $pieces[1] ) );
                array_walk( $params, array( $this, 'strtoval' ) ); // Convert string values to actual values
                $args = array_merge( $args, $params );
            }
            
            if( key_exists( $filter, $filters ) )
            {
                $value = call_user_func_array( $filters[$filter], $args );
            }
        }
        return $value;
    }
    
    /**
     * Convert the given string to its actual value.
     * 
     * @param string $str The string to be converted (passed by reference)
     */
    protected function strtoval( &$str )
    {
        if( 'false' === strtolower($str) ) $str = false;
        if( 'true' === strtolower($str) ) $str = true;
        if( false !== strrpos( $str, "'" ) ) $str = str_replace ( "'", "", $str );
        if( is_numeric( $str ) ) $str = floatval( $str );
    }
}
