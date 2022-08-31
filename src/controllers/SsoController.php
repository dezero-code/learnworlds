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
        // SSO with user 2115 - fabian+newpass@dezero.es
        // dd(Yii::app()->learnworlds->sso(2115, 'https://sandbox-futureforwork.mylearnworlds.com/'));

        // Return user information
        // dd(Yii::app()->learnworlds->get_user(2115, '630e29b8f4d2cfe0e900eb7b'));

        // Return a full list of courses
        // dd(Yii::app()->learnworlds->get_courses());

        // Return course information
        // dd(Yii::app()->learnworlds->get_course('curso-de-prueba'));

        // Enroll user to product (adds a new course inscription)
        dd(Yii::app()->learnworlds->enroll_to_course(2115, 'curso-de-prueba'));
    }
}
