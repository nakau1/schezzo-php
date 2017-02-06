<?php

namespace tests\unit\models\cedyna_my_pages;

use app\components\CedynaMyPageWithCache;
use app\models\CardValueCache;
use app\models\exceptions\CedynaMyPage\CedynaMyPageException;
use app\models\MonthlyTradingHistoryCache;
use app\models\PolletUser;
use app\models\TradingHistory;
use tests\unit\fixtures\CedynaMyPageWithCacheFixture;
use Yii;
use yii\codeception\TestCase;

class CedynaMyPageWithCacheTest extends TestCase
{
    public $appConfig = '@app/config/console.php';

    public function setUp()
    {
        parent::setUp();
    }

    public function fixtures()
    {
        return [
            CedynaMyPageWithCacheFixture::class,
        ];
    }

    /**
     * @test
     */
    public function 残高のキャッシュが無い場合にキャッシュを作成する()
    {
        // カード残高取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasNoCaches])->one();
        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $cardValueResult = $myPage->cardValue();

        // 取得したカード残高でキャッシュが生成されているか
        $cache = CardValueCache::find()->where(['pollet_user_id' => $user->id])->one();
        $this->assertNotEmpty($cache);
        $this->assertEquals($cardValueResult, $cache->value);
    }

    /**
     * @test
     */
    public function 残高のキャッシュが古い場合にキャッシュを利用しない()
    {
        // キャッシュを取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCache])->one();
        $cachedValue = CardValueCache::find()->where(['pollet_user_id' => $user->id])->one()->value;

        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $this->assertNotEquals($cachedValue, $myPage->cardValue());
    }

    /**
     * @test
     */
    public function 残高のキャッシュが古い場合にキャッシュを更新する()
    {
        // カード残高取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCache])->one();
        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $cardValueResult = $myPage->cardValue();

        // 取得したカード残高でキャッシュが更新されているか
        $cache = CardValueCache::find()->where(['pollet_user_id' => $user->id])->one();
        $this->assertEquals($cardValueResult, $cache->value);
    }

    /**
     * @test
     */
    public function 残高のキャッシュが古い場合に他人のキャッシュを更新しない()
    {
        // 別の人のキャッシュを取得
        $anotherUser = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCacheAnother])->one();
        $beforeCache = CardValueCache::find()->where(['pollet_user_id' => $anotherUser->id])->one();

        // カード残高取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCache])->one();
        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $myPage->cardValue();

        // 別の人のキャッシュが残高取得前後で値が変わっていないか
        $cache = CardValueCache::find()->where(['pollet_user_id' => $anotherUser->id])->one();
        $this->assertEquals($beforeCache->value, $cache->value);
    }

    /**
     * @test
     */
    public function 残高のキャッシュが新しい場合にキャッシュを取得する()
    {
        // キャッシュを取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasLiveCache])->one();
        $cachedValue = CardValueCache::find()->where(['pollet_user_id' => $user->id])->one()->value;

        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $this->assertEquals($cachedValue, $myPage->cardValue());
    }

    /**
     * @test
     */
    public function 残高のキャッシュが新しい場合にキャッシュを更新しない()
    {
        // キャッシュを取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasLiveCache])->one();
        $cachedValue = CardValueCache::find()->where(['pollet_user_id' => $user->id])->one()->value;

        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $myPage->cardValue();

        // 残高取得前後で値が変わっていないか
        $cache = CardValueCache::find()->where(['pollet_user_id' => $user->id])->one();
        $this->assertEquals($cachedValue, $cache->value);
    }

    /**
     * @test
     */
    public function 利用履歴のキャッシュがない場合にキャッシュを作成する()
    {
        // カード利用履歴取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasNoCaches])->one();
        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $histories = $myPage->tradingHistories(CedynaMyPageWithCacheFixture::$notExistsCacheMonth);
        $historiesAsArray = array_map(function (TradingHistory $record) {
            return $record->toArray();
        }, $histories);

        // 取得した利用履歴でキャッシュが生成されているか
        $cache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $user->id,
            'month'          => CedynaMyPageWithCacheFixture::$notExistsCacheMonth,
        ])->one();

        $this->assertNotEmpty($cache);
        $this->assertEquals($historiesAsArray, json_decode($cache->records_json, true));
    }

    /**
     * @test
     */
    public function 利用履歴のキャッシュが古い場合にキャッシュを利用しない()
    {
        // キャッシュを取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCache])->one();
        $cache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $user->id,
            'month'          => CedynaMyPageWithCacheFixture::$oldCacheMonth,
        ])->one();

        // カード利用履歴取得
        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $histories = $myPage->tradingHistories(CedynaMyPageWithCacheFixture::$oldCacheMonth);
        $historiesAsArray = array_map(function (TradingHistory $record) {
            return $record->toArray();
        }, $histories);

        $this->assertNotEquals($historiesAsArray, json_decode($cache->records_json, true));
    }

    /**
     * @test
     */
    public function 利用履歴のキャッシュが古い場合にキャッシュを更新する()
    {
        // カード利用履歴取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCache])->one();
        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);

        $histories = $myPage->tradingHistories(CedynaMyPageWithCacheFixture::$oldCacheMonth);
        $historiesAsArray = array_map(function (TradingHistory $record) {
            return $record->toArray();
        }, $histories);

        // 取得したカード利用履歴でキャッシュが更新されているか
        $cache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $user->id,
            'month'          => CedynaMyPageWithCacheFixture::$oldCacheMonth,
        ])->one();

        $this->assertEquals($historiesAsArray, json_decode($cache->records_json, true));
    }

    /**
     * @test
     */
    public function 利用履歴のキャッシュが古い場合に他人のキャッシュを更新しない()
    {
        // 別の人のキャッシュを取得
        $anotherUser = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCacheAnother])->one();
        $beforeCache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $anotherUser->id,
            'month'          => CedynaMyPageWithCacheFixture::$anotherCacheMonth,
        ])->one();

        // カード利用履歴取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCache])->one();
        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);
        $myPage->tradingHistories(CedynaMyPageWithCacheFixture::$oldCacheMonth);

        // 別の人のキャッシュが利用履歴取得前後で値が変わっていないか
        $cache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $anotherUser->id,
            'month'          => CedynaMyPageWithCacheFixture::$anotherCacheMonth,
        ])->one();

        $this->assertEquals($beforeCache->records_json, $cache->records_json);
    }

    /**
     * @test
     */
    public function 利用履歴のキャッシュが新しい場合にキャッシュを取得する()
    {
        // キャッシュを取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasLiveCache])->one();
        $cache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $user->id,
            'month'          => CedynaMyPageWithCacheFixture::$liveCacheMonth,
        ])->one();

        // カード利用履歴取得
        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);
        $histories = $myPage->tradingHistories(CedynaMyPageWithCacheFixture::$liveCacheMonth);
        $historiesAsArray = array_map(function (TradingHistory $record) {
            return $record->toArray();
        }, $histories);

        $this->assertEquals(json_decode($cache->records_json, true), $historiesAsArray);
    }

    /**
     * @test
     */
    public function 残高の取得に失敗した場合にキャッシュを更新しない()
    {
        // キャッシュを取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCache])->one();
        $beforeCache = CardValueCache::find()->where(['pollet_user_id' => $user->id])->one();

        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);
        // 残高取得を失敗させる
        $myPage->setUrls(['cardValue' => 'notfound']);
        try {
            $myPage->cardValue();
            $this->fail('期待する例外が発生しませんでした');
        } catch (CedynaMyPageException $ignored) {
        }

        // 処理後のキャッシュを取得
        $afterCache = CardValueCache::find()->where(['pollet_user_id' => $user->id])->one();
        $this->assertEquals($beforeCache->value, $afterCache->value);
    }

    /**
     * @test
     */
    public function 利用履歴の取得に失敗した場合にキャッシュを更新しない()
    {
        // キャッシュを取得
        $user = PolletUser::find()->active()->where(['id' => CedynaMyPageWithCacheFixture::$polletUserIdHasOldCache])->one();
        $beforeCache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $user->id,
            'month'          => CedynaMyPageWithCacheFixture::$oldCacheMonth,
        ])->one();

        $myPage = CedynaMyPageWithCache::getInstance();
        $myPage->login($user->cedyna_id, $user->rawPassword);
        // 残高取得を失敗させる
        $myPage->setUrls(['tradingHistories' => 'notfound']);
        try {
            $myPage->tradingHistories(CedynaMyPageWithCacheFixture::$oldCacheMonth);
            $this->fail('期待する例外が発生しませんでした');
        } catch (CedynaMyPageException $ignored) {
        }

        // 処理後のキャッシュを取得
        $afterCache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $user->id,
            'month'          => CedynaMyPageWithCacheFixture::$oldCacheMonth,
        ])->one();
        $this->assertEquals($beforeCache->records_json, $afterCache->records_json);
    }
}