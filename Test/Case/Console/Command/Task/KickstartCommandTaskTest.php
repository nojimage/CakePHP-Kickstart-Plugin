<?php

App::uses('TemplateTask', 'Console/Command/Task');
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

        $methods = array('in', 'out', 'err', 'error', '_exec', '_chdir', 'createFile');
        $this->Task = $this->getMock('KickstartCommandTask', $methods, array($out, $out, $in));
        $this->Task->Template = $this->getMock('TemplateTask', array('in', 'out', 'err', 'error', 'generate'), array($out, $out, $in));

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

    public function testExec() {
        $this->Task->expects($this->once())->method('_exec')->with('some command');
        $this->Task->exec('some command');
    }

    public function testExec_param_is_array() {
        $this->Task->expects($this->at(0))->method('_exec')->with('some command');
        $this->Task->expects($this->at(1))->method('_exec')->with('any command');
        $this->Task->exec(array('some command', 'any command'));
    }

    // =========================================================================

    public function testBake() {
        $this->Task->expects($this->once())->method('_exec')->with('php ' . APP . 'Console/cake.php bake db_config -app ' . $this->Task->params['app']);
        $this->Task->bake('db_config');
    }

    public function testBake_with_app_params_override() {
        $this->Task->expects($this->once())->method('_exec')->with('php ' . APP . 'Console/cake.php bake db_config -app foobar');
        $this->Task->bake('db_config -app foobar');
    }

    // =========================================================================

    public function testGetSimpletest() {
        $this->Task->expects($this->once())->method('_chdir')->with(ROOT . DS . 'vendors' . DS);
        $this->Task->expects($this->once())->method('_exec')->with('curl -L sourceforge.net/projects/simpletest/files/simpletest/simpletest_1.0.1/simpletest_1.0.1.tar.gz/download | tar xz');
        $this->Task->get_simpletest(1);
    }

    public function testGetSimpletest_with_target() {
        $this->Task->expects($this->once())->method('_chdir')->with(APP . 'foobar' . DS);
        $this->Task->expects($this->once())->method('_exec')->with('curl -L sourceforge.net/projects/simpletest/files/simpletest/simpletest_1.0.1/simpletest_1.0.1.tar.gz/download | tar xz');
        $this->Task->get_simpletest(array('target' => '{$APP}/foobar'));
    }

    // =========================================================================
    public function testGitSubmodule() {
        $this->Task->expects($this->once())->method('_exec')->with('git submodule add repo app/plugins');
        $this->Task->git_submodule(array(
            'repo' => 'repo',
            'target' => '{$APP}/plugins'
        ));
    }

    // =========================================================================
    public function testGeneratefile() {

        $this->Task->expects($this->at(0))->method('in')
                ->with('please input "arg2"', null, null)
                ->will($this->returnCallback(array($this, 'returnInValue')));

        $this->Task->expects($this->at(1))->method('in')
                ->with('arg3 message.', array('value1', 'value2', 'value3'), 'value1')
                ->will($this->returnCallback(array($this, 'returnInValue')));

        $this->Task->Template->expects($this->at(0))->method('generate')
                ->with('generatefiles', 'Config/core.php', array(
                    'arg1' => 'value1',
                    'arg2' => 'value2',
                    'arg3' => 'value3',
                ))
                ->will($this->returnValue('template result'));

        $this->Task->expects($this->once())->method('createFile')
                ->with(APP . 'Config/core.php', 'template result');
        $this->Task->generatefile(array(
            'template' => 'Config/core.php',
            'target' => '$APP/Config/core.php',
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

    public function returnInValue($params) {
        if ($params === 'please input "arg2"') {
            return 'value2';
        }

        return 'value3';
    }

}
