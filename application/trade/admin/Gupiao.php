<?php 

namespace app\trade\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\trade\model\Gupiao as GupiaoModel;
use think\Db;
use think\Hook; 
use think\Cache;
use Guzzlehttp\Client;
use GuzzleHttp\Promise;
use com\Pinyin;

/**
 * 订单管理控制器
 * @package app\trade\gupiao
 */
class Gupiao extends admin{
	public function index(){
	  // 查询
        $map = $this->getMap();
       
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = GupiaoModel::getgupiaolist($map,$order);
 // 导出按钮
        $btn_excel = [
            'title' => '导入Excel文件批量禁用',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('excel_add',http_build_query($this->request->param()))
        ];
         // 导入错误记录按钮
        $btn_error = [
            'title' => '导入错误记录',
            'icon'  => 'fa fa-fw fa-calendar-times-o',
            'href'  => url('excel_error')
        ];
       $btn_access = [
		    'title' => '审核',
		    'icon'  => 'fa fa-fw fa-key',
		    'href'  => url('edit', ['id' => '__id__'])
		];
        $btn_update = [
            'title' => '更新股票列表',
            'icon'  => 'fa fa-fw fa-cloud-upload',
            'class' => 'btn btn-primary ajax-get',
            'href'  => url('updatelist')
        ];

		// 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
         //->addFile('file', '文件', '', '', '1024', 'xls,xlsx')
        	->setTableName('gupiao_list')
        	//->hideCheckbox()
            ->setSearch(['title' => '名称','code'=>'代码'],'','',true) // 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['title', '股票名称'],
                ['code', '股票代码'],
                ['pinyin', '股票缩写'],
                ['add_time', '添加时间','datetime'],
                ['quota', '购买总限额(元)','text.edit'],
                ['status', '状态', 'status', '', ['禁用:danger', '启用:success']],
                ['right_button', '操作', 'btn']
            ])
            ->addValidate('Gupiao', 'quota') // 添加快捷编辑的验证器
            ->setColumnWidth('order_no,vip_phone', 130)
           // ->addRightButton('custom',$btn_access) // 批量添加右侧按钮
             ->addRightButtons('enable,disable')
             ->addTopButton('btn_update', $btn_update) // 添加授权按钮
            ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->addTopButton('custom', $btn_error) // 添加导出按钮

            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
	}

    public function excel_error(){
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = Db::name("gupiao_error")->where($map)->order($order)->paginate();
        // 使用ZBuilder快速创建数据表格
$bind_title = <<<JS
<script>
$(".builder-table-body tr").each(function(){
    $(this).find('td').eq(3).attr('title',$(this).find('td').eq(3).text());
})
</script>
JS;
        // 导出按钮
        $btn_excel = [
            'title' => '清空记录',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('error_clean',http_build_query($this->request->param()))
        ];
        return ZBuilder::make('table')
            ->setTableName('gupiao_error')
            ->hideCheckbox()
            ->setSearchArea([['text', 'gupiao_name', '股票名称搜索', 'like'],['text', 'gupiao_code', '股票代码搜索', 'like'],])
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['gupiao_name', '股票名称'],
                ['gupiao_code', '股票代码'],
                ['info', '内容'],
                ['add_time', '时间','datetime'],
            ])
            ->setColumnWidth('add_time', 150)
            ->setColumnWidth('info', 550)
            ->setRowList($data_list) // 设置表格数据
            ->addTopButton('custom', $btn_excel) // 添加导出按钮
            ->setExtraJs($bind_title)
            ->fetch(); // 渲染模板
    }
 public function error_clean(){
    $res = Db::name("gupiao_error")->where('id','>',0)->delete();
    if($res){
        $this->success("清空完成");
    }else{
        $this->error("清空失败");
    }
 }

    public function updatelist(){


                if(request()->isGet()){
                    $client = new \GuzzleHttp\Client(); 
                    $response = $client->get('http://q.jrjimg.cn/?q=cn|s|sa&c=m&n=hqa&o=pl,d&p=10099&_dc=1545887307343',[
                        'headers'=>[
                         'Content-Type'=>'application/json',
                         'Charset'=>'utf8'
                        ],
                        'decode_content' => false
                    ]);

                    $body = $response->getBody()->getContents();
                    $result =$this->checkbody($body);
                    $promises = [];
                    for($i=1;$i<=$result['Summary']['pages'];$i++){ 

                        $promises[$i.'pages'] =$client->getAsync('http://q.jrjimg.cn/?q=cn|s|sa&c=m&n=hqa&o=pl,d&p='.$i.'099&_dc=1545887307343');


                    }
                   $results = Promise\unwrap($promises);
                    $count = 0;
                     $error = 0;
                     $total = 0;
                     $update = 0;
                   Db::startTrans();
                   $update_result = true;
                    try{    
                   for($i=1;$i<=$result['Summary']['pages'];$i++){
                    $contents = $results[$i.'pages']->getBody()->getContents();
                     $list =$this->checkbody($contents);
                     $total+=count($list['HqData']);
                           foreach ($list['HqData'] as $key => $value) {
                               $value['2'] = str_replace('*','',$value['2']);
                               //$gupiaoinfo= GupiaoModel::get(['code'=>$value[1]]);
                                $gupiaoinfo= Db::name('gupiao_list')->where('code',$value[1])->find();
                               if($gupiaoinfo){
                                    /*$gupiaoinfo->title = $value[2];
                                    $gupiaoinfo->save();*/
                                    $gupiaoinfo['title'] = $value[2];
                                    Db::name('gupiao_list')->update($gupiaoinfo);
                                    $update++;
                                    
                               }else{                    
                                /*$user = GupiaoModel::create([
                                            'title'  =>  $value[2],
                                            'code' =>  $value[1],
                                            'pinyin'=>pinyin($value[2],'first'),
                                            'add_time'=>time()
                                        ]);*/
                                $data = [
                                    'title'=>$value[2],
                                    'code' =>  $value[1],
                                    'pinyin'=>strtoupper(Pinyin::pinyin($value[2],'first')),
                                    'add_time'=>time()
                                ];
                                $user=Db::name('gupiao_list')->insert($data);
                                   if($user){
                                        $count++;
                                    }else{
                                        $error++;
                                    }

                               }

                               
                            }

                        }

                       /* echo "添加".$count."<br>";
                        echo "失败".$error."<br>";
                        echo "更新".$update."<br>";
                        echo $total;*/
                        Db::commit();
                      
                    }catch (\Exception $e) {

                       $update_result =false; 
                        Db::rollback();
                        
                       
                    }


                    $update_result?$this->success('添加'.$count.'<br>失败'.$error.'<br>更新'.$update.'<br>总计'.$total):$this->error('更新失败');
                }





            }
            public function checkbody($body){

           
 
                $info = str_replace(';',"",substr($body,strpos($body,'=')+1));

                if(preg_match('/\w:/', $info)){
                        $info = preg_replace('/(\w+):/is', '"$1":', $info);
                    }
                $info_code=(mb_detect_encoding($info,array("ASCII",'UTF-8',"GB2312","GBK",'BIG5')));   
                if($info_code=='EUC-CN'){
                    //GBK2312
                $info =iconv("GB2312//IGNORE","UTF-8",$info); 
                } 
                 if($info_code=='CP936'){
                    $info =iconv("GBK//IGNORE","UTF-8",$info); 
                 }
              
                $info = json_decode($info,true);
                //print_r($info);   
                return $info;
                


            }


 public function excel_add()
    {
        // 提交数据
        if ($this->request->isPost()) {
            // 接收附件 ID
            $excel_file = $this->request->post('excel');
            // 获取附件 ID 完整路径
            $full_path = getcwd() . get_file_path($excel_file);
          /*  $import = plugin_action('Excel/Excel/read_excel_to_array',$full_path);
            die;*/
            // 只导入的字段列表
            $fields = [
                'name' => '股票名称',
                'code' => '股票代码'
            ];
            // 调用插件('插件',[路径,导入表名,字段限制,类型,条件,重复数据检测字段])
            $import = plugin_action('Excel/Excel/import', [$full_path,null, $fields, $type = 0, $where = null, $main_field = 'name']);
            if(!isset($import['data'][0]['Content'])){
                $this->error('文件解析失败');
            }
            $num = 0;
            $lss = array();
            foreach ($import['data'][0]['Content'] as $key => $value) {
                
                if($key > 1){
                    $check = Db::name("gupiao_list")->where("code",$value[1])->find();
                    if(!is_array($check) || !isset($check['id'])){
                        $this->setgupiao_error($value,'未查找到该股票，请核对股票代码');
                        continue;
                    }
                    if($check['status'] < 1){
                         $this->setgupiao_error($value,'该股票已为禁止状态');
                         continue;
                    }
                    $res = Db::name("gupiao_list")->where("code",$value[1])->setField('status',0);
                    if($res){
                        $num++;
                        continue;
                    }else{
                         $this->setgupiao_error($value,'股票状态设置失败，请手动设置');
                         continue;
                    }
                }
            }
            $this->success("执行成功，请点击到股票导入错误记录，查询执行错误股票");

        }

        // 创建演示用表单
        return ZBuilder::make('form')
            ->setPageTitle('导入Excel')
            ->addFormItems([ // 添加上传 Excel
                ['file', 'excel', '上传文件'],
            ])
            ->fetch();
    }


    private function setgupiao_error($value,$msg){
        $data['gupiao_name'] = $value[0];
        $data['gupiao_code'] = $value[1];
        $data['info'] = $msg;
        $data['add_time'] = time();
        Db::name("gupiao_error")->insert($data);
      
    }

}