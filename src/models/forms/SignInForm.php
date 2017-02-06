<?php

namespace app\models\forms;

use app\components\CedynaMyPageWithCache;
use app\models\PolletUser;
use app\models\PushNotificationToken;
use Yii;
use yii\base\Model;
use yii\web\HttpException;

/**
 * ログイン(カード認証)フォーム用モデル
 * @package app\models\forms
 */
class SignInForm extends Model
{
    const ACTIVATE_DURATION = 60*60*24 * 30; // 30日間

    const SCENARIO_ID_WITH_PW = 'required-cedynaID-and-password';
    const SCENARIO_ID_ONLY    = 'only-cedynaID';
    const SCENARIO_AUTO       = 'auto-login';

    public $cedyna_id;
    public $password;

    /**
     * 認証を実行する
     * @param $user PolletUser ユーザ
     * @return bool
     */
    public function authenticate($user)
    {
        if (!$this->validate()) {
            return false;
        }

        switch ($this->scenario) {
            case self::SCENARIO_ID_WITH_PW:
                $boval = $this->authenticateOnCedynaIdWithPasswordScenario($user);
                break;
            case self::SCENARIO_ID_ONLY:
                $boval = $this->authenticateOnCedynaIdOnlyScenario($user);
                break;
            case self::SCENARIO_AUTO:
                $boval = $this->authenticateAuto($user);
                break;
            default: return false; break; // dead-code
        }

        if ($boval) {
            $this->registerBalanceAtChargeIfNeeded($user);
        }

        return $boval;
    }

    /**
     * セディナIDとパスワード入力をするシナリオでの認証処理を行う
     * @param $user PolletUser ユーザ
     * @return bool 成功/失敗 失敗時はエラーを追加した状態になる
     * @throws HttpException
     */
    private function authenticateOnCedynaIdWithPasswordScenario($user)
    {
        // セディナの認証実行
        if (!CedynaMyPageWithCache::getInstance()->login($this->cedyna_id, $this->password)) {
            $this->addAuthorizeError();
            return false;
        }

        // 入力されたセディナIDに紐付くユーザを検索する
        $searchedUser = PolletUser::find()->active()->cedynaId($this->cedyna_id)->one();
        if (!$searchedUser) {
            $this->addAuthorizeError();
            return false;
        }

        // 入力されたパスワードと、抽出されたレコードのパスワードが異なるときは
        // 入力された方が認証可能なパスワードなので書き換える
        if ($this->password !== $searchedUser->rawPassword) {
            if (!$searchedUser->updatePassword($this->password)) {
                throw new HttpException(500);
            }
        }

        // 抽出されたユーザのトークンと、セッション上のユーザのトークンが違う場合
        if ($user->user_code_secret !== $searchedUser->user_code_secret) {
            $trans = Yii::$app->db->beginTransaction();
            try {

                // 重複が発生しないように先に元レコードを削除
                $userCodeSecret = $user->user_code_secret;

                if (!PushNotificationToken::override($user->id, $searchedUser->id)) {
                    throw new \Exception('failed override token');
                }

                // 新規ユーザの場合だけレコードを削除し、それ以外は ユーザコードを NULL にする
                // https://github.com/oz-sysb/schezzo/issues/429
                if ($user->isNewUser()) {
                    if ($user->delete() === false) {
                        throw new \Exception('failed delete original user');
                    }
                }
                else {
                    $user->setScenario(PolletUser::SCENARIO_USER_CODE_NULLABLE);
                    $user->user_code_secret = null;
                    if (!$user->save()) {
                        throw new \Exception('failed update original user');
                    }
                }

                $searchedUser->user_code_secret = $userCodeSecret;
                if (!$searchedUser->save()) {
                    throw new \Exception('failed update searched user');
                }

                $trans->commit();
            } catch (\Exception $e) {
                $trans->rollBack();
                throw new HttpException($e->getMessage());
            }
            Yii::$app->user->login($searchedUser);
        }

        return true;
    }

    /**
     * セディナIDのみを入力をするシナリオでの認証処理を行う
     * @param $user PolletUser ユーザ
     * @return bool 成功/失敗 失敗時はエラーを追加した状態になる
     */
    private function authenticateOnCedynaIdOnlyScenario($user)
    {
        // 入力されたセディナIDに紐付くユーザを検索してパスワードを取得する
        $searchedUser = PolletUser::find()
            ->active()
            ->userCodeSecret($user->user_code_secret)
            ->cedynaId($this->cedyna_id)
            ->one();
        if (!$searchedUser || !$searchedUser->rawPassword) {
            $this->addAuthorizeError();
            return false;
        }
        
        // セディナの認証実行
        if (!CedynaMyPageWithCache::getInstance()->login($this->cedyna_id, $searchedUser->rawPassword)) {
            $this->addAuthorizeError();
            return false;
        }

        return true;
    }

    /**
     * 自動ログインシナリオでの認証処理を行う
     * @param $user PolletUser ユーザ
     * @return bool 成功/失敗 失敗時はエラーを追加した状態になる
     */
    private function authenticateAuto($user)
    {
        // 入力されたセディナIDに紐付くユーザを検索してパスワードを取得する
        $searchedUser = PolletUser::find()
            ->active()
            ->userCodeSecret($user->user_code_secret)
            ->one();
        if (!$searchedUser || !$searchedUser->cedyna_id || !$searchedUser->rawPassword) {
            $this->addAuthorizeError();
            return false;
        }

        // セディナの認証実行
        if (!CedynaMyPageWithCache::getInstance()->login($searchedUser->cedyna_id, $searchedUser->rawPassword)) {
            $this->addAuthorizeError();
            return false;
        }

        return true;
    }

    /**
     *
     * @param PolletUser $originUser
     */
    private function registerBalanceAtChargeIfNeeded($originUser)
    {
        if (!$originUser->isIssued()) return;

        $user = PolletUser::findIdentity($originUser->id);
        $user->balance_at_charge = $user->myChargedValue;
        $user->save();
    }

    /**
     * 認証エラーを追加する
     */
    private function addAuthorizeError()
    {
        $this->addError('cedyna_id', '入力された情報ではログインできませんでした。入力内容をご確認ください。');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $ret = [
            ['cedyna_id',
                'required',
                'message' => '会員番号を入力してください。',
                'on'      => [self::SCENARIO_ID_ONLY, self::SCENARIO_ID_WITH_PW]
            ],
            ['cedyna_id',
                'match',
                'pattern' => '/^[0-9]{16}$/',
                'message' => '会員番号は16桁の数字で入力してください。',
                'on'      => [self::SCENARIO_ID_ONLY, self::SCENARIO_ID_WITH_PW]
            ],
            ['password',
                'required',
                'message' => 'パスワードを入力してください。',
                'on' => [self::SCENARIO_ID_WITH_PW]
            ],
            ['password', 'string', 'on' => [self::SCENARIO_ID_WITH_PW]],
        ];

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cedyna_id' => '会員番号',
            'password'  => 'パスワード',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_ID_ONLY    => ['cedyna_id'],
            self::SCENARIO_ID_WITH_PW => ['cedyna_id', 'password'],
            self::SCENARIO_AUTO       => [],
        ]);
    }

    /**
     * パスワード入力が必要かどうかを返す
     * @return bool
     */
    public function isNecessityInputPassword()
    {
        return $this->scenario == self::SCENARIO_ID_WITH_PW;
    }
}
