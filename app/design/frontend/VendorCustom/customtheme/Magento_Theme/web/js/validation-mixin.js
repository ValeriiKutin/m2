define(['jquery', 'mage/translate'], function($, $t) {
    'use strict';

    return function(targetWidget) {
        $.validator.addMethod(
            'cyrillicValidate',
            (value) => /^[а-яА-ЯіІїЇєЄйЙэЭъЪёЁ'-]+$/.test(value),
            $t('Please enter Cyrillic word')
        )
        return targetWidget;
    }
});
