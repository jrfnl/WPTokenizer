<?php
/**
 * Unit tests for WPTokenizer
 *
 * File:		test.WPTokenizer-get_nearest_comment.php
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
class WPTokenizerTestsGetNearestFunctionDocblock extends PHPUnit_Framework_TestCase {

	const TEST_FILE = 'caseGetNearestFunctionDocblock.php';

	/**
	 * @var		array	$tokenizer	Hold an instance of the tokenizer class
	 */
	protected $tokenizer;

	protected $tokens;


	protected function setUp() {
		$this->tokenizer = new WPTokenizer( null );
		$this->tokens    = $this->tokenizer->get_tokens( TEST_FILES_PATH . self::TEST_FILE );
	}

	/**
	 * @covers WPTokenizer::get_nearest_function_docblock()
	 */
	public function test_get_nearest_function_docblock() {

		$keys = $this->tokenizer->filter_on_value( $this->tokens, array( 'do_action' ) );
		$expected = '/**
		 * Test method
		 *
		 * @api A description for the action
		 * @api string	A description of the parameters passed
		 *
		 * @param 	string	$param
		 * @return string
		 */';

		foreach ( $keys as $key => $object ) {
			$this->assertEquals( $expected, $this->tokenizer->get_nearest_function_docblock( $this->tokens, $key ) );
		}
	}


	/**
	 * @covers WPTokenizer::get_nearest_function_docblock()
	 */
	public function test_get_nearest_function_docblock_none() {

		$keys = $this->tokenizer->filter_on_value( $this->tokens, array( 'do_action_ref_array' ) );

		foreach ( $keys as $key => $object ) {
			$this->assertEmpty( $this->tokenizer->get_nearest_function_docblock( $this->tokens, $key ) );
		}
	}


	/**
	 * @covers WPTokenizer::is_docblock_ambiguous()
	 */
	public function test_is_docblock_ambiguous() {

		$filter = array( 'do_action', 'do_action_ref_array' );


		$keys = $this->tokenizer->filter_on_value( $this->tokens, $filter );

		$i = 1;
		foreach ( $keys as $key => $object ) {

			if ( $i & 1 ) {
				//odd
				$this->assertFalse( $this->tokenizer->is_docblock_ambiguous( $this->tokens, $key, $filter ) );
			}
			else {
				//even
				$this->assertTrue( $this->tokenizer->is_docblock_ambiguous( $this->tokens, $key, $filter ) );
			}
			$i++;
		}
	}
}
?>