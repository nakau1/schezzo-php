<?php
$envConf = \app\Environment::get();

return [
    // admin
    $envConf['adminHost']                                           => 'admin/default/index',
    $envConf['adminHost'] . '/<controller:[\w-]+>'                  => 'admin/<controller>/index',
    $envConf['adminHost'] . '/<controller:[\w-]+>/<action:[\w-]+>'  => 'admin/<controller>/<action>',

    // worker
    $envConf['workerHost']                                          => 'worker/default/index',
    $envConf['workerHost'] . '/<controller:[\w-]+>'                 => 'worker/<controller>/index',
    $envConf['workerHost'] . '/<controller:[\w-]+>/<action:[\w-]+>' => 'worker/<controller>/<action>',

    // 交換API
    'exchange/<reception:[\w_-]+>'                                  => 'exchange-api/index',
    $envConf['exchangeHost'] . '/<action:[\w-]+>'                   => 'exchange/default/index',
    $envConf['exchangeHost'] . '/<action:[\w-]+>/<site_code:\w*>'   => 'exchange/default/<action>',

    // API
    'api/'                                                          => 'api/default/index',
    'api/initialize'                                                => 'api/default/initialize',
    'api/badge-count'                                               => 'api/default/badge-count',

    // DefaultController
    ''                                                              => 'default/index',
    'start'                                                         => 'default/start',
    'setting'                                                       => 'default/setting',
    'terms'                                                         => 'default/terms',
    'privacy-policy'                                                => 'default/privacy-policy',
    'card-terms'                                                    => 'default/card-terms',
    'js-error'                                                      => 'default/js-error',

    // GuideController
    'guide/first/visa-prepaid'                                      => 'guide/first-visa-prepaid',
    'guide/first/flow'                                              => 'guide/first-flow',
    'guide/first/usage'                                             => 'guide/first-usage',
    'guide/detail/member-number'                                    => 'guide/detail-member-number',
    'guide/detail/login-password'                                   => 'guide/detail-login-password',
    'guide/detail/card-pin'                                         => 'guide/detail-card-pin',
    'guide/detail/about-charge'                                     => 'guide/detail-about-charge',
    'guide/detail/available-shops'                                  => 'guide/detail-available-shops',
    'guide/detail/fee-in-foreign'                                   => 'guide/detail-fee-in-foreign',
    'guide/detail/about-steatment'                                  => 'guide/detail-about-steatment',
    'guide/detail/card-lost'                                        => 'guide/detail-card-lost',
    'guide/detail/change-registration'                              => 'guide/detail-change-registration',
    'guide/detail/card-management'                                  => 'guide/detail-card-management',
    'guide/detail/card-expiration'                                  => 'guide/detail-card-expiration',
    'guide/detail/recommended-environment'                          => 'guide/detail-recommended-environment',
];
