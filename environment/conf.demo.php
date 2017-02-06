<?php
//    'dsn' => 'mysql:host=demo-daifuku.c5ssjo3sunyf.ap-northeast-1.rds.amazonaws.com;dbname=daifuku',

return [
    // 環境モード
    'mode'             => 'demo',
    // DB
    'db'               => [
        'host'     => 'demo-daifuku.c5ssjo3sunyf.ap-northeast-1.rds.amazonaws.com',
        'database' => 'daifuku',
        'username' => 'daifuku',
        'password' => 'dorubakodorubako',
    ],
    'hulft'            => [
        'host'         => '10.29.0.223',
        'user'         => 'centos',
        'identityFile' => '/home/ec2-user/.ssh/demo-hulft',
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
    'appHost'          => 'https://demoapp.pollet.me',
    'adminHost'        => 'https://admin.demoapp.pollet.me',
    'memcacheHost'     => 'pollet-demo-cache.wvxhhh.cfg.apne1.cache.amazonaws.com',
    'workerHost'       => 'http://localhost',
    'exchangeHost'     => 'https://demoapi.pollet.me/exchange-api',
    'supportTo'        => 'ueda@tech-vein.com',
    'batchTo'          => 'ueda@tech-vein.com',
];