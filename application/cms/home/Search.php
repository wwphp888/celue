<?php
// +----------------------------------------------------------------------
// | 股票策略系统 [ V1.02 ]
// +----------------------------------------------------------------------
// | 版权所有 2018~2022 山东软淘电子商务有限公司 [ http://www.sdruantao.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://www.sdruantao.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\cms\home;

use think\Db;

/**
 * 前台搜索控制器
 * @package app\cms\admin
 */
class Search extends Common
{
    /**
     * 搜索列表
     * @param string $keyword 关键词
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     */
    public function index($keyword = '')
    {
        if ($keyword == '') $this->error('请输入关键字');
        $map = [
            'cms_document.trash'  => 0,
            'cms_document.status' => 1,
            'cms_document.title'  => ['like', "%$keyword%"]
        ];

        $data_list = Db::view('cms_document', true)
            ->view('admin_user', 'username', 'cms_document.uid=admin_user.id', 'left')
            ->where($map)
            ->order('create_time desc')
            ->paginate(config('list_rows'));

        $this->assign('keyword', $keyword);
        $this->assign('lists', $data_list);
        $this->assign('pages', $data_list->render());

        return $this->fetch(); // 渲染模板
    }
}