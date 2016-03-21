<?php
/**
 * @package   WordPress Dynamic CSS
 * @version   1.0.1
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
        $precompiled_css = '';
        $styles = array_filter($this->stylesheets, array( $this, 'filter_print' ) );
        
        // Bail if there are no styles to be printed
        if( count( $styles ) === 0 ) return;
        
        foreach( $styles as $style ) 
        {
            $precompiled_css .= $this->get_file_contents( $style['path'] )."\n";
        }
        $css = $this->compile_css( $precompiled_css );
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
        $precompiled_css = '';
        $styles = array_filter($this->stylesheets, array( $this, 'filter_external' ) );
        
        foreach( $styles as $style ) 
        {
            $precompiled_css .= $this->get_file_contents( $style['path'] )."\n";
        }
        $css = $this->compile_css( $precompiled_css );
        include 'style.phtml';
        wp_die();
    }
    
    /**
     * Add a style path to the pool of styles to be compiled
     * 
     * @param string $path The absolute path to the dynamic style
     * @param boolean $print Whether to print the compiled CSS to the document 
     * head, or include it as an external CSS file
     */
    public function enqueue_style( $path, $print )
    {
        $this->stylesheets[] = array(
            'path'  => $path,
            'print' => $print
        );
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
     * corresponding values retrieved by applying the filter 
     * wp_dynamic_css_get_variable_value.
     * 
     * @param string $css A string containing dynamic CSS (pre-compiled CSS with 
     * variables)
     * @uses wp_dynamic_css_get_variable_value filter
     * @return string The compiled CSS after converting the variables to their 
     * corresponding values
     */
    protected function compile_css( $css )
    {   
        return preg_replace_callback( '#\$([\w]+)#', function( $matches ) {
            return apply_filters( 'wp_dynamic_css_get_variable_value', $matches[1] );
        }, $css);
    }
}