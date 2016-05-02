# WordPress Dynamic CSS
[![License](https://scrutinizer-ci.com/g/askupasoftware/wp-dynamic-css/badges/build.png?b=master)](https://scrutinizer-ci.com/g/askupasoftware/wp-dynamic-css/build-status/master)
[![License](https://scrutinizer-ci.com/g/askupasoftware/wp-dynamic-css/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/askupasoftware/wp-dynamic-css/build-status/master)
[![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://raw.githubusercontent.com/askupasoftware/wp-dynamic-css/master/LICENSE)

**Contributors:** ykadosh  
**Tags:** theme, mods, wordpress, dynamic, css, stylesheet  
**Tested up to:** 4.5.1  
**Stable tag:** 1.0.4  
**Requires:** PHP 5.3.0 or newer  
**WordPress plugin:** [wordpress.org/plugins/wp-dynamic-css/](https://wordpress.org/plugins/wp-dynamic-css/)  
**License:** GPLv3 or later  
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html

A library for generating static stylesheets from dynamic content, to be used in WordPress themes and plugins.

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

## API Reference

### wp_dynamic_css_enqueue

*Enqueue a dynamic stylesheet*

```php 
function wp_dynamic_css_enqueue( $handle, $path, $print = true, $minify = false )
```

This function will either print the compiled version of the stylesheet to the document's <head> section, or load it as an external stylesheet if `$print` is set to false.

**Parameters**
* `$handle` (*string*) The stylesheet's name/id
* `$path` (*string*) The absolute path to the dynamic CSS file
* `$print` (*boolean*) Whether to print the compiled CSS to the document head, or load it as an external CSS file via an http request
* `$minify` (*boolean*) Whether to minify the CSS output

### wp_dynamic_css_set_callback

*Set the value retrieval callback function*

```php 
function wp_dynamic_css_set_callback( $handle, $callback )
```

Set a callback function that will be used to convert variables to actual values. The registered function will be used when the dynamic CSS file that is associated with the name in `$handle` is compiled. The callback function accepts 1 parameter which is the name of the variable, without the $ sign.

**Parameters**
* `$handle` (*string*) The name of the stylesheet to be associated with this callback function.
* `$callback` (*callable*) A callback (or "callable" as of PHP 5.4) can either be a reference to a function name or method within a class/object.

## TODO

* ~~Add support for loading the compiled CSS externally instead of printing to the document head~~ (Added in 1.0.1)
* ~~Add support for multiple value callback functions~~ (Added in 1.0.2)
* ~~Add option for minification~~ (Added in 1.0.4)
* Add support for caching and improve performance

## Changelog

**1.0.4**
* Set cache-control to no-cache so that changes to options are reflected immediately
* Added support for CSS minification

**1.0.3**
* Added support for variable subscripts

**1.0.2**
* Added support for multiple value callback functions

**1.0.1**
* Added support for loading the compiled CSS externally instead of printing to the document head

**1.0.0**
* Initial release
