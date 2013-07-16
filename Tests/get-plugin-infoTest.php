<?php
/**
 * Unit tests for WPTokenizer
 *
 * File:		test.WPTokenizer-get_plugin_info.php
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
class WPTokenizerTestsGetPluginInfo extends PHPUnit_Framework_TestCase {

	const TEST_FILE_1 = 'casePluginInfo.php';

	const TEST_FILE_2 = 'caseNoPluginInfo.php';

	const TEST_FILE_3 = 'casePluginInfoNotFirst.php';

	const TEST_DIR = 'PluginInfo';

	/**
	 * @var		array	$tokenizer	Hold an instance of the tokenizer class
	 */
	protected $tokenizer;

	protected $tokens;

	/**
	 * @var		string	$plugin_name	Store for the retrieved plugin name
	 */
	protected $plugin_name = '';


	protected function setUp() {
		$this->tokenizer = new WPTokenizer( null );
//		$this->tokens    = $this->tokenizer->get_tokens( TEST_FILES_PATH . self::TEST_FILE );
	}


	/**
	 * @covers WPTokenizer::get_plugin_name()
	 */
	public function test_get_plugin_name() {
		$this->tokens = $this->tokenizer->get_tokens( TEST_FILES_PATH . self::TEST_FILE_1 );
		$this->assertEquals( 'Health Check', $this->tokenizer->get_plugin_name( $this->tokens ) );

	}


	/**
	 * No name found
	 *
	 * @covers WPTokenizer::get_plugin_name()
	 */
	public function test_get_plugin_name_null() {
		$this->tokens = $this->tokenizer->get_tokens( TEST_FILES_PATH . self::TEST_FILE_2 );
		$this->assertNull( $this->tokenizer->get_plugin_name( $this->tokens ) );
	}


	/**
	 * Plugin info not at very top of file
	 *
	 * @covers WPTokenizer::get_plugin_name()
	 */
	public function test_get_plugin_name_not_first() {
		$this->tokens = $this->tokenizer->get_tokens( TEST_FILES_PATH . self::TEST_FILE_3 );
		$this->assertNull( $this->tokenizer->get_plugin_name( $this->tokens ) );
	}


	/**
	 * Several files, each with a different name in the info at the top
	 *
	 * @covers WPTokenizer::get_plugin_name()
	 */
	public function test_get_plugin_name_ambiguous() {
		$files = $this->tokenizer->get_files( TEST_FILES_PATH . self::TEST_DIR . DIRECTORY_SEPARATOR );
		foreach ( $files as $file ) {
			$tokens            = $this->tokenizer->get_tokens( TEST_FILES_PATH . $file );
			$this->plugin_name = $this->tokenizer->get_plugin_name( $tokens, $this->plugin_name );
		}
		$this->assertEquals( 'Health Check', $this->plugin_name );

	}
}
?>