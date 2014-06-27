{include file='header' pageTitle='wcf.acp.developerTools.database.table.title'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.developerTools.database.table{/lang}</h1>
	<h2>{$tableName}</h2>
</header>

<div class="contentNavigation">
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
			<h2>{lang}wcf.acp.developerTools.database.table.column.list{/lang} <span class="badge badgeInverse">{#$rows|count}</span></h2>
		</header>
		
		<table id="columnsTable" class="table">
			<thead>
				<tr>
					{foreach from=$columns item='column'}
						<th class="columnText column{@$column[Field]|ucfirst}">{@$column[Field]}</th>
					{/foreach}
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$rows item='row'}
					<tr>
						{foreach from=$columns item='column'}
							<td class="columnText column{@$column[Field]|ucfirst}">{$row[$column[Field]]}</td>
						{/foreach}
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
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
