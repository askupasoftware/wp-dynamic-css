# wp-dynamic-css

[![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://raw.githubusercontent.com/askupasoftware/wp-dynamic-css/master/LICENSE)

**Contributors:** ykadosh  
**Tags:** theme, mods, wordpress, dynamic, css, stylesheet  
**Tested up to:** 4.4.2  
**Stable tag:** 1.0.0  
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
* [Setting the Value Callback](#setting-the-value-callback)

## Overview

**WordPress Dynamic CSS** is a lightweight library for generating CSS stylesheets from dynamic content (i.e. content that can be modified by the user). The most obvious use case for this library is for creating stylesheets based on Customizer options. Using the special dynamic CSS syntax you can write CSS rules with variables that will be replaced by static values using a custom callback function that you provide.

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

In the above example, the stylesheet will be automatically rendered and printed to the `<head>` of the document. The value of `$body_bg_color` will be replaced by the value of `get_theme_mod('body_bg_color')`.

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

Coming soon

### Manually

[Download the package](https://github.com/askupasoftware/wp-dynamic-css/archive/master.zip) from github and include `bootstrap.php` in your project:

```php
require_once 'path/to/wp-dynamic-css/bootstrap.php';
```

## Dynamic CSS Syntax

The only difference between regular CSS syntax and the dynamic CSS syntax is that the latter allows you to use variables with the syntax `$my_variable_name`. Any variable in the dynamic CSS file is replaced by a value that is retrieved by a custom 'value callback' function. For example:

```css
body {
   background-color: $body_bg_color;
}
```

During run time, this file will be compiled into regular CSS by replacing all the variables to their corresponding values by calling the 'value callback' function and passing the variable name (without the $ sign) to that function.

Future releases may support a more compex syntax, so any suggestions are welcome. You can make a suggestion by creating an issue or submitting a pull request.

## Enqueueing Dynamic Stylesheets

## Setting the Value Callback

## TODO

* Add support for multiple value callback functions
* Add support for loading the rendered CSS externally instead of printing to the document head
