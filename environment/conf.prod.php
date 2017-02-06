<?php return [
    // 環境モード
    'mode'             => 'prod',
    // DB
    'db'               => [
        'host'     => 'prod-daifuku.c5ssjo3sunyf.ap-northeast-1.rds.amazonaws.com',
        'database' => 'daifuku',
        'username' => 'daifuku',
        'password' => 'oz-pol0301',
    ],
    'hulft'            => [
        'host'         => '10.29.0.223',
        'user'         => 'centos',
        'identityFile' => '/home/ec2-user/.ssh/prod-hulft',
    ],
    // セディナマイページURL スクレイピング用とリンク用
    'cedynaMyPageUrls' => [
        'sendIssuingFormLink' => 'https://www.prepaid-cedyna.jp/pollet/Contents/REC/EnterEmailAddress.aspx',
        'login'               => 'https://www.prepaid-cedyna.jp/pollet/Contents/MBR/MemberLogin.aspx',
        'activateCard'        => 'https://www.prepaid-cedyna.jp/pollet/Contents/MBR/TopPage.aspx',
        'cardValue'           => 'https://www.prepaid-cedyna.jp/pollet/Contents/MBR/TopPage.aspx',
        'tradingHistories'    => 'https://www.prepaid-cedyna.jp/pollet/Contents/MBR/UsageDetailsBalance.aspx',
        'passwordReset'       => 'https://www.prepaid-cedyna.jp/pollet/Contents/MBR/PasswordResetRequest.aspx',
        'memberSiteLink'      => 'https://www.prepaid-cedyna.jp/pollet/Contents/MBR/TopPage.aspx',
    ],
    'appHost'          => 'https://app.pollet.me',
    'adminHost'        => 'https://admin.app.pollet.me',
    'memcacheHost'     => 'pollet-prod-cache.wvxhhh.cfg.apne1.cache.amazonaws.com',
    'workerHost'       => 'http://localhost',
    'exchangeHost'     => 'https://api.pollet.me/exchange-api',
    'supportTo'        => 'system@polletcorp.com',
    'batchTo'          => 'system@polletcorp.com',
];