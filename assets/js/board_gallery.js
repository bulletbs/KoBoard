$(function () {
    $("#thumbs").bxSlider({
        pager: false,
        infiniteLoop: false,
        hideControlOnEnd: true,
        slideSelector: 'ul li',
        mode: 'horizontal',
        moveSlides: 6,
        minSlides: 1,
        maxSlides: 6,
        slideMargin: 5,
        slideWidth: 100
    });
    $("#thumbs a").on('click', function(e){
        e.preventDefault();
        $('#showroom img').remove();
        $('<img />').attr('src', $(this).attr('href')).css('display', 'none').appendTo('#showroom').fadeIn(200);
    });
    $('<img />').attr('src', $('#showstack img').eq(0).attr('src')).css('display', 'none').appendTo('#showroom').fadeIn(200);
});