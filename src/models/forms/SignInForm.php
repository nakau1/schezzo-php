<?php
namespace app\models\forms;

/**
 * Class SignInForm
 * @package app\models\forms
 */
class SignInForm extends Form
{
    public $user_identifier;
    public $password;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_identifier' => 'アカウント名、またはメールアドレス',
            'password'        => 'パスワード',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_identifier', 'password'], 'required'],
            [['user_identifier', 'password'], 'string'],
        ];
    }

    /**
     * 認証を実行する
     * @return bool
     */
    public function authenticate()
    {
        if (!$this->validate()) {
            return false;
        }
        return true;
    }
}
