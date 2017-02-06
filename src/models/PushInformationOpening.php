<?php

namespace app\models;

use app\models\queries\PushInformationOpeningQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "push_information_opening".
 * お知らせのプッシュ通知の開封を管理する
 *
 * @property integer $id
 * @property integer $pollet_user_id
 * @property integer $information_id
 * @property string  $created_at
 *
 * @property Information $information
 * @property PolletUser  $polletUser
 */
class PushInformationOpening extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'push_information_opening';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pollet_user_id', 'information_id'], 'required'],
            [['pollet_user_id', 'information_id'], 'integer'],
            [['information_id'], 'exist', 'skipOnError' => true, 'targetClass' => Information::className(), 'targetAttribute' => ['information_id' => 'id']],
            [['pollet_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => PolletUser::className(), 'targetAttribute' => ['pollet_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'pollet_user_id' => 'ポレットユーザID',
            'information_id' => 'お知らせID',
            'created_at'     => '作成日時',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getInformation()
    {
        return $this->hasOne(Information::className(), ['id' => 'information_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPolletUser()
    {
        return $this->hasOne(PolletUser::className(), ['id' => 'pollet_user_id']);
    }

    /**
     * @inheritdoc
     * @return PushInformationOpeningQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PushInformationOpeningQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'value'              => date('Y-m-d H:i:s'),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * 既読を追加する
     * @param integer $polletUserId ポレットユーザID
     * @param integer $informationId お知らせID
     * @return bool 成功/失敗
     */
    public function add($polletUserId, $informationId)
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $this->pollet_user_id = $polletUserId;
            $this->information_id = $informationId;

            if (!$this->save()) {
                throw new \Exception('failed add.');
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }
}
