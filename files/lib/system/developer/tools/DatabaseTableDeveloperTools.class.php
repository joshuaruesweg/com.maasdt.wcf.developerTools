<?php
namespace wcf\system\developer\tools;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\database\DatabaseException;
use wcf\system\exception\UserInputException;
use wcf\system\IAJAXInvokeAction;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Provides database table-related developer tools.
 * 
 * @author	Matthias Schmidt
 * @copyright	2014 Maasdt
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package	com.maasdt.wcf.developerTools
 * @subpackage	system.developer.tools
 * @category	Community Framework
 */
class DatabaseTableDeveloperTools extends SingletonFactory implements IAJAXInvokeAction {
	/**
	 * list of methods allowed for remote invoke
	 * @var	array<string>
	 */
	public static $allowInvoke = array('updateRow');
	
	/**
	 * Updates a row.
	 */
	public function updateRow() {
		WCF::getSession()->checkPermissions(array('admin.general.canUseAcp'));
		
		$newData = $oldData = $tableName = null;
		$newNullFields = $oldNullFields = array();
		
		// read data
		if (isset($_POST['parameters']['newData']) && is_array($_POST['parameters']['newData'])) $newData = $_POST['parameters']['newData'];
		if (isset($_POST['parameters']['newNullFields']) && is_array($_POST['parameters']['newNullFields'])) $newNullFields = $_POST['parameters']['newNullFields'];
		if (isset($_POST['parameters']['oldData']) && is_array($_POST['parameters']['oldData'])) $oldData = $_POST['parameters']['oldData'];
		if (isset($_POST['parameters']['oldNullFields']) && is_array($_POST['parameters']['oldNullFields'])) $oldNullFields = $_POST['parameters']['oldNullFields'];
		if (isset($_POST['parameters']['tableName'])) $tableName = StringUtil::trim($_POST['parameters']['tableName']);
		
		// validate table name
		if ($tableName === null) {
			throw new UserInputException('tableName');
		}
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlTable = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$tableName
		));
		
		if (!$statement->fetchColumn()) {
			throw new UserInputException('tableName');
		}
		
		// fetch table columns
		$sql = "SHOW COLUMNS
			FROM	".WCF::getDB()->escapeString($tableName);
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$columns = array();
		while ($row = $statement->fetchArray()) {
			$columns[] = $row['Field'];
		}
		
		// validate old data
		if ($oldData === null) {
			throw new UserInputException('oldData');
		}
		foreach ($oldData as $column => $value) {
			if (!in_array($column, $columns)) {
				throw new UserInputException('oldData');
			}
		}
		
		// validate old null fields
		foreach ($oldNullFields as $field) {
			if (!in_array($field, $columns)) {
				throw new UserInputException('oldNullFields');
			}
			
			$oldData[$field] = null;
		}
		
		// validate new data
		if ($newData === null) {
			throw new UserInputException('newData');
		}
		foreach ($newData as $column => $value) {
			if (!in_array($column, $columns)) {
				throw new UserInputException('oldData');
			}
		}
		
		// validate new null fields
		foreach ($newNullFields as $field) {
			if (!in_array($field, $columns)) {
				throw new UserInputException('oldNullFields');
			}
			
			$newData[$field] = null;
		}
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		foreach ($oldData as $column => $value) {
			if ($value === null) {
				$conditionBuilder->add(WCF::getDB()->escapeString($column).' IS NULL');
			}
			else {
				$conditionBuilder->add(WCF::getDB()->escapeString($column).' = ?', array($value));
			}
		}
		
		$setFields = array();
		$setValues = array();
		foreach ($newData as $column => $value) {
			if ($value === null) {
				$setFields[] = WCF::getDB()->escapeString($column).' = NULL';
			}
			else {
				$setFields[] = WCF::getDB()->escapeString($column).' = ?';
				$setValues[] = $value;
			}
		}
		
		$sql = "SELECT	*
			FROM	".WCF::getDB()->escapeString($tableName)."
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		try {
			$sql = "UPDATE	".WCF::getDB()->escapeString($tableName)."
				SET	".implode(', ', $setFields)."
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array_merge($setValues, $conditionBuilder->getParameters()));
		}
		catch (DatabaseException $e) {
			return array(
				'errorMessage' => $e->getErrorDesc()
			);
		}
	}
}
