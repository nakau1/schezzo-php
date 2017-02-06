<?php
namespace tests\unit\fixtures;

use app\models\BatchManagement;
use Yii;

class BacthManagementFixture extends PolletDbFixture
{
    private $batchName = '';
    public function __construct(string $batchName)
    {
        $this->batchName = $batchName;
    }

    public function load()
    {
        $this->setActive();
    }

    private function setActive()
    {
        $batchManagement = new BatchManagement();
        $batchManagement->name = $this->batchName;
        $batchManagement->status = BatchManagement::STATUS_ACTIVE;
        $batchManagement->save();
    }
}
