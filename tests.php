<?php
use PHPUnit\Framework\TestCase;

/**
 * To run the tests, use the following command:
 * $ phpunit --bootstrap compiler.php tests.php
 */
class CompilerTest extends TestCase {

	/**
	 * DynamicCSSCompiler::strtoval test
	 */
	public function testStrToVal() {

		// Make protected functions accessible through a reflection class
		$class = new ReflectionClass( 'DynamicCSSCompiler' );
		$method = $class->getMethod( 'strtoval' );
		$method->setAccessible( true );
		$dcss = DynamicCSSCompiler::get_instance();

		// Assert
		$str = "'value'";
		$method->invokeArgs( $dcss, [ &$str ] );
		$this->assertEquals( $str, 'value' );

		$str = 'false';
		$method->invokeArgs( $dcss, [ &$str ] );
		$this->assertEquals( $str, false );

		$str = 'true';
		$method->invokeArgs( $dcss, [ &$str ] );
		$this->assertEquals( $str, true );

		$str = '4';
		$method->invokeArgs( $dcss, [ &$str ] );
		$this->assertEquals( $str, 4 );

		$str = '4.321';
		$method->invokeArgs( $dcss, [ &$str ] );
		$this->assertEquals( $str, 4.321 );
	}

	/**
	 * DynamicCSSCompiler::apply_filters test
	 */
	public function testFilterApplication() {

		// Make protected functions accessible through a reflection class
		$class = new ReflectionClass( 'DynamicCSSCompiler' );
		$method = $class->getMethod( 'apply_filters' );
		$method->setAccessible( true );
		$dcss = DynamicCSSCompiler::get_instance();

		// Assert
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'simple_filter',
			'foo',
			[
				'simple_filter' => 'simple_filter_callback',
			],
		] ), 'foobar' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'simple_filter|simple_filter',
			'foo',
			[
				'simple_filter' => 'simple_filter_callback',
			],
		] ), 'foobarbar' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'complex_filter(\'bar\')',
			'foo',
			[
				'complex_filter' => 'complex_filter_callback',
			],
		] ), 'foobar' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'complex_filter(\'bar\',\'foo\')',
			'foo',
			[
				'complex_filter' => 'complex_filter_callback',
			],
		] ), 'foobarfoo' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'complex_filter(\'bar\')|complex_filter(\'foo\')',
			'foo',
			[
				'complex_filter' => 'complex_filter_callback',
			],
		] ), 'foobarfoo' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'add_filter(5,5)',
			'5 + 5 = ',
			[
				'add_filter' => 'add_filter_callback',
			],
		] ), '5 + 5 = 10' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'add_filter(5.5,5.5)',
			'5.5 + 5.5 = ',
			[
				'add_filter' => 'add_filter_callback',
			],
		] ), '5.5 + 5.5 = 11' );
	}

	/**
	 * DynamicCSSCompiler::compile_css test
	 */
	public function testCompilation() {

		// Make protected functions accessible through a reflection class
		$class = new ReflectionClass( 'DynamicCSSCompiler' );
		$method = $class->getMethod( 'compile_css' );
		$method->setAccessible( true );
		$dcss = DynamicCSSCompiler::get_instance();

		// Assert
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var1',
			'callback',
			[],
		] ), 'value1' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var2',
			'callback',
			[],
		] ), 'value2' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var3[\'index1\']',
			'callback',
			[],
		] ), 'value3' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var3[\'index2\'][\'subindex1\']',
			'callback',
			[],
		] ), 'value4' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var3[index2][subindex1]',
			'callback',
			[],
		] ), 'value4' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var3[index2][subindex1]',
			'callback',
			[],
		] ), 'value4' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var4|simpleFilter',
			'callback',
			[
				'simpleFilter' => 'simple_filter_callback',
			],
		] ), 'valuebar' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var4|simpleFilter|simpleFilter',
			'callback',
			[
				'simpleFilter' => 'simple_filter_callback',
			],
		] ), 'valuebarbar' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var4|complexFilter(6)',
			'callback',
			[
				'complexFilter' => 'complex_filter_callback',
			],
		] ), 'value6' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var5|complexFilter(e)|complexFilter(7)',
			'callback',
			[
				'complexFilter' => 'complex_filter_callback',
			],
		] ), 'value7' );
		$this->assertEquals( $method->invokeArgs( $dcss, [
			'$var3[index1]|simpleFilter',
			'callback',
			[
				'simpleFilter' => 'simple_filter_callback',
			],
		] ), 'value3bar' );
	}
}

/**
 * Value retrieval function
 */
function callback( $varname, $subscripts ) {
	$values = [
		'var1'  => 'value1',
		'var2'  => 'value2',
		'var3'  => [
			'index1' => 'value3',
			'index2' => [
				'subindex1' => 'value4',
			],
		],
		'var4'  => 'value',
		'var5'  => 'valu',
	];

	$val = $values[ $varname ];
	if ( null !== $subscripts ) {
		foreach ( $subscripts as $subscript ) {
			$val = $val[ $subscript ];
		}
	}

	return $val;
}

/**
 * Simple string concat filter
 */
function simple_filter_callback( $foo ) {
	return $foo . 'bar';
}

/**
 * String concat filter with parameters
 */
function complex_filter_callback( $value, $arg1 = '', $arg2 = '' ) {
	return $value . $arg1 . $arg2;
}

/**
 * Number adding filter
 */
function add_filter_callback( $value, $a, $b ) {
	return $value . ($a + $b);
}
