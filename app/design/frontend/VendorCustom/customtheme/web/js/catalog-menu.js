require(['jquery'], function($) {
    $(function() {
        var $btn = $('.catalog-btn'),
            $menu = $('.sections.nav-sections');

        function show() {
            $menu.css({display: 'block', pointerEvents: 'auto'});
        }

        function hide() {
            $menu.css({display: 'none', pointerEvents: 'none'});
        }

        $btn.hover(show, hide);
        $menu.hover(show, hide);
    });
});
