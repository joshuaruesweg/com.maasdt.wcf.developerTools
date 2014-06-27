<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list with all columns of a certain tables.
 * 
 * @author	Matthias Schmidt
 * @copyright	2014 Maasdt
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package	com.maasdt.wcf.developerTools
 * @subpackage	acp.page
 * @category	Community Framework
 */
class DatabaseTableColumnListPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.developerTools.databaseTables';
	
	/**
	 * columns of the tables
	 * @var	array
	 */
	public $columns = array();
	
	/**
	 * name of the table
	 * @var	string
	 */
	public $tableName = '';
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'columns' => $this->columns,
			'tableName' => $this->tableName
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// validate table
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlTable = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->tableName
		));
		
		if (!$statement->fetchColumn()) {
			throw new IllegalLinkException();
		}
		
		// fetch columns
		$sql = "SHOW COLUMNS
			FROM	".WCF::getDB()->escapeString($this->tableName);
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		while ($row = $statement->fetchArray()) {
			$this->columns[] = $row;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['tableName'])) $this->tableName = StringUtil::trim($_REQUEST['tableName']);
	}
}
