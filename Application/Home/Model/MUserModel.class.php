<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	用户
// +----------------------------------------------------------------------
class MUserModel extends Model{
	
	function __construct(){
		$this->user		= "user";
		$this->branch	= "branch";
		$this->partner	= "partner";
	}	
	
	/*
	* 注册
	* @post:
	**/
	public function Register($data) {		
		
	}
	
	/*
	* 登录
	* @post:
	* 	mobile		邮箱
	*	password	密码
	**/
	public function Login($post) {
		$result = M($this->user)->alias('u')
				->join(DB_PREFIX.$this->branch.' b on u.BRANCH_MAP_ID = b.BRANCH_MAP_ID')
				->where("u.USER_NO='".$post['user_no']."' and u.USER_STATUS != 2")
				->field('u.*,b.*,u.HOST_MAP_ID HOST_MAP_ID')
				->find();
		
		if(empty($result)) {
			return array('state'=>1, 'msg'=>"该用户工号不存在！");		
		}
		if($result['USER_PASSWD'] != $post['password']) {
			return array('state'=>1, 'msg'=>"密码错误！");
		}
		if($result['USER_STATUS'] == 1) {
			return array('state'=>1, 'msg'=>"该账户已冻结！");
		}
		//超过3个月, 注销
		/*if(C('SPECIAL_USER') != $result['USER_ID']){
			if (strtotime('+2 month',strtotime($result['ACTIVE_TIME'])) < time()) {
				$updata = array(
					'USER_STATUS'	=>	2,
					'ACCESS_TOKEN'	=>	strtoupper(md5($result['USER_NO'].$result['USER_PASSWD'].$result['USER_ID'])),
					'UPDATE_TIME'	=>	date('YmdHis')
				);
				D($this->user)->where("USER_ID='".$result['USER_ID']."'")->save($updata);
				return array('state'=>1, 'msg'=>"该账户超过15天未登录, 已被系统注销！");
			}
		}*/
		//登录成功
		$upuser = array(
			'LOGIN_IP'=>		get_client_ip(),
			'ACCESS_TOKEN'=>	strtoupper(md5($result['USER_NO'].$result['USER_PASSWD'].$result['USER_ID'])),
			'ACTIVE_TIME'=>		date('YmdHis'),
			'UPDATE_TIME'=>		date('YmdHis'),
		);
		D($this->user)->where("USER_ID='".$result['USER_ID']."'")->save($upuser);
		
		//返回数据
		$res = array(
			'state'=>		0, 
			'msg'=>			"恭喜您，登录成功！", 
			'userinfo'=>	$result
		);
		return $res;
	}
		
	
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countUser($where) {
		return M($this->user)->alias('u')
				->join(DB_PREFIX.$this->branch.' b on u.BRANCH_MAP_ID = b.BRANCH_MAP_ID', 'LEFT')
				->join(DB_PREFIX.$this->partner.' a on u.PARTNER_MAP_ID = a.PARTNER_MAP_ID', 'LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getUserlist($where, $field='*', $limit, $order='u.USER_ID desc') {
		return M($this->user)->alias('u')
				->join(DB_PREFIX.$this->branch.' b on u.BRANCH_MAP_ID = b.BRANCH_MAP_ID', 'LEFT')
				->join(DB_PREFIX.$this->partner.' a on u.PARTNER_MAP_ID = a.PARTNER_MAP_ID', 'LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(u.CREATE_TIME,"%Y-%m-%d %H:%i:%s") as CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addUser($data) {
		$result = M($this->user)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '用户添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateUser($where, $data) {
		$result = M($this->user)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '用户修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delUser($where) {
		$result = M($this->user)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '用户删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条	more
	* @post:
	**/
	public function findmoreUser($where, $field='u.*,b.*,a.*') {
		return M($this->user)->alias('u')
				->join(DB_PREFIX.$this->branch.' b on u.BRANCH_MAP_ID = b.BRANCH_MAP_ID', 'LEFT')
				->join(DB_PREFIX.$this->partner.' a on u.PARTNER_MAP_ID = a.PARTNER_MAP_ID', 'LEFT')
				->where($where)
				->field($field)
				->find();
	}
	
	/*
	* 获取单条
	* @post:
	**/
	public function findUser($where, $field='*') {
		return M($this->user)->where($where)->field($field)->find();
	}
}
