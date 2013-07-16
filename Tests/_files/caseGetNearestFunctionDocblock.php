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
		 * @api A description for the action
		 * @api string	A description of the parameters passed
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_docblock( $param ) {

			$param = do_action( 'top_docblock', $param );

			$param = do_action( 'top_docblock_ambiguous', $param );

			return $param;
		}


		function test_no_docblock( $param ) {

			$param = do_action_ref_array( 'top_no_docblock', $param );

			$param = do_action_ref_array( 'top_no_docblock_ambiguous', $param );

			return $param;
		}

	} /* End of class */
} /* End of class-exists wrapper */
?>