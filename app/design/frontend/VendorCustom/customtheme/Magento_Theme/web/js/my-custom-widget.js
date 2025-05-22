define(['jquery', 'validation','jquery-ui-modules/widget'], ($) => {
    $.widget('perspective.customWidget', {
        _create: function (){
            this._on(this.element, {
                'input .input-text': (event) => {
                    const $input = $(event.target)
                    console.log($input.valid())
                    if($input.valid()) {
                        $input.css('border-bottom', '3px solid green');

                    }else {
                        $input.css('border-bottom', '3px solid red');
                        return this;
                    }
                }
            })
        }
    });
    return $.perspective.customWidget;
})
/*ДЛЯ СЕБЕ
* _create - це для того, щоб функція викликалась при ініціалізації віджета
* this._on - це обробник подій(тут ми слухаємо подію input(при введені) на всіх інпутах де є клас .input-text)
* в $input у нас сам елемет інпут(дом елемент)
* ну і далі звичайна перевірка, якщо інпут валідний($input.valid() == true), тобто в файлі validation-mixin.js повертає на інпуті true, то цей інпут приймає стилі відповідні
*
*
*
*
*
* */
