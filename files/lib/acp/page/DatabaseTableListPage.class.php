<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Shows a list with all wcf database tables.
 * 
 * @author	Matthias Schmidt
 * @copyright	2014 Maasdt
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package	com.maasdt.wcf.developerTools
 * @subpackage	acp.page
 * @category	Community Framework
 */
class DatabaseTableListPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.developerTools.databaseTables';
	
	/**
	 * data of the wcf tables
	 * @var	array
	 */
	public $tables = array();
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign('tables', $this->tables);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// fetch wcf tables
		$sql = "SELECT		sqlTable
			FROM		wcf".WCF_N."_package_installation_sql_log
			WHERE		sqlColumn = ?
					AND sqlIndex = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			'',
			''
		));
		
		$tableNames = array();
		while ($table = $statement->fetchColumn()) {
			$tableNames[] = $table;
		}
		
		// get status of wcf tables
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('Name IN (?)', array($tableNames));
		
		$sql = "SHOW TABLE STATUS
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		while ($row = $statement->fetchArray()) {
			$this->tables[] = $row;
		}
	}
}
