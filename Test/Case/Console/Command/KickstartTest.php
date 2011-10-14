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
