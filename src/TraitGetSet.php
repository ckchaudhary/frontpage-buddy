<?php
/**
 * Reusable singleton trait
 *
 * @package FrontPageBuddy
 */

namespace RB\FrontPageBuddy;

defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( '\RB\FrontPageBuddy\TraitGetSet' ) ) {

	/**
	 * Magic __get and __set methods.
	 *
	 * @package FrontPageBuddy
	 */
	trait TraitGetSet {

		/**
		 * Magic __get method.
		 * It first tries to find a function get_property_name and call that if found.
		 * Otherwise, it tries to find a property $property_name and returns its value if found.
		 * Otherwise it returns null.
		 *
		 * @param string $property_name name of the property.
		 * @return mixed|null null if property is not found.
		 */
		public function __get( $property_name ) {
			$method = 'get_' . $property_name;
			if ( method_exists( $this, $method ) ) {
				return $this->{$method}();
			} elseif ( property_exists( $this, $property_name ) ) {
				return $this->$property_name;
			}

			return null;
		}

		/**
		 * Magic __set method.
		 * It tries to find a function set_property_name and call that if found.
		 * Otherwise, an exception is thrown.
		 *
		 * @param string $property_name name of the property.
		 * @param mixed  $value value to be set.
		 *
		 * @return mixed
		 *
		 * @throws \Exception When a set_$property_name function was not found.
		 */
		public function __set( $property_name, $value ) {
			$method = 'set_' . $property_name;
			if ( method_exists( $this, $method ) ) {
				return $this->{$method}( $value );
			}

			throw new \Exception( 'No setter method defined.', 500 );
		}
	}
}
