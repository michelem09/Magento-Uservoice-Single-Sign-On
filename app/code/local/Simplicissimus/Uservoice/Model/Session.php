<?php
/**
 * Simplicissimus Book Farm
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @copyright   Copyright (c) 2012 Simplicissimus Book Farm srl (http://www.simplicissimus.it)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Simplicissimus_Uservoice_Model_Session extends Mage_Customer_Model_Session
{
	public function _construct()
	{
		parent::_construct();
	}
	
	public function getToken()
	{
		if ($this->isLoggedIn())
		{
			// If you're acme.uservoice.com then this value would be 'acme'
			$uservoice_subdomain = Mage::getStoreConfig('uservoice_settings/settings/subdomain');
			
			// Get this from your UserVoice General Settings page
			$sso_key = Mage::getStoreConfig('uservoice_settings/settings/sso_key');
			
			$salted = $sso_key . $uservoice_subdomain;
			$hash = hash('sha1',$salted,true);
			$saltedHash = substr($hash,0,16);
			$iv = "OpenSSL for Ruby";
			
	
			$customer = $this->getCustomer();
			$user_data = array(
			  "guid" => $customer->getId(),
			  "expires" => null,
			  "display_name" => $customer->getFirstname(),
			  "email" => $customer->getEmail(),
			  "url" => null,
			  "avatar_url" => null
			);
			
			$data = json_encode($user_data);
			
			// double XOR first block
			for ($i = 0; $i < 16; $i++)
			{
			 $data[$i] = $data[$i] ^ $iv[$i];
			}
			
			$pad = 16 - (strlen($data) % 16);
			$data = $data . str_repeat(chr($pad), $pad);
				
			$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'','cbc','');
			mcrypt_generic_init($cipher, $saltedHash, $iv);
			$encryptedData = mcrypt_generic($cipher,$data);
			mcrypt_generic_deinit($cipher);
			
			$encryptedData = urlencode(base64_encode($encryptedData));
			
			return $encryptedData;
		}
		else
			return null;
	}
}
