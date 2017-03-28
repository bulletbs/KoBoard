$(function(){
    var base_uri = '/board/';

    var myMap = null;
    var initMap = false;

    $(document).ajaxStart(function() { $('#loading_layer').show(); });
    $(document).ajaxStop(function() { $('#loading_layer').hide(); });

    /* Отправить сообщение пользователю */
    $('#sendMessage').click(function(e){
        $('body').off('click', '#cancel_mailto');
        $('body').off('submit', '#mailtoForm');
        e.preventDefault();
        var this_id = $(this).data('id')
        $.ajax({
            url: base_uri + "send_message/"+ this_id,
            dataType: "json",
            success: function(data){
                $('#mailto').html(data.content);
                $('html, body').animate({scrollTop: $("#mailto").offset().top}, 500);
                $('body').on('click', '#cancel_mailto', function(e){
                    e.preventDefault();
                    $('body').off('click', '#cancel_mailto');
                    $('body').off('submit', '#mailtoForm');
                    $('#mailto').html('');
                });
                $('body').on('submit', '#mailtoForm', function(e){
                    e.preventDefault();
                    $.ajax({
                        url: base_uri + "send_message/"+ this_id,
                        method: 'post',
                        dataType: "json",
                        data: {
                            email: $(this).find('#mailto-email').val(),
                            text: $(this).find('#mailto-text').val(),
                            captcha: $(this).find('#captcha-key') ? $(this).find('#captcha-key').val() : null
                        },
                        success:function(data){
                            $('#mailto').html(data.content);
                        }
                    });
                });
            },
            error: function(){
                alert('An error occurred');
            }
        })
    });

    /* Показать телефон */
    $('#showContacts').click(function(e){
        e.preventDefault();
        $('#hidden_contacts').html('<img src="'+ base_uri +'show_phone/'+ $(this).data('id')+'">');
        $(this).remove();
    });

    /* Показать / скрыть карту */
    $('#toggleMap').click(function(e){
        e.preventDefault();
        if(!initMap){
            show_address($(this).data('address'));
            initMap = true;
        }
        $('#showAddress').toggle();
        $(this).text($('#showAddress').is(':visible') ? 'Скрыть карту' : 'Показать карту');
    });

    /* Проверка адреса  */
    $('#checkMap').click(function(e){
        e.preventDefault();
        var cityid = $('#city_id').val();
        var cityname = $('#city_id option:selected').text();
        var addr = $('#address').val();
        if(cityid==undefined){
            alert('Выберите регион и город');
            return null;
        }
        if(addr=='')
            return null;
        addr = cityname+', '+addr;

        if(!initMap){
            show_address(addr);
            $('#showAddress').toggle();
            initMap = true;
        }
        else{
            reshow_address(addr);
        }
        return null;
    });

    /* Жалоба */
    $('#go_abuse').click(function(e){
        e.preventDefault();
        $('#addabuse').toggle();
        $('html, body').animate({scrollTop: $("#addabuse").offset().top}, 500);
        $('#abuseform').submit(function(e){
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: base_uri+ 'addabuse',
                dataType: 'json',
                data: {
                    'ad_id':$(this).data('id'),
                    'type': $('#abuseType').val()
                },
                success: function(data){
                    $('#addabuse').html(data.message);
                    $('#addabuse').addClass('uk-alert uk-alert-success');
                }
            });
        });
    });

    /* Печать */
    $('#go_print').click(function(e){
        e.preventDefault();
        window.open($(this).data('link'),'qq','resizable=yes, scrollbars=yes, width=560, height=700');
    });


    /**
 * FUNCTIONS
 * */
    function show_address(showAddr){
        ymaps.geocode(showAddr, { results: 1 }).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            myMap = new ymaps.Map("showAddress", {
                center: firstGeoObject.geometry.getCoordinates(),
                zoom: 15,
                //type:"yandex#map",
                behaviors:['default', 'scrollZoom']
            });

            // Метка на карте
            myMap.balloon.open(
                firstGeoObject.geometry.getCoordinates(), {
                contentHeader: $('#baloonHeader').html(),
                contentBody: $('#baloonContent').html(),
                contentFooter: $('#baloonFooter').html()
                }, {closeButton: false}
            );

            // инструменты
            myMap.options.set('scrollZoomSpeed', 2);
            myMap.controls.add("zoomControl");
            myMap.controls.add("mapTools");
        });
    }

    function reshow_address(showAddr){
        ymaps.geocode(showAddr, { results: 1 }).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            myMap.setCenter(firstGeoObject.geometry.getCoordinates());
            myMap.geoObjects.add(firstGeoObject);
        });
    }


    /**
     * Подгрузка картинок
     */
    $(".detail-also-item-img img").lazyload({});
});