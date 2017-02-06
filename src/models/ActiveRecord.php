<?php
namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * ActiveRecord 基底抽象クラス
 * Class ActiveRecord
 * @package app\models
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }
}
