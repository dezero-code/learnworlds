<?php
/**
 * @package dzlab\learnworlds\models\_base
 */

namespace dzlab\learnworlds\models\_base;

use dz\db\ActiveRecord;
use dz\db\DbCriteria;
use dz\helpers\DateHelper;
use dz\helpers\StringHelper;
use user\models\User;
use Yii;

/**
 * DO NOT MODIFY THIS FILE! It is automatically generated by Gii.
 * If any changes are necessary, you must set or override the required
 * property or method in class "dzlab\learnworlds\models\LearnworldsCourse".
 *
 * This is the model base class for the table "learnworlds_course".
 * Columns in table "learnworlds_course" available as properties of the model,
 * followed by relations of table "learnworlds_course" available as properties of the model.
 *
 * -------------------------------------------------------------------------
 * COLUMN FIELDS
 * -------------------------------------------------------------------------
 * @property string $learnworlds_course_id
 * @property string $title
 * @property double $price
 * @property string $response_json
 * @property integer $last_sync_date
 * @property string $last_sync_endpoint
 * @property integer $disable_date
 * @property integer $disable_uid
 * @property integer $created_date
 * @property integer $created_uid
 * @property integer $updated_date
 * @property integer $updated_uid
 * @property string $uuid
 *
 * -------------------------------------------------------------------------
 * RELATIONS
 * -------------------------------------------------------------------------
 * @property mixed $createdUser
 * @property mixed $disableUser
 * @property mixed $updatedUser
 */
abstract class LearnworldsCourse extends ActiveRecord
{
	/**
	 * Constructor
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


	/**
	 * Returns the name of the associated database table
	 */
	public function tableName()
	{
		return 'learnworlds_course';
	}


	/**
	 * Label with translation support (from GIIX)
	 */
	public static function label($n = 1)
	{
		return Yii::t('app', 'LearnworldsCourse|LearnworldsCourses', $n);
	}


    /**
     * Returns the validation rules for attributes
     */
    public function rules()
    {
        return [
            ['learnworlds_course_id, title, last_sync_date, last_sync_endpoint, created_date, created_uid, updated_date, updated_uid', 'required'],
            ['last_sync_date, disable_date, disable_uid, created_date, created_uid, updated_date, updated_uid', 'numerical', 'integerOnly' => true],
            ['price', 'numerical'],
            ['learnworlds_course_id, title', 'length', 'max'=> 255],
            ['last_sync_endpoint', 'length', 'max'=> 128],
            ['uuid', 'length', 'max'=> 36],
            ['price, response_json, disable_date, disable_uid, uuid', 'default', 'setOnEmpty' => true, 'value' => null],
            ['response_json', 'safe'],
            ['learnworlds_course_id, title, price, response_json, last_sync_date, last_sync_endpoint, disable_date, disable_uid, created_date, created_uid, updated_date, updated_uid, uuid', 'safe', 'on' => 'search'],
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
            'createdUser' => [self::BELONGS_TO, User::class, ['created_uid' => 'id']],
            'disableUser' => [self::BELONGS_TO, User::class, ['disable_uid' => 'id']],
            'updatedUser' => [self::BELONGS_TO, User::class, ['updated_uid' => 'id']],
        ];
    }


    /**
     * Returns the attribute labels
     */
    public function attributeLabels()
    {
        return [
            'learnworlds_course_id' => Yii::t('app', 'Learnworlds Course'),
            'title' => Yii::t('app', 'Title'),
            'price' => Yii::t('app', 'Price'),
            'response_json' => Yii::t('app', 'Response Json'),
            'last_sync_date' => Yii::t('app', 'Last Sync Date'),
            'last_sync_endpoint' => Yii::t('app', 'Last Sync Endpoint'),
            'disable_date' => Yii::t('app', 'Disable Date'),
            'disable_uid' => null,
            'created_date' => Yii::t('app', 'Created Date'),
            'created_uid' => null,
            'updated_date' => Yii::t('app', 'Updated Date'),
            'updated_uid' => null,
            'uuid' => Yii::t('app', 'Uuid'),
            'createdUser' => null,
            'disableUser' => null,
            'updatedUser' => null,
        ];
    }


    /**
     * LearnworldsCourse models list
     * 
     * @return array
     */
    public function learnworldscourse_list($list_id = '')
    {
        $vec_output = [];

        $criteria = new DbCriteria;
        $criteria->select = ['learnworlds_course_id', 'title'];
        // $criteria->order = 't.id ASC';
        // $criteria->condition = '';
        
        $vec_models = LearnworldsCourse::model()->findAll($criteria);
        if ( !empty($vec_models) )
        {
            foreach ( $vec_models as $que_model )
            {
                $vec_output[$que_model->getAttribute('learnworlds_course_id')] = $que_model->title();
            }
        }

        return $vec_output;
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

		$criteria->compare('t.learnworlds_course_id', $this->learnworlds_course_id, true);
		$criteria->compare('t.title', $this->title, true);
		$criteria->compare('t.price', $this->price);
		$criteria->compare('t.response_json', $this->response_json, true);
		$criteria->compare('t.last_sync_date', $this->last_sync_date);
		$criteria->compare('t.last_sync_endpoint', $this->last_sync_endpoint, true);
		$criteria->compare('t.disable_date', $this->disable_date);
		$criteria->compare('t.created_date', $this->created_date);
		$criteria->compare('t.updated_date', $this->updated_date);
		$criteria->compare('t.uuid', $this->uuid, true);

		return new \CActiveDataProvider($this, [
			'criteria' => $criteria,
			'pagination' => ['pageSize' => 30],
			'sort' => ['defaultOrder' => ['learnworlds_course_id' => true]]
		]);
	}
	
	
	/**
	 * Returns fields not showed in create/update forms
	 */
	public function excludedFormFields()
	{
		return [
			'created_date' => true, 
			'created_uid' => true, 
			'updated_date' => true, 
			'updated_uid' => true, 
		];
	}


    /**
     * Automatic string representation for a model (from GIIX)
     */
    public static function representingColumn()
    {
        return 'title';
    }


    /**
     * Title used for this model
     */
    public function title()
    {
        return $this->title;
    }
}
