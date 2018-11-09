<?php
namespace app\index_v1\controller;

use think\Request;
use app\index_v1\validate\Common;
use app\index_v1\model\Queue;

class Index
{
    /**
     * 入队：入口
     * 出队：使用的是ThinkPHP自带的CLI请在command文件夹中进行查阅
     *
     * @param Request $request
     * @return void
     */
    public function send(Request $request)
    {
        $parameter = $request->only(['type', 'data']);

        $validate = new Common();

        if (!$validate->check($parameter)) {
            return json([ 'code' => 302, 'msg' => $validate->getError() ] , 200);
        }

        $jsonDecode = json_decode($parameter ['data'], true);

        if (empty($jsonDecode) || !is_array($jsonDecode))
        {
            $jsonDecode = [];
        }

        Queue::write_business($parameter ['type'], $jsonDecode);
    }
}