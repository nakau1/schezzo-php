<?php

namespace app\models\forms;

use app\models\PolletUser;

/**
 * Class DemoCedynaIdForm
 * @package app\models\forms
 */
class DemoCedynaIdForm extends SignInForm
{
    const SCENARIO_DEMO = 'demo';

    /**
     * 発番実行
     * @param $user PolletUser 発番する対象のユーザ
     * @return bool 成功/失敗
     */
    public function issue($user)
    {
        if (!$this->validate()) {
            return false;
        }

        $user->cedyna_id           = $this->cedyna_id;
        $user->registration_status = PolletUser::STATUS_ISSUED;

        if (!$user->save()) {
            $this->addError('cedyna_id', '保存に失敗しました');
            return false;
        }
        return true;
    }

    /**
     * セディナIDのユニークチェック
     * @param $attribute
     */
    public function validateUnique($attribute)
    {
        if (!$this->hasErrors()) {
            $user = new PolletUser();
            $user->cedyna_id = $this->cedyna_id;
            if (!$user->validate(['cedyna_id'])) {
                $this->addError($attribute, 'このセディナIDは既に存在してるので発番できません');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['cedyna_id', 'validateUnique', 'on' => self::SCENARIO_DEMO],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_DEMO  => ['cedyna_id'],
        ]);
    }
}