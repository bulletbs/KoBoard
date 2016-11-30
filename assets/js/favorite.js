$(function(){
    //$(document).ajaxStart(function() { $('#loading_layer').show(); });
    //$(document).ajaxStop(function() { $('#loading_layer').hide(); });

    var base_uri = '/board/';

    /**************************
     * Message item Section
     */
    if($('.message').length){
        $('.message').on('click', '#go_favorite,#go_favorite_2', function(e){
            e.preventDefault();
        var oper = $('#go_favorite').hasClass('delfav') ? 'del' : 'add';
            makeFavorite($(this).data('item'), oper);
            if(oper == 'del'){
                $('#go_favorite').text('В избранное');
                $('#go_favorite').removeClass('delfav');
                $('#go_favorite_2').removeClass('h1_favorite_out');
            }
            else{
                $('#go_favorite').text('Удалить из избранного');
                $('#go_favorite').addClass('delfav');
                $('#go_favorite_2').addClass('h1_favorite_out');
            }
            toastr.success('Объявление №'+$(this).data('item')+' ' + ($('#go_favorite').hasClass('delfav') ? 'добавлено в избранное' : 'удалено из избранного'), 'Избранное', {timeOut: 3000});
        });
        if($('#go_favorite').hasClass('delfav'))
            $('#go_favorite_2').addClass('h1_favorite_out');
    }

    /**************************
     * AD List Section
     */
    $('#adList').on('click', '.ico_out_favorite, .ico_favorite, .remove_favorite', function(e){
        e.preventDefault();
        if($(this).hasClass('remove_favorite')){
            makeFavorite($(this).data('id'), 'del');
            $(this).parents('tr,div.tm-favorite-block').fadeOut(200);
            return;
        }
        var oper = $(this).hasClass('ico_favorite') ? 'add' : 'del';
        makeFavorite($(this).data('item'), oper);
        $(this).attr('class', oper == 'del' ? 'ico_favorite' : 'ico_out_favorite');
        $(this).attr('title', oper == 'del' ? 'В избранное':'Удалить из избранного');
        toastr.success('Объявление №'+$(this).data('item')+' ' + (oper != 'del' ? 'добавлено в избранное' : 'удалено из избранного'), 'Избранное', {timeOut: 3000});
    });

    /* After load favorite icons checker */
    $('#adList .ico_favorite').each(function(){
        if(getCookie('board_favorites['+ $(this).data('item') +']') > 0){
            $(this).attr('class', 'ico_out_favorite');
            $(this).attr('title', 'Удалить из избранного');
        }
    });

    /**
     * Ajax favorite handler
     * @param id
     * @param oper
     */
    function makeFavorite(id, oper){
        $.ajax({
            data: {id:id, oper:oper},
            type: 'post',
            dataType: 'json',
            url: base_uri + "favset"
        }).done(function(data) {
            if(data.message)
                alert(data.message);
            $('#favCount').text(data.favcount);
            if(data.favcount > 0){
                $('#favorite_wrapper').removeClass('hide');
            }
            else{
                $('#favorite_wrapper').addClass('hide');
            }
        });
    }

    /**
     * Get cookie value
     * @param name
     * @returns {string}
     */
    function getCookie(name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }

});