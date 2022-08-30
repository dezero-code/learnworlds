<?php
/*
|--------------------------------------------------------------------------
| Controller class for Single Sign-on process
|--------------------------------------------------------------------------
*/

namespace dzlab\learnworlds\controllers;

use dz\helpers\Log;
use dz\helpers\StringHelper;
use dz\helpers\Url;
use dz\web\Controller;
use user\models\User;
use Yii;

class SsoController extends Controller
{
    /**
     * Main action
     */
    public function actionIndex()
    {
        $api = Yii::app()->learnworldsApi;
        dd("llego");
    }
}
