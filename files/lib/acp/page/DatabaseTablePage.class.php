<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows a list with the rows of a certain tables.
 * 
 * @author	Matthias Schmidt
 * @copyright	2014 Maasdt
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package	com.maasdt.wcf.developerTools
 * @subpackage	acp.page
 * @category	Community Framework
 */
class DatabaseTablePage extends SortablePage {
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
	 * displayed rows of the tables
	 * @var	array
	 */
	public $rows = array();
	
	/**
	 * name of the table
	 * @var	string
	 */
	public $tableName = '';
	
	/**
	 * list of visible columns
	 * @var	array<string>
	 */
	public $visibleColumns = array();
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'columns' => $this->columns,
			'rows' => $this->rows,
			'tableName' => $this->tableName,
			'visibleColumns' => $this->visibleColumns
		));
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::countItems()
	 */
	public function countItems() {
		$sql = "SELECT	COUNT(*)
			FROM	".WCF::getDB()->escapeString($this->tableName);
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		return $statement->fetchColumn();
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		// does nothing
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function readObjects() {
		$sql = "SELECT	*
			FROM	".WCF::getDB()->escapeString($this->tableName)."
			".($this->sortField && $this->sortOrder ? "ORDER BY ".$this->sortField." ".$this->sortOrder : '');
		$statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
		$statement->execute();
		
		while ($row = $statement->fetchArray()) {
			$this->rows[] = $row;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
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
		
		$primaryColumn = '';
		while ($row = $statement->fetchArray()) {
			$this->columns[] = $row;
			$this->validSortFields[] = $row['Field'];
			
			if ($row['Key'] == 'PRI' && $primaryColumn !== null) {
				if ($primaryColumn) {
					$primaryColumn = null;
				}
				else {
					$primaryColumn = $row['Field'];
				}
			}
		}
		
		if ($primaryColumn) {
			$this->defaultSortField = $primaryColumn;
		}
		
		foreach ($this->visibleColumns as $index => $column) {
			if (!in_array($column, $this->validSortFields)) {
				unset($this->visibleColumns[$index]);
			}
		}
		
		if (empty($this->visibleColumns)) {
			$this->visibleColumns = $this->validSortFields;
		}
		else if (!in_array(reset($this->validSortFields), $this->visibleColumns)) {
			$this->visibleColumns[] = reset($this->validSortFields);
		}
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['tableName'])) $this->tableName = StringUtil::trim($_REQUEST['tableName']);
		if (isset($_REQUEST['columns']) && is_array($_REQUEST['columns'])) $this->visibleColumns = ArrayUtil::trim($_REQUEST['columns']);
	}
}
