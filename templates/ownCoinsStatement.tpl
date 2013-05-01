{include file='documentHeader'}

<head>
	<title>TODO - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
</head>

<body id="tpl{$templateName|ucfirst}">
{include file='header' sandbox=false}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.jCoins.statement.title{/lang}</h1>
	</hgroup>
</header>
		
{include file='userNotice'}

<div class="contentNavigation">
    {hascontent}
    <nav>
	<ul id="jCoinsStatementButtonContainer" class="">
	    {content}
		{if $entrys|count > 1}<li><a class="button" href="{link controller='SumUpStatements'}{/link}" title="{link controller='SumUpStatements'}{/link}"><span>{lang}wcf.jCoins.statement.compress{/lang}</span></a></li>{/if}
		{event name='largeButtonsTop'}
	    {/content}
	</ul>
    </nav>
    {/hascontent}
</div>

<div class="marginTop statementBox">
    {if $entrys|count == 0}
	<p class="info">{lang}wcf.jCoins.statement.noresults{/lang}</p>
    {else}
	    
    <table class="table">
	<thead>
		<tr>
			<th class="columnID">{lang}wcf.jCoins.statement.id{/lang}</th>
			<th class="columnText">{lang}wcf.jCoins.statement.reason{/lang}</th>
			<th class="columnText">{lang}wcf.jCoins.statement.user{/lang}</th>
			<th class="columnSum">{lang}wcf.jCoins.statement.sum{/lang}</th>
			<th class="columnDate">{lang}wcf.jCoins.statement.date{/lang}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$entrys item=item}
		    <tr class="statementTableRow">
			<td>{#$item->entryID}</td>
			<td>{lang}{$item->reason}{/lang}</td>
			<td>{if $item->executedUserID == 0}{lang}wcf.jcoins.systemuser{/lang}{else}<a href="{link controller='User' object=$item->getExcetuedUser()}{/link}">{$item->getExcetuedUser()->username}</a>{/if}</td>
			<td>{if $item->sum > 0}<span class="badge green">+{#$item->sum}</span>{else}<span class="badge red">{#$item->sum}</span>{/if}</td>
			<td>{@$item->getTime()|time} </td>
		    </tr>
		    {/foreach}
	</tbody>
    </table>
    {/if}
</div>
    
    <div class="copyright"><a href="{link controller='JCoinsCredits'}{/link}">jCoins entwickelt von <strong>Joshua Rüsweg</strong></a></div>
		
{include file='footer' sandbox=false}
</body>
</html>