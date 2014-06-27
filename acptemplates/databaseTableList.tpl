{include file='header' pageTitle='wcf.acp.developerTools.database.table.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.developerTools.database.table.list{/lang}</h1>
</header>

<div class="contentNavigation">
	<nav>
		<ul>
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

<div class="tabularBox tabularBoxTitle marginTop">
	<header>
		<h2>{lang}wcf.acp.developerTools.database.table.list{/lang} <span class="badge badgeInverse">{#$tables|count}</span></h2>
	</header>
	
	<table class="table">
		<thead>
			<tr>
				<th class="columnTitle columnName" colspan="2">{lang}wcf.acp.developerTools.database.table.name{/lang}</th>
				<th class="columnDigits columnRows">{lang}wcf.acp.developerTools.database.table.rows{/lang}</th>
				
				{event name='columnHeads'}
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$tables item='table'}
				<tr>
					<td class="columnIcon">
						<a href="{link controller='DatabaseTableColumnList'}tableName={@$table[Name]}{/link}" title="{lang}wcf.acp.developerTools.database.table.column.list{/lang}" class="jsTooltip"><span class="icon icon16 icon-columns"></span></a>
						
						{event name='rowButtons'}
					</td>
					<td class="columnTitle columnName"><a href="{link controller='DatabaseTable'}tableName={@$table[Name]}{/link}">{$table['Name']}</a></td>
					<td class="columnDigits columnRows">{#$table[Rows]}</td>
					
					{event name='columns'}
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			{event name='contentNavigationButtonsBottom'}
		</ul>
	</nav>
</div>

{include file='footer'}
