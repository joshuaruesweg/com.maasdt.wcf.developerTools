{include file='header' pageTitle='wcf.acp.developerTools.database.table.title'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		$('.jsDatabaseTableColumnValueToggle').click(function() {
			var $toggle = $(this);
			
			var $isTruncated = $toggle.data('isTruncated');
			if ($isTruncated === undefined) {
				$isTruncated = true;
			}
			
			$toggle.data('isTruncated', !$isTruncated);
			if ($isTruncated) {
				$toggle.text($toggle.data('value'));
			}
			else {
				$toggle.text($toggle.data('truncateValue'));
			}
		});
	});
	//]]>
</script>

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
			<h2>{lang}wcf.acp.developerTools.database.table.column.list{/lang} <span class="badge badgeInverse">{#$rows|count}</span></h2>
		</header>
		
		<table id="columnsTable" class="table">
			<thead>
				<tr>
					{foreach from=$columns item='column'}
						<th class="columnText column{@$column[Field]|ucfirst}{if $sortField == $column[Field]} active {@$sortOrder}{/if}"><a href="{link controller='DatabaseTable'}tableName={@$tableName}&pageNo={@$pageNo}&sortField={@$column[Field]}&sortOrder={if $sortField == $column[Field] && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{@$column[Field]}</a></th>
					{/foreach}
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$rows item='row'}
					<tr>
						{foreach from=$columns item='column'}
							<td class="columnText column{@$column[Field]|ucfirst}">
								{if $column[Type]|substr:-4 == 'text'}
									{if $row[$column[Field]] != $row[$column[Field]]|truncate}
										<span class="jsDatabaseTableColumnValueToggle pointer" data-value="{$row[$column[Field]]|encodeJS}" data-truncate-value="{$row[$column[Field]]|truncate|encodeJS}">{$row[$column[Field]]|truncate}</span>
									{else}
										{$row[$column[Field]]}
									{/if}
								{else}
									{$row[$column[Field]]}
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
