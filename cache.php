<?php
/**
 * @package   WordPress Dynamic CSS
 * @version   1.0.5
 * @author    Askupa Software <contact@askupasoftware.com>
 * @link      https://github.com/askupasoftware/wp-dynamic-css
 * @copyright 2016 Askupa Software
 */

/**
 * Dynamic CSS Cache Handler
 */
class DynamicCSSCache
{
    /**
     * @var DynamicCSSCache The reference to *Singleton* instance of this class
     */
    private static $instance;
    
    /**
     * @var array The reference to the cached compiled CSS that is stored in the database 
     */
    private static $cache;
    
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return DynamicCSSCache The *Singleton* instance.
     */
    public static function get_instance()
    {
        if (null === static::$instance) 
        {
            static::$instance = new static();
            self::load_cache();
        }
        return static::$instance;
    }
    
    /**
     * Returns the compiled CSS for the given handle if it exists in the cache.
     * Returns false otherwise.
     * 
     * @param string $handle The handle of the stylesheet
     * @return boolean|string
     */
    public function get( $handle )
    {
        if( array_key_exists( $handle, self::$cache ) )
        {
            return self::$cache[$handle];
        }
        return false;
    }
    
    /**
     * Update the compiled CSS for the given handle.
     * 
     * @param string $handle The handle of the stylesheet
     * @param string $compiled_css
     */
    public function update( $handle, $compiled_css )
    {
        self::$cache[$handle] = $compiled_css;
        $this->update_option();
    }
    
    /**
     * Clear the compiled CSS from cache for the given handle.
     * 
     * @param string $handle The handle of the stylesheet
     */
    public function clear( $handle )
    {
        unset(self::$cache[$handle]);
        $this->update_option();
    }
    
    /**
     * Load the cache value from the database or create an empty array if the
     * cache is empty.
     */
    private static function load_cache()
    {
        self::$cache = get_option('wp-dynamic-css-cache');
        
        if( false === self::$cache )
        {
            self::$cache = array();
        }
    }
    
    /**
     * Update the database option with the local cache value.
     */
    private function update_option()
    {
        update_option('wp-dynamic-css-cache', self::$cache);
    }
}