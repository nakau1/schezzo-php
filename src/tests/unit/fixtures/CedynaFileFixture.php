<?php
namespace tests\unit\fixtures;

use yii\helpers\FileHelper;
use yii\test\Fixture;

class CedynaFileFixture extends Fixture
{
    private $testTemporaryPath;
    public $csvファイルが入っていないディレクトリ;
    public $csvファイルが1つ入っているディレクトリ;
    public $csvファイルが複数入っているディレクトリ;
    public $誤った拡張子が入っているディレクトリ;
    public $存在しないディレクトリ;
    public $書き込み権限がないディレクトリ;
    public $csvファイルが入っていないディレクトリのcsvファイル = [];
    public $csvファイルが1つ入っているディレクトリのcsvファイル = ['1abc.csv'];
    public $csvファイルが複数入っているディレクトリのcsvファイル = ['2abc.csv', 'def2.csv'];
    public $誤った拡張子が入っているディレクトリの正しい拡張子のファイル = ['xyz.csv', 'abc.txt'];
    public $誤った拡張子が入っているディレクトリの誤った拡張子のファイル = ['xyz', 'csv.log'];

    public $読み取りのテストに使うcsvのパス;
    public $読み取りのテストに使うcsvの内容;
    public $読み取りのテストに使うHeader付きcsvのパス;
    public $読み取りのテストに使うHeader付きcsvの内容;


    public function init()
    {
        $this->testTemporaryPath = '/tmp/phpunit';
        $this->csvファイルが入っていないディレクトリ = $this->testTemporaryPath.'/0';
        $this->csvファイルが1つ入っているディレクトリ = $this->testTemporaryPath.'/1';
        $this->csvファイルが複数入っているディレクトリ = $this->testTemporaryPath.'/2';
        $this->誤った拡張子が入っているディレクトリ = $this->testTemporaryPath.'/x';
        $this->存在しないディレクトリ = $this->testTemporaryPath.'/not_exists';
        $this->書き込み権限がないディレクトリ = $this->testTemporaryPath.'/permission_denied';

        $this->読み取りのテストに使うcsvのパス = $this->testTemporaryPath.'/test.csv';
        $this->読み取りのテストに使うcsvの内容 = <<<CSV
"test",123
"abc","def",45
CSV;
        $this->読み取りのテストに使うHeader付きcsvのパス = $this->testTemporaryPath . '/test_with_header.csv';
        $this->読み取りのテストに使うHeader付きcsvの内容 = <<<CSV
"Header","Header"
"test",123
"abc","def",45
CSV;
    }

    public function load()
    {
        FileHelper::createDirectory($this->csvファイルが入っていないディレクトリ);
        foreach ($this->csvファイルが入っていないディレクトリのcsvファイル as $filename) {
            touch("{$this->csvファイルが入っていないディレクトリ}/{$filename}");
        }
        FileHelper::createDirectory($this->csvファイルが1つ入っているディレクトリ);
        foreach ($this->csvファイルが1つ入っているディレクトリのcsvファイル as $filename) {
            touch("{$this->csvファイルが1つ入っているディレクトリ}/{$filename}");
        }
        FileHelper::createDirectory($this->csvファイルが複数入っているディレクトリ);
        foreach ($this->csvファイルが複数入っているディレクトリのcsvファイル as $filename) {
            touch("{$this->csvファイルが複数入っているディレクトリ}/{$filename}");
        }
        FileHelper::createDirectory($this->誤った拡張子が入っているディレクトリ);
        foreach ($this->誤った拡張子が入っているディレクトリの正しい拡張子のファイル as $filename) {
            touch("{$this->誤った拡張子が入っているディレクトリ}/{$filename}");
        }
        foreach ($this->誤った拡張子が入っているディレクトリの誤った拡張子のファイル as $filename) {
            touch("{$this->誤った拡張子が入っているディレクトリ}/{$filename}");
        }
        FileHelper::createDirectory($this->書き込み権限がないディレクトリ, 000);

        $sjisContent = mb_convert_encoding($this->読み取りのテストに使うcsvの内容, 'SJIS');
        file_put_contents($this->読み取りのテストに使うcsvのパス, $sjisContent);

        $sjisContent = mb_convert_encoding($this->読み取りのテストに使うHeader付きcsvの内容, 'SJIS');
        file_put_contents($this->読み取りのテストに使うHeader付きcsvのパス, $sjisContent);
    }

    public function unload()
    {
        // FileHelper は使えない
        if (file_exists($this->書き込み権限がないディレクトリ)) {
            rmdir($this->書き込み権限がないディレクトリ);
        }

        FileHelper::removeDirectory($this->testTemporaryPath);
    }
}
