<?php

/**
 * This is an example class for test purposes
 * @package CamelCase
 */
class CamelCase {

	public $property1;

	public $property2;


	/**
	 * @param   array       $property1
	 * @param   array|null  $property2
	 */
	public function __construct( $property1, $property2 = null ) {
		$this->property1 = (array) $property1;
		$this->property2 = $property2;
	}

	public function loops() {
		if ( is_array( $this->property1 ) && count( $this->property1 ) > 0 ) {
			foreach ( $this->property1 as $k => $v ) {
				if ( $k === $v ) {
					$this->property1[$k] = (int) $v * 2;
				}
			}
		}
		if ( is_array( $this->property2 ) && count( $this->property2 ) > 0 ) {
			for ( $i = 0; $i < count( $this->property2 ); $i++ ) {
				echo 'We are at ' . '(int)' . ' ' . $i;
			}
		}
		while ( current( $this->property1 ) && 1 === 1 ) {
			print 'We are at ' . key( $this->property1 );
			next( $this->property1 );
		}
	}
}

/**
 * This is an example function
 * 
 * @param $param
 */
function example( $param ) {
	print $param;
}

$test = new CamelCase( $testing );

?>