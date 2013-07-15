<?php
/**
 * Unit tests for WPTokenizer
 *
 * File:		test.WPTokenizer-filtering.php
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
class WPTokenizerTestsFiltering extends PHPUnit_Framework_TestCase {

	const TEST_FILE = 'caseGeneral.php';

	/**
	 * @var		array	$tokenizer	Hold an instance of the tokenizer class
	 */
	protected $tokenizer;

	protected $tokens;


	protected function setUp() {
		$this->tokenizer = new WPTokenizer( null );
		$this->tokens    = $this->tokenizer->get_tokens( TEST_FILES_PATH . self::TEST_FILE );
//		$this->tokenizer->print_to_table_helper( $this->tokens );
	}


	/**
	 * @covers WPTokenizer::filter_on_value_and_type()
	 */
	public function test_filter_on_value_and_type() {
		$this->assertEmpty( $this->tokenizer->filter_on_value_and_type( $this->tokens, array( 'apply_filters' ), 'T_STRING' ) );

		$this->assertCount( 1, $this->tokenizer->filter_on_value_and_type( $this->tokens, array( '(int)' ), 'T_INT_CAST' ) );
	}


	/**
	 * @covers WPTokenizer::filter_on_value()
	 */
	public function test_filter_on_value() {
		$this->assertEmpty( $this->tokenizer->filter_on_value( $this->tokens, array( 'apply_filters' ) ) );

		$this->assertCount( 2, $this->tokenizer->filter_on_value( $this->tokens, array( 'is_array' ) ) );

		$this->assertCount( 2, $this->tokenizer->filter_on_value( $this->tokens, array( '$param' ) ) );

		$this->assertCount( 1, $this->tokenizer->filter_on_value( $this->tokens, array( '(int)' ) ) );

		$this->assertCount( 1, $this->tokenizer->filter_on_value( $this->tokens, array( "'(int)'" ) ) );

		$this->assertCount( 3, $this->tokenizer->filter_on_value( $this->tokens, array( '$property1' ) ) );

		$this->assertCount( 8, $this->tokenizer->filter_on_value( $this->tokens, array( 'property1' ) ) );
	}


	/**
	 * @covers WPTokenizer::filter_on_token_type()
	 */
	public function test_filter_on_token_type() {
		$this->assertEmpty( $this->tokenizer->filter_on_token_type( $this->tokens, 'T_COMMENT' ) );

		$this->assertCount( 3, $this->tokenizer->filter_on_token_type( $this->tokens, 'T_DOC_COMMENT' ) );

		$this->assertCount( 32, $this->tokenizer->filter_on_token_type( $this->tokens, 'T_VARIABLE' ) );

		$this->assertCount( 3, $this->tokenizer->filter_on_token_type( $this->tokens, 'T_FUNCTION' ) );

		$this->assertCount( 3, $this->tokenizer->filter_on_token_type( $this->tokens, 'T_IF' ) );

		$this->assertCount( 4, $this->tokenizer->filter_on_token_type( $this->tokens, 'T_CONSTANT_ENCAPSED_STRING' ) );
	}
}
?>