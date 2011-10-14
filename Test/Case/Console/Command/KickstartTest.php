<?php

App::uses('Shell', 'Console');
App::uses('KickstartShell', 'Kickstart.Console/Command');
App::uses('KickstartCommandTask', 'Kickstart.Console/Command/Task');

/**
 *
 * @property KickstartShell $Shell
 */
class KickstartShellTestCase extends CakeTestCase {

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        CakePlugin::load('Kickstart');

        $out = $this->getMock('ConsoleOutput', array(), array(), '', false);
        $in = $this->getMock('ConsoleInput', array(), array(), '', false);

        $methods = array('in', 'out', 'err', 'error', 'hr', 'createFile', '__exec');
        $this->Shell = $this->getMock('KickstartShell', $methods, array($out, $out, $in));
        $this->Shell->KickstartCommand = $this->getMock('KickstartCommandTask', array('in', 'out', 'err', 'error', 'hr', 'createFile', '_exec', '_chdir'), array($out, $out, $in));

        $this->Shell->params['app'] = APP_DIR;
        $this->Shell->params['working'] = APP;
        $this->Shell->args = array('Kickstart.test');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() {
        parent::tearDown();
        unset($this->Shell);
        CakePlugin::unload();
    }

    // =========================================================================

    public function testRead() {
        $this->Shell->command = 'read';
        $this->Shell->expects($this->once())->method('out');
        $this->Shell->read();
        $this->assertIdentical($this->Shell->steps, array(
            array('exec' => array('ls -l')),
            array('exec' => 'pwd'),
            array('exec' => 'date'),
        ));
    }

    public function testRead_plugin_failback() {
        $this->Shell->command = 'read';
        $this->Shell->args = array('test');
        $this->Shell->expects($this->once())->method('out');
        $this->Shell->read();
        $this->assertIdentical($this->Shell->steps, array(
            array('exec' => array('ls -l')),
            array('exec' => 'pwd'),
            array('exec' => 'date'),
        ));
    }

    // =========================================================================

    public function testRun() {
        $this->Shell->command = 'run';
        //
        $this->Shell->expects($this->at(0))->method('out')
                ->with("---\nexec: \n  - ls -l\n");
        $this->Shell->expects($this->at(1))->method('in')
                ->with('run this command?', array('y', 'N'), 'N')
                ->will($this->returnCallback(array($this, '_returnValueAtRun')));
        $this->Shell->KickstartCommand->expects($this->at(0))->method('_exec')
                ->with('ls -l');
        //
        $this->Shell->expects($this->at(2))->method('out')
                ->with("---\nexec: pwd\n");
        $this->Shell->expects($this->at(3))->method('in')
                ->with('run this command?', array('y', 'N'), 'N')
                ->will($this->returnCallback(array($this, '_returnValueAtRun')));
        $this->Shell->KickstartCommand->expects($this->at(1))->method('_exec')
                ->with('pwd');
        //
        $this->Shell->expects($this->at(4))->method('out')
                ->with("---\nexec: date\n");
        $this->Shell->expects($this->at(5))->method('in')
                ->with('run this command?', array('y', 'N'), 'N')
                ->will($this->returnCallback(array($this, '_returnValueAtRun')));
        $this->Shell->KickstartCommand->expects($this->at(2))->method('_exec')
                ->with('date'); //*/

        $this->Shell->run();
    }

    public function _returnValueAtRun($params) {
        return 'y';
    }

}
