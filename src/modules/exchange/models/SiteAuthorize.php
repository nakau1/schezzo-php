<?php
namespace app\modules\exchange\models;

use app\models\ChargeSource;
use app\modules\exchange\helpers\Messages;
use yii\base\Model;

/**
 * Class SiteAuthorize
 * @package app\modules\exchange\models
 *
 * @property ChargeSource $chargeSource
 */
class SiteAuthorize extends Model
{
    public $site_code;
    public $api_key;

    private $chargeSource;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_code', 'api_key'], 'required', 'message' => Messages::REQUIRED_EMPTY],
            [['site_code', 'api_key'], 'string', 'message' => Messages::INVALID_PARAM],
        ];
    }

    /**
     * @inheritdoc
     */
    public function authorize()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->chargeSource = ChargeSource::find()->andWhere([
            ChargeSource::tableName(). '.charge_source_code' => $this->site_code,
            ChargeSource::tableName(). '.api_key'            => $this->api_key,
        ])->one();

        $ret = !is_null($this->chargeSource);
        if (!$ret) {
            $this->addError('site_code', Messages::ERR_UNAUTHORIZED);
        }
        return $ret;
    }

    /**
     * 認証されたチャージ元を取得する
     * @return ChargeSource チャージ元
     */
    public function getChargeSource()
    {
        return $this->chargeSource;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}