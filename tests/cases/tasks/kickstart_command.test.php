<?php

App::import('Shell', array('Shell'));

if (!defined('DISABLE_AUTO_DISPATCH')) {
    define('DISABLE_AUTO_DISPATCH', true);
}

if (!class_exists('ShellDispatcher')) {
    ob_start();
    $argv = false;
    require CAKE . 'console' . DS . 'cake.php';
    ob_end_clean();
}

Mock::generatePartial(
                'ShellDispatcher', 'TestKickstartCommandMockShellDispatcher',
                array('getInput', 'stdout', 'stderr', '_stop', '_initEnvironment', 'dispatch')
);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'vendors' . DS . 'shells' . DS . 'tasks' . DS . 'kickstart_command.php';

class TestKickstartCommond extends KickstartCommandTask {

    public function parsePath($pathString) {
        return parent::_parsePath($pathString);
    }

}

Mock::generatePartial(
                'TestKickstartCommond', 'MockKickstartCommond',
                array('in', 'out', 'hr', 'createFile', 'error', 'err', '_exec', '_chdir')
);

/**
 *
 * @property KickstartCommondTask $Shell
 */
class KickstartCommondTaskTestCase extends CakeTestCase {

    public function startTest($method) {
        $this->Dispatcher = new TestKickstartCommandMockShellDispatcher();
        $this->Shell = new MockKickstartCommond($this->Dispatcher);
        $this->Shell->params['app'] = APP_DIR;
        $this->Shell->params['working'] = APP;
        $this->Shell->Dispatch = $this->Dispatcher;
    }

    public function endTest($method) {
        unset($this->Shell);
        unset($this->Dispatcher);
        ClassRegistry::flush();
    }

    // =========================================================================

    public function test_parsePath() {
        $this->assertIdentical($this->Shell->parsePath('$ROOT/app/webroot'), ROOT . '/app/webroot', 'replace ROOT: %s');
        $this->assertIdentical($this->Shell->parsePath('ROOT/app/webroot'), 'ROOT/app/webroot', 'not replaced ROOT: %s');
        $this->assertIdentical($this->Shell->parsePath('{$APP}/webroot'), APP . 'webroot', 'replace APP: %s');
        $this->assertIdentical($this->Shell->parsePath('APP/webroot'), 'APP/webroot', 'not replaced APP: %s');
        $this->assertIdentical($this->Shell->parsePath('${CONFIGS}'), APP . 'config/', 'replaced CONFIGS: %s');
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

}
