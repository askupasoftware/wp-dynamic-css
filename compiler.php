<?php
/**
 * @package   WordPress Dynamic CSS
 * @version   1.0.2
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
        $compiled_css = '';
        $styles = array_filter($this->stylesheets, array( $this, 'filter_print' ) );
        
        // Bail if there are no styles to be printed
        if( count( $styles ) === 0 ) return;
        
        foreach( $styles as $style ) 
        {
            $css = $this->get_file_contents( $style['path'] );
            $compiled_css .= $this->compile_css( $css, $this->callbacks[$style['handle']] )."\n";
        }
        
        echo "<style id=\"wp-dynamic-css\">\n";
        include 'style.phtml';
        echo "</style>";
    }
    
    /**
     * Parse all styles in $this->stylesheets and print them if the flag 'print'
     * is not set to true. Used for loading styles externally via an http request.
     */
    public function compile_external_styles()
    {
        header( "Content-type: text/css; charset: UTF-8" );
        $compiled_css = '';
        $styles = array_filter($this->stylesheets, array( $this, 'filter_external' ) );
        
        foreach( $styles as $style ) 
        {
            $css = $this->get_file_contents( $style['path'] );
            $compiled_css .= $this->compile_css( $css, $this->callbacks[$style['handle']] )."\n";
        }
        
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
     */
    public function enqueue_style( $handle, $path, $print )
    {
        $this->stylesheets[] = array(
            'handle'=> $handle,
            'path'  => $path,
            'print' => $print
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
     * Get the contents of a given file
     * 
     * @param string $path The absolute path to the file
     * @return string The file contents
     */
    protected function get_file_contents( $path )
    {
        ob_start();
        include $path;
        return ob_get_clean();
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
        return preg_replace_callback( '#\$([\w]+)#', function( $matches ) use ( $callback ) {
            return call_user_func_array( $callback, array($matches[1]) );
        }, $css);
    }
}
