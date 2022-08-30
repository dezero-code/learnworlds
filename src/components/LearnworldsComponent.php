<?php
/**
 * Learnworlds Component
 * 
 * Helper classes to work with Learnworlds API v2
 * 
 * @see https://learnworlds.dev/
 */

namespace dzlab\learnworlds\components;

use dz\base\ApplicationComponent;
use dz\helpers\DateHelper;
use dz\helpers\Json;
use dz\helpers\Log;
use dz\helpers\StringHelper;
use dz\helpers\Url;
use user\models\User;
use Yii;

class LearnworldsComponent extends ApplicationComponent
{
    /**
     * @var bool. Debug mode? (log all responses)
     */ 
    public $is_debug = false;


    /**
     * @var bool Sanbdox environment
     */
    public $is_sandbox = false;

    /**
     * @var array Learnworlds configuration
     */
    protected $vec_config = [];


    /**
     * @var object LearnworldsApi
     */
    protected $api;


    /**
     * Init function
     */
    public function init()
    {
        // Learnworlds configuration
        $this->vec_config = Yii::app()->config->get('components.learnworlds');

        // Debug mode?
        if ( isset($this->vec_config['is_debug']) )
        {
            $this->is_debug = $this->vec_config['is_debug'];
        }

        // Sandbox environment?
        if ( isset($this->vec_config['is_sandbox']) )
        {
            $this->is_sandbox = $this->vec_config['is_sandbox'];
        }

        // Init the component API
        $this->api = Yii::app()->learnworldsApi;

        parent::init();
    }
}