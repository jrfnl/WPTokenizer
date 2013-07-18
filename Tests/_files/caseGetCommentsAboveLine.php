<?php
/**
 * This is an example class for test purposes
 * @package CamelCase
 */

if ( !class_exists( 'CamelCase' ) ) {

	class CamelCase {

		/**
		 * Test method
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_inline( $param ) {

			$param = apply_filters( 'no_comment', $param );

			// This is an inline slash comment
			$param = apply_filters( 'inline_slash', $param );

			# This is an inline hash comment
			$param = apply_filters( 'inline_hash', $param );

			/* This is an inline star comment */
			$param = apply_filters( 'inline_starred', $param );

			/*
			 This is an inline comment
			 It spans two lines
			 */
			$param = apply_filters( 'inline_starred_multi-line', $param );

			/* Add filter hook for param
			   @api string	$new_param Allows a developer to filter the param string
			   before it is send to the screen */
			$param = apply_filters( 'inline_star_with_tag', $param );

			/**
			 * This is an inline DocBlock comment
			 */
			$param = apply_filters( 'inline_docblock', $param );

			/**
			 * This is an inline DocBlock comment
			 * @api	string	$param	param description
			 */
			$param = apply_filters( 'inline_docblock_with_tag', $param );

			return $param;
		}


		function test_multi_line_not_docblock( $param ) {

			// This line 1 of a slashed multi-line comment
			// This line 2 of a slashed multi-line comment
			$param = apply_filters( 'filter_multi-line_slash', $param );

			# This line 1 of a hashed multi-line comment
			# This line 2 of a hashed multi-line comment
			$param = apply_filters( 'filter_multi-line_hash', $param );

			/* This line 1 of a star-non DocBlock multi-line comment */
			/* This line 2 of a star-non DocBlock comment */
			$param = apply_filters( 'filter_multi-line_star', $param );

			// This line 1 of a slashed multi-line comment
			# This line 2 of a hashed multi-line comment
			/* This line 3 of a star-non DocBlock comment */
			$param = apply_filters( 'filter_multi-line_mixed', $param );

			return $param;
		}


		function test_comment_not_alone_on_line( $param ) {

			$a     = 1 + 2 + 3; // This comment belongs to this line, not to the line below
			$param = apply_filters( 'filter_comment_not_alone_on_line', $param );

			return $param;
		}

	} /* End of class */
} /* End of class-exists wrapper */
?>