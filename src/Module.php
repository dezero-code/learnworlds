<?php
/**
 * Module to integrate with Learnworlds API for DZ Framework
 */

namespace dzlab\learnworlds;

use Yii;

class Module extends \dz\web\Module
{
    /**
     * @var array mapping from controller ID to controller configurations.
     */
    public $controllerMap = [];


    /**
     * Default controller
     */
    // public $defaultController = 'user';


    /**
     * Load specific CSS or JS files for this module
     */
    public $cssFiles = null; // ['learnworlds.css'];
    public $jsFiles = null; // ['learnworlds.js'];


    /**
     * This method is called when the module is being created
     * you may place code here to customize the module or the application
     */
    public function init()
    {
        // Init this module with current path
        $this->init_module(__DIR__);

        // Going on with the init process
        parent::init();
    }
}