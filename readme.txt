=== WordPress Dynamic CSS ===
Contributors: Askupa Software
Tags: dynamic css, css, customizer, get_theme_mod, css variables
Requires at least: 3.0
Tested up to: 4.4.2
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Dynamic CSS compiler for WordPress

== Description ==

**WordPress Dynamic CSS** is a lightweight library for generating CSS stylesheets from dynamic content (i.e. content that can be modified by the user). 
The most obvious use case for this library is for creating stylesheets based on Customizer options. 
Using the special dynamic CSS syntax you can write CSS rules with variables that will be replaced by static values using a custom callback function that you provide.

### Basic Example

First, add this to your `functions.php` file:

```php
// 1. Load the library
require_once 'wp-dynamic-css/bootstrap.php';

// 2. Set the callback function (used to convert variables to actual values)
function my_dynamic_css_callback( $var_name )
{
   return get_theme_mod($var_name);
}
wp_dynamic_css_set_callback( 'my_dynamic_css_callback' );

// 3. Enqueue the stylesheet (using an absolute path, not URL)
wp_dynamic_css_enqueue( 'path/to/my-style.css' );

// 4. Nope, only three steps
```

Then, create a file called `my-style.css` and write this in it:

```css
body {
   background-color: $body_bg_color;
}
```

In the above example, the stylesheet will be automatically compiled and printed to the `<head>` of the document. The value of `$body_bg_color` will be replaced by the value of `get_theme_mod('body_bg_color')`.

Now, let's say that `get_theme_mod('body_bg_color')` returns the value `#fff`, then `my-style.css` will be compiled to:

```css
body {
   background-color: #fff;
}
```

You can find detailed documentation on how to use Kirki on the [GitHub page](https://github.com/askupasoftware/wp-dynamic-css)

**Useful Links**

* [Official page on GitHub](https://github.com/askupasoftware/wp-dynamic-css)
* [Report an issue](https://github.com/askupasoftware/wp-dynamic-css/issues)
* [Submit a pull request](https://github.com/askupasoftware/wp-dynamic-css/pulls)

== Installation ==

Follow the instructions on the plugin's [GitHub page](https://github.com/askupasoftware/wp-dynamic-css) for detailed explanation and examples

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

