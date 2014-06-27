{include file='header' pageTitle='wcf.acp.developerTools.languageDiff'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.developerTools.languageDiff{/lang}</h1>
</header>

{include file='formError'}

<div class="contentNavigation">
	<nav>
		<ul>
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

{if $languages|count > 1}
	<form method="post" action="{link controller='LanguageDiff'}{/link}">
		<div class="container containerPadding marginTop">
			<fieldset>
				<legend>{lang}wcf.acp.developerTools.languageDiff.languages{/lang}</legend>
				<small>{lang}wcf.acp.developerTools.languageDiff.languages.description{/lang}</small>
				
				<dl{if $errorField == 'languageID1'} class="formError"{/if}>
					<dt><label for="languageID1">{lang}wcf.acp.developerTools.languageDiff.languageID1{/lang}</label></dt>
					<dd>
						{htmlOptions name='languageID1' options=$languages selected=$languageID1}
						{if $errorField == 'languageID1'}
							<small class="innerError">
								{if $errorType == 'empty' || $errorType == 'noValidSelection'}
									{lang}wcf.global.form.error.{$errorType}{/lang}
								{else}
									{lang}wcf.acp.developerTools.languageDiff.languageID1.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorField == 'languageID2'} class="formError"{/if}>
					<dt><label for="languageID2">{lang}wcf.acp.developerTools.languageDiff.languageID2{/lang}</label></dt>
					<dd>
						{htmlOptions name='languageID2' options=$languages selected=$languageID2}
						{if $errorField == 'languageID2'}
							<small class="innerError">
								{if $errorType == 'empty' || $errorType == 'noValidSelection'}
									{lang}wcf.global.form.error.{$errorType}{/lang}
								{else}
									{lang}wcf.acp.developerTools.languageDiff.languageID2.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
	
	{if !$success|empty}
		{if $languageItems|count}
			<div class="tabularBox tabularBoxTitle marginTop">
				<header>
					<h2>{lang}wcf.acp.developerTools.languageDiff.languageItems{/lang} <span class="badge badgeInverse">{#$languageItems|count}</span></h2>
				</header>
				
				<table class="table">
					<thead>
						<tr>
							<th class="columnID columnTagID">{lang}wcf.global.objectID{/lang}</th>
							<th class="columnTitle columnLanguageItem active ASC">{lang}wcf.acp.developerTools.languageDiff.languageItem{/lang}</th>
							
							{event name='columnHeads'}
						</tr>
					</thead>
					
					<tbody>
						{foreach from=$languageItems item='languageItem'}
							<tr class="jsTagRow">
								<td class="columnID">{#$languageItem->languageItemID}</td>
								<td class="columnTitle columnLanguageItem">{$languageItem->languageItem}</td>
								
								{event name='columns'}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		{else}
			<p class="info">{lang}wcf.global.noItems{/lang}</p>
		{/if}
	{/if}
{else}
	<p class="info">{lang}wcf.acp.developerTools.languageDiff.oneLanguageOnly{/lang}</p>
{/if}

{include file='footer'}
