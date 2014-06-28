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
	 * columns of the relevant database table
	 * @var	array<string>
	 */
	protected $columns = array();
	
	/**
	 * name of the relevant database table
	 * @var	string
	 */
	protected $tableName = '';
	
	/**
	 * list of methods allowed for remote invoke
	 * @var	array<string>
	 */
	public static $allowInvoke = array('deleteRow', 'updateRow');
	
	/**
	 * Deletes a row.
	 */
	public function deleteRow() {
		WCF::getSession()->checkPermissions(array('admin.general.canUseAcp'));
		
		$rowData = $tableName = null;
		$rowNullFields = array();
		
		// read data
		if (isset($_POST['parameters']['rowData']) && is_array($_POST['parameters']['rowData'])) $rowData = $_POST['parameters']['rowData'];
		if (isset($_POST['parameters']['rowNullFields']) && is_array($_POST['parameters']['rowNullFields'])) $rowNullFields = $_POST['parameters']['rowNullFields'];
		if (isset($_POST['parameters']['tableName'])) $this->tableName = StringUtil::trim($_POST['parameters']['tableName']);
		
		// validate table name and read columns
		$this->validateTableName();
		$this->readTableColumns();
		
		// validate row data
		$this->validateData('row', $rowData, $rowNullFields);
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		foreach ($rowData as $column => $value) {
			if ($value === null) {
				$conditionBuilder->add(WCF::getDB()->escapeString($column).' IS NULL');
			}
			else {
				$conditionBuilder->add(WCF::getDB()->escapeString($column).' = ?', array($value));
			}
		}
		
		$sql = "DELETE FROM	".WCF::getDB()->escapeString($this->tableName)."
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
	}
	
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
		if (isset($_POST['parameters']['tableName'])) $this->tableName = StringUtil::trim($_POST['parameters']['tableName']);
		
		// validate table name and read columns
		$this->validateTableName();
		$this->readTableColumns();
		
		// validate old data
		$this->validateData('old', $oldData, $oldNullFields);
		
		// validate new data
		$this->validateData('new', $newData, $newNullFields);
		
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
			FROM	".WCF::getDB()->escapeString($this->tableName)."
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		try {
			$sql = "UPDATE	".WCF::getDB()->escapeString($this->tableName)."
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
	
	/**
	 * Reads the columns of the table with the given name.
	 * 
	 * @param	string		$tableName
	 */
	protected function readTableColumns() {
		$sql = "SHOW COLUMNS
			FROM	".WCF::getDB()->escapeString($this->tableName);
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		while ($row = $statement->fetchArray()) {
			$this->columns[] = $row['Field'];
		}
	}
	
	/**
	 * Validates the given data.
	 * 
	 * @param	string			$dataType
	 * @param	array<mixed>		$data
	 * @param	array<string>		$nullFields
	 */
	protected function validateData($dataType, &$data, $nullFields) {
		// validate old data
		if ($data === null) {
			throw new UserInputException($dataType.'Data');
		}
		foreach ($data as $column => $value) {
			if (!in_array($column, $this->columns)) {
				throw new UserInputException($dataType.'Data');
			}
		}
		
		// validate old null fields
		foreach ($nullFields as $field) {
			if (!in_array($field, $this->columns)) {
				throw new UserInputException($dataType.'NullFields');
			}
			
			$data[$field] = null;
		}
	}
	
	/**
	 * Checks if the table name is the name of an existing wcf table.
	 */
	protected function validateTableName() {
		if ($this->tableName === null) {
			throw new UserInputException('tableName');
		}
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlTable = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->tableName
		));
		
		if (!$statement->fetchColumn()) {
			throw new UserInputException('tableName');
		}
	}
}
