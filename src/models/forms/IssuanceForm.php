<?php

namespace app\models\forms;

use app\components\CedynaMyPage;
use app\models\traits\ValidateTrait;
use yii\base\Model;

/**
 * カード発行手続入力フォーム用モデル
 * @package app\models\forms
 */
class IssuanceForm extends Model
{
    use ValidateTrait;

    public $mail_address;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['mail_address'], 'required', 'message' => 'メールアドレスを入力してください。'],
            [['mail_address'], 'validateLaxEmail'],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'mail_address' => 'メールアドレス',
        ];
    }

    function validate($attributeNames = null, $clearErrors = true)
    {
        // バリデーション前に前後のスペースは除去しておく
        if (is_string($this->mail_address)) {
            $this->mail_address = trim($this->mail_address);
        }
        return parent::validate($attributeNames, $clearErrors);
    }

    /**
     * 認証メールを送信する
     *
     * @param int $polletId カード会員番号との紐づけに使う(セディナの処理の必須パラメータ)
     * @return bool
     */
    public function send(int $polletId)
    {
        if (!$this->validate()) {
            return false;
        }

        return CedynaMyPage::getInstance()->sendIssuingFormLink($this->mail_address, $polletId);
    }

    /***
     * required implementation of ValidateTrait
     * @return $this
     */
    protected function getModel()
    {
        return $this;
    }
}
