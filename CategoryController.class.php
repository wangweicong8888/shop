<?php
namespace Admin\Controller;
use Think\Controller;
class CategoryController extends Controller
{
    public function add()
    {
        $model = D('category');
        if (IS_POST)
        {
            if($model->create(I('.post'),1))
            {
                if ($id = $model->add())
                {
                    $this->success('添加成功',U('lst?p='.I('get.p')));
                    exit;
                }
            }
            $this->error($model->getError());
        }
        //设置页面信息
        $catData = $model->getTree();
        $this->assign(array(
            'catData' => $catData,
            '_page_title' => '添加分类',
            '_page_btn_name' => '分类列表',
            '_page_btn_link' => U('lst'),
        ));
        $this->display();
    }
    public function edit()
    {
        $id = I('get.id');
        $model = D('category');
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
        $data = $model->find($id);
        //取出所有分类数据做下拉框
        $catData = $model->getTree();
        //取当前分类的子分类
        $children = $model->getChildren($id);

        $this->assign(array(
            'data' => $data,
            'children' => $children,
            'catData'  => $catData,
            '_page_title' => '修改商品' ,
            '_page_btn_name'=> '商品列表',
            '_page_btn_link'=>U('lst'),
         ));
        $this->display();
    }
    public function lst()
    {
        $model = D('category');
        $data = $model->getTree();
        //设置页面信息
        $this->assign(array(
            'data' => $data,
            '_page_title' => '商品列表展示' ,
            '_page_btn_name'=> '添加新商品',
            '_page_btn_link'=>U('add'),
        ));
        $this->display();
    }
    //删除
    public function delete()
    {
        $model = D('category');
        if (FALSE !== $model->delete(I('get.id')))
        {
            $this->success('删除成功',U('lst'));
        }else{
            $this->error('删除失败，原因是：'.$model->getError());
        }
    }
}