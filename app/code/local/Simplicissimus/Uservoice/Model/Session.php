<?php
/**
 * NOTICE OF LICENSE
 *
 * This software is under the MIT License
 * Copyright (c) 2012 Michele Marcucci
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @license     http://opensource.org/licenses/mit-license.php The MIT License (MIT) 
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
