/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'noonpg',
                component: 'Noonpayments_Noonpg/js/view/payment/method-renderer/noon-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);