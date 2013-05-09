<?php
namespace wcf\form;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil; 
use wcf\data\user\UserProfile; 
use wcf\data\jCoins\statement\StatementEditor;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\notification\object\JCoinsTransferNotificationObject; 

/**
 * a transfer form for jcoins
 * 
 * @author	Joshua Rüsweg
 * @package	de.joshsboard.jcoins
 * @subpackage	wcf.form
 */
class JCoinsTransferForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;

	/**
	 * @see	wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;

	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	//public $neededModules = array('MODULE_JCOINS');

	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('user.jcoins.canTransfer');

	/**
	 * the sum to transfer
	 * @var integer
	 */
	public $sum = 0; 
	
	/**
	 * a reason for the transfer
	 * @var string
	 */
	public $reason = ''; 
	
	/**
	 * the user
	 * @var wcf\data\user\UserProfile 
	 */
	public $user = array(); 
	
	/**
	 * all user name for the transfer
	 * @var string
	 */
	public $usernames = ""; 
	
	/**
	 * is the transfer moderat
	 * @var boolean
	 */
	public $isModerativ = false; 
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['id']) && !isset($_POST['username'])) {
			$this->userID = intval($_REQUEST['id']);
	    		$this->user[] = UserProfile::getUserProfile($this->userID);
		}
	}

	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['sum'])) $this->sum = (int) $_POST['sum'];
		if (isset($_POST['reason'])) $this->reason = StringUtil::trim($_POST['reason']);
		if (isset($_POST['username'])) $this->usernames = StringUtil::trim($_POST['username']);
		if (isset($_POST['isModerativ']) && $_POST['isModerativ'] == 1 && WCF::getSession()->getPermission('mod.jCoins.canModTransfer')) $this->isModerativ = true; 
		
		if (count(explode(',', $this->usernames)) > 0) {
			$users = explode(',', $this->usernames); 

			foreach($users AS $user) {
				$user = StringUtil::trim($user); 
				if (!empty($user)) $this->user[] = UserProfile::getUserProfileByUsername($user);
			}
		}
	}

	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
	    
		// remove user doubles
		$this->user = array_unique($this->user);
	    
		if ($this->sum < 1 && !$this->isModerativ) {
			throw new UserInputException('sum', 'tooLess');
		}
		
		if (StringUtil::length($this->reason) > 255) {
			throw new UserInputException('reason', 'tooLong');
		}

		if (StringUtil::length($this->reason) < 3) {
			throw new UserInputException('reason', 'tooShort');
		}
		
		if (count($this->user) == 0) {
			throw new UserInputException('username', 'empty');
		}
		
		foreach ($this->user AS $user) {
			if ($user->isIgnoredUser(WCF::getUser()->userID)) {
				WCF::getTPL()->assign(array(
					'ignoredUsername' => $user->username
				));
				
				throw new UserInputException('user', 'isIgnored');
			}
		}
		
		if (WCF::getUser()->jCoinsBalance < ($this->sum * count($this->user)) && !$this->isModerativ) {
			throw new UserInputException('sum', 'tooMuch');  
		}
		
		parent::validate();
	}

	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		foreach ($this->user AS $user) {
			$stmt = StatementEditor::create(array(
				'userID'		=> $user->userID,
				// executed by the System
				'executedUserID'	=> WCF::getUser()->userID, 
				'time'			=> TIME_NOW, 
				'reason'		=> $this->reason, 
				'sum'			=> $this->sum,
				'changeBalance'		=> true
			));
			
			UserNotificationHandler::getInstance()->fireEvent('jCoinsTransfer', 'de.joshsboard.wcf.JCoins.transfer.notification', new JCoinsTransferNotificationObject($stmt), array($user->userID));

			if (!$this->isModerativ) {
				StatementEditor::create(array(
					'userID'		=> WCF::getUser()->userID,
					// executed by the System
					'executedUserID'	=> $user->userID, 
					'time'			=> TIME_NOW, 
					'reason'		=> $this->reason, 
					'sum'			=> $this->sum * -1
				));
			}
		}
		
		$this->saved(); 
		
		$this->sum = 0; 
		$this->reason = "";
		$this->user = array(); 
		
		WCF::getTPL()->assign(array(
			'success' => true
		)); 
	}

	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'user' => $this->user,
			'sum' => $this->sum,
			'reason' => $this->reason
		));
	}
}