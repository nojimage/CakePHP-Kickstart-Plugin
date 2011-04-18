<?php

App::import('Shell', array('Shell', 'Kickstart.Kickstart'));

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
                'ShellDispatcher', 'TestKickstartShellMockShellDispatcher',
                array('getInput', 'stdout', 'stderr', '_stop', '_initEnvironment', 'dispatch')
);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'vendors' . DS . 'shells' . DS . 'tasks' . DS . 'kickstart_command.php';

class TestKickstartShellKickstartCommondTask extends KickstartCommandTask {

}

Mock::generatePartial(
                'TestKickstartShellKickstartCommondTask', 'MockKickstartShellKickstartCommondTask',
                array('in', 'out', 'hr', 'createFile', 'error', 'err', '_exec', '_chdir')
);

class TestKickstartShell extends KickstartShell {

    public function parsePath($pathString) {
        return parent::_parsePath($pathString);
    }

}

Mock::generatePartial(
                'TestKickstartShell', 'MockKickstartShell',
                array('in', 'out', 'hr', 'createFile', 'error', 'err', '__exec')
);

/**
 *
 * @property ShellDispatcher $Dispatcher
 * @property TestKickstartShell $Shell
 */
class KickstartShellTestCase extends CakeTestCase {

    public function startTest($method) {
        $this->Dispatcher = new TestKickstartShellMockShellDispatcher();
        $this->Shell = new MockKickstartShell($this->Dispatcher);
        $this->Shell->params['app'] = APP_DIR;
        $this->Shell->params['working'] = APP;
        $this->Shell->args = array('kickstart.test');
        $this->Shell->Dispatch = $this->Dispatcher;
        $this->Shell->KickstartCommand = new MockKickstartShellKickstartCommondTask();
    }

    public function endTest($method) {
        unset($this->Shell);
        unset($this->Dispatcher);
        ClassRegistry::flush();
    }

    // =========================================================================

    public function test_read() {
        $this->Shell->command = 'read';
        $this->Shell->expectOnce('out');
        $this->Shell->read();
        $this->assertIdentical($this->Shell->steps, array(
            array('exec' => array('ls -l')),
            array('exec' => 'pwd'),
            array('exec' => 'date'),
        ));
    }

    // =========================================================================

    public function test_run() {
        $this->Shell->command = 'run';
        //
        $this->Shell->expectAt(0, 'out', array("---\nexec: \n  - ls -l\n"));
        $this->Shell->expectAt(0, 'in', array('run this command?', array('y', 'N'), 'N'));
        $this->Shell->setReturnValueAt(0, 'in', 'y');
        $this->Shell->KickstartCommand->expectAt(0, '_exec', array('ls -l'));
        //
        $this->Shell->expectAt(1, 'out', array("---\nexec: pwd\n"));
        $this->Shell->expectAt(1, 'in', array('run this command?', array('y', 'N'), 'N'));
        $this->Shell->setReturnValueAt(1, 'in', 'y');
        $this->Shell->KickstartCommand->expectAt(1, '_exec', array('pwd'));
        //
        $this->Shell->expectAt(2, 'out', array("---\nexec: date\n"));
        $this->Shell->expectAt(2, 'in', array('run this command?', array('y', 'N'), 'N'));
        $this->Shell->setReturnValueAt(2, 'in', 'y');
        $this->Shell->KickstartCommand->expectAt(2, '_exec', array('date'));

        $this->Shell->run();
    }

}
