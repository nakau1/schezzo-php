<?php
namespace app\components;

use app\helpers\YearMonth;
use app\models\exceptions\CedynaMyPage\ScrapingException;
use app\models\exceptions\CedynaMyPage\UnauthorizedException;
use app\models\TradingHistory;
use Carbon\Carbon;
use Goutte\Client;
use InvalidArgumentException;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;
use Yii;
use yii\base\Component;

/**
 * Class CedynaMyPage
 * @package app\components
 */
class CedynaMyPage extends Component
{
    /**
     * @var string スクレイピング時のユーザーエージェント
     */
    const HTTP_USER_AGENT = 'pollet (+system@polletcorp.com)';
    /**
     * @var string 経路ID、セディナ取決めの固定値
     */
    const ROUTE_ID = '0001';
    /**
     * @var string
     */
    protected $sendIssuingFormLinkUrl = '';
    /**
     * @var string
     */
    protected $loginUrl = '';
    /**
     * @var string
     */
    protected $activateCardUrl = '';
    /**
     * @var string
     */
    protected $cardValueUrl = '';
    /**
     * @var string
     */
    protected $tradingHistoriesUrl = '';

    /**
     * @var string
     */
    protected $cedynaId;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var bool
     */
    protected $loggedIn = false;
    /**
     * 取引履歴の取得対象項目
     *
     * @var array
     */
    private $historyTargets = [
        'ご利用',
        'ご利用取消',
        'チャージ手数料',
        'チャージ手数料取消',
        'チャージ',
    ];
    /**
     * 対象URL
     * @var array
     */
    public $urls = [];
    /**
     * @var Client
     */
    public $client;

    /**
     * init
     */
    public function init()
    {
        parent::init();

        $this->setUrls($this->urls);
        $this->client = new Client();
        $this->client->setServerParameters([
            'HTTP_USER_AGENT' => self::HTTP_USER_AGENT,
        ]);
        $this->client->setMaxRedirects(3);
    }

    /**
     * スクレイピング対象URLをセット
     *
     * @param string[] $urls
     */
    public function setUrls(array $urls)
    {
        $this->sendIssuingFormLinkUrl = $urls['sendIssuingFormLink'] ?? '';
        $this->loginUrl = $urls['login'] ?? '';
        $this->activateCardUrl = $urls['activateCard'] ?? '';
        $this->cardValueUrl = $urls['cardValue'] ?? '';
        $this->tradingHistoriesUrl = $urls['tradingHistories'] ?? '';
    }

    /**
     * 設定ファイルを反映したインスタンスを生成する
     *
     * @return CedynaMyPage|object
     */
    public static function getInstance()
    {
        return Yii::$app->get('cedynaMyPage');
    }

    /**
     * セディナのカード発行申込みページにメールアドレスを入力し、送信する
     *
     * @param string $email
     * @param string $polletId
     * @return bool
     */
    public function sendIssuingFormLink(string $email, string $polletId): bool
    {
        try {
            $crawler = $this->client->request('GET', $this->getIssuingFormLinkWithParam($polletId));
            $posts = $this->getAllHiddenData($crawler, '#form');
            $form = $crawler->filter('#form')->form();

            $crawler = $this->client->request(
                $form->getMethod(),
                $form->getUri(),
                array_merge($posts, [
                    $this->getInputNameById(
                        $crawler,
                        '#MainContent_DetailContent_cDetail_mail_Dtm_mail_address_TextBox'
                    ) => $email,
                    $this->getInputNameById(
                        $crawler,
                        '#MainContent_DetailContent_cDetail_mail_Dtm_mail_address2_TextBox'
                    ) => $email,
                    // ボタンのvalueを送らないとログイン出来ない
                    $this->getInputNameById(
                        $crawler,
                        '#MainContent_DetailButtonContent_BtnDecision'
                    ) => $crawler->filter(
                        '#MainContent_DetailButtonContent_BtnDecision'
                    )->first()->attr('value'),
                ])
            );

            return mb_strpos(
                    $crawler->filter('.page-guide')->first()->text(),
                    'お申込みページのURLをお送りしました'
                ) !== false;

        } catch (InvalidArgumentException $e) {
            throw new ScrapingException('スクレイピング中にエラーが発生しました', 0, $e);
        }
    }

    /**
     * セディナのマイページにログインを施行する。
     *
     * @param string $cedynaId
     * @param string $password
     * @return bool
     */
    public function login(string $cedynaId, string $password)
    {
        try {
            $crawler = $this->client->request('GET', $this->loginUrl);

            $posts = $this->getAllHiddenData($crawler, '#formMain');
            $form = $crawler->filter('#formMain')->form();
            $this->client->request(
                $form->getMethod(),
                $form->getUri(),
                array_merge($posts, [
                    $this->getInputNameById($crawler, '#TbxMemberNumber') => $cedynaId,
                    $this->getInputNameById($crawler, '#TbxPassword')     => $password,
                    // ボタンのvalueを送らないとログイン出来ない
                    $this->getInputNameById($crawler, '#BtnLogin')        => $crawler->filter(
                        '#BtnLogin'
                    )->first()->attr('value'),
                ])
            );
            /** @var Response $response */
            $response = $this->client->getResponse();
            if ($response->getStatus() != 200) {
                return false;
            }
            // トップページにアクセスしてログイン出来たか調べる
            $crawler = $this->client->request('GET', $this->cardValueUrl);
            $logoutButton = $crawler->filter('#BtnLogout');
            if ($logoutButton->count() === 0 || mb_strpos(trim($logoutButton->attr('value')), 'ログアウト') === false) {
                return false;
            }
            $this->loggedIn = true;
            $this->cedynaId = $cedynaId;
            $this->password = $password;
            // マイページトップのアクティベートボタンをすべて押下
            $this->clickActivateMyPageRecursive($crawler);

            return true;
        } catch (InvalidArgumentException $e) {
            throw new ScrapingException('スクレイピング中にエラーが発生しました', 0, $e);
        }
    }

    /**
     * カード残高を取得する。
     * @return int
     */
    public function cardValue(): int
    {
        if (!$this->loggedIn) {
            Yii::warning('ログインしていません');
            throw new UnauthorizedException('ログインしていません');
        }

        try {
            $crawler = $this->client->request('GET', $this->cardValueUrl);

            return $this->removeCardValueFormat($crawler->filter('#member-balance')->first()->text());
        } catch (InvalidArgumentException $e) {
            throw new ScrapingException('スクレイピング中にエラーが発生しました', 0, $e);
        }
    }

    /**
     * カード利用履歴を取得する。
     *
     * @param string $month
     * @return TradingHistory[]
     */
    public function tradingHistories(string $month): array
    {
        if (!$this->loggedIn) {
            Yii::warning('ログインしていません');
            throw new UnauthorizedException('ログインしていません');
        }
        list($fromDate, $toDate) = YearMonth::getTimestampsFromTo($month);

        try {
            $crawler = $this->client->request('GET', $this->tradingHistoriesUrl);
            // カード番号「すべて」を選択（カードを選択するとチャージ履歴が出てこない）
            $cardNumber = $crawler->filter(
                '#MainContent_SearchContent_cSearch_Srh_card_id_ddlList > option:nth-child(1)'
            )->first()->attr('value');

            $posts = $this->getAllHiddenData($crawler, '#form');
            $form = $crawler->filter('#form')->form();
            $crawler = $this->client->request(
                $form->getMethod(),
                $form->getUri(),
                array_merge($posts, [
                    $this->getInputNameById(
                        $crawler,
                        '#MainContent_SearchContent_cSearch_Srh_card_id_ddlList'
                    ) => $cardNumber,
                    $this->getInputNameById(
                        $crawler,
                        '#MainContent_SearchContent_cSearch_Srh_transaction_datetime_TextBox'
                    ) => date('Y/m/d', $fromDate),
                    $this->getInputNameById(
                        $crawler,
                        '#MainContent_SearchContent_cSearch_Srh_transaction_datetime2_TextBox'
                    ) => date('Y/m/d', $toDate),
                    $this->getInputNameById(
                        $crawler,
                        '#MainContent_SearchContent_BtnSearch'
                    ) => $crawler->filter('#MainContent_SearchContent_BtnSearch')->first()->attr('value'),
                ])
            );

            return array_filter($crawler->filter('.list-row')->each(function (Crawler $node) {
                $tradingType = $node->filter('.Label_Lst_procType2')->text();
                if (in_array($tradingType, $this->historyTargets, true)) {
                    $history = new TradingHistory();
                    $history->spentValue = $this->removeCardValueFormat($node->filter('.Label_Lst_procAmount')->text());
                    $history->shop = $node->filter('.Label_Lst_merchantName')->text();
                    $history->tradingDate = Carbon::parse($node->filter('.Label_Lst_purchaseDate')->text());
                    $tradingTypeDefinitions = [
                        'ご利用'     => TradingHistory::TYPE_USE,
                        'チャージ手数料' => TradingHistory::TYPE_CHARGE_FEE,
                        'チャージ'    => TradingHistory::TYPE_CHARGE,
                    ];
                    $history->tradingType = $tradingTypeDefinitions[$tradingType];

                    return $history;
                }

                return false;
            }), function ($history) {
                return $history != false;
            });
        } catch (InvalidArgumentException $e) {
            throw new ScrapingException('スクレイピング中にエラーが発生しました', 0, $e);
        }
    }


    /**
     * アクティベートボタンを全て押下する
     *
     * ページに'activate-card'クラスのタグがなくなるまで再帰的に処理し続ける
     * @param Crawler $crawler
     */
    private function clickActivateMyPageRecursive(Crawler $crawler)
    {
        $crawler->filter('.activate-card')->each(function (Crawler $node) {
            $node->selectLink('アクティベート')->link();
        });
    }

    /**
     * 指定formのhiddenフィールドを全て取得する
     *
     * @param Crawler $crawler
     * @param         $formId
     * @return array
     */
    private function getAllHiddenData(Crawler $crawler, string $formId)
    {
        $inputs = $crawler->filter("{$formId} input[type='hidden']")->each(function (Crawler $node) {
            $hidden[$node->attr('name')] = $node->attr('value') ?? '';
            return $hidden;
        });

        $results = [];
        foreach ($inputs as $input) {
            foreach ($input as $key => $val) {
                if (empty($key)) {
                    continue;
                }
                $results[$key] = $val;
            }
        }

        return $results;
    }

    /**
     * フォーマットされた金額を数値に変換する
     *
     * @param string $formattedCardValue
     * @return int
     */
    private function removeCardValueFormat(string $formattedCardValue): int
    {
        return (int)str_replace(',', '', $formattedCardValue);
    }

    /**
     * IDからnameを取得する
     *
     * @param Crawler $crawler
     * @param string  $id
     * @return null|string
     */
    private function getInputNameById(Crawler $crawler, string $id)
    {
        return $crawler->filter($id)->first()->attr('name');
    }

    /**
     * 提携先ID(polletId)と経路ID(先方取決めの固定値)を引き渡してカード発行を行う
     *
     * @param string $polletId 提携先idとして渡すpolletId
     * @return string パラメタ付きカード発行登録用URL
     */
    public function getIssuingFormLinkWithParam(string $polletId)
    {
        $queryData = ['partner_id' => $polletId, 'route_id' => self::ROUTE_ID];
        return $this->sendIssuingFormLinkUrl . '?' . http_build_query($queryData);
    }
}
