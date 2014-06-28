{include file='header' pageTitle='wcf.acp.developerTools.database.table.column.list.title'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.developerTools.database.table.column.list{/lang}</h1>
	<h2>{$tableName}</h2>
</header>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='DatabaseTable'}tableName={@$tableName}{/link}" class="button"><span class="icon icon16 icon-align-justify"></span> <span>{lang}wcf.acp.developerTools.database.table.rows{/lang}</span></a></li>
			<li><a href="{link controller='DatabaseTableList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.developerTools.databaseTables{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

<div class="tabularBox tabularBoxTitle marginTop">
	<header>
		<h2>{lang}wcf.acp.developerTools.database.table.column.list{/lang} <span class="badge badgeInverse">{#$columns|count}</span></h2>
	</header>
	
	<table id="columnsTable" class="table">
		<thead>
			<tr>
				<th class="columnTitle columnField">{lang}wcf.acp.developerTools.database.table.column.field{/lang}</th>
				<th class="columnText columnType">{lang}wcf.acp.developerTools.database.table.column.type{/lang}</th>
				<th class="columnText columnNull">{lang}wcf.acp.developerTools.database.table.cell.value.null{/lang}</th>
				
				{event name='columnHeads'}
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$columns item='column'}
				<tr>
					<td class="columnTitle columnField">{@$column[Field]}</td>
					<td class="columnText columnType">{@$column[Type]|strtoupper}</td>
					<td class="columnText columnNull"><span class="icon icon16 icon-{if $column['Null'] == 'YES'}check{else}remove{/if}"></span></td>
					
					{event name='columns'}
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='DatabaseTable'}tableName={@$tableName}{/link}" class="button"><span class="icon icon16 icon-align-justify"></span> <span>{lang}wcf.acp.developerTools.database.table.rows{/lang}</span></a></li>
			<li><a href="{link controller='DatabaseTableList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.developerTools.databaseTables{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsBottom'}
		</ul>
	</nav>
</div>

{include file='footer'}
