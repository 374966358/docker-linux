<?php
namespace app\index_v1\validate;

use think\Validate;

class Common extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'type'  => 'require',
        'data'  => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'type'  => '类型必须填写',
        'data'  => '数据缺失',
    ];
}
