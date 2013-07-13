<?php
/**
 * WP Tokenizer
 *
 * Token Analyser with some extra WordPress specific methods
 *
 *
 *
 *
 * File:		class.WPTokenizer.php
 * @package		WPTokenizer
 * @version		1.0
 * @link		https://github.com/jrfnl/WPTokenizer
 * @author		Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *				<wp.tokenizer@adviesenzo.nl>
 * @copyright	(c) 2013, Advies en zo, Meedenken en -doen <wp.tokenizer@adviesenzo.nl> All rights reserved
 * @license		http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @since		2013-07-03
 */


/**
 * @todo Work out nearest comment code for Docblock above function definition
 *
 */

if ( !class_exists( 'WPTokenizer' ) ) {
	/**
	 * WP Tokenizer
	 *
	 * @package		WPTokenizer
	 * @version		1.0
	 * @link		https://github.com/jrfnl/WPTokenizer
	 * @author		Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
	 *				<wp.tokenizer@adviesenzo.nl>
	 * @copyright	(c) 2013, Advies en zo, Meedenken en -doen <wp.tokenizer@adviesenzo.nl>
	 *				All rights reserved
	 * @license		http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
	 */
	class WPTokenizer {

		/**
		 * Version number of this class
		 * @type	string	VERSION		version number of this class
		 */
		const VERSION = '1.0';


		/**
		 * @var		array	$extensions		Which file extensions to look for
		 */
		private $extensions = array( 'php' );


		/**
		 * @var		string	$plugin_name	Store for the retrieved plugin name
		 */
		public $plugin_name = '';



		/**
		 * Constructor.
		 *
		 * @param	string	$path		Plugin/Theme path
		 */
		public function __construct( $path ) {

			$file_list = $this->get_files( $path, true, $this->extensions );

			$slash = ( strrchr( $path, DIRECTORY_SEPARATOR ) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR );

			require_once 'include/php-token-stream/PHP/Token/Stream/Autoload.php';

			// Initialize & cache all the token streams
			foreach ( $file_list as $file_name ) {
				$tokens = PHP_Token_Stream_CachingFactory::get( $path . $slash . $file_name );

				/* Save some data we'll always need */

				// Only look for plugin name in top-level files
				if ( strpos( $file_name, DIRECTORY_SEPARATOR ) === false ) {
					$this->plugin_name = $this->get_plugin_name( $tokens, $this->plugin_name );
				}

/*$this->print_to_table_helper( $tokens, 510, 437 );
pr_var( $tokens->getFunctions(), 'functions', true );
pr_var( $tokens->getClasses(), 'functions', true );*/
			}

//exit;

		}


		/**
		 * Retrieve the (cached) file list for path
		 *
		 * @param	string			$path
		 * @param	bool			$recursive
		 * @param	array|string	$exts
		 * @return	array			File list
		 */
		public function get_files( $path, $recursive = true, $exts = null ) {
			if ( !isset( $exts ) ) {
				$exts = $this->extensions;
			}
			include_once( 'include/DirectoryWalker/class.DirectoryWalker.php' );
			return DirectoryWalker::get_file_list( $path, $recursive, $exts );
		}

		/**
		 * Retrieve the (cached) tokens object
		 *
		 * @param	string	$path_to_file
		 * @return	object
		 */
		public function get_tokens( $path_to_file ) {
			return PHP_Token_Stream_CachingFactory::get( $path_to_file );
		}


		/**
		 *
		 * @todo	Extend this function to be able to retrieve all header tokens
		 *			See notes for inspiration
		 * @todo	Also: for theme's this information is contained in style.css! Figure out a way to deal with that.
 Plugin

    Author (Plugin)
    Author URI (Plugin)
    Description (Plugin)
    Domain Path (Plugin)
    Network (Plugin)
    Plugin Name (Plugin)
    Plugin URI (Plugin)
    Site Wide Only (Plugin; deprecated in favor of Network)
    Text Domain (Plugin)
    Version (Plugin) 

Theme

    Author (Theme)
    Author URI (Theme)
    Description (Theme)
    Status (Theme)
    Tags (Theme)
    Template (Theme)
    Theme Name (Theme)
    Theme URI (Theme)
    Version (Theme)
		 *
		 * @param	array			$tokens
		 * @param	string|null		$name
		 * @return	string|null
		 */
		public function get_plugin_name( $tokens, $name = null ) {

			foreach ( $tokens as $token ) {
				// Only look at the very start of the file, break out as soon as code is encountered
				if ( ( ! $token instanceof PHP_Token_OPEN_TAG &&
					! $token instanceof PHP_Token_WHITESPACE ) &&
					( ! $token instanceof PHP_TOKEN_DOC_COMMENT &&
					! $token instanceof PHP_TOKEN_COMMENT ) ) {
					break;
				}

				// We have a comment - check to see if we can parse the plugin name based on the WP readme standard
				if ( $token instanceof PHP_Token_DOC_COMMENT ||
					$token instanceof PHP_Token_COMMENT ) {
					if ( preg_match( '`[\s\*]+Plugin Name: ([^\n\r]+)[\n\r]`', $token->__toString(), $matches ) ) {
						$name = $matches[1];
						break;
					}
				}
			}
			return $name;
		}


		/**
		 * @param $tokens
		 * @param $values
		 * @param $types
		 * @return array
		 */
		public function filter_on_value_and_type( $tokens, $values, $types ) {
			$selection = $this->filter_on_value( $tokens, $values );
			return $this->filter_on_token_type( $selection, $types );
		}


		/**
		 * @param $tokens
		 * @param $values
		 * @return array|bool
		 */
		public function filter_on_value( $tokens, $values ) {
			// Nothing to filter on - break execution
			if ( ( !is_string( $values ) && !is_array( $values ) ) || ( ( is_string( $values ) && $values === '' ) || ( is_array( $values ) && count( $values ) === 0 ) ) ) {
				return false;
			}

			if ( is_string( $values ) ) {
				$values = array( $values );
			}

			$filtered_tokens = array();
			foreach ( $tokens as $k => $token ) {
				if ( in_array( $token->__toString(), $values ) ) {
					$filtered_tokens[$k] = $token;
				}
			}
			return $filtered_tokens;
		}


		/**
		 * @param $tokens
		 * @param $types
		 * @return array
		 */
		public function filter_on_token_type( $tokens, $types ) {

			// Nothing to filter on - break execution
			if ( ( !is_string( $types ) && !is_array( $types ) ) || ( ( is_string( $types ) && $types === '' ) || ( is_array( $types ) && count( $types ) === 0 ) ) ) {
				return false;
			}

			// Make $types usable
			if ( is_string( $types ) ) {
				$types = array( $types );
			}

			foreach ( $types as $k => $type ) {
				$types[$k] = 'PHP_Token_' . substr( $type, 2 );
			}


			// Do the filtering
			$filtered_tokens = array();
			foreach ( $tokens as $k => $token ) {
				foreach ( $types as $type ) {
					if ( $token instanceof $type ) {
						$filtered_tokens[$k] = $token;
					}
				}
			}
			return $filtered_tokens;
		}



		/**
		 * Get the signature of a function call rather than of a function definition
		 *
		 * @param $tokens
		 * @param $token
		 * @param $key
		 * @return mixed
		 */
		public function get_signature( $tokens, $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $tokens, $key );
			$return     = str_replace( 'anonymous function', $token->__toString(), $tmp_object->getSignature() );
			unset( $tmp_object );
			return $return;
		}

		/**
		 * Get the arguments of a function call rather than for a function definition
		 *
		 * @param $tokens
		 * @param $token
		 * @param $key
		 * @return array
		 */
		public function get_arguments( $tokens, $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $tokens, $key );
			$return     = $tmp_object->getArguments();
			unset( $tmp_object );
			return $return;
		}


		/**
		 * Get the DocBlock directly above a line (if any)
		 *
		 * @param $tokens
		 * @param $token
		 * @param $key
		 * @return null|string
		 */
		public function get_docblock( $tokens, $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $tokens, $key );
			$return     = $tmp_object->getDocblock();
			unset( $tmp_object );
			return $return;
		}



		/**
		 * Is the token at position $key within a function ?
		 *
		 * @param	array	$tokens		Array of token objects
		 * @param	int		$key		Token key
		 * @return	array|false			Array with the function details including function name or false
		 */
		function is_within_function( $tokens, $key ) {

			$return = false;

			$line = $tokens[$key]->getLine();
			$functions = $tokens->getFunctions();

			if ( is_array( $functions ) && count( $functions ) > 0 ) {
				foreach ( $functions as $name => $details ) {
					if ( $line >= $details['startLine'] && $line <= $details['endLine'] ) {
						$details['name'] = $name;
						$return = $details;
						break;
					}
				}
			}

			return $return;
		}
/*
[name (string)]	=> string() : ‘mimetypes_link_icons_init’
[docblock (string)] => string[62] : ‘/** * Initialize the class * * @return void * /’
[keywords (string)] => string[0] : ‘’
[visibility (string)] => null : ( = NULL )
[signature (string)] => string[27] : ‘mimetypes_link_icons_init()’
[startLine (string)] => int : 1994
[endLine (string)] => int : 1996
[ccn (string)] => int : 1
[file (string)] => string[60] : ‘I:\000_GitHub\MimeTypes-Link-Icons\mime_type_link_images.php’
*/


		/**
		 * Is the token at position $key within a class|interface|trait method ?
		 *
		 * @param	array	$tokens		Array of token objects
		 * @param	int		$key		Token key
		 * @param	string	$type		class|interface|trait - if not set, will check within all
		 * @return	array|false			Array with the method details including class and method name or false
		 */
		function is_within_method( $tokens, $key, $type = null ) {

			$return = false;

			if ( !isset( $type ) ) {
				foreach ( array( 'class', 'interface', 'trait' ) as $the_type ) {
					if ( $return === false ) {
						$return = $this->is_within_method( $tokens, $key, $the_type );
					}
				}
			}
			else {
				$line = $tokens[$key]->getLine();
				$set  = null;

				switch ( $type ) {
					case 'class':
						$set = $tokens->getClasses();
						break;
					case 'trait':
						$set = $tokens->getTraits();
						break;
					case 'interface':
						$set = $tokens->getInterfaces();
						break;
				}

				if ( is_array( $set ) && count( $set ) > 0 ) {

					foreach ( $set as $class => $set_details ) {

						if ( ( $line >= $set_details['startLine'] && $line <= $set_details['endLine'] ) &&
							( is_array( $set_details['methods'] ) && count( $set_details['methods'] ) > 0 ) ) {

							foreach ( $set_details['methods'] as $name => $details ) {
								if ( $line >= $details['startLine'] && $line <= $details['endLine'] ) {
									$details['type']        = $type;
									$details['name']        = $class;
									$details['method_name'] = $name;
									$return                 = $details;
									break 2; // Stop the outer foreach
								}
							}
						}
					}
				}
			}

			return $return;
		}
/*
[type (string)] => (string) ‘class’
[name (string)] => (string) ‘mimetypes_link_icons’
[methode_name (string)] => (string) ‘init_statics’
[docblock (string)] => string[237] : ‘/** * Set the static path and directory variables for this class * Is called from the global space *before* instantiating the class to make * sure the correct values are available to the object * * @return void * /’
[keywords (string)] => string[6] : ‘static’
[visibility (string)] => string[6] : ‘public’
[signature (string)] => string[14] : ‘init_statics()’
[startLine (string)] => int : 373
[endLine (string)] => int : 380
[ccn (string)] => int : 3
[file (string)] => string[60] : ‘I:\000_GitHub\MimeTypes-Link-Icons\mime_type_link_images.php’


To be determined if I should add any of the below (class level, i.e. $set_details[]):
	[parent (string)] => bool : ( = false )
	[interfaces (string)] => bool : ( = false )
	[keywords (string)] => string[0] : ‘’
	[docblock (string)] => string[352] : ‘/** * @package WordPress\Plugins\MimeTypes Link Icons * @version 3.1 * @link http://wordpress.org/extend/plugins/mimetypes-link-icons/ MimeTypes Link Icons WordPress plugin * * @copyright 2010 - 2013 Toby Cox, Juliette Reinders Folmer * @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 * /’
	[startLine (string)] => int : 69
	[endLine (string)] => int : 1979
	[package (string)] => Array:
	(
			[namespace (string)] => string[0] : ‘’
			[fullPackage (string)] => string[9] : ‘WordPress’
			[category (string)] => string[0] : ‘’
			[package (string)] => string[9] : ‘WordPress’
			[subpackage (string)] => string[0] : ‘’
	)
	[file (string)] => string[60] : ‘I:\000_GitHub\MimeTypes-Link-Icons\mime_type_
link_images.php’
*/




		/**
		 * Get nearest comment above the current line
		 *
		 * Will stop searching if it reaches the start of the calling function, though it *will*
		 * check the docblock above the calling function.
		 *
		 * If $break_at is defined, it will stop searching if it comes across a string contained in the $break_at array to avoid ambiguity in interpreting the comment at the top of the function.
		 * If $tag is defined, it will only return the nearest comment (using above definition) if it contains
		 * the requested tag.
		 * @todo deal with break_at
		 * @todo deal with tag
		 *
		 * @todo figure out a way to deal with multi-line non-DocBlock comments
		 *
		 *
		 * @param	array			$tokens		Array of token objects
		 * @param	int				$key		Key of the token for which we're trying to get the comment
		 * @param	array|string	$break_at	String or array of strings at which to break off the search
		 * @param	array|string	$must_have_tag		phpDoc tag string or array of phpDoc strings to search for in the comment
		 * @return	string
		 */
		public function get_nearest_comment( $tokens, $key, $break_at = null, $must_have_tag = null ) {

			if ( isset( $break_at ) && !is_array( $break_at ) ) {
				$break_at = (array) $break_at;
			}
			if ( isset( $tag ) && !is_array( $tag ) ) {
				$tag = array( $tag );
			}




//			$tokens            = $this->tokens();
//pr_var( $tokens );
//pr_var( $key, 'key', true );
			$currentLineNumber = $tokens[$key]->getLine();
			$prevLineNumber    = $currentLineNumber - 1;

			//$within_function = $this->is_within_function( $tokens, $key );
			//$within_method = $this->is_within_method( $tokens, $key );

			for ( $i = $key - 1; $i; $i-- ) {
				if ( !isset( $tokens[$i] ) ) {
					return;
				}

				// Isset ?
				// is not in break_at array ?

				// is docblock ? -> parse & test for only internal/ignore, if ok, return, else continue

				// is comment ? -> look if there is nothing else (but whitespace) on the same line, if there is, this comment does not apply to the requested line -> look for doc block at top of function/method
				// -> else -> parse & test for only internal/ignore, if ok, save & continue to check for more lines
				// -> else discard and continue to check for more lines

				// only continue looking for docblock at the top if part of function/method!!






				if ( isset( $break_at ) && in_array( $tokens[$i]->__toString(), $break_at ) ) {
					// Token in break at array, break off searching
					return;
				}


/*
$parsed_comment = $this->parse_comment( $tokens[$i]->__toString() );
if ( ( is_array( $parsed_comment ) && count( $parsed_comment ) === 1 ) && ( array_key_exist( 'internal', $parsed_comment ) || array_key_exists( 'ignore', $parsed_comment ) ) {
	// not the comment we're looking for
	continue;
}
else {
	unset( $parsed_comment['ignore'], $parsed_comment['internal'] );
	return $parsed_comment;
}
*/

//pr_var( array( 'class' => get_class( $tokens[$i] ), 'line' => $tokens[$i]->getLine(), 'string' => $tokens[$i]->__toString() ), '$tokens['.$i. ']', true );
				if ( $tokens[$i] instanceof PHP_Token_FUNCTION ||
					$tokens[$i] instanceof PHP_Token_CLASS ||
					$tokens[$i] instanceof PHP_Token_TRAIT ) {
					// Some other trait, class or function, no docblock can be
					// used for the current token
//print 'breaking because of function | class | trait<br>';
					break;
				}

				$line = $tokens[$i]->getLine();

				if ( $line == $currentLineNumber ||
					( $line == $prevLineNumber &&
					$tokens[$i] instanceof PHP_Token_WHITESPACE ) ) {
//print 'continue because of same line or prev line, but whitespace<br>';
					continue;
				}

				// @todo - work out a way to continue if the comment is @internal
				// @todo - work out a way to get the docblock for the calling function
				if ( $line < $currentLineNumber &&
					( !$tokens[$i] instanceof PHP_Token_DOC_COMMENT &&
					!$tokens[$i] instanceof PHP_Token_COMMENT ) ) {
//print 'breaking because doc comment found<br>';
					break;
				}

				return (string) $tokens[$i];
			}

			return;
		}






		/**
		 * Retrieve the nearest comment and parse it as a phpDoc style comment
		 *
		 * @param $tokens
		 * @param $key
		 * @param null $tag
		 * @return array
		 */
		public function parse_nearest_comment( $tokens, $key, $tag = null ) {
			return $this->parse_comment( $this->get_nearest_comment( $tokens, $key, $tag ), $tag );
		}


		/**
		 * @todo Have a really good look at this function !!!
		 */
		public function strip_comment_markers( $comment ) {
			static $search  = array( '`(?:^(/\*+)|(\*/)$|[\n\r][ \t]+(\*)[\s]|^(//)|^(#))`', '`([ \t\r]{2,})`' );
			static $replace = array( '', ' ' );

			// Parse out all the line endings and comment delimiters
			$comment = trim( preg_replace( $search, $replace, trim( $comment ) ) );
			return $comment;
		}


		/**
		 * Parse comments to their individual parts
		 *
		 * Superfluous whitespace will be removed from the resulting values
		 * Note: new lines are *not* removed from values, other superfluous whitespace is.
		 * The reason for this is to allow people to use nl2br for displaying the comments.
		 * If you don't want the new lines, just str_replace() them in your own code.
		 *
		 * @todo may be figure out a way to deal with {inline @link} comments ? Probably not needed
		 *
		 * @param	string			$string		Comment string
		 * @param	array|string	$tags		a phpDoc tags or an array of phpDocs tags to filter for
		 * @return	array			Array containing the parsed comment, optionally filtered to only
		 *							contain instances of $tag
		 */
		public function parse_comment( $string, $tags = null ) {

//			static $search = array( '`(?:^(/\*+)|(\*/)$|[\n\r][ \t]+(\*)[\s]|^(//)|^(#))`', '`([ \t\r]{2,})`' );
/*			static $replace = array( '', ' ' );

			// Parse out all the line endings and comment delimiters
			$string = trim( preg_replace( $search, $replace, trim( $string ) ) );
*/
			$string = $this->strip_comment_markers( $string );

			// Match the individual comment parts
			$found = preg_match_all( '`(?:@([a-z-]+)\s+)?([^@]+)`', $string, $matches, PREG_SET_ORDER );

			if ( $found > 0 ) {
				$comment = array();

				/* Create an array of the parsed comments */
				foreach ( $matches as $match ) {
					$match = array_map( 'trim', $match );

					if ( $match[1] === '' && $match[2] !== '' ) {
						// No @tag found, tag it as 'description'
						$comment['description'][] = $match[2];
					}
					else if ( $match[2] !== '' ) {
						$parsed_line = $this->comment_parse_line( $match[2], $match[1] );
						if ( $parsed_line !== false ) {
							$comment[$match[1]][] = $parsed_line;
						}
					}
					else {
						$comment[$match[1]][] = '';
					}
				}
				unset( $match );


				if ( ! isset( $tags ) || ( ( !is_string( $tags ) && !is_array( $tags ) ) || ( ( is_string( $tags ) && $tags === '' ) || ( is_array( $tags ) && count( $tags ) === 0 ) ) ) ) {
					return $comment;
				}
				else {
					if ( is_string( $tags ) ) {
						$tags = array( $tags );
					}
					$tags = array_flip( $tags );
					return array_intersect_key( $comment, $tags );
				}
			}
			return false;
		}


		/**
		 * Parse a phpDoc style comment line for it's syntactical parts
		 *
		 * @link http://www.phpdoc.org/docs/latest/for-users/phpdoc/types.html
		 * @todo	work out parse routines for the other types
		 * @todo	re-work the function to allow developers to pass their own regex/routine for a certain tag
		 *
		 * @param	string			$string		Comment line string to be parsed according to phpDoc standard
		 * 										with the tag already removed
		 * @param	string			$tag		The associated tag which syntax should be followed
		 * @return	array|string	An array containing the parsed parts or the unaltered string if
		 * 							the line could not be parsed or has no syntax to parse by.
		 */
		public function comment_parse_line( $string, $tag ) {
// (?P<name>pattern).
			$return = $string;

			switch ( $tag ) {
				case 'api': // no specified syntax, project dependent, but presume similar syntax
				case 'param': // @param [Type] [name] [<description>]
				case 'return': // @return [Type] [<description>]
				case 'var': // no docs yet, but presume similar syntax
				case 'staticvar': // phpDoc1 @staticvar data-type description
				case 'type': //@type ["Type"] [element_name] [<description>] - (= successor of var)

//					$found = preg_match( '`((?:\|?(?:string|integer|int|boolean|bool|float|double|object|mixed|array|resource|void|null|callback|false|true|self))+)(?:\s+(\$[\w]+))?(\s+[^$]*)?$`', $string, $match );
					$found = preg_match( '`(?P<type>(?:\|?(?:string|integer|int|boolean|bool|float|double|object|mixed|array|resource|void|null|callback|false|true|self))+)(?:\s+(?P<var_name>\$[\w]+))?(?P<description>\s+[^$]*)?$`', $string, $match );
//pr_var( $match );
					if ( $found > 0 ) {
						$return = array( 'type' => $match[1] );
						if ( isset( $match[2] ) && $match[2] !== '' ) {
							$return['var_name'] = $match[2];
						}
						if ( isset( $match[3] ) && $match[3] !== '' ) {
							$return['comment'] = trim( $match[3] );
						}
//						return $return;
					}
					unset( $found, $match );
					break;


				case 'abstract': // phpDoc1 icm php4 - @abstract
				case 'final': // phpDoc1 icm php4 - @final
				case 'static': // phpDoc1 - @static
				case 'filesource': //@filesource
					//ignore as no content, shouldn't normally even be passed to this function
					$return = false;
					break;


				case 'access': // phpDoc1 @access private protected public


				case 'author': //@author [name] [<email address>]
					$found = preg_match( '`^([^<$]+)\s+(?:<([^>]+)>)?$`', $string, $match );
					if ( $found > 0 ) {
						$return = array( 'name' => $match[1] );
						if ( isset( $match[2] ) && $match[2] !== '' ) {
							$return['email'] = trim( $match[2] );
						}
//						return $return;
					}
					unset( $found, $match );
					break;






				case 'link': // @link [URI] [<description>] OR phpDoc1 alternative syntax: @link URL, URL, URL...
				case 'license': // @license [<url>] [name]
//					break;


				case 'deprecated': //@deprecated [<version>] [<description>]
//					break;


/**
 * My function
 *
 * Here is an inline example:
 * <code>
 * <?php
 * echo strlen('6');
 * ?>
 * </code>
 * @example /path/to/example.php How to use this function
 * @example another-example.inc This example is in the "examples" subdirectory
 */
				case 'example': //@example [location] [<start-line> [<number-of-lines>] ] [<description>]
//					break;


				case 'global': //@global [Type] [name] || @global [Type] [description]
//					break;


				case 'package': //@package [level 1]\[level 2]\[etc.]
				case 'subpackage': //@subpackage [name]
//					break;


				case 'see': //@see [URI | FQSEN] [<description>]
							// phpDoc1 @see file.ext|element_name|class::method_name()|class::$variable_name|function_name()|function function_name unlimited number of values separated by commas
				case 'uses': //@uses [FQSEN] [<description>]
							// @uses file.ext|element_name|class::method_name()|class::$variable_name|function_name()|function function_name description of how the element is used
				case 'used-by': //@used-by [FQSEN] [<description>]
				case 'usedby': // phpDoc1 syntax @usedby [FQSEN] [<description>]
//					break;


				case 'since': //@since [version] [<description>]
				case 'version': //@version [<vector>] [<description>]
//					break;


				case 'throws': //@throws [Type] [<description>]
//					break;


				// Magic methods, very low priority
				case 'method': // @method [return type] [name]([[type] [parameter]<, ...>]) [<description>]
//					break;
				// Magic properties, very low priority
				case 'property': //@property [Type] [name] [<description>]
				case 'property-read': //@property-read [Type] [name] [<description>]
				case 'property-write': //@property-write [Type] [name] [<description>]
//					break;

				// phpDoc specific, very low priority
				case 'source': //@source [<start-line> [<number-of-lines>] ] [<description>]
//					break;

				case 'tutorial': // phpDoc1 @tutorial package/ subpackage/ tutorial name.ext #section.subsection description
//					break;

				case 'access': // phpDoc1 @access private protected public
				case 'category': //@category [description]
				case 'copyright': //@copyright [description]
				case 'ignore': //@ignore [<description>]
				case 'internal': //@internal [description]
				case 'name': // phpDoc1 @name $global_variable_name
				case 'todo': //@todo [description]
				default: // proprietary tags
					$return = $string;
					break;
			}

			return $return;
		}



//			$this->print_to_table_helper( $tokens, 0, 100 );
//			$this->print_to_table_helper( $tokens, 510, 437 );
		/**
		 * @param $tokens
		 * @param $start_position
		 * @param $end_position
		 */
		function print_to_table_helper( $tokens, $start_position, $end_position ) {

			$start   = $start_position;
			$end     = $end_position;
			$reverse = false;

			if ( $end_position < $start_position ) {
				$reverse = true;
				$start   = $end_position;
				$end     = $start_position;
			}

			print '<table>
		<tr>
			<th style="width: 40px;">Key</th>
			<th style="width: 40px;">Line</th>
			<th style="width: 140px;">Type</th>
			<th>Content</th>
		</tr>';

			$rows = array();
			if ( $tokens->offsetExists( $start ) ) {
				$tokens->seek( $start );

				while ( $tokens->key() < $end && $tokens->offsetExists( $tokens->key() ) ) {
//                for( $i = $start; $i < $end; $i++ ) {
					$rows[] = '
			<tr>
				<td>' . $tokens->key() . '</td>
				<td>' . $tokens->current()->getLine() . '</td>
				<td>' . get_class( $tokens->current() ) . '</td>
				<td>' . $tokens->current()->__toString() . '</td>
			</tr>';

/*
				<td>' . $this->tokens[$i]->key() . '</td>
				<td>' . $this->getLine() . '</td>
				<td>' . get_class( $token ) . '</td>
				<td>' . $this->__toString() . '</td>

 */
					if ( $tokens->key() > $end ) {
						break;
					}
					$tokens->next();
				}
				if ( $reverse === true ) {
					$rows = array_reverse( $rows );
				}
				array_walk( $rows, array( 'self', 'print_it' ) );
			}
			else {
				print '<tr><td colspan="4">No tokens found between the given positions.</td></tr>';
			}

			print '</table>';

		}

		/**
		 * @param $value
		 * @param $key
		 */
		static function print_it( $value, $key ) {
			print $value;
		}
	} /* End of class */
} /* End of class-exists wrapper */
?>