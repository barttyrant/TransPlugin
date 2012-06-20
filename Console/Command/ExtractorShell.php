<?php

/**
 * Internationalization Management Shell
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 1.2.0.5669
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('AppShell', 'Console/Command');

/**
 * Console shell responsible for extracting the translation lines from the source
 * @author Bart Tyrant 
 * 
 * @property ExtraExtractTask $ExtraExtract
 */
class ExtractorShell extends AppShell {
    
    
    /**
     * Contains database source to use
     *
     * @var string
     */
    public $dataSource = 'default';

    /**
     * Contains tasks to load and instantiate
     *
     * @var array
     */
    public $tasks = array('DbConfig', 'Trans.ExtraExtract');

    
    public function main() {
        $this->out(__CLASS__ . ': no method called');
    }

    public function extract() {
        $this->ExtraExtract->execute();
        // later
    }

    function __searchDirectory($path = null) {

        if ($path === null) {
            $path = $this->path . DS;
        }
        if (strpos($path, '/webroot/') || strpos($path, '/vendors/')) {
            return array();
        }
        $files = glob("$path*.{php,ctp,thtml,inc,tpl}", GLOB_BRACE);
        $dirs = glob("$path*", GLOB_ONLYDIR);

        $files = $files ? $files : array();
        $dirs = $dirs ? $dirs : array();

        foreach ($dirs as $dir) {
            if (!preg_match("!(^|.+/)(CVS|.svn)$!", $dir)) {
                $files = array_merge($files, $this->__searchDirectory("$dir" . DS));
                if (($id = array_search($dir . DS . 'extract.php', $files)) !== FALSE) {
                    unset($files[$id]);
                }
            }
        }
        return $files;
    }

    

    /**
     * Override startup of the Shell
     *
     * @return mixed
     */
    public function startup() {
        $this->_welcome();
        if (isset($this->params['datasource'])) {
            $this->dataSource = $this->params['datasource'];
        }

        if ($this->command && !in_array($this->command, array('help'))) {
            if (!config('database')) {
                $this->out(__d('cake_console', 'Your database configuration was not found. Take a moment to create one.'), true);
                return $this->DbConfig->execute();
            }
        }
    }

    /**
     * Get and configure the Option parser
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser() {
        $parser = parent::getOptionParser();
        return $parser->description(
                __d('cake_console', 'I18n Shell initializes i18n database table for your application and generates .pot files(s) with translations.')
            )->addSubcommand('initdb', array(
                'help' => __d('cake_console', 'Initialize the i18n table.')
            ))->addSubcommand('extract', array(
                'help' => __d('cake_console', 'Extract the po translations from your application'),
                'parser' => $this->ExtraExtract->getOptionParser()
            ));
    }

}
