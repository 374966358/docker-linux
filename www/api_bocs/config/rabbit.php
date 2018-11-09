<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | rabbit配置文件
// +----------------------------------------------------------------------

return [
    'server' => [
        'host'  => '172.16.35.122',
        'port'  => '5672',
        'user'  => 'linux',
        'pass'  => 'linux',
        'vhost' => '/',
    ],

    'businessInfo' => [
        'exchangeName'  => 'ex_business_platform',
        'exchangeType'  => 'direct',
        'queueName'     => 'business_info',
        'bindnigKey'    => 'rk_business_info',
    ],

    'businessConfirm' => [
        'exchangeName'  => 'ex_business_platform',
        'exchangeType'  => 'direct',
        'queueName'     => 'business_info',
        'bindnigKey'    => 'rk_business_info',
    ],
];
