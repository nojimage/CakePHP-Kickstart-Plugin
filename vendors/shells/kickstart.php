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
App::import('Vendor', 'Kickstart.Spyc', false);

class KickstartShell extends Shell {

    public $uses = array();
    public $steps = array();
    protected $_loaded = array();
    protected $_constants = array();
    protected $_pwd = '';

    /**
     *
     */
    public function startup() {

        parent::startup();

        // get constants
        $constants = get_defined_constants(true);
        $this->_constants = $constants['user'];
        // overrride params
        $this->_constants['ROOT'] = isset($this->params['root']) ? $this->params['root'] : $this->_constants['ROOT'];
        $this->_constants['APP'] = isset($this->params['working']) ? $this->params['working'] : $this->_constants['APP'];

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

            $this->out(sprintf(__("Command '%s' not found", true), $this->args[0]));
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
            if (!method_exists($this, '_' . key($step))) {
                continue;
            }
            $this->out(Spyc::YAMLDump($step, true));
            if (strtolower($this->in(__d('kickstart', 'run this command?', true), array('y', 'N'), 'N')) === 'y'
                    || !$this->interactive) {

                $this->_pwd = getcwd();
                chdir(ROOT);

                call_user_func(array($this, '_' . key($step)), current($step));

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
            $this->args[0] = 'kickstart.kickstart';
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

        if ($plugin) {
            $pluginPath = App::pluginPath($plugin);
        }
        $pos = strpos($fileName, '..');

        if ($pos === false) {
            if ($pluginPath && file_exists($pluginPath . 'config' . DS . $fileName . '.yml')) {
                $fileName = $pluginPath . 'config' . DS . $fileName . '.yml';
                $found = true;
            } elseif (file_exists(CONFIGS . $fileName . '.yml')) {
                $fileName = CONFIGS . $fileName . '.yml';
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

    /**
     * parse path string
     *
     * @param string $pathString
     * @return string
     */
    protected function _parsePath($pathString) {
        $pathString = preg_replace_callback('/\$([a-z0-9_]+)|{\$([a-z0-9_]+)}|\${([a-z0-9_]+)}/i', array($this, '_variableReplace'), $pathString);
        return str_replace(DS . DS, DS, $pathString);
    }

    /**
     * @param array $var
     * @return string
     */
    protected function _variableReplace($var) {
        return @$this->_constants[array_pop($var)];
    }

    // =========================================================================
    /**
     *
     * @param array $commands
     */
    protected function _exec($commands) {
        if (!is_array($commands)) {
            $commands = array($commands);
        }
        foreach ($commands as $cmd) {
            $this->__exec($cmd);
        }
    }

    /**
     * exec command
     *
     * @param string $command
     * @return array
     */
    protected function __exec($command) {
        passthru($this->_parsePath($command));
    }

    /**
     * change dir
     *
     * @param string $dir
     */
    protected function _chdir($dir) {
        chdir($this->_parsePath($dir));
    }

    /**
     * cake bake
     *
     * @param string $command
     */
    protected function _bake($command) {
        $this->__exec('php ' . CAKE . 'console/cake.php bake ' . $command);
    }

    /**
     * get simpletest files
     *
     * @param array $params
     */
    protected function _get_simpletest($params) {
        if (isset($params['target'])) {
            $params['target'] = $this->shortPath($this->_parsePath($params['target']));
        } else {
            $params['target'] = 'vendors';
        }
        $params['target'] = rtrim($params['target'], DS) . DS;

        $this->_chdir(ROOT . DS . $params['target']);

        $path = 'sourceforge.net/projects/simpletest/files/simpletest/simpletest_1.0.1/simpletest_1.0.1.tar.gz/download';

        $command = sprintf('curl -L %s | tar xz', $path);
        $this->__exec($command);
    }

    /**
     * git submodule add
     *
     * @param array $params
     */
    protected function _git_submodule($params) {
        if (!isset($params['repo']) || !isset($params['target'])) {
            $this->err(__d('kickstart', '\'git_submodule\' need \'repo\' and \'target\' options.', true));
            return;
        }

        $params['target'] = $this->shortPath($this->_parsePath($params['target']));
        if (file_exists($params['target'])
                && !$this->in(
                        sprintf(__d('kickstart', '%s is exists. are you sure overwrite?', true), $params['target']),
                        array('y', 'N'), 'N') === 'y') {
            return;
        }
        $command = sprintf('git submodule add %s %s', $params['repo'], $params['target']);
        $this->__exec($command);
    }

}