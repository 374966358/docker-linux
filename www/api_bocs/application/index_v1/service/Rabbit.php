<?php
namespace app\index_v1\service;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbit
{
    private static $connection;
    private static $channel;
    private static $exchangeName;
    private static $bindingKey;
    private static $queueName;

    /**
     * 创建RabbitMQ连接
     *
     * @param array $configArray 连接信息数组: 
     * [
     *      host: 地址,
     *      port：端口,
     *      user：用户,
     *      pass：密码,
     *      exchangeName：交换机名称,
     *      exchangeType：交换机类型,
     *      queueName：队列名称,
     *      bindnigKey：绑定KEY
     * ]
     */
    public static function connection(array $configArray)
    {
        try
        {
            // 连接RabbitMQ
            self::$connection = new AMQPStreamConnection(
                $configArray ['host'],
                $configArray ['port'],
                $configArray ['user'],
                $configArray ['pass']
            );

            self::$channel = self::$connection->channel();

            self::$exchangeName = $configArray ['exchangeName'];
            self::$bindingKey = $configArray ['bindnigKey'];
            self::$queueName = $configArray ['queueName'];

            self::binding(
                $configArray ['exchangeType']
            );
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage(), 300);
        }
    }

    /**
     * 用于绑定RabbitMQ的
     *
     * @param string $exchangeType 交换机类型
     * @return void
     */
    private static function binding(string $exchangeType)
    {
        /**
         * 创建交换机
         * 参数一：$exhcangeName 交换器名字
         * 参数二：$exchangeType 交换器类型
         *          fanout：扇形交换器 会发送消息到它所知道的所有队列，每个消费者获取的消息都是一致的
         *          headers：头部交换器
         *          direct：直连交换器，该交换机将会对绑定键（binding key）和路由键（routing key）进行精确匹配
         *          topic：话题交换器 该交换机会对路由键正则匹配，必须是 * (一个单词) 、#(多个单词，以.分割) 、      user.key .abc.* 类型的key
         * 参数三：$passive false
         * 参数四：durable false
         * 参数五：auto_detlete false
         */
        self::$channel->exchange_declare(self::$exchangeName, $exchangeType, false, false, false);

        /**
         * 创建消费者队列
         * 参数一：队列名称
         * 参数二：false
         * 参数三：true
         * 参数四：false
         * 参数五：false
         * 注意：如果是非持久话队列请将第三个参数改为false
         */
        self::$channel->queue_declare(self::$queueName, false, true, false, false);

        /**
         * 绑定交换机与队列关系
         * 参数一：队列名称
         * 参数二：交换机名称
         * 参数三：绑定的KEY
         */
        self::$channel->queue_bind(self::$queueName, self::$exchangeName, self::$bindingKey);
    }

    /**
     * 发送到RabbitMQ
     *
     * @param string $message 要发送的消息
     * @param integer $lasting 是否持久化 1 为非 2 为是
     * @param array $properties 可为空参照AMQPMessage第二个参数
     * @return void
     */
    public static function publish(string $message, int $lasting = 2, array $properties = [])
    {
        // 判断AMQPMessage第二个参数并填充默认数据
        if ($properties === [])
        {
            $properties = [
                'content_type' => 'text/plain',
                'delivery_mode' => $lasting
            ];
        }

        /**
         * 创建要发送的信息 ，可以创建多个消息
         * 参数一：要发送的消息
         * 参数二：设置的属性，比如设置该消息持久化[‘delivery_mode’=>2]
         */
        $messageResponse = new AMQPMessage($message, $properties);

        /**
         * 发送消息
         * 参数一：AMQPMessage对象
         * 参数二：交换机名字
         * 参数三：路由键 如果交换机类型
         *          fanout：该值会被忽略，因为该类型的交换机会把所有它知道的队列发消息，无差别区别
         *          direct：只有精确匹配该路由键的队列，才会发送消息到该队列
         *          topic：只有正则匹配到的路由键的队列，才会发送到该队列
         */
        self::$channel->basic_publish($messageResponse, self::$exchangeName, self::$bindingKey);

        /**
         * 关闭通道和连接
         */
        self::$channel->close();
        self::$connection->close();
    }

    /**
     * 消费信息
     *
     * @param object $callback 回调函数
     * @return void
     */
    public static function consume($callback)
    {
        /**
         * 消费消息
         * 参数一：队列名称
         * 参数二：未知
         * 参数三：false
         * 参数四：no_ack false 时，表示进行ack应答，确保消息已经处理
         * 参数五：false
         * 参数六：回调函数
         */
        self::$channel->basic_consume(self::$queueName, '', false, false, false, false, $callback);

        while (count(self::$channel->callbacks))
        {
            self::$channel->wait();
        }

        self::$channel->close();
        self::$connection->close();
    }
}