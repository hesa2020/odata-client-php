<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
use SaintSystems\OData\ODataClient;
class CrmAPI
{
	private static $initialized = false;
	private static $instance;
	private static $odataClient;
	private static $_service_url;
	private static $api_version = '8.0';
	private static $api_version_latest = '';
	private static $_username = '';
	private static $_password = '';

	public static function GetInstance()
	{
		if(!isset(self::$instance))
		{
			self::$instance = new CrmAPI();
		}
		return self::$instance;
	}

	function __construct()
	{
		if(!isset(self::$instance))
		{
			self::$instance = $this;
		}
		self::$instance->CI =& get_instance();
	}

	static function Initialize($new_service_url, $username, $password, $version = '8.0')
	{
		if(self::$initialized) return;
		$initialized = true;
		require_once(ROOTPATH.'vendor/autoload.php');

		self::$_service_url = $new_service_url;
		self::$_username    = $username;
		self::$_password    = $password;
		self::$api_version  = $version;

		self::$odataClient = new SaintSystems\OData\ODataClient($new_service_url);
	    self::$odataClient->getHttpProvider()->setExtraOptions(array('auth' => [$username, $password, 'ntlm']));
	}

	function GetVersion()
	{
		return self::$odataClient->get('api/data/v'.self::$api_version.'/RetrieveVersion');
	}

	function GetApiVersion()
	{
		if(self::$api_version_latest == '')
		{
			$version = explode('.', self::GetVersion()[0]->Version);
			self::$api_version_latest = $version[0].'.'.$version[1];
		}
		return 'api/data/v'.self::$api_version_latest.'/';
	}

	function GetCustomers($filter = '')
	{
		return self::$odataClient->get(self::GetApiVersion().'accounts'.$filter);
	}

	function GetCustomer($customer_id)
	{
		return self::$odataClient->get(self::GetApiVersion().'accounts('.$customer_id.')');
	}

	function UpdateCustomer($id, $customer)
	{
		return self::$odataClient->patch(self::GetApiVersion().'accounts('.$id.')', $customer);
	}

	function CreateCustomer($customer)
	{
		return self::$odataClient->post(self::GetApiVersion().'accounts', $customer);
	}

	function GetContacts($filter = '')
	{
		//return self::$odataClient->from(self::GetApiVersion().'contacts'.$filter)->get();
		$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', self::$_service_url.self::GetApiVersion().'contacts'.$filter, ['auth' => [self::$_username, self::$_password, 'ntlm']]);
		$response_obj = json_decode($response->getBody());
		return isset($response_obj->value) ? $response_obj->value : array();
	}

	function GetContact($contact_id)
	{
		return self::$odataClient->get(self::GetApiVersion().'contacts('.$contact_id.')');
	}

	function UpdateContact($id, $contact)
	{
		return self::$odataClient->patch(self::GetApiVersion().'contacts('.$id.')', $contact);
	}

	function CreateContact($contact)
	{
		return self::$odataClient->post(self::GetApiVersion().'contacts', $contact);
	}
}
