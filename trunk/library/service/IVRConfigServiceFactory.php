<?php
require_once 'models/CustomerManager.php';
require_once 'service/IVRConfigTropoService.php';

class IVRConfigServiceFactory{
	const DEFAULT_IVR_PROVIDER = "tropo";
	private static $configSVC;
	/*
	*	get ivr configuration service
	*/
	public static function getIvrConfiguration($accountId) {
	
		if (is_null (self::$configSVC)){
			
			$ivr_provider = self::DEFAULT_IVR_PROVIDER;
			$customerManager = new CustomerManager();
			$customer = $customerManager->getById($accountId);
			if(isset($customer["ivr_provider"])){
				$ivr_provider = $customer["ivr_provider"];
			}
		
			switch ($ivr_provider) {
				case "tropo":
					self::$configSVC = new IVRConfigTropoService($accountId);
					break;
				default:
					// set default config service to tropo for now
					self::$configSVC = new IVRConfigTropoService($accountId);
			}
		}
		
		return self::$configSVC;
		
	}
}