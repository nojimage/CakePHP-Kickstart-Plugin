<?php

/**
 * run all tests
 */
class AllKickstartTestsTest extends PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new CakeTestSuite('All Kickstart plugin tests');
		$suite->addTestDirectoryRecursive(dirname(__FILE__));
		return $suite;
	}

}
