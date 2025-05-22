define(['jquery', 'mage/translate'], function($, $t) {
    'use strict';

    return function(targetWidget) {
        const regex = /^[а-яА-ЯіІїЇєЄйЙэЭъЪёЁ'-]+$/;
        $.validator.addMethod(
            'cyrillicValidate',
            (value) => regex.test(value),
            $t('Please enter cyrillic word')
        )
        return targetWidget;
    }
});
