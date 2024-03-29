/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    /* Form with auto submit feature */
    $('form[data-auto-submit="true"]').submit();

    //Add form keys.
    $(document).on(
        'submit',
        'form',
        function (e) {
            var formKeyElement,
                form = $(e.target),
                formKey = $('input[name="form_key"]').val();
            if(form.find('input[name="merchant_identifier"]').length && form.find('input[name="service_command"]').length){
                return;
            }    
            if (formKey && !form.find('input[name="form_key"]').length && form[0].method !== 'get') {
                formKeyElement = document.createElement('input');
                formKeyElement.setAttribute('type', 'hidden');
                formKeyElement.setAttribute('name', 'form_key');
                formKeyElement.setAttribute('value', formKey);
                formKeyElement.setAttribute('auto-added-form-key', '1');
                form.get(0).appendChild(formKeyElement);
            }
        }
    );
});
