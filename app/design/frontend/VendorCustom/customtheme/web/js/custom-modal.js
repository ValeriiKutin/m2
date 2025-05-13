define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (config, element) {
        var options = {
            type: 'popup',
            responsive: true,
            title: 'Модальне вікно',
            buttons: [{
                text: $.mage.__('Закрити'),
                class: 'action-secondary',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(options, $('#custom-modal'));

        $('#open-modal').on('click', function () {
            $('#custom-modal').modal('openModal');
        });
    };
});
