<?php
namespace app\components;

use Yii;

/**
 * Class User
 * @package app\components
 */
class User extends \yii\web\User
{
    /**
     * php7.0 の session_regenerate_id() にバグがあるため上書き
     *
     * @param null|\yii\web\IdentityInterface $identity
     * @param int                             $duration
     */
    public function switchIdentity($identity, $duration = 0)
    {
        $this->setIdentity($identity);

        if (!$this->enableSession) {
            return;
        }

        /* Ensure any existing identity cookies are removed. */
        if ($this->enableAutoLogin) {
            $this->removeIdentityCookie();
        }

        $session = Yii::$app->getSession();
        if (!YII_ENV_TEST) {
            if ($session->getIsActive()) {
                // 再生成を10秒に1回だけにする
                if (empty(time() % 10)) {
                    @session_regenerate_id(true);
                }
            }
        }
        $session->remove($this->idParam);
        $session->remove($this->authTimeoutParam);

        if ($identity) {
            $session->set($this->idParam, $identity->getId());
            if ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
            if ($this->absoluteAuthTimeout !== null) {
                $session->set($this->absoluteAuthTimeoutParam, time() + $this->absoluteAuthTimeout);
            }
            if ($duration > 0 && $this->enableAutoLogin) {
                $this->sendIdentityCookie($identity, $duration);
            }
        }
    }
}
