define(['jquery', 'slick'], function($){
    'use strict';
    return function(){
        $(document).ready(function(){
            $('.custom-slider').slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: false,
                autoplaySpeed: 3000,
                dots: true,
                responsive: [
                    {
                        breakpoint: 1024, // планшети і менші ноутбуки
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 768, // мобільні пристрої
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
        });
    }
});
