<?php

/**
 * Kickstart command base class
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
 */

/**
 *
 * @property TemplateTask $Template
 */
class KickstartCommandTask extends Shell {

    public $tasks = array('Template');

    /**
     * parse path string
     *
     * @param string $pathString
     * @return string
     */
    protected function _parsePath($pathString) {
        $pathString = preg_replace_callback('/\$([a-z0-9_]+)|{\$([a-z0-9_]+)}|\${([a-z0-9_]+)}/i', array($this, '_variableReplace'), $pathString);
        $pathString = preg_replace('/(?<!:)' . preg_quote(DS . DS, '/') . '/', DS, $pathString);
        return $pathString;
    }

    /**
     * @param array $var
     * @return string
     */
    protected function _variableReplace($var) {
        static $constants = null;
        if (!isset($constants)) {
            $constants = get_defined_constants(true);
            $constants = $constants['user'];
            // overrride params
            $constants['ROOT'] = isset($this->params['root']) ? $this->params['root'] : $constants['ROOT'];
            $constants['APP'] = isset($this->params['working']) ? $this->params['working'] : $constants['APP'];
        }
        return @$constants[array_pop($var)];
    }

    /**
     * exec command
     *
     * @param string $command
     * @return array
     */
    protected function _exec($command) {
        passthru($this->_parsePath($command));
    }

    /**
     * chdir
     *
     * @param string $dir
     */
    protected function _chdir($dir) {
        chdir($this->_parsePath($dir));
    }

    // =========================================================================
    // Commands
    // =========================================================================
    /**
     * exec
     *
     * @param mixed $command
     */
    public function exec($command) {
        if (is_array($command) && Set::numeric(array_keys($command))) {
            array_walk($command, array($this, __METHOD__));
            return;
        }
        $this->_exec($command);
    }

    /**
     * cake bake
     *
     * @param mixed $command
     */
    public function bake($command) {
        if (is_array($command) && Set::numeric(array_keys($command))) {
            array_walk($command, array($this, __METHOD__));
            return;
        }

        if (!preg_match('/ -app .+/', $command)) {
            $command .= ' -app ' . $this->params['app'];
        }

        $this->_exec('php ' . CAKE . 'console/cake.php bake ' . $command);
    }

    /**
     * get simpletest files
     *
     * @param mixed $params
     */
    public function get_simpletest($params) {

        if (is_array($params) && Set::numeric(array_keys($params))) {
            array_walk($params, array($this, __METHOD__));
            return;
        }

        if (isset($params['target'])) {
            $params['target'] = $this->shortPath($this->_parsePath($params['target']));
        } else {
            $params = array();
            $params['target'] = 'vendors';
        }
        $params['target'] = trim($params['target'], DS) . DS;

        $this->_chdir(ROOT . DS . $params['target']);

        $path = 'sourceforge.net/projects/simpletest/files/simpletest/simpletest_1.0.1/simpletest_1.0.1.tar.gz/download';

        $command = sprintf('curl -L %s | tar xz', $path);
        $this->_exec($command);
    }

    /**
     * git submodule add
     *
     * @param mixed $params
     */
    public function git_submodule($params) {

        if (is_array($params) && Set::numeric(array_keys($params))) {
            array_walk($params, array($this, __METHOD__));
            return;
        }

        if (!isset($params['repo']) || !isset($params['target'])) {
            $this->err(__d('kickstart', '\'git_submodule\' need \'repo\' and \'target\' options.', true));
            return;
        }

        $params['target'] = $this->shortPath($this->_parsePath($params['target']));
        $params['target'] = trim($params['target'], DS);

        if (file_exists($params['target'])
                && !$this->in(
                        sprintf(__d('kickstart', '%s is exists. are you sure overwrite?', true), $params['target']),
                        array('y', 'N'), 'N') === 'y') {
            return;
        }
        $command = sprintf('git submodule add %s %s', $params['repo'], $params['target']);
        $this->_exec($command);
    }

    /**
     * generate file from template
     *
     * @param array $params
     */
    public function generatefile($params) {

        if (is_array($params) && Set::numeric(array_keys($params))) {
            array_walk($params, array($this, __METHOD__));
            return;
        }

        if (!isset($params['template']) || !isset($params['target'])) {
            $this->err(__d('kickstart', '\'generatefile\' need \'template\' and \'target\' options.', true));
            return;
        }

        $params['target'] = $this->shortPath($this->_parsePath($params['target']));
        $params['target'] = trim($params['target'], DS);

        $vars = array();

        // set vars
        if (!empty($params['vars'])) {
            $vars = array_merge($vars, $params['vars']);
        }

        // input vars
        $_interactive = $this->interactive;
        $this->interactive = true;
        if (!empty($params['ask'])) {
            foreach ($params['ask'] as $key => $opt) {
                $prompt = isset($opt['message']) ? $opt['message'] : sprintf(__d('kickstart', 'please input "%s"', true), $key);
                $options = isset($opt['options']) ? $opt['options'] : null;
                $default = isset($opt['default']) ? $opt['default'] : null;
                $vars[$key] = $this->in($prompt, $options, $default);
            }
        }
        $this->interactive = $_interactive;

        // setup template path
        $this->Template->templatePaths = array(dirname(dirname(__FILE__)) . DS . 'templates' . DS);
        // generate file
        $this->createFile(ROOT . DS . $params['target'], $this->Template->generate('generatefiles', $params['template'], $vars));
    }

}
