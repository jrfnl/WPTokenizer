<?php
/**
 * Unit tests for WPTokenizer
 *
 * File:		test.WPTokenizer-is_within.php
 * @package		WPTokenizer
 * @subpackage	UnitTests
 * @version		1.0
 * @link		https://github.com/jrfnl/WPTokenizer
 * @author		Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *				<wp.tokenizer@adviesenzo.nl>
 * @copyright	(c) 2013, Advies en zo, Meedenken en -doen <wp.tokenizer@adviesenzo.nl> All rights reserved
 * @license		http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @since		Unit tests available since release 1.0
 */


if ( !defined( 'TEST_FILES_PATH' ) ) {
	/**
	 * Determine the path where files needed for the tests are placed
	 */
	define(
	'TEST_FILES_PATH',
		dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR
	);
}

/**
 * Include the class to be tested
 */
require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'class.WPTokenizer.php';


/**
 * Unit tests for the WPTokenizer class.
 *
 * @package		WPTokenizer
 * @subpackage	UnitTests
 * @version		1.0
 * @link		https://github.com/jrfnl/WPTokenizer
 * @author		Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *				<wp.tokenizer@adviesenzo.nl>
 * @copyright	(c) 2013, Advies en zo, Meedenken en -doen <wp.tokenizer@adviesenzo.nl> All rights reserved
 * @license		http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @since		Unit tests available since release 1.0
 */
class WPTokenizerTestsIsWithin extends PHPUnit_Framework_TestCase {

	const TEST_FILE = 'caseGeneral.php';

	/**
	 * @var		array	$tokenizer	Hold an instance of the tokenizer class
	 */
	protected $tokenizer;

	protected $tokens;


	protected function setUp() {
		$this->tokenizer = new WPTokenizer( null );
		$this->tokens    = $this->tokenizer->get_tokens( TEST_FILES_PATH . self::TEST_FILE );
	}


	/*
	 * Key legend:
	 *
	 * 10  = within class, not method
	 * 250 = within method
	 * 319 = within function
	 * 330 = outside both
	 */



	/**
	 * @covers WPTokenizer::is_within_function()
	 */
	public function test_is_within_function() {
		$this->assertFalse( $this->tokenizer->is_within_function( $this->tokens, 10 ) );
		$this->assertFalse( $this->tokenizer->is_within_function( $this->tokens, 250 ) );
		$this->assertNotEquals( false, $this->tokenizer->is_within_function( $this->tokens, 319 ) );
		$this->assertNotEmpty( $this->tokenizer->is_within_function( $this->tokens, 319 ) );
		$this->assertFalse( $this->tokenizer->is_within_function( $this->tokens, 330 ) );
	}


	/**
	 * @covers WPTokenizer::is_within_function()
	 */
	public function test_is_within_function_results() {
		$this->assertArrayHasKey( 'function_name', $this->tokenizer->is_within_function( $this->tokens, 319 ) );
		$this->assertArrayNotHasKey( 'class_name', $this->tokenizer->is_within_function( $this->tokens, 319 ) );
		$this->assertContains( 'example', $this->tokenizer->is_within_function( $this->tokens, 319 ) );
	}


	/**
	 * @covers WPTokenizer::is_within_method()
	 */
	public function test_is_within_method() {
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 10 ) );
		$this->assertNotEquals( false, $this->tokenizer->is_within_method( $this->tokens, 250 ) );
		$this->assertNotEmpty( $this->tokenizer->is_within_method( $this->tokens, 250 ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 319 ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 330 ) );
	}


	/**
	 * @covers WPTokenizer::is_within_method()
	 */
	public function test_is_within_method_class() {
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 10, 'class' ) );
		$this->assertNotEquals( false, $this->tokenizer->is_within_method( $this->tokens, 250, 'class' ) );
		$this->assertNotEmpty( $this->tokenizer->is_within_method( $this->tokens, 250, 'class' ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 319, 'class' ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 330, 'class' ) );
	}


	/**
	 * @covers WPTokenizer::is_within_method()
	 */
	public function test_is_within_method_interface() {
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 10, 'interface' ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 250, 'interface' ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 319, 'interface' ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 330, 'interface' ) );
	}


	/**
	 * @covers WPTokenizer::is_within_method()
	 */
	public function test_is_within_method_trait() {
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 10, 'trait' ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 250, 'trait' ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 319, 'trait' ) );
		$this->assertFalse( $this->tokenizer->is_within_method( $this->tokens, 330, 'trait' ) );
	}


	/**
	 * @covers WPTokenizer::is_within_method()
	 */
	public function test_is_within_method_results() {
		$this->assertArrayHasKey( 'method_name', $this->tokenizer->is_within_method( $this->tokens, 250, 'class' ) );
		$this->assertArrayHasKey( 'class_name', $this->tokenizer->is_within_method( $this->tokens, 250, 'class' ) );
		$this->assertArrayNotHasKey( 'function_name', $this->tokenizer->is_within_method( $this->tokens, 250, 'class' ) );
		$this->assertContains( 'CamelCase', $this->tokenizer->is_within_method( $this->tokens, 250 ) );
	}
}
?>