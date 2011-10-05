<?php

App::uses('ShellDispatcher', 'Console');
App::uses('KickstartCommandTask', 'Kickstart.Console/Command/Task');

/**
 *
 * @property KickstartCommondTask $Task
 */
class KickstartCommondTaskTestCase extends CakeTestCase {

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $out = $this->getMock('ConsoleOutput', array(), array(), '', false);
        $in = $this->getMock('ConsoleInput', array(), array(), '', false);

        $methods = array('in', 'out', 'err', 'error', '_exec', '_chdir');
        $this->Task = $this->getMock('KickstartCommandTask', $methods, array($out, $out, $in));

        $this->Task->params['app'] = APP_DIR;
        $this->Task->params['working'] = APP;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() {
        parent::tearDown();
        unset($this->Task);
        CakePlugin::unload();
    }

    // =========================================================================

    public function testParsePath_replace_ROOT() {
        $this->assertEquals(ROOT . '/app/webroot', $this->Task->parsePath('$ROOT/app/webroot'));
    }

    public function testParsePath_not_replaced_ROOT() {
        $this->assertEquals('ROOT/app/webroot', $this->Task->parsePath('ROOT/app/webroot'));
    }

    public function testParsePath_replaced_APP() {
        $this->assertEquals(APP . 'webroot', $this->Task->parsePath('{$APP}/webroot'));
    }

    public function testParsePath_not_replaced_APP() {
        $this->assertEquals('APP/webroot', $this->Task->parsePath('APP/webroot'));
    }

    public function testParsePath_uri() {
        $this->assertEquals('https://github.com/cakephp/debug_kit.git', $this->Task->parsePath('https://github.com/cakephp/debug_kit.git'));
        $this->assertEquals('git://github.com/cakephp/debug_kit.git', $this->Task->parsePath('git://github.com/cakephp/debug_kit.git'));
        $this->assertEquals('/path_to/any/file', $this->Task->parsePath('/path_to/any//file'));
    }

    // =========================================================================

    public function test_exec() {
        $this->Shell->expectOnce('_exec', array('some command'));
        $this->Shell->exec('some command');
    }

    public function test_exec_array() {
        $this->Shell->expectAt(0, '_exec', array('some command'));
        $this->Shell->expectAt(1, '_exec', array('any command'));
        $this->Shell->expectCallCount('_exec', 2);
        $this->Shell->exec(array('some command', 'any command'));
    }

    // =========================================================================

    public function test_bake() {
        $this->Shell->expectOnce('_exec', array('php ' . CAKE . 'console/cake.php bake db_config -app ' . $this->Shell->params['app']));
        $this->Shell->bake('db_config');
    }

    public function test_bake_with_app_params_override() {
        $this->Shell->expectOnce('_exec', array('php ' . CAKE . 'console/cake.php bake db_config -app foobar'));
        $this->Shell->bake('db_config -app foobar');
    }

    // =========================================================================

    public function test_get_simpletest() {
        $this->Shell->expectOnce('_chdir', array(ROOT . DS . 'vendors' . DS));
        $this->Shell->expectOnce('_exec', array('curl -L sourceforge.net/projects/simpletest/files/simpletest/simpletest_1.0.1/simpletest_1.0.1.tar.gz/download | tar xz'));
        $this->Shell->get_simpletest(1);
    }

    public function test_get_simpletest_with_target() {
        $this->Shell->expectOnce('_chdir', array(APP . 'foobar' . DS));
        $this->Shell->expectOnce('_exec', array('curl -L sourceforge.net/projects/simpletest/files/simpletest/simpletest_1.0.1/simpletest_1.0.1.tar.gz/download | tar xz'));
        $this->Shell->get_simpletest(array('target' => '{$APP}/foobar'));
    }

    // =========================================================================
    public function test_git_submodule() {
        $this->Shell->expectOnce('_exec', array('git submodule add repo app/plugins'));
        $this->Shell->git_submodule(array(
            'repo' => 'repo',
            'target' => '{$APP}/plugins'
        ));
    }

    // =========================================================================
    public function test_generatefile() {

        $this->Shell->expectAt(0, 'in', array('please input "arg2"', null, null));
        $this->Shell->setReturnValueAt(0, 'in', 'value2');
        $this->Shell->expectAt(1, 'in', array('arg3 message.', array('value1', 'value2', 'value3'), 'value1'));
        $this->Shell->setReturnValueAt(1, 'in', 'value3');

        $this->Shell->Template->expectOnce('generate', array('generatefiles', 'config/core.php', array(
                'arg1' => 'value1',
                'arg2' => 'value2',
                'arg3' => 'value3',
                )));
        $this->Shell->Template->setReturnValueAt(0, 'generate', 'template result');
        $this->Shell->expectOnce('createFile', array(APP . 'config/core.php', 'template result'));

        $this->Shell->generatefile(array(
            'template' => 'config/core.php',
            'target' => '$APP/config/core.php',
            'vars' => array(
                'arg1' => 'value1',
            ),
            'ask' => array(
                'arg2' => true,
                'arg3' => array(
                    'message' => 'arg3 message.',
                    'options' => array('value1', 'value2', 'value3'),
                    'default' => 'value1',
                ),
            ),
        ));
    }

}
