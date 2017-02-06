<?php
namespace app\models\cedyna_files;

class NumberedCedynaIdData
{
    public $pollet_user_id;
    public $cedyna_id;

    public function __construct(array $data)
    {
        $this->cedyna_id = $data[2];
        $this->pollet_user_id = $data[3];
    }
}