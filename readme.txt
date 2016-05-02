=== WordPress Dynamic CSS ===
Contributors: Askupa Software
Tags: dynamic css, css, customizer, get_theme_mod, css variables, css compiler
Requires at least: 3.0
Tested up to: 4.4.2
Stable tag: 1.0.4
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Dynamic CSS compiler for WordPress themes and plugins

== Description ==

**WordPress Dynamic CSS** is a lightweight library for generating CSS stylesheets from dynamic content (i.e. content that can be modified by the user). 
The most obvious use case for this library is for creating stylesheets based on Customizer options. 
Using the special dynamic CSS syntax you can write CSS rules with variables that will be replaced by static values using a custom callback function that you provide.

**As of version 1.0.2** this plugin supports multiple callback functions, thus making it safe to use by multiple plugins/themes at the same time.

### Basic Example

First, add this to your `functions.php` file:

<pre>
// 1. Load the library (skip this if you are loading the library as a plugin)
require_once 'wp-dynamic-css/bootstrap.php';

// 2. Enqueue the stylesheet (using an absolute path, not a URL)
wp_dynamic_css_enqueue( 'my_dynamic_style', 'path/to/my-style.css' );

// 3. Set the callback function (used to convert variables to actual values)
function my_dynamic_css_callback( $var_name )
{
   return get_theme_mod($var_name);
}
wp_dynamic_css_set_callback( 'my_dynamic_style', 'my_dynamic_css_callback' );

// 4. Nope, only three steps
</pre>

Then, create a file called `my-style.css` and write this in it:

<pre>
body {
   background-color: $body_bg_color;
}
</pre>

In the above example, the stylesheet will be automatically compiled and printed to the <head> of the document. The value of <code>$body_bg_color</code> will be replaced by the value of `get_theme_mod('body_bg_color')`.

Now, let's say that <code>get_theme_mod('body_bg_color')</code> returns the value <code>#fff</code>, then <code>my-style.css</code> will be compiled to:

<pre>
body {
   background-color: #fff;
}
</pre>

You can find detailed documentation on how to use this library on the [GitHub page](https://github.com/askupasoftware/wp-dynamic-css)

**Useful Links**

* [Official page on GitHub](https://github.com/askupasoftware/wp-dynamic-css)
* [Report an issue](https://github.com/askupasoftware/wp-dynamic-css/issues)
* [Submit a pull request](https://github.com/askupasoftware/wp-dynamic-css/pulls)

== Installation ==

Please follow the instructions on the plugin's [GitHub page](https://github.com/askupasoftware/wp-dynamic-css) for detailed explanation and examples.

== Changelog ==

= 1.0.4 =
* (FIX) Set cache-control to no-cache so that changes to options are reflected immediately
* (NEW) Added support for CSS minification

= 1.0.3 =
* (NEW) Added support for variable subscripts

= 1.0.2 =
* (NEW) Added support for multiple callback functions
* (FIX) The library is now safe to use by multiple plugins/themes in the same installation

= 1.0.1 =
* (NEW) Added support for loading compiled CSS as an external stylesheet

= 1.0.0 =
* Initial release

== Upgrade Notice ==

