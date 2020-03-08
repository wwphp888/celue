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

namespace app\cms\model;

use think\Model as ThinkModel;

/**
 * 广告分类模型
 * @package app\cms\model
 */
class AdvertType extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CMS_ADVERT_TYPE__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
}