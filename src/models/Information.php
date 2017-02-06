<?php

namespace app\models;

use app\models\queries\InformationQuery;
use Carbon\Carbon;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "information".
 * お知らせを管理する
 *
 * @property integer                  $id
 * @property string                   $heading
 * @property string                   $body
 * @property string                   $begin_date
 * @property string                   $end_date
 * @property integer                  $sends_push   プッシュ通知を送信するかどうか
 * @property integer                  $is_important 重要なお知らせかどうか
 * @property string                   $publishing_status
 * @property string                   $updated_at
 * @property string                   $created_at
 *
 * @property PushInformationOpening[] $pushInformationOpenings
 */
class Information extends ActiveRecord
{
    const CRON_TIMING = 60; // cronの実行頻度(分)

    /** @var string 公開状態…公開 */
    const PUBLISHING_STATUS_PUBLIC = 'public';
    /** @var string 公開状態…非公開 */
    const PUBLISHING_STATUS_PRIVATE = 'private';
    /** @var string 公開状態…削除 */
    const PUBLISHING_STATUS_REMOVED = 'removed';

    /** @var bool 既読かどうか */
    public $isOpened = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'information';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['heading', 'body', 'publishing_status'], 'required'],
            [['body', 'begin_date', 'end_date'], 'string'],
            [['sends_push', 'is_important'], 'integer'],
            [['heading'], 'string', 'max' => 50],
            [['publishing_status'], 'string', 'max' => 35],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'heading'           => '表題',
            'body'              => '本文',
            'begin_date'        => '開始日時',
            'end_date'          => '終了日時',
            'sends_push'        => 'プッシュ通知を送信するかどうか',
            'is_important'      => '重要なお知らせかどうか',
            'publishing_status' => '公開状態',
            'updated_at'        => '更新日時',
            'created_at'        => '作成日時',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getPushInformationOpenings()
    {
        return $this->hasMany(PushInformationOpening::className(), ['information_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return InformationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InformationQuery(get_called_class());
    }

    /**
     * 一時間範囲の対象お知らせを取得する
     *
     * @return Information[]|array
     */
    public static function findCurrentPushNotificationTargets()
    {
        $start = Carbon::now()->minute(0)->second(0);

        return self::find()
            ->active()
            ->andWhere([
                Information::tableName() . '.sends_push' => 1,
            ])
            ->andWhere([
                '>=',
                Information::tableName() . '.begin_date',
                $start->format('Y-m-d H:i:s'),
            ])->andWhere([
                '<=',
                Information::tableName() . '.begin_date',
                $start->addMinute(self::CRON_TIMING)->subSecond(1)->format('Y-m-d H:i:s'),
            ])
            ->all();
    }

    /**
     * @return array
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
