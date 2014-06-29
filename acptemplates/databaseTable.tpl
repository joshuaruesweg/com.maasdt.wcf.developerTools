{include file='header' pageTitle='wcf.acp.developerTools.database.table.title'}

{if $rows|count}
	<script data-relocate="true" src="{@$__wcf->getPath()}acp/js/WCF.ACP.DeveloperTools.js"></script>
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.acp.developerTools.database.table.columnSettings': '{lang}wcf.acp.developerTools.database.table.columnSettings{/lang}',
				'wcf.acp.developerTools.database.table.columnSettings.visibleColumns': '{lang}wcf.acp.developerTools.database.table.columnSettings.visibleColumns{/lang}',
				'wcf.acp.developerTools.database.table.cell.value.null': '{lang}wcf.acp.developerTools.database.table.cell.value.null{/lang}',
				'wcf.acp.developerTools.database.table.row.delete.confirmMessage': '{lang}wcf.acp.developerTools.database.table.row.delete.confirmMessage{/lang}',
				'wcf.acp.developerTools.database.table.row.edit': '{lang}wcf.acp.developerTools.database.table.row.edit{/lang}'
			});
			
			var $columns = { };
			{foreach from=$columns item='column'}
				$columns['{@$column[Field]}'] = { {implode from=$column key='columnKey' item='columnData'}'{@$columnKey|encodeJS}': '{@$columnData|encodeJS}'{/implode} };
			{/foreach}
			
			var $rows = { };
			{foreach from=$rows key='rowID' item='row'}
				$rows[{@$rowID}] = { {implode from=$columns item='column'}'{@$column[Field]|encodeJS}': {if $row[$column[Field]] === null}null{else}'{$row[$column[Field]]|encodeJS}'{/if}{/implode} };
			{/foreach}
			
			var $visibleColumns = [ {implode from=$visibleColumns item='column'}'{@$column}'{/implode} ];
			
			new WCF.ACP.DeveloperTools.DatabaseTable.RowManager('{@$tableName}', $columns, $rows, $visibleColumns);
		});
		//]]>
	</script>
{/if}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.developerTools.database.table{/lang}</h1>
	<h2>{$tableName}</h2>
</header>

<div class="contentNavigation">
	{pages print=true assign='pagesLinks' controller='DatabaseTable' link="tableName=$tableName&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='DatabaseTableColumnList'}tableName={@$tableName}{/link}" class="button"><span class="icon icon16 icon-columns"></span> <span>{lang}wcf.acp.developerTools.database.table.column.list{/lang}</span></a></li>
			<li><a href="{link controller='DatabaseTableList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.developerTools.databaseTables{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $rows|count}
	<div class="tabularBox tabularBoxTitle marginTop" style="overflow-x: scroll;">
		<header>
			<h2>
				<span class="icon icon16 icon-refresh jsRefreshButton jsTooltip pointer" title="{lang}wcf.global.button.refresh{/lang}"></span>
				<span class="icon icon16 icon-cog jsColumnSettingsButton jsTooltip pointer" title="{lang}wcf.acp.developerTools.database.table.columnSettings{/lang}"></span>
				{lang}wcf.acp.developerTools.database.table.row.list{/lang} <span class="badge badgeInverse">{#$rows|count}</span>
			</h2>
		</header>
		
		<table id="columnsTable" class="table">
			<thead>
				<tr>
					{foreach from=$columns item='column' name='columns'}
						<th class="columnText column{@$column[Field]|ucfirst}{if $sortField == $column[Field]} active {@$sortOrder}{/if}"{if $tpl[foreach][columns][iteration] == 1} colspan="2"{/if} data-field="{@$column[Field]}"><a href="{link controller='DatabaseTable'}tableName={@$tableName}&pageNo={@$pageNo}&sortField={@$column[Field]}&sortOrder={if $sortField == $column[Field] && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{@$column[Field]}</a></th>
					{/foreach}
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$rows key='rowID' item='row'}
					<tr class="jsRow" data-object-id="{@$rowID}">
						<td class="columnIcon">
							<span class="icon icon16 icon-pencil jsEditButton jsTooltip pointer" title="{lang}wcf.global.button.edit{/lang}" data-object-id="{@$rowID}"></span>
							<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$rowID}"></span>
							
							{event name='rowIcons'}
						</td>
						{foreach from=$columns item='column'}
							<td class="columnText column{@$column[Field]|ucfirst}" data-field="{@$column[Field]}">
								{if $row[$column[Field]] === null}
									<em>{lang}wcf.acp.developerTools.database.table.cell.value.null{/lang}</em>
								{elseif $column[Type]|substr:-4 == 'text'}
									{assign var='__truncatedValue' value=$row[$column[Field]]|truncate}
									{if $row[$column[Field]] != $__truncatedValue}
										<span class="jsDatabaseTableColumnValueToggle pointer" data-value="{$row[$column[Field]]|encodeJS}" data-truncated-value="{$__truncatedValue|encodeJS}">{$__truncatedValue}</span>
									{else}
										<span>{$row[$column[Field]]}</span>
									{/if}
								{else}
									<span>{$row[$column[Field]]}</span>
								{/if}
							</td>
						{/foreach}
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='DatabaseTableColumnList'}tableName={@$tableName}{/link}" class="button"><span class="icon icon16 icon-columns"></span> <span>{lang}wcf.acp.developerTools.database.table.column.list{/lang}</span></a></li>
				<li><a href="{link controller='DatabaseTableList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.developerTools.databaseTables{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
