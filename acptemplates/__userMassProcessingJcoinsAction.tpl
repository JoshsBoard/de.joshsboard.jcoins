{if $__wcf->session->getPermission('admin.jcoins.canExecuteMassProcessing')}
<label><input type="radio" name="action" value="addJCoins" {if $action == 'addJCoins'}checked="checked" {/if}/> {lang}wcf.acp.jcoins.massprocessing.addjcoins{/lang}</label>
{/if}