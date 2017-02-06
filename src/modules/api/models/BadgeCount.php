<?php
namespace app\modules\api\models;

use app\models\Information;
use app\models\PolletUser;
use Yii;
use yii\base\Model;

/**
 * Class Badge
 * @package app\modules\api\models
 */
class BadgeCount extends Model
{
    public $pollet_id;

    /* @var PolletUser $user */
    public $user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pollet_id'], 'required', 'message' => '{attribute} は必須です'],
            [['pollet_id'], 'validateExistsUser'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pollet_id' => 'ユーザー識別ID',
        ];
    }

    /**
     * @return bool
     */
    public function count()
    {
        $ret = 0;
        if ($this->user) {
            $ret = Information::find()->joinOpening(true, $this->user->id)->published()->important()->count();
        }
        return $ret;
    }

    /**
     * ユーザの存在チェック
     * @param $attribute
     * @return bool
     */
    public function validateExistsUser($attribute)
    {
        $user = null;
        if ($this->pollet_id) {
            $user = PolletUser::find()->active()->userCodeSecret($this->pollet_id)->one();
        }

        $ret = !is_null($user);
        if (!$ret) {
            $this->addError($attribute, 'ユーザが存在しません');
        }

        $this->user = $user;
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}