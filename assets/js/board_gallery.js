$(function () {
    $("#thumbs").bxSlider({
        pager: false,
        infiniteLoop: false,
        hideControlOnEnd: true,
        slideSelector: 'ul li',
        mode: 'horizontal',
        moveSlides: 6,
        minSlides: 1,
        maxSlides: 7,
        slideMargin: 5,
        slideWidth: 100
    });
    $("#thumbs a").on('click', function(e){
        e.preventDefault();
        $('#showroom img').remove();
        $('#showstack img').eq( $(this).parent().index() ).clone().css('display', 'none').appendTo('#showroom').fadeIn(200);
    });
    $('#showstack img').eq(0).clone().css('display', 'none').appendTo('#showroom').fadeIn(200);
});