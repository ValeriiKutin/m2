define(['jquery', 'validation','jquery-ui-modules/widget'], ($) => {
    $.widget('perspective.customWidget', {
        _create: function (){
            this._on(this.element, {
                'input .input-text': (event) => {
                    const $input = $(event.target)
                    if(!$input.valid()) {
                        $input.css('border-bottom', '3px solid red');
                        return this;
                    }else {
                        $input.css('border-bottom', '3px solid green');
                    }
                }
            })
        }
    });
    return $.perspective.customWidget;
})
