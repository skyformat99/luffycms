<?php
namespace app\admin\model;

use think\exception\PDOException;
use \think\Db;
use \think\Model;

class Role extends Model
{
    protected $auto = ['status'];

    protected $type = [
        'id'          => 'integer',
        'status'      => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    /**
     * 获取状态
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-04-19T16:00:40+0800
     * @param    string                   $value [description]
     * @return   [type]                          [description]
     */
    public function getStatusAttr($value, $data)
    {
        $status = [1 => '<span class="label label-success">启用</span>', 0 => '<span class="label label-warning">禁用</span>'];
        return $status[$value];
    }

    /**
     * 获取器 获取用户数量
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-05-19T15:47:23+0800
     * @param    [type]                   $value [description]
     * @param    [type]                   $data  [description]
     * @return   [type]                          [description]
     */
    public function getUserCountAttr($value, $data)
    {
        return Db::table('user')->where([
            'role_id' => $data['id'],
        ])->count();
    }

    /**
     * 多对多关联权限表
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-05-19T14:10:40+0800
     * @return   [type]                   [description]
     */
    public function rule()
    {
        return $this->belongsToMany('Rule', 'role_rule', 'rule_id', 'role_id');
    }

    /**
     * 添加角色
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-05-19T15:04:27+0800
     * @param    array                    $data [description]
     */
    public function addRole(array $data)
    {
        return Db::transaction(function () use ($data) {
            $roleId = Role::save([
                'status' => $data['status'],
                'name'   => $data['name'],
                'remark' => $data['remark'],
            ]);

            if ($roleId === false) {
                throw new PDOException("用户组添加失败");
            }

            $roleModle     = Role::find($roleId);
            $data['rules'] = array_map('intval', $data['rules']);
            //插入关联表
            $roleModle->rule()->saveAll($data['rules']);
        });
    }

}
