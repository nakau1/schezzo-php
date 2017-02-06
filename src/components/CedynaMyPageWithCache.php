<?php

namespace app\components;

use app\models\CardValueCache;
use app\models\exceptions\CedynaMyPage\ScrapingException;
use app\models\exceptions\CedynaMyPage\UnauthorizedException;
use app\models\MonthlyTradingHistoryCache;
use app\models\PolletUser;
use app\models\TradingHistory;
use Yii;

/**
 * Class CedynaMyPageWithCache
 * セディナのマイページ。取得結果はキャッシュされる。
 *
 * @package app\components
 */
class CedynaMyPageWithCache extends CedynaMyPage
{
    private $cardValueCacheSeconds;
    private $tradingHistoryCacheSeconds;

    /**
     * 設定ファイルを反映したインスタンスを生成する
     *
     * @inheritdoc
     * @return CedynaMyPageWithCache|object
     */
    public static function getInstance()
    {
        return Yii::$app->get('cedynaMyPageWithCache');
    }

    /**
     * @param int $seconds
     * @return $this
     */
    public function setCardValueCacheSeconds(int $seconds)
    {
        $this->cardValueCacheSeconds = $seconds;

        return $this;
    }

    /**
     * @param int $seconds
     * @return $this
     */
    public function setTradingHistoryCacheSeconds(int $seconds)
    {
        $this->tradingHistoryCacheSeconds = $seconds;

        return $this;
    }

    /**
     * カード残高のキャッシュを取得する。
     * キャッシュが有効でない場合はnullを返す。
     *
     * @param string $cedynaId
     * @return int|null
     */
    public function cardValueCache(string $cedynaId)
    {
        $polletUser = PolletUser::find()->active()->cedynaId($cedynaId)->one();
        if (empty($polletUser)) {
            return null;
        }

        $cache = $polletUser->cardValueCache;
        if ($cache && $this->cardValueCacheIsLive(strtotime($cache->updated_at))) {
            return $cache->value;
        } else {
            return null;
        }
    }

    /**
     * カード残高を取得する。取得結果はキャッシュする。
     *
     * @inheritdoc
     * @return int
     * @throws UnauthorizedException
     * @throws ScrapingException
     */
    public function cardValue(): int
    {
        // キャッシュから取得
        $cache = $this->cardValueCache($this->cedynaId);
        if ($cache !== null) {
            return $cache;
        }

        // スクレイピングで取得
        $cardValue = parent::cardValue();

        // キャッシュに保存
        $polletUser = PolletUser::find()->active()->cedynaId($this->cedynaId)->one();
        CardValueCache::store($cardValue, $polletUser->id);

        return $cardValue;
    }

    /**
     * カード利用履歴のキャッシュを取得する。
     * キャッシュが有効でない場合はnullを返す。
     *
     * @param string $cedynaId
     * @param string $month
     * @return array|null
     */
    public function tradingHistoriesCache(string $cedynaId, string $month)
    {
        $polletUser = PolletUser::find()->active()->cedynaId($cedynaId)->one();
        if (empty($polletUser)) {
            return null;
        }

        /** @var MonthlyTradingHistoryCache $cache */
        $cache = $polletUser->getMonthlyTradingHistoryCaches()->where(['month' => $month])->one();
        if ($cache && $this->tradingHistoryCacheIsLive(strtotime($cache->updated_at))) {
            return array_map(function (array $record) {
                return TradingHistory::createFromArray($record);
            }, json_decode($cache->records_json, true));
        } else {
            return null;
        }
    }

    /**
     * カード利用履歴を取得する。取得結果はキャッシュする。
     *
     * @param string $month 'yymm'の形式の月(2016年9月は'1609')
     * @return TradingHistory[]
     */
    public function tradingHistories(string $month): array
    {
        if (!$this->loggedIn) {
            return [];
        }
        // キャッシュから取得
        $cache = $this->tradingHistoriesCache($this->cedynaId, $month);
        if ($cache !== null) {
            return $cache;
        }

        // スクレイピングで取得
        $histories = parent::tradingHistories($month);

        // キャッシュに保存
        $polletUser = PolletUser::find()->active()->cedynaId($this->cedynaId)->one();
        $recordsJson = json_encode(array_map(function (TradingHistory $history) {
            return $history->toArray();
        }, $histories));
        MonthlyTradingHistoryCache::store($month, $recordsJson, $polletUser->id);

        return $histories;
    }

    /**
     * @param int $updatedAt
     * @return bool
     */
    private function cardValueCacheIsLive(int $updatedAt): bool
    {
        return $this->cacheIsLive($updatedAt, $this->cardValueCacheSeconds);
    }

    /**
     * @param int $updatedAt
     * @return bool
     */
    private function tradingHistoryCacheIsLive(int $updatedAt): bool
    {
        return $this->cacheIsLive($updatedAt, $this->tradingHistoryCacheSeconds);
    }

    /**
     * @param int $updatedAt
     * @param int $cacheSeconds
     * @return bool
     */
    private function cacheIsLive(int $updatedAt, int $cacheSeconds): bool
    {
        $livingTime = time() - $updatedAt;

        return $livingTime <= $cacheSeconds;
    }
}
