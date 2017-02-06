<?php
namespace app\models\charge_source_cooperation;

use app\models\exceptions\PointSiteApiCooperation\RequestFailedException;
use app\models\exceptions\PointSiteApiCooperation\NotCooperationException;
use app\models\exceptions\PointSiteApiCooperation\PointSiteApiNotFoundException;
use app\models\exceptions\PointSiteApiCooperation\ResponseBodyEmptyException;
use app\models\PointSiteApi;
use app\models\PointSiteToken;
use Exception;
use Faker;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Model;

class PointSiteApiCooperation extends Model
{
    /**
     * 有効なポイント残高を円換算で取得する
     *
     * @param string $chargeSourceCode
     * @param int $polletUserId
     * @return int
     */
    public static function fetchValidPointAsCash(string $chargeSourceCode, int $polletUserId): int
    {
        $curl = self::initCurlInstance(self::getToken($chargeSourceCode, $polletUserId));
        $endpoint = self::findApiUrl($chargeSourceCode, PointSiteApi::API_NAME_FETCH_POINT);
        $curl->get($endpoint);

        if ($curl->responseCode !== 200) {
            Yii::error("ポイント数の取得に失敗しました。pollet_user_id={$polletUserId}; {$curl->response}");
            throw new RequestFailedException($curl->response);
        }

        $response = json_decode($curl->response);

        return intval($response->valid_value);
    }

    /**
     * アクセストークン発行リクエスト
     *
     * @param string $code
     * @param string $url
     * @param string $actionName
     * @return mixed
     * @throws ResponseBodyEmptyException
     */
    public static function getAccessToken(string $code, string $url, string $actionName = '')
    {
        $curl = self::initCurlInstance();
        $query = ['code' => $code];
        $curl->setOption(CURLOPT_POSTFIELDS, http_build_query($query))->post($url);

        if ($curl->responseCode !== 200) {
            Yii::error('the request is not success. responseCode=[' . $curl->responseCode . ']' . $actionName);
            throw new RequestFailedException($curl->response);
        }
        $response = json_decode($curl->response);
        if (is_null($response) || !isset($response->token)) {
            Yii::error('the request token response is empty.' . $actionName);
            throw new ResponseBodyEmptyException();
        }

        return $response->token;
    }

    /**
     * 交換リクエストの実行
     *
     * @param string $chargeSourceCode
     * @param int $value
     * @param int $polletUserId
     * @param int $chargeRequestId
     * @return bool 処理が成功したかどうか
     * @throws Exception
     */
    public static function exchange(string $chargeSourceCode, int $value, int $polletUserId, int $chargeRequestId)
    {
        if ($value > self::fetchValidPointAsCash($chargeSourceCode, $polletUserId)) {
            return false;
        }

        $curl = self::initCurlInstance(self::getToken($chargeSourceCode, $polletUserId));
        $params = [
            'charge_id'    => $chargeRequestId,
            'charge_value' => $value,
        ];
        $endpoint = self::findApiUrl($chargeSourceCode, PointSiteApi::API_NAME_EXCHANGE);
        $curl->setOption(CURLOPT_POSTFIELDS, http_build_query($params))->post($endpoint);

        if ($curl->responseCode === 200) {
            return true;
        }
        // fixme ログの出力内容 本番つなぎ込みの際にきめる
        Yii::error($curl->response);

        return false;
    }

    /**
     * 交換キャンセルリクエストを行う。
     *
     * @param string $chargeSourceCode
     * @param int $chargeRequestId
     */
    public static function cancelExchange(string $chargeSourceCode, int $chargeRequestId)
    {
        $curl = self::initCurlInstance();
        $params = [
            'charge_id'    => $chargeRequestId,
        ];
        $endpoint = self::findApiUrl($chargeSourceCode, PointSiteApi::API_NAME_CANCEL_EXCHANGE);
        $curl->setOption(CURLOPT_POSTFIELDS, http_build_query($params))->delete($endpoint);

        if ($curl->responseCode !== 200) {
            // 失敗
            throw new RequestFailedException($curl->response);
        }
    }

    /**
     * トークン取得
     *
     * @param string $chargeSourceCode
     * @param int $polletUserId
     * @return string
     */
    public static function getToken(string $chargeSourceCode, int $polletUserId)
    {
        try {
            return PointSiteToken::find()->where([
                'pollet_user_id'     => $polletUserId,
                'charge_source_code' => $chargeSourceCode,
            ])->one()->token;
        } catch (Exception $e) {
            throw new NotCooperationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $chargeSourceCode
     * @param string $apiName
     * @return string
     * @throws PointSiteApiNotFoundException
     */
    public static function findApiUrl(string $chargeSourceCode, string $apiName)
    {
        try {
            return PointSiteApi::find()->where([
                'api_name'           => $apiName,
                'charge_source_code' => $chargeSourceCode,
                'publishing_status'  => PointSiteApi::PUBLISHING_STATUS_PUBLIC,
            ])->one()->url;
        } catch (Exception $e) {
            Yii::error("APIが存在しません; api_name={$apiName}, charge_source_code={$chargeSourceCode}");
            throw new PointSiteApiNotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 共通の初期化処理を行ったCurlインスタンスを生成する
     * @todo requestToApiを使っているところをこのメソッドを使うように修正する
     *
     * @param string|null $accessToken
     *
     * @return Curl
     */
    private static function initCurlInstance(string $accessToken = null)
    {
        $curl = Yii::$app->get('curl');

        $header = [
            'Content-Type: application/x-www-form-urlencoded',
        ];
        if (!empty($accessToken)) {
            // HTTPヘッダ・インジェクション回避のためエンコードする
            $header[] = 'Authorization: Bearer '.rawurlencode($accessToken);
        }
        $curl->setOption(CURLOPT_HTTPHEADER, $header);

        return $curl;
    }
}
