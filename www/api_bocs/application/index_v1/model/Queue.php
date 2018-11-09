<?php
namespace app\index_v1\model;

use think\Model;
use app\index_v1\service\Rabbit;

class Queue extends Model
{
    /**
     * RabbitMQ服务器配置
     *
     * @return void
     */
    private static function server_config()
    {
        $serverConfig = [
            'host'  => config('rabbit.server.host'),
            'port'  => config('rabbit.server.port'),
            'user'  => config('rabbit.server.user'),
            'pass'  => config('rabbit.server.pass'),
            'vhost' => config('rabbit.server.vhost'),
        ];

        return $serverConfig;
    }

    /**
     * 商机队列配置
     *
     * @return void
     */
    private static function write_business_config()
    {
        $businessConfig = [
            'exchangeName'  => config('rabbit.businessInfo.exchangeName'),
            'exchangeType'  => config('rabbit.businessInfo.exchangeType'),
            'queueName'     => config('rabbit.businessInfo.queueName'),
            'bindnigKey'    => config('rabbit.businessInfo.bindnigKey'),
        ];

        return $businessConfig;
    }

    /**
     * 写入商机队列
     *
     * @param string $type 传入的类型参考手册
     * @param array $data 传入的数据
     * @return void
     */
    public static function write_business(string $type, array $data = [])
    {
        $config = array_merge(self::server_config(), self::write_business_config());

        self::send_queue($config, $type, $data);
    }

    /**
     * 执行Queue
     *
     * @param array $config
     * @param string $type
     * @param array $data
     * @return void
     */
    private static function send_queue(array $config, string $type, array $data)
    {
        $queryId = session_create_id();

        $message = json_encode([
            'q_id'      => $queryId,
            'q_type'    => $type,
            'q_data'    => $data,
            'q_time'    => date('Y-m-d H:i:s')
        ]);

        try
        {
            Rabbit::connection($config);
            Rabbit::publish($message);
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage(), 300);
        }
    }
}