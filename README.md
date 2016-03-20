# wp-dynamic-css

[![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)](https://raw.githubusercontent.com/aristath/kirki/master/LICENSE)

**Contributors:** ykadosh
**Tags:** theme, mods, wordpress, dynamic, css, stylesheet
**Tested up to:** 4.4.2
**Stable tag:** 1.0.0
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

A library for generating static stylesheets from dynamic content, to be used in WordPress themes and plugins.

## Contents

* [Overview](#overview)
* [Installation](#installation)
    * [Via Composer](#via-composer)
    * [Via WordPress.org](#via-wordpressorg)
    * [Manually](#manually)
* [Dynamic CSS Syntax](#dynamic-css-syntax)
* [Enqueueing Dynamic Stylesheets](#enqueueing-dynamic-stylesheets)
* [Setting the Value Callback](#setting-the-value-callback)

## Overview

**WordPress Dynamic CSS** is a lightweight library for generating CSS stylesheets from dynamic content (i.e. content that can be modified by the user). The most obvious use case for this library is for creating stylesheets based on Customizer options. Using the special dynamic CSS syntax you can write CSS rules with variables instead of static values.

## Basic Example

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

### Via WordPress.org

### Manually

## Dynamic CSS Syntax

## Enqueueing Dynamic Stylesheets

## Setting the Value Callback

