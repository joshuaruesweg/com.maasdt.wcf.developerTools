/**
 * @author	Matthias Schmidt
 * @copyright	2014 Maasdt
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package	com.maasdt.wcf.developerTools
 */

/**
 * Initializes WCF.ACP.DeveloperTools namespace.
 */
WCF.ACP.DeveloperTools = { };

/**
 * Initializes WCF.ACP.DeveloperTools.DatabaseTable namespace.
 */
WCF.ACP.DeveloperTools.DatabaseTable = { };

/**
 * Handles the columns of a database table.
 */
WCF.ACP.DeveloperTools.DatabaseTable.ColumnManager = Class.extend({
	/**
	 * action proxy object to manipulate columns
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * name of the table the handled columns belong to
	 * @var	string
	 */
	_tableName: '',
	
	/**
	 * Initializes a new WCF.ACP.DeveloperTools.DatabaseTable.ColumnManager object.
	 * 
	 * @param	string		tableName
	 */
	init: function(tableName) {
		this._tableName = tableName;
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			url: 'index.php/AJAXInvoke/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		// add event listeners
		$('.jsDeleteButton').click($.proxy(this._deleteColumn, this));
	},
	
	/**
	 * Handles clicking on an delete icon.
	 * 
	 * @param	Event		event
	 */
	_deleteColumn: function(event) {
		WCF.System.Confirmation.show(WCF.Language.get('wcf.acp.developerTools.database.table.column.delete.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				this._proxy.setOption('data', {
					actionName: 'deleteColumn',
					className: 'wcf\\system\\developer\\tools\\DatabaseTableDeveloperTools',
					parameters: {
						column: $(event.currentTarget).data('column'),
						tableName: this._tableName
					}
				});
				this._proxy.sendRequest();
			}
		}, this));
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (data.actionName) {
			case 'deleteColumn':
				$('.jsDeleteButton[data-column=' + data.column + ']').parents('tr').wcfBlindOut('up');
			break;
		}
		
		new WCF.System.Notification().show();
	},
});

/**
 * Handles the rows of a database table.
 */
WCF.ACP.DeveloperTools.DatabaseTable.RowManager = Class.extend({
	/**
	 * dialog used for the column settings
	 * @var	jQuery
	 */
	_columnSettingsDialog: null,
	
	/**
	 * data of the columns
	 * @var	object
	 */
	_columns: { },
	
	/**
	 * dialog used to edit the row
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * field name of the first column which cannot be hidden
	 * @var	string
	 */
	_firstColumn: null,
	
	/**
	 * action proxy object to manipulate rows
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * data of the displayed rows
	 * @var	object
	 */
	_rows: { },
	
	/**
	 * name of the table the handled rows belong to
	 * @var	string
	 */
	_tableName: '',
	
	/**
	 * name of the visible columns
	 * @var	array<string>
	 */
	_visibleColumns: [ ],
	
	/**
	 * Initializes a new WCF.ACP.DeveloperTools.DatabaseTable.RowManager object.
	 * 
	 * @param	string		tableName
	 * @param	object		columns
	 * @param	object		rows
	 * @param	array<string>	visibleColumns
	 */
	init: function(tableName, columns, rows, visibleColumns) {
		this._tableName = tableName;
		this._columns = columns;
		this._rows = rows;
		this._visibleColumns = visibleColumns;
		
		this._toggleColumns();
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			url: 'index.php/AJAXInvoke/?t=' + SECURITY_TOKEN + SID_ARG_2ND
		});
		
		// add event listeners
		$('.jsRefreshButton').click($.proxy(this._refresh, this));
		$('.jsColumnSettingsButton').click($.proxy(this._showColumnSettings, this));
		$('.jsDeleteButton').click($.proxy(this._deleteRow, this));
		$('.jsEditButton').click($.proxy(this._editRow, this));
		$('.jsDatabaseTableColumnValueToggle').click($.proxy(this._toggleValue, this));
	},
	
	/**
	 * Creates the dialog for the column settings.
	 */
	_createColumnSettingsDialog: function() {
		this._columnSettingsDialog = $('<div />').hide().appendTo(document.body);
		
		var $fieldset = $('<fieldset />').appendTo(this._columnSettingsDialog);
		var $formElement = $('<dl />').append($('<dt />').text(WCF.Language.get('wcf.acp.developerTools.database.table.columnSettings.visibleColumns'))).appendTo($fieldset);
		var $dd = $('<dd />').appendTo($formElement);
		
		for (var $field in this._columns) {
			// the first column cannot be hiiden
			if (this._firstColumn === null) {
				this._firstColumn = $field;
				continue;
			}
			
			var $columnData = this._columns[$field];
			
			$dd.append($('<label><input type="checkbox" name="showColumn_' + $field + '"' + (this._visibleColumns.indexOf($field) !== -1 ? ' checked="checked"' : '') + ' /> ' + $field + '</label>'))
		}
		
		var $formSubmit = $('<div class="formSubmit" />').appendTo(this._columnSettingsDialog);
		$('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.submit') + '</button>').click($.proxy(this._updateVisibleColumns, this)).appendTo($formSubmit);
	},
	
	/**
	 * Creates the dialog to edit the row.
	 */
	_createDialog: function() {
		this._dialog = $('<div />').hide().appendTo(document.body);
		
		var $fieldset = $('<fieldset />').appendTo(this._dialog);
		for (var $field in this._columns) {
			var $columnData = this._columns[$field];
			
			var $formElement = $('<dl />').appendTo($fieldset);
			$formElement.append($('<dt><label for="column_' + $field + '">' + $field + '</label></dt>'));
			
			var $dd = $('<dd />').appendTo($formElement);
			
			if ($columnData['Null'] === 'YES') {
				$dd.append($('<label><input type="checkbox" name="column_' + $field + '_null" /> ' + WCF.Language.get('wcf.acp.developerTools.database.table.cell.value.null') + '</label>'));
			}
			
			// determine input type
			var $inputElement = null;
			if (/^tinyint/.test($columnData.Type)) {
				$inputElement = $('<input type="number" name="column_' + $field + '" class="tiny" />');
			}
			else if (/int\(\d{1,2}\)$/.test($columnData.Type)) {
				$inputElement = $('<input type="number" name="column_' + $field + '" class="small" />');
			}
			else if (/text$/.test($columnData.Type)) {
				$inputElement = $('<textarea name="column_' + $field + '" cols="40" rows="3" />');
			}
			else if (/^enum\(/.test($columnData.Type)) {
				var $options = $columnData.Type.match(/^enum\('(.+)'\)$/)[1].split(/','/g);
				
				$inputElement = $('<select name="column_' + $field + '" />');
				for (var $i = 0; $i < $options.length; $i++) {
					$inputElement.append($('<option value="' + $options[$i] + '">' + $options[$i] + '</option>'));
				}
			}
			else {
				$inputElement = $('<input type="text" name="column_' + $field + '" class="long" />');
			}
			
			$dd.append($inputElement);
			
			if ($columnData['Null'] === 'YES') {
				$inputElement.disable();
				
				$dd.find('input[name=column_' + $field + '_null]').change(function() {
					var $inputElement = $('[name=' + $(this).attr('name').replace(/_null$/, '') + ']');
					if ($(this).is(':checked')) {
						$inputElement.disable();
					}
					else {
						$inputElement.enable();
					}
				});
			}
		}
		
		var $formSubmit = $('<div class="formSubmit" />').appendTo(this._dialog);
		$('<button class="buttonPrimary">' + WCF.Language.get('wcf.global.button.submit') + '</button>').click($.proxy(this._submit, this)).appendTo($formSubmit);
	},
	
	/**
	 * Handles clicking on an delete icon.
	 * 
	 * @param	Event		event
	 */
	_deleteRow: function(event) {
		var $rowID = $(event.currentTarget).data('objectID');
		if (this._rows[$rowID] === undefined) {
			console.debug('[WCF.ACP.DeveloperTools.DatabaseTable.RowManager] Unknown row id "' + $rowID + '"');
			return false;
		}
		
		WCF.System.Confirmation.show(WCF.Language.get('wcf.acp.developerTools.database.table.row.delete.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				var $rowNullFields = [ ];
				
				for (var $field in this._rows[$rowID]) {
					if (this._rows[$rowID][$field] === null) {
						$rowNullFields.push($field);
					}
				}
				
				this._proxy.setOption('data', {
					actionName: 'deleteRow',
					className: 'wcf\\system\\developer\\tools\\DatabaseTableDeveloperTools',
					parameters: {
						rowData: this._rows[$rowID],
						rowNullFields: $rowNullFields,
						tableName: this._tableName
					}
				});
				this._proxy.sendRequest();
				
				delete this._rows[$rowID];
			}
		}, this));
	},
	
	/**
	 * Handles clicking on an edit icon.
	 * 
	 * @param	Event		event
	 */
	_editRow: function(event) {
		var $rowID = $(event.currentTarget).data('objectID');
		if (this._rows[$rowID] === undefined) {
			console.debug('[WCF.ACP.DeveloperTools.DatabaseTable.RowManager] Unknown row id "' + $rowID + '"');
			return false;
		}
		
		if (this._dialog === null) {
			this._createDialog();
		}
		
		this._setDialogData($rowID);
		
		this._dialog.wcfDialog({
			title: WCF.Language.get('wcf.acp.developerTools.database.table.row.edit')
		});
	},
	
	/**
	 * Refreshes the page using the selected visible columns.
	 */
	_refresh: function() {
		// strip old visible columns first
		var $href = window.location.href.replace(/&columns\[\]=\w+/g, '');
		
		for (var $i = 0; $i < this._visibleColumns.length; $i++) {
			$href += '&columns[]=' + this._visibleColumns[$i];
		}
		
		window.location.href = $href;
	},
	
	/**
	 * Sets the data in the dialog.
	 * 
	 * @param	integer		rowID
	 */
	_setDialogData: function(rowID) {
		var $data = this._rows[rowID];
		this._dialog.data('rowID', rowID);
		
		for (var $field in $data) {
			if (this._columns[$field]['Null'] === 'YES') {
				this._dialog.find('[name=column_' + $field + '_null]').prop('checked', $data[$field] === null);
				this._dialog.find('[name=column_' + $field + ']').disable();
			}
			
			this._dialog.find('[name=column_' + $field + ']').val();
			if (this._columns[$field]['Null'] === 'NO' || $data[$field] !== null) {
				this._dialog.find('[name=column_' + $field + ']').enable().val($data[$field]);
			}
		}
		
		this._dialog.find('.error').remove();
	},
	
	/**
	 * Shows the column settings.
	 */
	_showColumnSettings: function() {
		if (this._columnSettingsDialog === null) {
			this._createColumnSettingsDialog();
		}
		
		this._columnSettingsDialog.wcfDialog({
			title: WCF.Language.get('wcf.acp.developerTools.database.table.columnSettings')
		});
	},
	
	/**
	 * Handles submitting the dialog form.
	 */
	_submit: function() {
		var $oldData = this._rows[this._dialog.data('rowID')];
		var $oldNullFields = [ ];
		var $newData = { };
		var $newNullFields = [ ];
		var $rowData = { };
		
		for (var $field in $oldData) {
			if ($oldData[$field] === null) {
				$oldNullFields.push($field);
			}
		}
		
		for (var $field in this._columns) {
			$newData[$field] = this._dialog.find('[name=column_' + $field + ']').val();
			
			if (this._columns[$field]['Null'] === 'YES' && this._dialog.find('[name=column_' + $field + '_null]').is(':checked')) {
				$newNullFields.push($field);
				$rowData[$field] = null;
			}
			else {
				$rowData[$field] = $newData[$field];
			}
		}
		
		this._rows[this._dialog.data('rowID')] = $rowData;
		
		this._proxy.setOption('data', {
			actionName: 'updateRow',
			className: 'wcf\\system\\developer\\tools\\DatabaseTableDeveloperTools',
			parameters: {
				newData: $newData,
				newNullFields: $newNullFields,
				oldData: $oldData,
				oldNullFields: $oldNullFields,
				tableName: this._tableName
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data && data.errorMessage) {
			this._dialog.prepend($('<p class="error" />').text(data.errorMessage));
			this._dialog.wcfDialog('render');
		}
		else {
			new WCF.System.Notification().show();
			
			if (this._dialog) {
				this._dialog.wcfDialog('close');
			}
			
			this._updateData();
		}
	},
	
	/**
	 * Toggles the visibility of the columns.
	 */
	_toggleColumns: function(event) {
		for (var $field in this._columns) {
			if (this._visibleColumns.indexOf($field) !== -1) {
				$('th[data-field=' + $field + '], td[data-field=' + $field + ']').show();
			}
			else {
				$('th[data-field=' + $field + '], td[data-field=' + $field + ']').hide();
			}
		}
	},
	
	/**
	 * Handles toggling a value between its original and its truncated value.
	 * 
	 * @param	Event		event
	 */
	_toggleValue: function(event) {
		var $toggle = $(event.currentTarget);
		
		var $isTruncated = $toggle.data('isTruncated');
		if ($isTruncated === undefined) {
			$isTruncated = true;
		}
		
		$toggle.data('isTruncated', !$isTruncated);
		if ($isTruncated) {
			$toggle.text($toggle.data('value'));
		}
		else {
			$toggle.text($toggle.data('truncatedValue'));
		}
	},
	
	/**
	 * Updates the data of the displayed table.
	 */
	_updateData: function() {
		$('.jsRow').each($.proxy(function(index, element) {
			var $row = $(element);
			var $rowID = $row.data('objectID');
			
			if (this._rows[$rowID] === undefined) {
				$row.wcfBlindOut('up');
			}
			else {
				for (var $field in this._rows[$rowID]) {
					var $value = this._rows[$rowID][$field];
					var $valueElement = $row.find('[data-field=' + $field + ']').children();
					
					if ($value === null) {
						$valueElement.replaceWith($('<em />').text(WCF.Language.get('wcf.acp.developerTools.database.table.cell.value.null')));
					}
					else if (this._columns[$field].Type.substr(-4) === 'text') {
						if ($value !== $value.substr(0, 80)) {
							var $replacement = $('<span class="jsDatabaseTableColumnValueToggle pointer" />').click($.proxy(this._toggleValue, this)).data('value', $value).html($value.substr(0, 80) + '&hellip;');
							$replacement.data('truncatedValue', $replacement.html());
							$valueElement.replaceWith($replacement);
						}
						else {
							$valueElement.replaceWith($('<span />').text($value));
						}
					}
					else {
						$valueElement.replaceWith($('<span />').text($value));
					}
				}
			}
		}, this));
	},
	
	/**
	 * Updates the visible columns.
	 */
	_updateVisibleColumns: function() {
		this._visibleColumns = [ this._firstColumn ];
		this._columnSettingsDialog.find('input[name^=showColumn_]:checked').each($.proxy(function(index, element) {
			this._visibleColumns.push($(element).attr('name').replace(/showColumn_/, ''));
		}, this));
		
		this._toggleColumns();
		
		new WCF.System.Notification().show();
		
		this._columnSettingsDialog.wcfDialog('close');
	}
});
