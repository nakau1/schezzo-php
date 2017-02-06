<?php
namespace app\components;

use app\models\cedyna_files\CedynaFile;
use app\models\exceptions\CedynaFile\DirectoryNotWritableException;
use app\models\exceptions\CedynaFile\FileAlreadyExistsException;
use app\models\exceptions\CedynaFile\FileWritingFailedException;
use app\models\exceptions\Hulft\ReceivingException;
use app\models\exceptions\Hulft\SendingException;
use Yii;
use yii\base\Component;

/**
 * HULFTサーバとのファイル送受信やサーバ上でのオペレーションを行う
 * @package app\components
 */
class Hulft extends Component
{
    // 配信に失敗すると次の配信時に追加になってしまうので長めに取っておく
    // 7200秒 = 2時間 でタイムアウト
    const COMMAND_SEND_PAYMENT      = 'utlsend -f RCDPOL01 -sync -w 7200';
    const FILE_SEND_PAYMENT         = 'rcdpol01.txt';

    // 次のファイルができてしまうので、
    // 3600秒 = 1時間 経過したらエラー扱いにする
    const COMMAND_RECEIVE_PAYMENT   = 'utlrecv -f SCDPOL01 -sync -w 3600';
    const FILE_RECEIVE_PAYMENT      = 'scdpol01.txt';

    // 86400 = 24時間 でタイムアウト
    const COMMAND_RECEIVE_CEDYNA_ID = 'utlrecv -f SCDPOL02 -sync -w 86400';
    const FILE_RECEIVE_CEDYNA_ID    = 'scdpol02.txt';

    // 86400 = 24時間 でタイムアウト
    const COMMAND_RECEIVE_TRADING   = 'utlrecv -f SCDPOL03 -sync -w 86400';
    const FILE_RECEIVE_TRADING      = 'scdpol03.txt';

    private $host;
    private $user;
    private $identityFile;

    /**
     * init
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user)
    {
        $this->user = $user;
    }

    /**
     * @param string $identityFile
     */
    public function setIdentityFile(string $identityFile)
    {
        $this->identityFile = $identityFile;
    }

    /**
     * 入金ファイルを同期配信する。
     * 配信が完了するまで待ち状態になる。
     *
     * @param CedynaFile $file 送るファイル
     * @throws SendingException HULFTの終了ステータスが0以外の場合例外を投げる。
     *                          例外コードとして終了ステータスを格納する。
     * @throws DirectoryNotWritableException HULFT送信用のディレクトリに書き込みできない
     * @throws FileAlreadyExistsException    前回のファイルがまだ送信されてない
     * @throws FileWritingFailedException    ファイルの書き込みに失敗した
     */
    public function sendCedynaPaymentFileSync(CedynaFile $file)
    {
        $sendFile = $file->copyTo(Yii::$app->params['hulftPath'].'/send');
        $sendFile->renameTo(self::FILE_SEND_PAYMENT);

        $this->execOnHulftServer(self::COMMAND_SEND_PAYMENT, $output, $status);

        if ($status !== 0) {
            throw new SendingException(implode(PHP_EOL, $output), $status);
        }
    }

    /**
     * 入金結果ファイルの同期送信要求を行う。
     * 集信が完了するまで待ち状態になる。
     *
     * @return string 受け取ったファイルのパス
     * @throws ReceivingException 終了ステータスが0以外の場合例外を投げる。
     *                            例外コードとして終了ステータスを格納する。
     */
    public function receiveCedynaPaymentFileSync()
    {
        $this->execOnHulftServer(self::COMMAND_RECEIVE_PAYMENT, $output, $status);

        if ($status !== 0) {
            throw new ReceivingException(implode(PHP_EOL, $output), $status);
        }

        return Yii::$app->params['hulftPath'].'/recv/'.self::FILE_RECEIVE_PAYMENT;
    }

    /**
     * セディナID発番通知ファイルの同期送信要求を行う。
     * 集信が完了するまで待ち状態になる。
     *
     * @return string 受け取ったファイルのパス
     * @throws ReceivingException 終了ステータスが0以外の場合例外を投げる。
     *                            例外コードとして終了ステータスを格納する。
     */
    public function receiveNumberedCedynaIdFileSync()
    {
        $this->execOnHulftServer(self::COMMAND_RECEIVE_CEDYNA_ID, $output, $status);

        if ($status !== 0) {
            throw new ReceivingException(implode(PHP_EOL, $output), $status);
        }

        return Yii::$app->params['hulftPath'].'/recv/'.self::FILE_RECEIVE_CEDYNA_ID;
    }

    /**
     * 取引履歴情報ファイルの同期送信要求を行う。
     * 集信が完了するまで待ち状態になる。
     *
     * @return string 受け取ったファイルのパス
     * @throws ReceivingException 終了ステータスが0以外の場合例外を投げる。
     *                            例外コードとして終了ステータスを格納する。
     */
    public function receiveTradingHistoryFileSync()
    {
        $this->execOnHulftServer(self::COMMAND_RECEIVE_TRADING, $output, $status);

        if ($status !== 0) {
            throw new ReceivingException(implode(PHP_EOL, $output), $status);
        }

        return Yii::$app->params['hulftPath'].'/recv/'.self::FILE_RECEIVE_TRADING;
    }

    /**
     * HULFTサーバでコマンドを実行する
     * @param string     $command サーバで実行するコマンド
     * @param array|null $output  標準出力結果が格納される
     * @param int|null   $status  終了ステータスが格納される
     * @return string 標準出力内容（最終行）
     */
    protected function execOnHulftServer(string $command, &$output = null, &$status = null)
    {
        $sshCommand = 'ssh -i '.escapeshellarg($this->identityFile)
            .' '.escapeshellarg("{$this->user}@{$this->host}")
            .' '.escapeshellarg($command);

        return exec($sshCommand, $output, $status);
    }
}
