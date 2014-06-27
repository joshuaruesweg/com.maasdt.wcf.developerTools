<?php
namespace wcf\acp\form;
use wcf\data\language\item\LanguageItemList;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the form to compare two languages to find missing language items.
 * 
 * @author	Matthias Schmidt
 * @copyright	2014 Maasdt
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package	com.maasdt.wcf.developerTools
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LanguageDiffForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.developerTools.languageDiff';
	
	/**
	 * id of the first selected language
	 * @var	integer
	 */
	public $languageID1 = 0;
	
	/**
	 * id of the second selected language
	 * @var	integer
	 */
	public $languageID2 = 0;
	
	/**
	 * list of language items missing in the second language
	 * @var	array<\wcf\data\language\item\LanguageItem>
	 */
	public $languageItems = array();
	
	/**
	 * list of available languages
	 * @var	array<\wcf\data\language\Language>
	 */
	public $languages = array();
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languageID1' => $this->languageID1,
			'languageID2' => $this->languageID2,
			'languageItems' => $this->languageItems,
			'languages' => $this->languages
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		$this->languages = LanguageFactory::getInstance()->getLanguages();
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['languageID1'])) $this->languageID1 = intval($_POST['languageID1']);
		if (isset($_POST['languageID2'])) $this->languageID2 = intval($_POST['languageID2']);
	}
	
	/**
	 * @see	\wcf\form\AbstractForm::save()
	 */
	public function save() {
		parent::save();
		
		$languageItemList = new LanguageItemList();
		$languageItemList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_language_item language_item2 ON (language_item.languageItem = language_item2.languageItem AND language_item2.languageID = ".$this->languageID2.")";
		$languageItemList->sqlOrderBy = "language_item.languageItem";
		$languageItemList->getConditionBuilder()->add('language_item.languageID = ?', array($this->languageID1));
		$languageItemList->getConditionBuilder()->add('language_item2.languageItemID IS NULL');
		$languageItemList->readObjects();
		$this->languageItems = $languageItemList->getObjects();
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (!$this->languageID1) {
			throw new UserInputException('languageID1');
		}
		else if (!isset($this->languages[$this->languageID1])) {
			throw new UserInputException('languageID1', 'noValidSelection');
		}
		
		if (!$this->languageID2) {
			throw new UserInputException('languageID2');
		}
		else if (!isset($this->languages[$this->languageID2])) {
			throw new UserInputException('languageID2', 'noValidSelection');
		}
		
		if ($this->languageID1 == $this->languageID2) {
			throw new UserInputException('languageID2', 'sameAsLanguage1');
		}
	}
}
