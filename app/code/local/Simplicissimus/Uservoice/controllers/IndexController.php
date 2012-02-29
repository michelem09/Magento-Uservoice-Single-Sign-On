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
 * @copyright	Copyright (c) 2012 Simplicissimus Book Farm srl (http://www.simplicissimus.it)
 * @license		http://opensource.org/licenses/afl-3.0.php	Academic Free License (AFL 3.0)
 */
class Simplicissimus_Uservoice_IndexController extends Mage_Core_Controller_Front_Action
{

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
	public function indexAction()
	{
		return true;
	}
	
    public function loginAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        
        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->loadLayout();
        $this->renderLayout();
    }
	
	public function loginPostAction()
	{
		if ($this->_getSession()->isLoggedIn()) {
			$this->_redirect('*/*/');
			return;
		}
		$session = $this->_getSession();

		if ($this->getRequest()->isPost()) {
			$login = $this->getRequest()->getPost('login');
			$this->_returnUrl = $this->getRequest()->getPost('returnUrl');
			
			if (!empty($login['username']) && !empty($login['password'])) {
				try {
					$session->login($login['username'], $login['password']);
				} catch (Mage_Core_Exception $e) {
					switch ($e->getCode()) {
						case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
							$value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
							$message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
							break;
						case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
							$message = $e->getMessage();
							break;
						default:
							$message = $e->getMessage();
					}
					$session->addError($message);
					$session->setUsername($login['username']);
				} catch (Exception $e) {
					// Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
				}
			} else {
				$session->addError($this->__('Login and password are required.'));
			}
		}

		$this->_loginPostRedirect();
	}
	
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {

            // Set default URL to redirect customer to
			$token = Mage::getSingleton('simplicissimus_uservoice/session')->getToken();

			$session->setBeforeAuthUrl('https://' . Mage::getStoreConfig('uservoice_settings/settings/subdomain') . '.uservoice.com' . $this->_returnUrl . '?sso=' . $token);
            
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
        
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
        
    }
	
}
