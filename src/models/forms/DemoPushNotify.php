<?php
namespace app\models\forms;

use app\components\PushNotify;
use app\models\Information;
use app\models\PolletUser;
use yii\base\Model;

/**
 * Class DemoPushNotify
 * @package app\models\forms
 */
class DemoPushNotify extends Model
{
    const TYPE_INFO_DETAIL    = 0;
    const TYPE_CHARGE_SUCCESS = 1;
    const TYPE_CHARGE_FAILED  = 2;

    public $uuid;
    public $type;

    /**
     * プッシュ通知送信を実行
     * @return bool
     */
    public function send()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = PolletUser::find()->active()->uuid($this->uuid)->one();
        if (!$user) {
            $this->addError('uuid', 'ユーザが存在しません');
            return false;
        }
        $badgeCount = $user->unreadInformationCount;

        switch ($this->type) {
            case self::TYPE_INFO_DETAIL:
                $information = Information::find()->andWhere([
                    Information::tableName() . '.id' => 1, // 1固定
                ])->one();
                if (!$information) {
                    $this->addError('type', 'ID:1のお知らせが存在しません');
                    return false;
                }
                $message = $information->heading;
                $uri = 'information/detail?id='. $information->id;
                $confirmText = PushNotify::CONFIRM_TEXT_INFO_DETAIL;
                break;
            case self::TYPE_CHARGE_SUCCESS:
                $message = '1,000円チャージされました。';
                $uri = 'top';
                $confirmText = PushNotify::CONFIRM_TEXT_TOP;
                $badgeCount += 1;
                break;
            case self::TYPE_CHARGE_FAILED:
                $message = 'チャージに失敗しました。';
                $uri = 'inquiry';
                $confirmText = PushNotify::CONFIRM_TEXT_INQUIRY;
                $badgeCount += 1;
                break;
            default:
                return false;
        }

        $pushNotify = new PushNotify();
        return $pushNotify->sendToUser($user, $message, $uri, $badgeCount, $confirmText);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uuid' => 'UUID',
            'type' => 'プッシュ通知の種類',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['uuid', 'type'], 'required', 'message' => '{attribute}を指定してください'],
        ]);
    }

    /**
     * @return string[]
     */
    public function types()
    {
        return [
            'お知らせ(ID=1固定)　',
            'チャージ成功(1000円固定)　',
            'チャージ失敗　',
        ];
    }
}