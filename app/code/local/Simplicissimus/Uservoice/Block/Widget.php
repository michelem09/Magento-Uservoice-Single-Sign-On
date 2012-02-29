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
class Simplicissimus_Uservoice_Block_Widget extends Mage_Core_Block_Template
{    
    protected function _construct()
    {
    }
    
    public function getToken()
    {
    	$token = Mage::getSingleton('simplicissimus_uservoice/session')->getToken();
		return $token;
    }    
}