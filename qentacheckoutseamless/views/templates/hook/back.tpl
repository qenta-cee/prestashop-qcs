{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<link href="{$this_path|escape:'htmlall':'UTF-8'}theme.css" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
<h3>{l s='You will be redirected in a moment.' mod='qentacheckoutseamless'}</h3>
<p>{l s='If the redirect does not work please click' mod='qentacheckoutseamless'}
	<a href="{$orderConfirmation|escape:'htmlall':'UTF-8'}" id="qcsRedirectAnchor" target="_parent" > {l s='here' mod='qentacheckoutseamless'}.</a>
</p>
<script type="text/javascript">
  document.getElementById("qcsRedirectAnchor").click();
</script>
</body>
</html>

