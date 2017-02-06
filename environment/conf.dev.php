<?php
return [
    // 環境モード
    'mode'             => 'dev',
    // DB
    'db'               => [
        'host'     => '127.0.0.1',
        'database' => 'daifuku',
        'username' => 'root',
        'password' => 'oz-vision123',
    ],
    // セディナマイページURL スクレイピング用とリンク用
    'cedynaMyPageUrls' => [
        'sendIssuingFormLink' => 'https://www.test-cdn.net/pollet/Contents/REC/EnterEmailAddress.aspx',
        'login'               => 'https://www.test-cdn.net/pollet/Contents/MBR/MemberLogin.aspx',
        'activateCard'        => 'https://www.test-cdn.net/pollet/Contents/MBR/TopPage.aspx',
        'cardValue'           => 'https://www.test-cdn.net/pollet/Contents/MBR/TopPage.aspx',
        'tradingHistories'    => 'https://www.test-cdn.net/pollet/Contents/MBR/UsageDetailsBalance.aspx',
        'passwordReset'       => 'https://www.test-cdn.net/pollet/Contents/MBR/PasswordResetRequest.aspx',
        'memberSiteLink'      => 'https://www.test-cdn.net/pollet/Contents/MBR/TopPage.aspx',
    ],
    'appHost'          => 'http://schezzo.vagrant.net',
    'adminHost'        => 'http://admin.schezzo.vagrant.net',
    'workerHost'       => 'http://schezzo.vagrant.net/worker',
    'memcacheHost'     => 'localhost',
    'exchangeHost'     => 'http://schezzo.vagrant.net/exchange-api',
    'supportTo'        => 'ueda@tech-vein.com',
    'batchTo'          => 'ueda@tech-vein.com',
];