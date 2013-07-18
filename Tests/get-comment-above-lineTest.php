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
class WPTokenizerTestsGetCommentsAboveLine extends PHPUnit_Framework_TestCase {

	const TEST_FILE = 'caseGetCommentsAboveLine.php';

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
	 * Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 */
	public function test_get_comment_above_line_no_comment() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'no_comment'" ) );
		$this->assertEmpty( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
	}


	/**
	 * Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_inline_slash() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'inline_slash'" ) );

		$expected = '// This is an inline slash comment';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This is an inline slash comment';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_inline_hash() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'inline_hash'" ) );

		$expected = '# This is an inline hash comment';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This is an inline hash comment';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}



	/**
	 * Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_inline_starred() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'inline_starred'" ) );

		$expected = '/* This is an inline star comment */';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This is an inline star comment';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_inline_starred_multi_line() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'inline_starred_multi-line'" ) );

		$expected = '/*
			 This is an inline comment
			 It spans two lines
			 */';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This is an inline comment' . "\n" . 'It spans two lines';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_inline_star_with_tag() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'inline_star_with_tag'" ) );

		$expected = '/* Add filter hook for param
			   @api string	$new_param Allows a developer to filter the param string
			   before it is send to the screen */';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'Add filter hook for param' . "\n" . '@api string	$new_param Allows a developer to filter the param string' . "\n" . 'before it is send to the screen';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_inline_docblock() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'inline_docblock'" ) );

		$expected = '/**
			 * This is an inline DocBlock comment
			 */';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This is an inline DocBlock comment';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_inline_docblock_with_tag() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'inline_docblock_with_tag'" ) );

		$expected = '/**
			 * This is an inline DocBlock comment
			 * @api	string	$param	param description
			 */';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This is an inline DocBlock comment' . "\n" . '@api	string	$param	param description';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Multi-line Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_multiline_slash() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'filter_multi-line_slash'" ) );

		$expected = '// This line 1 of a slashed multi-line comment' . "\n" . '// This line 2 of a slashed multi-line comment';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This line 1 of a slashed multi-line comment' . "\n" . 'This line 2 of a slashed multi-line comment';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Multi-line Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_multiline_hash() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'filter_multi-line_hash'" ) );

		$expected = '# This line 1 of a hashed multi-line comment' . "\n" . '# This line 2 of a hashed multi-line comment';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This line 1 of a hashed multi-line comment' . "\n" . 'This line 2 of a hashed multi-line comment';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Multi-line Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_multiline_star() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'filter_multi-line_star'" ) );
		$expected = '/* This line 1 of a star-non DocBlock multi-line comment */' . "\n" . '/* This line 2 of a star-non DocBlock comment */';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This line 1 of a star-non DocBlock multi-line comment' . "\n" . 'This line 2 of a star-non DocBlock comment';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Multi-line Inline Comments
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_multiline_mixed() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'filter_multi-line_mixed'" ) );
		$expected = '// This line 1 of a slashed multi-line comment' . "\n" . '# This line 2 of a hashed multi-line comment' . "\n" . '/* This line 3 of a star-non DocBlock comment */';
		$comment  = trim( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
		$this->assertEquals( $expected, $comment );

		$expected = 'This line 1 of a slashed multi-line comment' . "\n" . 'This line 2 of a hashed multi-line comment' . "\n" . 'This line 3 of a star-non DocBlock comment';
		$this->assertEquals( $expected, $this->tokenizer->strip_comment_markers( $comment ) );
	}


	/**
	 * Inline comment belonging to other code
	 *
	 * @covers WPTokenizer::get_comment_above_line()
	 * @covers WPTokenizer::strip_comment_markers()
	 */
	public function test_get_comment_above_line_does_not_belong_to_line() {

		$key = $this->tokenizer->filter_on_value( $this->tokens, array( "'filter_comment_not_alone_on_line'" ) );
		$this->assertEmpty( $this->tokenizer->get_comment_above_line( $this->tokens, key( $key ) ) );
	}

}
?>