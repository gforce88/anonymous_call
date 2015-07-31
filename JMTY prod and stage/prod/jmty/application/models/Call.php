<?php
require_once 'log/LoggerFactory.php';
class Application_Model_Call extends Zend_Db_Table_Abstract {
	protected $_name = 'calls';
	
	public function init() {
		$this->logger = LoggerFactory::getSysLogger ();
	}
	
	//创建拨号记录
	public function createCall($params = array()) {
		$this->logger->logInfo("Application_Model_Call","createCall","create call record");
		$newrow = $this->createRow ();
		$newrow->firstName = $params["firstName"];
		$newrow->lastName = $params["lastName"];
		$newrow->patientNumber = $params["patientNumber"];
		$newrow->patientEmail = $params["patientEmail"];
		$newrow->paypaltoken = $params["paypaltoken"];
		$newrow->trytimes = 1;
		$newrow->save();
		return $newrow;
	}
	
	//更新病人的tropo sessionId
	public function updatePatientSessionId($inx=null,$callsessionId=null){
		$row = $this->find ($inx )->current ();
		if ($row) {
			$row->patientSessionId = $callsessionId;
			$row->save ();
			return $row;
		}
	}
	
	//更新专家的tropo sessionId
	public function updateSpecialistSessionId($inx=null,$specialistSessionId=null){
		$row = $this->find ($inx )->current ();
		if ($row) {
			$row->specialistSessionId = $specialistSessionId;
			$row->save ();
			return $row->inx;
		}
	}
	
	//查询病人的拨号次数
	public function checkCallTimes($inx = null){
		$row = $this->find ($inx )->current ();
		return $row ["trytimes"];
	}
	
	//更新病人拨号次数
	public function updateTryTimes($patientSessionId = null){
		$select = $this->select ();
		$select->where ( 'patientSessionId = ?', $patientSessionId );
		$row = $this->fetchRow ( $select );
		$row->trytimes = $row ["trytimes"] + 1;
		$row->save ();
		return $row;
	}
	
	
	//更新病人接通电话的时间
	public function updatePatientCallTime($patientSessionId = null){
		$select = $this->select ();
		$select->where ( 'patientSessionId = ?', $patientSessionId );
		$row = $this->fetchRow ( $select );
		$time = date ( 'Y-m-d H:i:s' );
		if ($row) {
			$row->patientCallTime = $time;
			$row->save ();
			return $row;
		}else{
			return null;
		}
	}
	
	//更新专家接通电话的时间
	public function updateSpecialistCallTime($specialistSessionId = null){
		$select = $this->select ();
		$select->where ( 'specialistSessionId = ?', $specialistSessionId );
		$row = $this->fetchRow ( $select );
		$time = date ( 'Y-m-d H:i:s' );
		if ($row) {
			$row->specialistCallTime = $time;
			$row->save ();
			return $row;
		}else{
			return null;
		}
	}
	
	//更新专家挂电话的时间，即会议结束时间
	public function updateGrpCallEndTime($specialistSessionId = null){
		$select = $this->select ();
		$select->where ( 'patientSessionId = ?', $specialistSessionId );
		$row = $this->fetchRow ( $select );
		$time = date ( 'Y-m-d H:i:s' );
		if ($row) {
			$row->grpCallEndTime = $time;
			$row->save ();
			return $row;
		}else{
			return null;
		}
	}
	
	//根据专家sessionId获取call
	public function findCallBySpecialistSessionId($specialistSessionId = null){
		$select = $this->select ();
		$select->where ( 'specialistSessionId = ?', $specialistSessionId );
		$row = $this->fetchRow ( $select );
		return $row;
	}
}

