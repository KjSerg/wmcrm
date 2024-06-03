const $ = require('jq').jQuery;

function burgerInit(){
    const $doc = $(document);
    const $window = $(window);
    const $el = $doc.find('.header-bottom');
    const $header = $doc.find('.header');
    const $burger = $doc.find('.burger');
    let status = false;
    $doc.ready(function (){
        $doc.on('click', '.burger', function(e){
            e.preventDefault();
            const $t = $(this);

            if($t.hasClass('active')){
                $t.removeClass('active');
                $header.removeClass('active-menu');
                $el.slideUp();
                status = false;
            }else {
                $t.addClass('active');
                $header.addClass('active-menu');
                $el.slideDown();
                status = true;
            }
        });
    });

    $window.on('resize', function (){
        if($window.width() >= 870 && status){
            status = false;
            $el.removeAttr('style');
            $header.removeClass('active-menu');
            $burger.removeClass('active');
        }
    })
}
burgerInit();