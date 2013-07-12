<?php

namespace Archives\Model;

use Zend\Db\TableGateway\TableGateway;
use module\Application\src\Model\Tool;

class Auto {
	protected $tableGateway;
	private $user;
	function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
		$this->user = Tool::getSession ( 'auth', 'user' );
	}
	/**
	 * @todo 保存数据
	 * @param Array $data
	 * @param string $id
	 */
	function save($data, $id = null) {
		$data=(array)$data;
		$data['uid'] = $this->user->id;
		$data['domain'] = $this->user->domain;
		if ($id)
		{
			$this->tableGateway->update($data,array('id'=>$id));
		}else {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
		}
		return $id;
	}
	/**
	 * @todo 读取单条数据
	 * @param int $id
	 * @throws \Exception
	 * @return mixed
	 */
	function getAutoID($id) {
		$id = (int)$id;
		$rowSet  = $this->tableGateway->select(array('id'=>$id));
		$row = $rowSet->current();
		if (!$row)
		{
			throw new \Exception('数据为空');
		}
		return $row;
	}
	/**
	 * @todo  删除数据
	 * @param Int
	 *  $id
	 */
	function delete($id)
	{
		$this->tableGateway->delete(array('id'=>$id));
	}
	/**
	 * @todo 汽车变速器
	 * @return multitype:string
	 */
	function transmission()
	{
		$t=array('1'=>'手动','2'=>'自动','3'=>'手自一体','4'=>'无极变速');
		return $t;
	}
}