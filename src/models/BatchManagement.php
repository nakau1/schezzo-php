<?php

namespace app\models;

use app\models\queries\BatchManagementQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "batch_management".
 * バッチの起動状態を管理する
 *
 * @property integer $id
 * @property string  $name
 * @property string  $status
 */
class BatchManagement extends ActiveRecord
{
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'batch_management';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'status'], 'required'],
            [['name'], 'string', 'max' => 256],
            [['status'], 'string', 'max' => 15],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'     => 'ID',
            'name'   => '名称',
            'status' => 'ステータス',
        ];
    }

    /**
     * @inheritdoc
     * @return BatchManagementQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BatchManagementQuery(get_called_class());
    }

    /**
     * @param string $name
     */
    public static function activate(string $name)
    {
        $record = self::findByName($name) ?? new static();
        $record->name = $name;
        $record->status = static::STATUS_ACTIVE;
        $record->save();
    }

    /**
     * @param string $name
     */
    public static function inactivate(string $name)
    {
        $record = self::findByName($name) ?? new static();
        $record->name = $name;
        $record->status = static::STATUS_INACTIVE;
        $record->save();
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function isActive(string $name): bool
    {
        $record = self::findByName($name);

        return $record !== null && $record->status === static::STATUS_ACTIVE;
    }

    /**
     * @param string $name
     * @return BatchManagement|array|null
     */
    private static function findByName(string $name)
    {
        return BatchManagement::find()->where(['name' => $name])->one();
    }
}
