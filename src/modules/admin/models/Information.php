<?php

namespace app\modules\admin\models;

use app\models\queries\InformationQuery;
use Yii;
use app\helpers\Date;

/**
 * Class Information
 *
 * @package app\modules\admin\models
 */
class Information extends \app\models\Information
{
    /** @var boolean */
    public $is_public;

    public function init()
    {
        if ($this->isNewRecord) {
            $this->is_public    = true;
            $this->sends_push   = true;
            $this->is_important = true;
            $this->begin_date   = Date::today();
            $this->end_date     = Date::today()->addMonth()->lastOfMonth();
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'sends_push'   => 'プッシュ通知を送信',
            'is_important' => '重要なお知らせ',
            'is_public'    => '公開する',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['begin_date', 'end_date'], 'validateDates'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $ret = parent::load($data, $formName);
        if ($ret) {
            $this->is_public = $data[$this->formName()]['is_public'] ?? false;
        }
        return $ret;
    }

    /**
     * 読み込み時に is_public を取得するようにクエリに付加している
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        /** @var InformationQuery $query */
        $query = parent::findByCondition($condition);
        return $query->joinIsPublic();
    }

    /**
     * 保存時に is_public を見て、ステータスを分岐させている
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->publishing_status = $this->is_public ? self::PUBLISHING_STATUS_PUBLIC : self::PUBLISHING_STATUS_PRIVATE;
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * 物理削除をせずに削除ステータスに更新するようにオーバライドしている
     * @inheritdoc
     */
    public function delete()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $this->publishing_status = self::PUBLISHING_STATUS_REMOVED;
            if (!parent::save()) {
                throw new \Exception('failed delete');
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * 開始日が終了日を超えているかどうかのバリデーション
     */
    public function validateDates()
    {
        if (strtotime($this->end_date) < strtotime($this->begin_date)) {
            $message = '開始日が終了日を超えています';
            $this->addError('begin_date', $message);
            $this->addError('end_date', $message);
        }
    }
}