<?php
class AllMediaTest extends CakeTestSuite {
    public static function suite() {
    	
    	$testsDir = CakePlugin::path('Media') . 'Test'.DS;
    	
        $suite = new CakeTestSuite('All media plugin tests');
        
        $suite->addTestDirectory($testsDir . 'Case' . DS . 'Lib');
        $suite->addTestDirectory($testsDir . 'Case' . DS . 'Model' . DS . 'Behavior');
        return $suite;
    }
}

