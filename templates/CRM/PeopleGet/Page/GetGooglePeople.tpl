<h3>Import your google contacts</h3>

{if isset($errorMessage) }
<p>{ts 1=$errorMessage}%1{/ts}</p>
{/if}
{if ($peopleGet) }
<a href="/civicrm/import/googlepeople/get?reset=1">Import your contacts now</a>
{else}
<p>In order to import your google contacts, you must choose and authenticate the 
account for which you want to import the contacts. You probably want to choose your
organizational account, not your individual google account (if you have one).</p>
<a href="{$authorizeUrl}">Choose and authenticate your google account</a>
{/if}
