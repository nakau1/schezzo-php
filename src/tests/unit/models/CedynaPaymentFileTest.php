<?php

namespace tests\unit\models;

use app\models\ChargeRequestHistory;
use app\models\cedyna_files\CedynaPaymentFile;
use tests\unit\fixtures\CedynaPaymentFileFixture;
use yii\codeception\TestCase;
use Faker;

class CedynaPaymentFileTest extends TestCase
{
    /** @var CedynaPaymentFile cedynaPaymentFile */
    public $cedynaPaymentFile;
    public $appConfig = '@app/config/console.php';

    public function setUp()
    {
        $this->cedynaPaymentFile = new CedynaPaymentFile();
        parent::setUp();
    }

    public function fixtures()
    {
        return [
            'fixture' => CedynaPaymentFileFixture::class
        ];
    }
    /**
     * @test
     */
    public function 入金ファイルに出力するデータの成形()
    {
        $chargeRequest = ChargeRequestHistory::find()->where(['id' => 100001])->one();
        $outputRow = $this->cedynaPaymentFile->outputRow($chargeRequest);

        //レコード区分
        $this->assertEquals('"D"', $outputRow[0]);
        //入金種別
        $this->assertEquals('"0421"', $outputRow[1]);
        //イシュアコード
        $this->assertEquals('"CEDYNA"', $outputRow[2]);
        //提携先コード
        $this->assertEquals('"0002"', $outputRow[3]);
        //カード種別区分
        $this->assertEquals('"00020001"', $outputRow[4]);
        //会員グループ番号
        $this->assertEquals('"'.$chargeRequest->polletUser->cedyna_id.'"', $outputRow[5]);
        //会員番号
        $this->assertEquals('"'.$chargeRequest->polletUser->cedyna_id.'"', $outputRow[6]);
        //カードID
        $this->assertEquals('""', $outputRow[7]);
        //入金額
        $this->assertEquals('"'.$chargeRequest->charge_value.'"', $outputRow[8]);
        //加盟店名（チャージ理由）
        $this->assertEquals('"'.$chargeRequest->cause.'"', $outputRow[9]);
        //処理結果
        $this->assertEquals('""', $outputRow[10]);
        //エラーコード
        $this->assertEquals('""', $outputRow[11]);
    }
}