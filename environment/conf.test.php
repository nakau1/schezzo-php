<?php
return [
    // 環境モード
    'mode'             => 'test',
    // DB
    'db'               => [
        'host'     => 'test-daifuku.c5ssjo3sunyf.ap-northeast-1.rds.amazonaws.com',
        'database' => 'daifuku',
        'username' => 'daifuku',
        'password' => 'dorubakodorubako',
    ],
    // スクレイピング対象のセディナマイページURL
    'cedynaMyPageUrls' => [
        'sendIssuingFormLink' => 'https://testapp.pollet.me/demo/cedyna-send-email',
        'login'               => 'https://testapp.pollet.me/demo/cedyna-login',
        'activateCard'        => 'https://testapp.pollet.me/demo/cedyna-my-page',
        'cardValue'           => 'https://testapp.pollet.me/demo/cedyna-my-page',
        'tradingHistories'    => 'https://testapp.pollet.me/demo/cedyna-trading-history',
        'passwordReset'       => 'https://www.test-cdn.net/pollet/Contents/MBR/PasswordResetRequest.aspx',
        'memberSiteLink'      => 'https://www.test-cdn.net/pollet/Contents/MBR/TopPage.aspx',
    ],
    'appHost'          => 'https://testapp.pollet.me',
    'adminHost'        => 'https://admin.demoapp.pollet.me',
    'workerHost'       => 'http://localhost',
    'memcacheHost'     => 'pollet-demo-cache.wvxhhh.cfg.apne1.cache.amazonaws.com',
    'exchangeHost'     => 'https://testapp.pollet.me/exchange-api',
    'supportTo'        => 'ueda@tech-vein.com',
    'batchTo'          => 'ueda@tech-vein.com',
];