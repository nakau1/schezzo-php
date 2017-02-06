<?php

namespace tests\unit\models\cedyna_files;

use app\models\cedyna_files\CedynaFile;
use app\models\exceptions\CedynaFile\FileNotExistsException;
use RuntimeException;
use tests\unit\fixtures\CedynaFileFixture;
use yii\codeception\TestCase;

/**
 * Class CedynaFileTest
 * @package tests\unit\models\cedyna_files
 * @property CedynaFileFixture $fixture
 */
class CedynaFileTest extends TestCase
{
    public $appConfig = '@app/config/console.php';

    public function setUp()
    {
        parent::setUp();
    }

    public function fixtures()
    {
        return [
            'fixture' => CedynaFileFixture::class,
        ];
    }

    /**
     * @test
     * @dataProvider ディレクトリ名と存在するcsvファイル
     *
     * @param array $expectFileNames
     * @param string $directory
     */
    public function ディレクトリに存在するcsvファイルをすべて取得できる(array $expectFileNames, string $directory)
    {
        $findFiles = CedynaFile::findAll($directory);
        $findFileNames = array_map(function (CedynaFile $file) {
            return $file->getName();
        }, $findFiles);

        foreach ($expectFileNames as $fileName) {
            $this->assertContains($fileName, $findFileNames);
        }
    }

    /**
     * @return array
     */
    public function ディレクトリ名と存在するcsvファイル()
    {
        return [
            [
                $this->fixture->csvファイルが入っていないディレクトリのcsvファイル,
                $this->fixture->csvファイルが入っていないディレクトリ,
            ],
            [
                $this->fixture->csvファイルが1つ入っているディレクトリのcsvファイル,
                $this->fixture->csvファイルが1つ入っているディレクトリ,
            ],
            [
                $this->fixture->csvファイルが複数入っているディレクトリのcsvファイル,
                $this->fixture->csvファイルが複数入っているディレクトリ,
            ],
            [
                $this->fixture->誤った拡張子が入っているディレクトリの正しい拡張子のファイル,
                $this->fixture->誤った拡張子が入っているディレクトリ,
            ],
        ];
    }

    /**
     * @test
     */
    public function ディレクトリに存在するcsvファイルとtxtファイル以外を取得しない()
    {
        $findFiles = CedynaFile::findAll($this->fixture->誤った拡張子が入っているディレクトリ);
        $findFileNames = array_map(function (CedynaFile $file) {
            return $file->getName();
        }, $findFiles);

        foreach ($this->fixture->誤った拡張子が入っているディレクトリの誤った拡張子のファイル as $fileName) {
            $this->assertNotContains($fileName, $findFileNames);
        }
    }

    /**
     * @test
     */
    public function ファイルの移動ができる()
    {
        $srcDirectory = $this->fixture->csvファイルが1つ入っているディレクトリ;
        $dstDirectory = $this->fixture->csvファイルが入っていないディレクトリ;
        $filename = $this->fixture->csvファイルが1つ入っているディレクトリのcsvファイル[0];
        $originalContent = file_get_contents("{$srcDirectory}/{$filename}");

        $cedynaFile = new CedynaFile("{$srcDirectory}/{$filename}");
        $cedynaFile->moveTo($dstDirectory);

        // 移動先にファイルができている
        $this->assertFileExists("{$dstDirectory}/{$filename}");
        // 移動元のファイルがなくなっている
        $this->assertFileNotExists("{$srcDirectory}/{$filename}");
        // 移動後の内容が移動前と同一である
        $this->assertEquals($originalContent, file_get_contents("{$dstDirectory}/{$filename}"));
    }

    /**
     * @test
     * @dataProvider 書き込みできないディレクトリ
     * @param string $dstDirectory
     */
    public function 移動先にファイルが書き込めない場合例外が発生する(string $dstDirectory)
    {
        $srcDirectory = $this->fixture->csvファイルが1つ入っているディレクトリ;
        $filename = $this->fixture->csvファイルが1つ入っているディレクトリのcsvファイル[0];

        $cedynaFile = new CedynaFile("{$srcDirectory}/{$filename}");
        $this->expectException(RuntimeException::class);
        $cedynaFile->moveTo($dstDirectory);
    }

    /**
     * @test
     * @dataProvider 書き込みできないディレクトリ
     * @param string $dstDirectory
     */
    public function 移動先にファイルが書き込めない場合移動元のファイルが消えない(string $dstDirectory)
    {
        $srcDirectory = $this->fixture->csvファイルが1つ入っているディレクトリ;
        $filename = $this->fixture->csvファイルが1つ入っているディレクトリのcsvファイル[0];
        $originalContent = file_get_contents("{$srcDirectory}/{$filename}");

        $cedynaFile = new CedynaFile("{$srcDirectory}/{$filename}");

        try {
            $cedynaFile->moveTo($dstDirectory);
            $this->fail();
        } catch (RuntimeException $ignored) {
        }

        // 移動元のファイルが消えない
        $this->assertFileExists("{$srcDirectory}/{$filename}");
        // 処理後の内容が処理前と同一である
        $this->assertEquals($originalContent, file_get_contents("{$srcDirectory}/{$filename}"));
    }

    /**
     * @return array
     */
    public function 書き込みできないディレクトリ()
    {
        return [
            '存在しないディレクトリ'                   => [$this->fixture->存在しないディレクトリ],
            '書き込み権限がないディレクトリ（rootユーザーだと失敗）' => [$this->fixture->書き込み権限がないディレクトリ],
        ];
    }

    /**
     * @test
     */
    public function csvファイルを読み取ることができる()
    {
        $file = new CedynaFile($this->fixture->読み取りのテストに使うcsvのパス);
        $lines = [];

        foreach ($file->readLinesAll() as $line) {
            $lines[] = $line;
        }

        // 文字列のダブルクォートが取り除かれていること
        $this->assertEquals('test', $lines[0][0]);
        // 数値を読み取ることができる
        $this->assertEquals(123, $lines[0][1]);
        // 2行目以降を読み取ることができる
        $this->assertEquals(['abc', 'def', 45], $lines[1]);
    }

    /**
     * @test
     */
    public function 存在しないファイルを読み取ろうとした場合例外が発生する()
    {
        $file = new CedynaFile("{$this->fixture->csvファイルが入っていないディレクトリ}/not_exists.csv");
        $this->expectException(FileNotExistsException::class);
        //IDEで$ignoredに出るwarningは無視していい
        foreach ($file->readLinesAll() as $ignored) {
        };
    }

    /**
     * @test
     */
    public function ファイル名を一意に変更する()
    {
        $beforeRenameFiles = CedynaFile::findAll($this->fixture->csvファイルが複数入っているディレクトリ);
        //rename前のファイルパスを取り出す
        $beforeRenameNames = array_map(function (CedynaFile $file) {
            return $file->getName();
        }, $beforeRenameFiles);

        $afterRenameFiles = CedynaFile::renameToUnique($beforeRenameFiles);
        $afterRenameNames = array_map(function (CedynaFile $file) {
            return $file->getName();
        }, $afterRenameFiles);

        //rename後のディレクトリからファイルを取得しパスを取り出す
        $afterRenameResultFiles = CedynaFile::findAll($this->fixture->csvファイルが複数入っているディレクトリ);
        $afterRenameResultNames = array_map(function (CedynaFile $file) {
            return $file->getName();
        }, $afterRenameResultFiles);

        //ファイル名が変更されていることを確認
        $this->assertContains($afterRenameResultNames[0], $afterRenameNames);
        $this->assertContains($afterRenameResultNames[1], $afterRenameNames);

        //元のファイルが存在していないことを確認
        $this->assertNotContains($beforeRenameNames[0], $afterRenameNames);
        $this->assertNotContains($beforeRenameNames[1], $afterRenameNames);
    }

    /**
     * @test
     * @dataProvider 文字コードがSJISであることを判定できるデータ
     */
    public function 文字コードがSJISであることを判定できる(bool $isSjis, string $evalString)
    {
        $file = new CedynaFile("{$this->fixture->csvファイルが入っていないディレクトリ}/test.csv");
        $this->assertEquals($isSjis, $file->setSaveContent($evalString)->isSjisSaveContent());
    }

    public function 文字コードがSJISであることを判定できるデータ()
    {
        return [
            // SJIS
            [true, mb_convert_encoding('aAあア亜', 'SJIS')],
            // UTF8
            [false, 'aAあア亜'],
            // EUC-JP
            [false, mb_convert_encoding('aAあア亜', 'EUC-JP')],
            // 二重エンコード
            [false, mb_convert_encoding(mb_convert_encoding('aあア亜', 'SJIS'), 'SJIS')],
            // 失敗するが、ASCII文字のみの場合、SJISに変換しても中身は同じなので問題ない
            // [true, mb_convert_encoding('abcABC', 'SJIS')],
        ];
    }

    /**
     * @test
     */
    public function ファイルを削除できる()
    {
        $srcDirectory = $this->fixture->csvファイルが1つ入っているディレクトリ;
        $filename = $this->fixture->csvファイルが1つ入っているディレクトリのcsvファイル[0];
        $cedynaFile = new CedynaFile("{$srcDirectory}/{$filename}");

        $this->assertFileExists($cedynaFile->getPath());
        $cedynaFile->remove();
        $this->assertFileNotExists($cedynaFile->getPath());
    }

    /**
     * @test
     */
    public function 存在しないファイルを削除しようとしてもエラーにならない()
    {
        $srcDirectory = $this->fixture->csvファイルが入っていないディレクトリ;
        $cedynaFile = new CedynaFile("{$srcDirectory}/not_exists.csv");

        $this->assertFileNotExists($cedynaFile->getPath());
        $cedynaFile->remove();
        $this->assertFileNotExists($cedynaFile->getPath());
    }
}