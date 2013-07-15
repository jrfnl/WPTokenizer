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
		 * @var		array	$not		Which comment tags to remove from parse comment results
		 */
		protected $default_not = array(
			'internal',
			'ignore',
		);


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
		 * If $path is given, will initialize & fill the directory and token caches
		 *
		 * @param	string|null	$path		Plugin/Theme path
		 */
		public function __construct( $path = null ) {

			if ( !is_null( $path ) ) {
				$file_list = $this->get_files( $path, true, $this->extensions );

				$slash = ( strrchr( $path, DIRECTORY_SEPARATOR ) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR );

				// Initialize & cache all the token streams
				foreach ( $file_list as $file_name ) {
					$tokens = $this->get_tokens( $path . $slash . $file_name );

					/* Save some data we'll always need */

					// Only look for plugin name in top-level files
					if ( strpos( $file_name, DIRECTORY_SEPARATOR ) === false ) {
						$this->plugin_name = $this->get_plugin_name( $tokens, $this->plugin_name );
					}
				}
			}
		}


		/**
		 * Change the value of the protected $default_not property which determines which
		 * comment properties to exclude from being returned in a parsed comment.
		 *
		 * @param   array|string|null    $not
		 * @return  bool                 Whether the property was changed
		 */
		public function set_not( $not ) {
			if( !isset( $not ) ) {
				$this->default_not = null;
			}
			if ( is_string( $not ) && $not !== '' ) {
				$not = explode( ',', $not );
				$not = array_map( 'trim', $not );
			}

			if ( is_array( $not ) && count( $not ) > 0 ) {
				$this->default_not = $not;
				return true;
			}
			else {
				return false;
			}
		}


		/**
		 * Retrieve the (cached) file list for path
		 *
		 * @param	string			$path       Directory path
		 * @param	bool			$recursive  Defaults to true
		 * @param	array|string	$exts       Defaults to null (=don't filter on exts)
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
		 * @param	string	$path_to_file       Path to file
		 * @return	object
		 */
		public function get_tokens( $path_to_file ) {
			if( !function_exists( 'PHP_TokenStream_Autoload' ) ) {
				require_once 'include/php-token-stream/PHP/Token/Stream/Autoload.php';
			}
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
		 * @param	object	$tokens		Array of token objects
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

				// We have a comment - check to see if we can parse the plugin name based on the WP PHP header file standard
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
		 * @param	array	$tokens		Array of token objects
		 * @param $values
		 * @param $types
		 * @return array
		 */
		public function filter_on_value_and_type( $tokens, $values, $types ) {
			$selection = $this->filter_on_value( $tokens, $values );
			return $this->filter_on_token_type( $selection, $types );
		}


		/**
		 * @param	array	$tokens		Array of token objects
		 * @param $values
		 * @return array|bool
		 */
		public function filter_on_value( $tokens, $values ) {
			// Nothing to filter on - break execution
			if ( ( !is_string( $values ) && !is_array( $values ) ) || ( ( is_string( $values ) && $values === '' ) || ( is_array( $values ) && count( $values ) === 0 ) ) ) {
				return false;
			}

			if ( is_string( $values ) ) {
				$values = explode( ',', $values );
				$values = array_map( 'trim', $values );
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
		 * @param	array	$tokens		Array of token objects
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
				$types = explode( ',', $types );
				$types = array_map( 'trim', $types );
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
		 * @param	array	$tokens		Array of token objects
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
		 * @param	array	$tokens		Array of token objects
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
		 * @param	array	$tokens		Array of token objects
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

			$line      = $tokens[$key]->getLine();
			$functions = $tokens->getFunctions();

			if ( is_array( $functions ) && count( $functions ) > 0 ) {
				foreach ( $functions as $name => $details ) {
					if ( $line >= $details['startLine'] && $line <= $details['endLine'] ) {
						$details['function_name'] = $name;
						$return          = $details;
						break;
					}
				}
				unset( $name, $details );
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
				unset( $the_type );
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
									$details['class_name']  = $class;
									$details['method_name'] = $name;
									$return                 = $details;
									break 2; // Stop the outer foreach
								}
							}
							unset( $name, $details );
						}
					}
					unset( $class, $set_details );
				}
			}

			return $return;
		}
/*
[type (string)] => (string) ‘class’
[name (string)] => (string) ‘mimetypes_link_icons’
[method_name (string)] => (string) ‘init_statics’
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
		 * Get nearest relevant comment above the current line
		 *
		 * First checks directly above the line, if no comment is found there and the line is
		 * part of a function or method call, it will look for the docblock for the function/method.
		 *
		 * If $break_at is defined, it will disregard the docblock for the function/method if it comes
		 * across a string contained in the $break_at array (between the start of the function and the
		 * line in question) to avoid ambiguity in interpreting the comment at the top of the function.
		 *
		 * @param	array			$tokens		Array of token objects
		 * @param	int				$key		Key of the token for which we're trying to get the comment
		 * @return	string
		 */
		public function get_comment_above_line( $tokens, $key ) {

			$current_line_number = $tokens[$key]->getLine();

			$comment = null;

			for ( $i = $key - 1; $i ; $i-- ) {
				if ( !isset( $tokens[$i] ) ) {
					break; // if not set, there will be nothing more before this either
				}
				
				if ( $tokens[$i] instanceof PHP_Token_FUNCTION ||
					$tokens[$i] instanceof PHP_Token_CLASS ||
					$tokens[$i] instanceof PHP_Token_TRAIT ) {
					// Some other trait, class or function, no docblock can be
					// used for the current token
//print 'breaking because of function | class | trait<br>';
					break;
				}

				$line = $tokens[$i]->getLine();

				if ( $line === $current_line_number ||
					( $line <= ( $current_line_number - 1 ) && $tokens[$i] instanceof PHP_Token_WHITESPACE ) ) {
//print 'continue because of same line or prev line, but whitespace<br>';
					continue;
				}
				
				if ( $line < $current_line_number &&
					( !$tokens[$i] instanceof PHP_Token_DOC_COMMENT &&
					!$tokens[$i] instanceof PHP_Token_COMMENT ) ) {
//print 'breaking because doc comment found<br>';
					break;
				}
				
				else {
					$comment[] = $tokens[$i]->__toString();
					// comment found
				}

				// ignore everything on the same line - should this just be < ?
/*				if ( $tokens[$i]->getLine() !== $current_line_number ) {

					// We're trying to get a comment just above the line

					if ( $tokens[$i] instanceof PHP_Token_DOC_COMMENT || $tokens[$i] instanceof PHP_Token_COMMENT ) {
						//verify there is nothing else on the same line
						$comment_line_number = $tokens[$i]->getLine();

/*							for ( $j = $i - 1; $j > $min_line_number; $j-- ) {
							if ( $comment_line_number !== $tokens[$j]->getLine() ) {
								$comment[] = $tokens[$i]->toString(); // capture the comment ?
								break 1;
							}
							else if ( 1 === 1 /*is whitespace or comment* / ) {
							}
						}* /
						//only if there isn't, capture the comment
						$comment[] = $tokens[$i]->__toString(); // capture the comment ?
							// change current line to this one and see if there is another comment line above this one
					}
					else if ( $tokens[$i] instanceof PHP_Token_WHITESPACE ) {
						continue;
					}
					else if ( !isset( $comment ) && ( $function !== false || $method !== false ) ) {
						//ok, no comment found directly above the line
						if ( $function !== false && ( is_string( $function['docblock'] ) && $function['docblock'] !== '' ) ) {
							$docblock = $function['docblock'];
						}
						if ( $method !== false && ( is_string( $method['docblock'] ) && $method['docblock'] !== '' ) ) {
							$docblock = $method['docblock'];
						}


						if ( isset( $docblock ) && isset( $break_at ) ) {
							// No need to look for break at strings if there is no docblock
							// or if the break at strings are not defined...

							$search_to_top = true;
						}
						else {
							// no need to look further, we may have a docblock though
							break;
						}
					}
					else {
						// not in a function or method and not comment or whitespace, so let's break this off
						break;
					}
				}*/
			}

			if ( isset( $comment ) && is_array( $comment ) && count( $comment ) > 0 ) {
				$comment = array_reverse( $comment );
				$comment = implode( "\n\r", $comment );
				return $comment;
			}
			else {
				return false;
			}

				// Isset ?
				// is not in break_at array ?

				// is docblock ? -> parse & test for only internal/ignore, if ok, return, else continue

				// is comment ? -> look if there is nothing else (but whitespace) on the same line, if there is, this comment does not apply to the requested line -> look for doc block at top of function/method
				// -> else -> parse & test for only internal/ignore, if ok, save & continue to check for more lines
				// -> else discard and continue to check for more lines

				// only continue looking for docblock at the top if part of function/method!!




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
/*				if ( $tokens[$i] instanceof PHP_Token_FUNCTION ||
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

			return;*/
		}


		/**
		 * @param $tokens
		 * @param $key
		 *
		 * @return null
		 */public function get_nearest_function_docblock( $tokens, $key ) {
			
			$function = $this->is_within_function( $tokens, $key );

			if ( $function !== false && isset( $function['docblock'] ) ) {
				return $function['docblock'];
			}
			else {
				$method = $this->is_within_method( $tokens, $key );
				if ( $method !== false && isset( $method['docblock'] ) ) {
					return $method['docblock'];
				}
			}
			return null;
		}


		/**
		 *
		 *
		 *
		 * @param $tokens
		 * @param $key
		 * @param $break_at
		 *
		 * @return bool|null    boolean indication of whether docblock is ambiguous or null if
		 *                        $break_at does not contain valid value(s)
		 */
		public function is_docblock_ambiguous( $tokens, $key, $break_at ) {

			// Make sure $break_at is usable
			if ( isset( $break_at ) && ( is_string( $break_at ) && $break_at !== '' ) ) {
				$break_at = explode( ',', $break_at );
				$break_at = array_map( 'trim', $break_at );
			}

			$ambiguous = null;

			if ( isset( $break_at ) && ( is_array( $break_at ) && count( $break_at ) > 0 ) ) {

				// Is the token at $key part of a function or method ?
				$min_line_number = 0;
				$function        = $this->is_within_function( $tokens, $key );
				$method          = false;

				if ( $function !== false && isset( $function['startLine'] ) ) {
					$min_line_number = $function['startLine'];
				}
				else {
					$method = $this->is_within_method( $tokens, $key );
					if ( $method !== false && isset( $method['startLine'] ) ) {
						$min_line_number = $method['startLine'];
					}
				}
//$this->print_to_table_helper( $tokens, $min_line_number, $key );
				if ( $function !== false || $method !== false ) {

					for ( $i = ( $key - 1 ); $tokens[$i]->getLine() >= $min_line_number; $i-- ) {

						if ( !isset( $tokens[$i] ) ) {
							$ambiguous = false;
//print '<span style="font-weight: bold; color: purple;">not ambiguous - token ' . $i . 'not set</span><br />';
							break; // if not set, there will be nothing more before this either
						}

						if ( isset( $break_at ) && in_array( $tokens[$i]->__toString(), $break_at ) ) {
							// ok, encountered an ambiguous tag
							$ambiguous = true;
//print '<span style="font-weight: bold; color: purple;">ambiguous - token "' . $tokens[$i]->__toString() . '" in_array break_at</span><br />';
							break;
						}
					}
				}
				else {
					// not part of function or method, so always ambiguous.
//print '<span style="font-weight: bold; color: purple;">ambiguous - not part of function or method</span><br />';
					$ambiguous = true;
				}
			}
			return $ambiguous;
		}


		/**
		 * @param $tokens
		 * @param $key
		 * @param $break_at
		 * @param $tags
		 * @param $not
		 *
		 * @return array|false|null
		 */
		public function get_parsed_nearest_relevant_comment( $tokens, $key, $break_at, $tags, $not/*, $hook_name*/ ) {
//print str_pad( '<span style="font-weight: bold; color: blue;">' . $hook_name . '</span> ', 150, '=' );
			$parsed  = null;
			$comment = $this->get_comment_above_line( $tokens, $key );
//pr_var( $comment, 'comment from above line', true );
			if ( isset( $comment ) && $comment !== '' ) {
				$parsed = $this->parse_comment( $comment, $tags, $not );
			}
//pr_var( $parsed, 'parsed comment from above line', true );
			if ( !isset( $parsed ) || $parsed === false ) {
				$docblock = $this->get_nearest_function_docblock( $tokens, $key );
//pr_var( $docblock, 'function docblock', true );
				$ambiguous = $this->is_docblock_ambiguous( $tokens, $key, $break_at );
//pr_var( $ambiguous, 'docblock ambiguous ?', true );
				if ( !isset( $ambiguous ) || $ambiguous === false ) {
					$parsed = $this->parse_comment( $docblock, $tags, $not );
//pr_var( $parsed, 'parsed comment from docblock', true );
				}
			}
			return $parsed;
		}



		/**
		 * Retrieve the nearest comment and parse it as a phpDoc style comment
		 *
		 * @param   array              $tokens
		 * @param   int                $key
		 * @param   array|string|null  $tags
		 * @param   array|string|null  $not
		 * @return  array
		 */
/*		public function parse_nearest_comment( $tokens, $key, $tags = null, $not = null ) {
			return $this->parse_comment( $this->get_nearest_comment( $tokens, $key ), $tags, $not );
		}
*/

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
		 * Parse a comment to it's individual parts
		 *
		 * Superfluous whitespace will be removed from the resulting values
		 * Note: new lines are *not* removed from values, other superfluous whitespace is.
		 * The reason for this is to allow people to use nl2br for displaying the comments.
		 * If you don't want the new lines, just str_replace() them in your own code.
		 *
		 * @todo may be figure out a way to deal with {inline @link} comments ? Probably not needed
		 *
		 * @param	string				$string		Comment string
		 * @param	array|string|null	$tags		[optional] A phpDoc tag or an array of phpDocs tags to filter
		 *											for (remove all else )
		 * @param	array|string|null	$not		[optional] A phpDoc tag or an array of phpDocs tags to filter
		 * 											out (remove from the result )
		 *                                          If this parameter is not set, the default will be used
		 * @return	array|false			Array containing the filtered parsed comment parts
		 */
		public function parse_comment( $string, $tags = null, $not = null ) {

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
					else if ( $match[1] !== '' && $match[2] !== '' ) {
						$parsed_line = $this->comment_parse_line( $match[2], $match[1] );
						if ( $parsed_line !== false ) {
							$comment[$match[1]][] = $parsed_line;
						}
					}
					else if ( $match[1] !== '' ) {
						$comment[$match[1]][] = '';
					}
				}
				unset( $match );


				// Filter the results to only contain specific tags
				if ( isset( $tags ) && ( ( is_string( $tags ) && $tags !== '' ) || ( is_array( $tags ) && count( $tags ) > 0 ) ) ) {
					if ( is_string( $tags ) ) {
						$tags = explode( ',', $tags );
						$tags = array_map( 'trim', $tags );
					}
					$tags    = array_flip( $tags );
					$comment = array_intersect_key( $comment, $tags );
				}

				// Filter the results and remove the tags to be excluded
				if ( isset( $not ) && ( is_string( $not ) && $not !== '' ) ) {
					if ( is_string( $not ) ) {
						$not = explode( ',', $not );
						$not = array_map( 'trim', $not );
					}
				}
				if ( !isset( $not ) ||  ( !is_array( $not ) || count( $not ) === 0 ) ) {
					$not = $this->default_not;
				}
				$not     = array_flip( $not );
				$comment = array_diff_key( $comment, $not );



				// Only return the array if there is anything left of it after the filter actions, otherwise default to false at the end
				if ( count( $comment ) > 0 ) {
					return $comment;
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
		function print_to_table_helper( $tokens, $start_position = null, $end_position = null ) {

			$start   = ( isset( $start_position ) ? (int) $start_position : 0 );
			$end     = ( isset( $end_position ) ? (int) $end_position : ( count( $tokens ) - 1 ) );
			$reverse = false;

			if ( $end < $start ) {
				$reverse = true;
				$tmp   = $start;
				$start = $end;
				$end   = $tmp;
				unset( $tmp );
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