<?php
namespace app\index_v1\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use app\index_v1\service\Rabbit;

/**
 * 运行命令：
 * 根目录下 php think 类名
 */
class Business extends Command
{
    protected function configure()
    {
        $this->setName('Business')
            ->setDescription('Business');
    }

    protected function execute(Input $input, Output $output)
    {
        $config = [
            'host'          => config('rabbit.server.host'),
            'port'          => config('rabbit.server.port'),
            'user'          => config('rabbit.server.user'),
            'pass'          => config('rabbit.server.pass'),
            'vhost'         => config('rabbit.server.vhost'),
            'exchangeName'  => config('rabbit.businessConfirm.exchangeName'),
            'exchangeType'  => config('rabbit.businessConfirm.exchangeType'),
            'queueName'     => config('rabbit.businessConfirm.queueName'),
            'bindnigKey'    => config('rabbit.businessConfirm.bindnigKey'),
        ];

        $con = Rabbit::connection($config);

        $callback = function ($message) {
            Db::table('tp5_test')->insert([
                'a' => $message->body
            ]);

            $message->delivery_info ['channel']->basic_ack($message->delivery_info ['delivery_tag']);
        };

        Rabbit::consume($callback);
    }
}