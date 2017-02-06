<?php
date_default_timezone_set('Asia/Tokyo');

/**
 * Class PolletAcceptanceTester
 */
class PolletAcceptanceTester extends AcceptanceTester
{
    /**
     * 簡易UUIDを作成する
     * @return string
     */
    public static function generateUuid()
    {
        return implode('-', [
            substr(uniqid(), 0, 8),
            substr(uniqid(), 0, 4),
            substr(uniqid(), 0, 4),
            substr(uniqid(), 0, 4),
            substr(uniqid(), 0, 12),
        ]);
    }
}