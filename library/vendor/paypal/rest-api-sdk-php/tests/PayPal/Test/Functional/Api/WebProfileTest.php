<?php

namespace PayPal\Test\Api;

use PayPal\Api\Patch;
use PayPal\Common\PPModel;
use PayPal\Rest\ApiContext;
use PayPal\Rest\IResource;
use PayPal\Api\CreateProfileResponse;
use PayPal\Transport\PPRestCall;
use PayPal\Api\WebProfile;

/**
 * Class WebProfile
 *
 * @package PayPal\Test\Api
 */
class WebProfileFunctionalTest extends \PHPUnit_Framework_TestCase
{

    public $operation;

    public $response;

    public $mode = 'mock';

    public $mockPPRestCall;

    public function setUp()
    {
        $className = $this->getClassName();
        $testName = $this->getName();
        $operationString = file_get_contents(__DIR__ . "/../resources/$className/$testName.json");
        $this->operation = json_decode($operationString, true);
        $this->response = true;
        if (array_key_exists('body', $this->operation['response'])) {
            $this->response = json_encode($this->operation['response']['body']);
        }

        $this->mode = getenv('REST_MODE') ? getenv('REST_MODE') : 'mock';
        if ($this->mode != 'sandbox') {

            // Mock PPRest Caller if mode set to mock
            $this->mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PPRestCall')
                ->disableOriginalConstructor()
                ->getMock();

            $this->mockPPRestCall->expects($this->any())
                ->method('execute')
                ->will($this->returnValue(
                    $this->response
                ));
        }
    }

    /**
     * Returns just the classname of the test you are executing. It removes the namespaces.
     * @return string
     */
    public function getClassName()
    {
        return join('', array_slice(explode('\\', get_class($this)), -1));
    }

    public function testCreate()
    {
        $request = $this->operation['request']['body'];
        $obj = new WebProfile($request);
        $obj->setName(uniqid());
        $result = $obj->create(null, $this->mockPPRestCall);
        $this->assertNotNull($result);
        return $result;
    }

    /**
     * @depends testCreate
     * @param $createProfileResponse CreateProfileResponse
     * @return WebProfile
     */
    public function testGet($createProfileResponse)
    {
        $result = WebProfile::get($createProfileResponse->getId(), null, $this->mockPPRestCall);
        $this->assertNotNull($result);
        $this->assertEquals($createProfileResponse->getId(), $result->getId());
        $this->assertEquals($this->operation['response']['body']['presentation']['logo_image'], $result->getPresentation()->getLogoImage());
        $this->assertEquals($this->operation['response']['body']['input_fields']['no_shipping'], $result->getInputFields()->getNoShipping());
        $this->assertEquals($this->operation['response']['body']['input_fields']['address_override'], $result->getInputFields()->getAddressOverride());

        return $result;
    }


    /**
     * @depends testGet
     * @param $webProfile WebProfile
     */
    public function testGetList($webProfile)
    {
        $result = WebProfile::get_list(null, $this->mockPPRestCall);
        $this->assertNotNull($result);
        $found = false;
        $foundObject = null;
        foreach ($result as $webProfileObject) {
            if ($webProfileObject->getId() == $webProfile->getId()) {
                $found = true;
                $foundObject = $webProfileObject;
                break;
            }
        }
        $this->assertTrue($found, "The Created Web Profile was not found in the get list");
        $this->assertEquals($webProfile->getId(), $foundObject->getId());
        $this->assertEquals($this->operation['response']['body'][0]['presentation']['logo_image'], $foundObject->getPresentation()->getLogoImage());
        $this->assertEquals($this->operation['response']['body'][0]['input_fields']['no_shipping'], $foundObject->getInputFields()->getNoShipping());
        $this->assertEquals($this->operation['response']['body'][0]['input_fields']['address_override'], $foundObject->getInputFields()->getAddressOverride());

    }

    /**
     * @depends testGet
     * @param $webProfile WebProfile
     */
    public function testUpdate($webProfile)
    {
        $boolValue = $webProfile->getInputFields()->getNoShipping();
        $newValue = ($boolValue + 1) % 2;
        $webProfile->getInputFields()->setNoShipping($newValue);
        $result = $webProfile->update(null, $this->mockPPRestCall);
        $this->assertNotNull($result);
        $this->assertEquals($webProfile->getInputFields()->getNoShipping(), $newValue);
    }

    /**
     * @depends testGet
     * @param $webProfile WebProfile
     */
    public function testPartialUpdate($webProfile)
    {
        $patches = array();
        $patches[] = new Patch('{
             "op": "add",
             "path": "/presentation/brand_name",
             "value":"new_brand_name"
          }');
        $patches[] = new Patch('{
             "op": "remove",
            "path": "/flow_config/landing_page_type"

          }');
        $result = $webProfile->partial_update($patches, null, $this->mockPPRestCall);
        $this->assertTrue($result);
    }

    /**
     * @depends testGet
     * @param $createProfileResponse CreateProfileResponse
     */
    public function testDelete($createProfileResponse)
    {
        $webProfile = new WebProfile();
        $webProfile->setId($createProfileResponse->getId());
        $result = $webProfile->delete(null, $this->mockPPRestCall);
        $this->assertTrue($result);
    }

}
