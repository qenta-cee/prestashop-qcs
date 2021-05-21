<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessLink extends Link
{
    public function getIframeCssUrl($cssFile)
    {
        $baseUrl = $this->getBaseLink(null, true);
        return sprintf('%smodules/qentaceecheckoutseamless/views/css/%s', $baseUrl, $cssFile);
    }
}
