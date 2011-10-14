<?php

/**
 * Kickstart Shell
 *
 * CakePHP 1.3
 * PHP versions 5
 *
 * Copyright 2011, nojimage (http://php-tips.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @version   1.0
 * @author    nojimage <nojimage at gmail.com>
 * @copyright 2011 nojimage (http://php-tips.com/)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link    　http://php-tips.com/
 *
 * =====
 * Usage:
 *
 * in console
 *
 *  cake kickstart
 *
 */
App::uses('Spyc', 'Kickstart.Vendor');

/**
 *
 * @property KickstartCommand $KickstartCommand
 */
class KickstartShell extends Shell {

    public $uses = array();
    public $tasks = array('KickstartCommand');
    public $steps = array();
    protected $_loaded = array();
    protected $_pwd = '';

    /**
     *
     */
    public function startup() {

        parent::startup();

        if (!empty($this->params['y']) && !is_bool($this->params['y'])) {
            array_unshift($this->args, $this->params['y']);
            $this->interactive = false;
        }
    }

    /**
     *
     */
    public function main() {
        $this->run();
    }

    /**
     *
     */
    public function help() {
        $head = "-----------------------------------------------\n";
        $head .= "Usage: cake kickstart <command> <params1> <params2>...\n";
        $head .= "-----------------------------------------------\n";
        $head .= "Commands:\n";

        $commands = array(
            'run' => "run <-y> <config1> <config2>\n",
            "\t" . "execute kickstart scripts.",
            'help' => "help [<command>]\n" .
            "\t" . "Displays this help message, or a message on a specific command.",
        );

        $this->out($head);

        if (!isset($this->args[0])) {

            foreach ($commands as $cmd) {
                $this->out("{$cmd}\n\n");
            }
        } elseif (isset($commands[strtolower($this->args[0])])) {

            $this->out($commands[strtolower($this->args[0])] . "\n\n");
        } else {

            $this->out(sprintf(__d('kickstart', "Command '%s' not found"), $this->args[0]));
        }
    }

    /**
     * execute kickstart steps
     */
    public function run() {
        // read steps
        $this->read(false);

        // execute steps
        foreach ($this->steps as $step) {

            if (!method_exists($this->KickstartCommand, key($step))) {
                continue;
            }

            $this->out(Spyc::YAMLDump($step, true));

            if (strtolower($this->in(__d('kickstart', 'run this command?'), array('y', 'N'), 'N')) === 'y'
                    || !$this->interactive) {

                $this->_pwd = getcwd();
                chdir(ROOT);

                call_user_func(array($this->KickstartCommand, key($step)), current($step));

                chdir($this->_pwd);
            }
        }
    }

    /**
     *
     * @param bool $output
     */
    public function read($output = true) {
        if (empty($this->args)) {
            $this->args[0] = 'Kickstart.kickstart';
        }
        foreach ($this->args as $config) {
            $this->_loadSteps($config);
        }
        if ($output) {
            $this->out(Spyc::YAMLDump($this->steps, true));
        }
    }

    /**
     * read configuration file
     *
     * @param string $config
     */
    protected function _loadSteps($fileName) {
        $plugin = $pluginPath = $found = false;
        list($plugin, $fileName) = pluginSplit($fileName);

        if (!empty($plugin)) {
            $pluginPath = App::pluginPath($plugin);
        } else {
            $pluginPath = App::pluginPath('Kickstart');
        }
        $pos = strpos($fileName, '..');

        if ($pos === false) {
            if (!empty($plugin) && !empty($pluginPath) && file_exists($pluginPath . 'Config' . DS . $fileName . '.yml')) {
                $fileName = $pluginPath . 'Config' . DS . $fileName . '.yml';
                $found = true;
            } elseif (file_exists(APP . 'Config' . DS . $fileName . '.yml')) {
                $fileName = APP . 'Config' . DS . $fileName . '.yml';
                $found = true;
            } elseif (!empty($pluginPath) && file_exists($pluginPath . 'Config' . DS . $fileName . '.yml')) {
                $fileName = $pluginPath . 'Config' . DS . $fileName . '.yml';
                $found = true;
            } elseif (file_exists(ROOT . $fileName . '.yml')) {
                $fileName = ROOT . $fileName . '.yml';
                $found = true;
            }
        }

        if (!$found || in_array($fileName, $this->_loaded)) {
            return false;
        }

        $this->_loaded[] = $fileName;

        $_steps = Spyc::YAMLLoad($fileName);
        foreach ($_steps as $key => $val) {
            if (strtolower($key) === 'include') {
                // include another file
                if (!is_array($val)) {
                    $val = array($val);
                }
                array_walk($val, array($this, '_loadSteps'));
            } else if (is_int($key)) {
                $this->steps[] = $val;
            } else {
                $this->steps[] = array($key => $val);
            }
        }
    }

}
