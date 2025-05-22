var config = {
    map: {
        "*": {
            modal: "Magento_Cms/js/modal",
            customWidget: "Magento_Theme/js/my-custom-widget"
        }
    },
    paths: {
        slick: 'js/slick.min'
    },
    shim: {
        slick: {
            deps: ['jquery']
        }
    },
    config: {
        mixins: {
            "mage/validation": {
                "Magento_Theme/js/validation-mixin": true
            }
        }
    }
};

