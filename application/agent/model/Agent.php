<?php
namespace app\agent\model;

use think\Model;
use think\helper\Hash;
use think\Db;
use util\Tree;

class Agent extends Model{



    protected $table = '__AGENT__';

    // 自动写入时间戳
     //protected $autoWriteTimestamp = true;
    protected $autoWriteTimestamp = 'timestrap';
    protected $createTime = 'agent_time';
  	 protected $updateTime = false;
   // protected $updateTime = false;
   // protected $readonly = ['register_time'];
    // 对密码进行加密
    public function setAgentPasswordAttr($value)
    {
        return Hash::make((string)$value);
    }

    // 获取注册ip


     public function withdraw()
    {
        return $this->hasMany('Withdraw','withdraw_agent');
    }

    /**
     * 获取树形节点
     * @param int $id 需要隐藏的节点id
     * @param string $default 默认第一个节点项，默认为“顶级节点”，如果为false则不显示，也可传入其他名称
     * @param string $module 模型名
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     */
    public static function getMenuTree($id = 0, $default = '', $module = '')
    {
        $result[0]       = '一级代理';

      	if($id!=0){
      		$where['agent_parent'] = $id;
      	}
        // 获取节点
        $menus = Tree::toList(self::where($where)->order('agent_parent,id')->column('id,agent_parent as pid,agent_username as title'));

        //print_r($menus);exit;
        foreach ($menus as $menu) {
            $result[$menu['id']] = $menu['title_display'];
        }

             //print_r($result);exit;
     
        // 隐藏默认节点项
        if ($default === false) {
            unset($result[0]);
        }

        return $result;
    }






















}