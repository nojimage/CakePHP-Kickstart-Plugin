<?php

/**
 * run all tests
 */
class AllTest extends PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new CakeTestSuite('All tests');
		$suite->addTestDirectoryRecursive(dirname(__FILE__));
		return $suite;
	}

}
