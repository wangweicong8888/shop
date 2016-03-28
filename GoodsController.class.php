<?php
namespace Admin\Controller;
use Think\Controller;
class GoodsController extends Controller
{
	public function goods_number()
	{
	    header('Content-Type:text/html;charset=utf8');
	    //接收商品id
	    $id = I('get.id');
	    // 根据商品id取出这件商品所有可选属性的值
	    $gaModel = D('goods_attr');
	    $gaData = $gaModel->alias('a')
	    ->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr=b.attr')
	    ->where(array(
	    	'a.goods_id' => array('eq',$id),
	    	'b.attr_type' => array('eq','可选')，
	    ))->select();
	}
	// 处理删除属性
	public function ajaxDelAttr()
	{
	    $goodsId = addslashes(I('get.goods_id'));
	    $gaid = addslashes(I('get.gaid'));
	    $gaModel = D('goods_attr');
	    $gaModel->delete($gaid);
	    // 删除相关库存量
	    $gnModel = D('goods_number');
	    $gnModel->where(array(
	    	'goods_id' => array('EXP',"=$goodsId or AND FIND_IN_SET($gaid,attr_list)"),
	    ))->delete();
	}
	//处理属性的ajax请求
	public function ajaxGetAttr()
	{

	    $typeId = I('get.type_id');
	    // var_dump($typeId);die;
	    $attrModel = D('Attribute');
	    $attrData = $attrModel->where(array(
	    	'type_id'=> array('eq',$typeId),
	    ))->select();
	    // var_dump($attrData);die;
	    echo json_encode($attrData);
	}
	//ajax 处理删除图片的请求
	public function ajaxDelPic()
	{
	    $picId = I('get.picid');
	    //删除从库和硬盘中
	    $gpModel = D('goods_pic');
	    $pic = $gpModel->field('pic,sm_pic,mid_pic,big_pic')->find($picId);
	    //从硬盘删除
	    deleteImage($pic);
	    $gpModel->delete($picId);
	}
	public function add()
	{
		if (IS_POST)
		{
			// ob_start();$s=array($_POST);foreach($s as $v){var_dump($v);}die('<pre>'.preg_replace(array('/\]\=\>\n(\s+)/m','/</m','/>/m'),array('] => ','&lt;','&gt;'),ob_get_clean()).'</pre>');
			set_time_limit(0);//设置执行时间,到底结束
			$model = D('goods');
			// var_dump(I('post.'));die;
			if ($model->create(I('post.'), 1))
			{
//				echo 1;die;
				if ($model->add())
				{
					$this->success('成功',U('lst'));
					exit;
				}
			}
			//
			$error = $model->getError();
			$this->error($error);
		}
		//取出所有品牌
		$brandModel = D('brand');
		$brandData = $brandModel->select();
		//取出所有的会员级别
		$mlModel = D('member_level');
		$mlData = $mlModel->select();
		//取出所有的分类做下拉框
		$catModel = D('category');
		$catData = $catModel->getTree();
		//
		 $this->assign(array(
		 	'catData' => $catData,
		 	'mlData' => $mlData,
		 	'brandData' => $brandData,
	    	'_page_title' => '添加新商品' ,
	    	'_page_btn_name'=> '商品列表',
	    	'_page_btn_link'=>U('lst'),
	     ));
		$this->display();
	}
	public function lst()
	{
	    $model = D('goods');
	    //返回数据和翻页
	    $data = $model->search();
	    // var_dump($data);die;
	    //取出分类数据做下拉框
	    $catModel = D('category');
		$catData = $catModel->getTree();

	    $this->assign($data);
	    $this->assign(array(
	    	'catData' => $catData,
	    	'_page_title' => '商品列表展示' ,
	    	'_page_btn_name'=> '添加新商品',
	    	'_page_btn_link'=>U('add'),
	     ));
	    $this->display();
	}
	public function edit()
	{
		$id = I('get.id');
		$model = D('goods');
	    if (IS_POST)
		{
			if ($model->create(I('post.'), 2))
			{
				if (FALSE !== $model->save())
				{
					$this->success('成功',U('lst'));
					exit;
				}
			}
			//
			$error = $model->getError();
			$this->error($error);
		}
		$brandModel = D('brand');
		$brandData = $brandModel->select();
		//会员价格信息
		$mlModel = D('member_level');
		$mlData = $mlModel->select();
		//取出所有的分类做下拉框
		$catModel = D('category');
		$catData = $catModel->getTree();
		//取出这件商品的会员价格信息
		$mpModel = D('member_price');
		$mpData = $mpModel->where(array(
			'goods_id'=>array('eq',$id),
		))->select();
		//是二维数组
		$_mpData = array();
		foreach ($mpData as $k => $v)
		{
			$_mpData[$v['level_id']] = $v['price'];//转一维数组键是等级，值是价格
		}
		// 取出相册中现有图片
		$gpModel = D('goods_pic');
		$gpData = $gpModel->field('id,mid_pic')->where(array(
			'goods_id'=>array('eq',$id),
		))->select();
		// 取出扩展分类ID
		$gcModel = D('goods_cat');
		$gcData = $gcModel->field('cat_id')->where(array(
			'goods_id' => array('eq',$id),
		))->select();
		// 取出这件商品已经设置了的属性值
		// $gaModel = D('goods_attr');
		// $gaData = $gaModel->alias('a')
		// ->field('a.*,b.attr_name,b.attr_type,b.attr_option_values')
		// ->join('LEFT JOIN __ATTRIBUTE__ b ON  a.attr_id=b.id')
		// ->where(array(
		// 	'a.goods_id' => array('eq',$id),
		// ))->select();
		$data = $model->find($id);

		// 取出当前类型下所有属性
		$attrModel = D('Attribute');
		$attrData = $attrModel->alias('a')
		->field('a.id attr_id,a.attr_name,a.attr_type,a.attr_option_values,b.attr_value,b.id')
		->join('LEFT JOIN __GOODS_ATTR__ b ON (a.id=b.attr_id AND b.goods_id='.$id.')')
		->where(array(
			'a.type_id'=> array('eq',$data['type_id']),
		))->select();
		// ob_start();$s=array($attrData);foreach($s as $v){var_dump($v);}die('<pre>'.preg_replace(array('/\]\=\>\n(\s+)/m','/</m','/>/m'),array('] => ','&lt;','&gt;'),ob_get_clean()).'</pre>');


		$this->assign('data',$data);
		 $this->assign(array(
		 	'catData' => $catData,
		 	'mlData' => $mlData,
		 	'mpData'=> $_mpData,
		 	'gpData'=> $gpData,
		 	'gcData' => $gcData,
		 	'attrData' => $attrData,
		 	'brandData' => $brandData,
	    	'_page_title' => '修改商品' ,
	    	'_page_btn_name'=> '商品列表',
	    	'_page_btn_link'=>U('lst'),
	     ));
		$this->display();
	}
	public function delete()
	{
	    $model = D('goods');
	    if (FALSE != $model->delete(I('get.id'))) {
	    	$this->success('删除成功！',U('lst'));
	    }else{
	    	$this->error('删除失败！原因：'.$model->getError());
	    }
	}
}