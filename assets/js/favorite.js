$(function(){
    $('#message_icons').on('click', '#ico_out_favorite, #ico_favorite', function(e){
        var oper = $(this).attr('id') == 'ico_favorite' ? 'add' : 'del';
        makeFavorite($(this).data('item'), oper);
        $(this).attr('id', oper == 'del' ? 'ico_favorite' : 'ico_out_favorite');
        $(this).text(oper == 'del' ? 'В избранное':'Удалить из избранного');
        e.preventDefault();
    });
    $('#adList').on('click', '.ico_out_favorite, .ico_favorite', function(e){
        var oper = $(this).hasClass('ico_favorite') ? 'add' : 'del';
        makeFavorite($(this).data('item'), oper);
        $(this).attr('class', oper == 'del' ? 'ico_favorite' : 'ico_out_favorite');
        $(this).attr('title', oper == 'del' ? 'В избранное':'Удалить из избранного');
        e.preventDefault();
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
            datatype: 'json',
            url: "/board/favset"
        }).done(function(data) {
                if(data.message)
                    alert(data.message);
                if(data.favcount)
                    $('#favCount').text(data.favcount)
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