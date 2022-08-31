<?php
/**
 * @package dzlab\learnworlds\src\models 
 */

namespace dzlab\learnworlds\models;

use dz\db\DbCriteria;
use dz\helpers\DateHelper;
use dz\helpers\Log;
use dz\helpers\StringHelper;
use dzlab\learnworlds\models\_base\LearnworldsCourseUser as BaseLearnworldsCourseUser;
use dzlab\learnworlds\models\LearnworldsCourse;
use dzlab\learnworlds\models\LearnworldsUser;
use user\models\User;
use Yii;

/**
 * LearnworldsCourseUser model class for "learnworlds_course_user" database table
 *
 * Columns in table "learnworlds_course_user" available as properties of the model,
 * and there are no model relations.
 *
 * -------------------------------------------------------------------------
 * COLUMN FIELDS
 * -------------------------------------------------------------------------
 * @property string $learnworlds_course_id
 * @property string $learnworlds_user_id
 * @property integer $user_id
 * @property integer $created_date
 * @property integer $created_uid
 *
 * -------------------------------------------------------------------------
 * RELATIONS
 * -------------------------------------------------------------------------
 */
class LearnworldsCourseUser extends BaseLearnworldsCourseUser
{
	/**
	 * Constructor
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	
	/**
	 * Returns the validation rules for attributes
	 */
	public function rules()
	{
		return [
			['learnworlds_course_id, learnworlds_user_id, user_id, created_date, created_uid', 'required'],
			['user_id, created_date, created_uid', 'numerical', 'integerOnly' => true],
			['learnworlds_course_id', 'length', 'max'=> 255],
			['learnworlds_user_id', 'length', 'max'=> 32],
			['learnworlds_course_id, learnworlds_user_id, user_id, created_date, created_uid', 'safe', 'on' => 'search'],
		];
	}
	

	/**
	 * Define relations with other objects
	 *
	 * There are four types of relations that may exist between two active record objects:
	 *   - BELONGS_TO: e.g. a member belongs to a team;
	 *   - HAS_ONE: e.g. a member has at most one profile;
	 *   - HAS_MANY: e.g. a team has many members;
	 *   - MANY_MANY: e.g. a member has many skills and a skill belongs to a member.
	 */
	public function relations()
	{
		return [
            'course' => [self::BELONGS_TO, LearnworldsCourse::class, ['learnworlds_course_id' => 'learnworlds_course_id']],
            'learnworldsCourse' => [self::BELONGS_TO, LearnworldsCourse::class, ['learnworlds_course_id' => 'learnworlds_course_id']],
            'learnworldsUser' => [self::BELONGS_TO, LearnworldsUser::class, ['learnworlds_user_id' => 'learnworlds_user_id']],
            'user' => [self::BELONGS_TO, User::class, ['user_id' => 'id']],

            // Custom relations
		];
	}


    /**
     * External behaviors
     */
    /*
    public function behaviors()
    {
        return \CMap::mergeArray([
            // Date format
            'DateBehavior' => [
                'class' => '\dz\behaviors\DateBehavior',
                'columns' => [
                    'disable_date' => 'd/m/Y - H:i'
                ],
            ],

        ], parent::behaviors());
    }
    */

	
	/**
	 * Returns the attribute labels
	 */
	public function attributeLabels()
	{
		return [
			'learnworlds_course_id' => null,
			'learnworlds_user_id' => null,
			'user_id' => null,
			'created_date' => Yii::t('app', 'Created Date'),
			'created_uid' => null,
		];
	}


    /**
     * Generate an ActiveDataProvider for search form of this model
     *
     * Used in CGridView
     */
    public function search()
    {
        $criteria = new DbCriteria;
        
        $criteria->with = [];
        // $criteria->together = true;

        $criteria->compare('t.created_date', $this->created_date);

        return new \CActiveDataProvider($this, [
            'criteria' => $criteria,
            'pagination' => ['pageSize' => 30],
            'sort' => ['defaultOrder' => ['learnworlds_course_id' => true]]
        ]);
    }


    /**
     * LearnworldsCourseUser models list
     * 
     * @return array
     */
    public function learnworldscourseuser_list($list_id = '')
    {
        $vec_output = [];

        $criteria = new DbCriteria;
        $criteria->select = ['learnworlds_course_id', 'learnworlds_course_id', 'learnworlds_user_id'];
        // $criteria->order = 't.id ASC';
        // $criteria->condition = '';
        
        $vec_models = LearnworldsCourseUser::model()->findAll($criteria);
        if ( !empty($vec_models) )
        {
            foreach ( $vec_models as $que_model )
            {
                $vec_output[$que_model->getAttribute('learnworlds_course_id')] = $que_model->title();
            }
        }

        return $vec_output;
    }
}
