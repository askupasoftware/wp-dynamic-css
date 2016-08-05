# WordPress Dynamic CSS [![License](https://scrutinizer-ci.com/g/askupasoftware/wp-dynamic-css/badges/build.png?b=master)](https://scrutinizer-ci.com/g/askupasoftware/wp-dynamic-css/build-status/master) [![License](https://scrutinizer-ci.com/g/askupasoftware/wp-dynamic-css/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/askupasoftware/wp-dynamic-css/build-status/master) [![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://raw.githubusercontent.com/askupasoftware/wp-dynamic-css/master/LICENSE)
A library for generating static stylesheets from dynamic content, to be used in WordPress themes and plugins.


**Contributors:** ykadosh  
**Tags:** theme, mods, wordpress, dynamic, css, stylesheet  
**Tested up to:** 4.5.3  
**Stable tag:** 1.0.5  
**Requires:** PHP 5.3.0 or newer  
**WordPress plugin:** [wordpress.org/plugins/wp-dynamic-css/](https://wordpress.org/plugins/wp-dynamic-css/)  
**License:** GPLv3 or later  
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html  

--

**WordPress Dynamic CSS** allows you to convert this:
```css
body {
   background-color: $body_bg_color;
}
```
into this:
```css
body {
   background-color: #fff;
}
```
Using dynamic user data.

## Contents

* [Overview](#overview)
    * [Basic Example](#basic-example)
* [Installation](#installation)
    * [Via Composer](#via-composer)
    * [Via WordPress.org](#via-wordpressorg)
    * [Manually](#manually)
* [Dynamic CSS Syntax](#dynamic-css-syntax)
* [Enqueueing Dynamic Stylesheets](#enqueueing-dynamic-stylesheets)
    * [Loading the Compiled CSS as an External Stylesheet](#loading-the-compiled-css-as-an-external-stylesheet)
* [Setting the Value Callback](#setting-the-value-callback)
* [API Reference](#api-reference)
    * [wp_dynamic_css_enqueue](#wp_dynamic_css_enqueue)
    * [wp_dynamic_css_set_callback](#wp_dynamic_css_set_callback)
* [Changelog](#changelog)

## Overview

**WordPress Dynamic CSS** is a lightweight library for generating CSS stylesheets from dynamic content (i.e. content that can be modified by the user). The most obvious use case for this library is for creating stylesheets based on Customizer options. Using the special dynamic CSS syntax you can write CSS rules with variables that will be replaced by static values using a custom callback function that you provide.  
**As of version 1.0.2** this library supports multiple callback functions, thus making it safe to use by multiple plugins/themes at the same time.

### Basic Example

First, add this to your `functions.php` file:

```php
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
```

Then, create a file called `my-style.css` and write your (dynamic) CSS in it:

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

Simple, right?

## Installation

### Via Composer

If you are using the command line:
```$ composer require askupa-software/wp-dynamic-css:dev-master```

Or simply add the following to your `composer.json` file:
```javascript
"require": {
     "askupa-software/wp-dynamic-css": "dev-master"
 }
```
And run the command `composer install`

This will install the package in the directory `wp-content/plugins`. For custom install path, add this to your `composer.json` file:

```javascript
"extra": {
     "installer-paths": {
         "vendor/askupa-software/{$name}": ["askupa-software/wp-dynamic-css"]
     }
 }
```

### Via WordPress.org

Install and activate the plugin hosted on WordPress.org: [wordpress.org/plugins/wp-dynamic-css/](https://wordpress.org/plugins/wp-dynamic-css/)

When the plugin is activated, all the library files are automatically included so you don't need to manually include them.

### Manually

[Download the package](https://github.com/askupasoftware/wp-dynamic-css/archive/master.zip) from github and include `bootstrap.php` in your project:

```php
require_once 'path/to/wp-dynamic-css/bootstrap.php';
```

## Dynamic CSS Syntax

This library allows you to use special CSS syntax that is similar to regular CSS syntax with added support for variables with the syntax `$my_variable_name`. Since these variables are replaced by values during run time (when the page loads), files that are using this syntax are therefore called **dynamic CSS** files. Any variable in the dynamic CSS file is replaced by a value that is retrieved by a custom 'value callback' function. 

A **dynamic CSS** file will look exactly like a regular CSS file, only with variables. For example:

```css
body {
   background-color: $body_bg_color;
}
```

During run time, this file will be compiled into regular CSS by replacing all the variables to their corresponding values by calling the 'value callback' function and passing the variable name (without the $ sign) to that function.

**Array variables (since 1.0.3)**  

Version 1.0.3 added support for array subscripts, using a syntax similar to that of PHP. For example:
```css
body {
   font-family: $font['font-family'];
}
```
The callback function should accept a second variable that will hold an array of subscript names. A more in-depth explanation can be found in the [Setting the Value Callback](#setting-the-value-callback) section.

Future releases may support a more compex syntax, so any suggestions are welcome. You can make a suggestion by creating an issue or submitting a pull request.

**Piped filters (since 1.0.5)** 

Version 1.0.5 added support for piped filters. Filters can be registered using the function `wp_dynamic_css_register_filter( $handle, $filter_name, $callback )` where `$filter_name` corresponds to the name fo the filter to be used in the stylesheet. For example, if a filter named `myFilter` was registered, it can be applied using the following syntax:

```css
body {
   font-family: $myVar|myFilter;
}
```

Filters can also be stacked together:

```css
body {
   font-family: $myVar|myFilter1|myFilter2;
}
```

And can even take additional parameters:

```css
body {
   font-family: $myVar|myFilter1('1',2,3.4);
}
```

See [Registering Filters](#registering-filters) to learn how to register filter callback functions.

## Enqueueing Dynamic Stylesheets

To enqueue a dynamic CSS file, call the function `wp_dynamic_css_enqueue` and pass it a handle and the **absolute path** to your CSS file:

```php
wp_dynamic_css_enqueue( 'my_dynamic_style', 'path/to/dynamic-style.css' );
```

This will print the contents of `dynamic-style.css` into the document `<head>` section after replacing all the variables to their values using the given callback function set by `wp_dynamic_css_set_callback()`.

The first argument - the stylesheet handle - is used as an id for associating a callback function to it.

If multiple calls to `wp_dynamic_css_enqueue()` are made with different CSS files, then their contents will be appended to the same `<style>` section in the document `<head>`.

### Loading the Compiled CSS as an External Stylesheet

Instead of printing the compiled CSS to the head of the document, you can alternatively load it as an external stylesheet by setting the second parameter to `false`:

```php
wp_dynamic_css_enqueue( 'my_dynamic_style', 'path/to/dynamic-style.css', false );
```

This will reduce the loading time of your document since the call to the compiler will be made asynchronously as an http request. Additionally, styelsheets that are loaded externally can be cached by the browser, as opposed to stylesheets that are printed to the head.

The disadvantage of this approach is that the Customizer's live preview will not show the changes take effect without manually reloading the page.

## Setting the Value Callback

The example given in the overview section uses the `get_theme_mod()` function to retrieve the value of the variables:

```php
function my_dynamic_css_callback( $var_name )
{
   return get_theme_mod($var_name);
}
wp_dynamic_css_set_callback( 'my_dynamic_style', 'my_dynamic_css_callback' );
```

However, the `get_theme_mod()` function also takes a second argument, which is the default value to be used when the modification name does not exists (e.g. no changes were made in Customizer since the theme was activated).

In that case, we can tweak our callback function to return default values as well:

```php
$theme_mod_defaults = array(
   'body_bg_color' => '#fff',
   'body_text_color' => 'black'
);

function my_dynamic_css_callback( $var_name )
{
   return get_theme_mod($var_name, @$theme_mod_defaults[$var_name]);
}
wp_dynamic_css_set_callback( 'my_dynamic_style', 'my_dynamic_css_callback' );
```

Your CSS file can look something like this:

```css
body {
   background-color: $body_bg_color;
   color: $body_text_color;
}
```

Which will be compiled to this (provided that no changes were made by the user in Customizer):

```css
body {
   background-color: #fff;
   color: black;
}
```

**Array variables**  

It is also possible to access array values using subscripts. An example dynamic CSS file may look like:

```css
body {
   background-color: $body_bg_color;
   color: $body_text_color;
   font: $font['font-size']px '$font['font-family']';
}
```

However, in this case the callback function is passed 2 parameters: one holding the variable name, and a second holding an array of subscript names. The second variable is always going to be an array since there may be more than one subscript (multidimensional arrays). To retrieve to array value, the subscripts array is to be looped through to get each subscript. For example:

```php
$theme_mod_defaults = array(
   'body_bg_color' => '#fff',
   'body_text_color' => 'black'
   'font' => array(
      'font-familiy' => 'Arial',
      'font-size' => 14
   )
);

function my_dynamic_css_callback( $var_name, $subscripts = null )
{
   $val = get_theme_mod($var_name, @$theme_mod_defaults[$var_name]);
   if( null !== $subscripts )
   {
      foreach( $subscripts as $subscript )
      {
          $val = $val[$subscript];
      }
   }
   return $val;
}
wp_dynamic_css_set_callback( 'my_dynamic_style', 'my_dynamic_css_callback' );
```

which compiles to:

```css
body {
   background-color: #fff;
   color: black;
   font: 14px 'Arial';
}
```

## Registering Filters

Filters are functions the alter the value of the variables. Filters can be registered using the function `wp_dynamic_css_register_filter( $handle, $filter_name, $callback )`. A registered filter can only be used within the stylesheet whose handle is given. A filter callback function takes the value of the variable as a parameter and should return the filtered value. For example: 

```php
function my_filter_callback( $value ) 
{
   return trim( $value );
}
wp_dynamic_css_register_filter( 'my_dynamic_style', 'myFilter', 'my_filter_callback' );
```

The filter can then be applied using the `|` operator. For example:

```css
body {
   font-family: $myVar|myFilter;
}
```

Filters can also take additional arguments. For example: 

```php
function my_filter_callback( $value, $arg1, $arg2 ) 
{
   return $value.$arg1.$arg2;
}
wp_dynamic_css_register_filter( 'my_dynamic_style', 'myFilter', 'my_filter_callback' );
```

To pass addidional arguments, use braces `()` and separate each argument with a comma (no spaces are allowd). For example:

```css
body {
   font-family: $myVar|myFilter(',arial',',sans-serif');
}
```

Accepted argument types are strings, integers, floats and boolean values. Strings must have single quotes around them. For exmaple:

```css
body {
   font-family: $myVar|myFilter(1,2,3,'4',5.6,true,false);
}
```

## API Reference

### wp_dynamic_css_enqueue

*Enqueue a dynamic stylesheet*

```php 
function wp_dynamic_css_enqueue( $handle, $path, $print = true, $minify = false, $cache = false )
```

This function will either print the compiled version of the stylesheet to the document's <head> section, or load it as an external stylesheet if `$print` is set to false. If `$cache` is set to true, a compiled version of the stylesheet will be stored in the database as soon as it is first compiled. The compiled version will be served thereafter until `wp_dynamic_css_clear_cache()` is called.

**Parameters**
* `$handle` (*string*) The stylesheet's name/id
* `$path` (*string*) The absolute path to the dynamic CSS file
* `$print` (*boolean*) Whether to print the compiled CSS to the document head, or load it as an external CSS file via an http request
* `$minify` (*boolean*) Whether to minify the CSS output
* `$cache` (*boolean*) Whether to store the compiled version of this stylesheet in cache to avoid compilation on every page load.

### wp_dynamic_css_set_callback

*Set the value retrieval callback function*

```php 
function wp_dynamic_css_set_callback( $handle, $callback )
```

Set a callback function that will be used to convert variables to actual values. The registered function will be used when the dynamic CSS file that is associated with the name in `$handle` is compiled. The callback function is passed the name of the variable as its first parameter (without the `$`), and the variable subscripts as its second parameter (if applicable). The callback function should return the variable's actual value.

**Parameters**
* `$handle` (*string*) The name of the stylesheet to be associated with this callback function.
* `$callback` (*callable*) The value retrieval callback function.

### wp_dynamic_css_clear_cache

*Clear the cached compiled CSS for the given handle.*

```php 
function wp_dynamic_css_clear_cache( $handle )
```

Registered dynamic stylesheets that have the `$cache` flag set to true are compiled only once and then stored in cache. Subsequesnt requests are served statically from cache until `wp_dynamic_css_clear_cache()` is called and clears it, forcing the compiler to recompile the CSS.

**Parameters**
* `$handle` (*string*) The name of the stylesheet to be cleared from cache.

### wp_dynamic_css_register_filter

*Register a filter function for a given stylesheet handle.*

```php 
function wp_dynamic_css_register_filter( $handle, $filter_name, $callback )
```

A registered filter can be used to alter the value of a variable by using the `|` operator in the stylesheet. The callback function is passed the variable's value as its first argument, and any additional arguments thereafter.

**Parameters**
* `$handle` (*string*) The handle of the stylesheet in which this filter is to be used.
* `$filter_name` (*string*) The name of the filter to be used in the dynamic CSS file.
* `$callback` (*callable*) The actual filter function. Accepts the variable's value as the first argument. Should return the filtered value.
