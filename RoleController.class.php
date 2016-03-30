<?php
namespace Admin\Controller;
use Think\Controller;
class RoleController extends Controller
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Role');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('lst?p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
        // 取出所有权限
        $priModel = D('privilege');
        $priData = $priModel->getTree();

		// 设置页面中的信息
		$this->assign(array(
            'priData' => $priData,
			'_page_title' => '添加角色',
			'_page_btn_name' => '角色列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function edit()
    {
    	$id = I('get.id');
    	if(IS_POST)
    	{
    		$model = D('Role');
    		if($model->create(I('post.'), 2))
    		{
    			if($model->save() !== FALSE)
    			{
    				$this->success('修改成功！', U('lst', array('p' => I('get.p', 1))));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	$model = M('Role');
    	$data = $model->find($id);
    	$this->assign('data', $data);

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '修改角色',
			'_page_btn_name' => '角色列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Role');
    	if($model->delete(I('get.id', 0)) !== FALSE)
    	{
    		$this->success('删除成功！', U('lst', array('p' => I('get.p', 1))));
    		exit;
    	}
    	else
    	{
    		$this->error($model->getError());
    	}
    }
    public function lst()
    {
    	$model = D('Role');
    	$data = $model->search();
        // ob_start();$s=array($data);foreach($s as $v){var_dump($v);}die('<pre>'.preg_replace(array('/\]\=\>\n(\s+)/m','/</m','/>/m'),array('] => ','&lt;','&gt;'),ob_get_clean()).'</pre>');
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '角色列表',
			'_page_btn_name' => '添加角色',
			'_page_btn_link' => U('add'),
		));
    	$this->display();
    }
}