<?php
namespace app\components;

use yii\base\Component;
use yii\base\Security;

class Crypt extends Component
{
    /** @var Security */
    private $security;

    private $keySalt = 'f3271597bb0f3c0dbcef8f2a9869e2d25f8d75b69e7ca9a56a436743f656d5d0e837f7a2e36da1c4c6aca97ea12e07aea2c820eb582d1e3c134adb495fec60ca';
    private $stretchingCount = 100000;
    private $hkdfInfo = 'pollet';

    public function init()
    {
        parent::init();

        $this->security = new Security();
        $this->security->cipher = 'AES-256-CBC';
    }

    /**
     * 暗号化
     *
     * @param string $data 暗号化対象
     * @param string $key  暗号化に使うキー
     * @return string
     */
    public function encrypt(string $data, string $key): string
    {
        $encryptionKey = $this->getEncryptionKey($key);

        return base64_encode($this->security->encryptByKey($data, $encryptionKey, $this->hkdfInfo));
    }

    /**
     * 復号
     *
     * @param string $encryptedData 復号対象の文字列
     * @param string $key           暗号化に使ったキー
     * @return string
     */
    public function decrypt(string $encryptedData, string $key): string
    {
        $encryptionKey = $this->getEncryptionKey($key);

        return $this->security->decryptByKey(base64_decode($encryptedData), $encryptionKey, $this->hkdfInfo);
    }

    private function getEncryptionKey(string $seed)
    {
        return $this->security->pbkdf2('sha512', $seed, $this->keySalt, $this->stretchingCount);
    }
}
