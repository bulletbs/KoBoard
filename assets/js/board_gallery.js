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
    //$('#showstack img').eq(0).clone().css('display', 'none').appendTo('#showroom').fadeIn(200);

    var active_thumb = 0;
    $("#thumbs a").on('click', function(e){
        e.preventDefault();
        var thumb_index = $(this).siblings().length ? $(this).index() : $(this).parent().index();
        var thumb_link = $(this).attr('href');
        if(thumb_index == active_thumb)
            return true;
        $('#showroom img').fadeOut(200, function(){
            $('#showroom img').attr('id','showroom_old').css('z-index', 10);

            if($('#showstack img#showstack_loaded_'+thumb_index).length){
                $('#showstack img#showstack_loaded_'+thumb_index).clone().attr('id','showroom_new').css('z-index', 20).appendTo('#showroom');
                flipImages(200);
            }
            else{
                console.log('try to load image #'+thumb_index);
                $('<img id="showroom_loading">').attr('src', '/media/css/images/loading.gif').css('z-index', 1).appendTo('#showroom');
                var newImage = $('<img>').attr('src', thumb_link);
                newImage.on('load', function(){
                    newImage.unbind().attr('id', 'showstack_loaded_'+thumb_index).appendTo('#showstack').clone().attr('id','showroom_new').css('z-index', 20).appendTo('#showroom');
                    $('#showroom_loading').remove();
                    flipImages(200);
                });
            }
            active_thumb = thumb_index;
        });
    });

    /**
     * Flip showroom images
     * @param fadetime
     */
    function flipImages(fadetime){
        $('#showroom_new').fadeIn(fadetime);
        $('#showroom_old').fadeOut(fadetime, function(){
            $(this).remove();
        });
    }
});