<?php
/**
 * Learnworlds API client
 * 
 * Helper classes to work with Learnworlds API v2
 * 
 * @see https://learnworlds.dev/
 */

namespace dzlab\learnworlds\components;

// use dz\base\ApplicationComponent;
use dz\rest\RestClient;
use dz\helpers\DateHelper;
use dz\helpers\Json;
use dz\helpers\Log;
use dz\helpers\StringHelper;
use dz\helpers\Url;
use user\models\User;
use Yii;

class LearnworldsApi extends RestClient
{
    /**
     * @var bool Sanbdox environment
     */
    public $is_sandbox = false;


    /**
     * @var string Last used endpoint
     */
    public $last_endpoint;


    /**
     * @var array Learnworlds configuration
     */
    protected $vec_config = [];


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

        if ( $this->is_sandbox )
        {
            // API key for SANDBOX
            $this->api_key = getenv('LEARNWORLDS_SANDBOX_API_CLIENT_ID');
        }
        else
        {
            // API key for production
            $this->api_key = getenv('LEARNWORLDS_API_CLIENT_ID');
        }

        parent::init();
    }
}