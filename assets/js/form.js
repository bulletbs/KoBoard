$(function(){
    var base_uri = '/board/';

    var myMap = null;
    var initMap = false;

    $(document).ajaxStart(function() { $('#loading_layer').show(); });
    $(document).ajaxStop(function() { $('#loading_layer').hide(); });
    $('#titleInput').limit('80','#titleLeft');
    $('#textInput').limit('4096','#textLeft');

    /* Category handlers */
    $('#catMain').change(function(){
        loadSubCat();
    });
    $(document).on('change', '#catChild', function(){
        $('#mainCategory').val( $(this).val() );
        loadFilters($(this));
        setPrice();
    });

    /* REegion handlers */
    $('#region').change(function(){
        loadRegionCities();
    });
    $(document).on('change', '#city', function(){
        $('#city_id').val( $(this).val() );
    });

    setParentFiltersChangeHandler();
    setLabels();
    setPrice();
    setStyle();

    /* Tips initialization */
    $('#addForm .poshytip').poshytip({
        className: 'tip-yellowsimple',
        showOn : 'focus',
        showTimeout: 1,
        alignTo: 'target',
        alignX: 'right',
        alignY: 'center',
        offsetX: 8,
        allowTipHover: false,
        content: function(){
            var tip = $('#' + $(this).attr('id') + 'Tip');
            if(tip.html() != 'undefinded')
                return tip.html();
            return null;
        }
    });
    $('#photosInput').poshytip({
        className: 'tip-yellowsimple',
        showOn : 'over',
        showTimeout: 1,
        alignTo: 'target',
        alignX: 'center',
        alignY: 'top',
        offsetY: 8,
        allowTipHover: false,
        content: function(){
            return $('#photosInputTip').html();
        }
    });

    /**
     * Loading filters
     * @param selectEl
     */
    function loadFilters(selectEl){
        var catVal = selectEl.val();
        if(catVal > 0){
            $.ajax({
                url: base_uri + "ajax_filters" + (typeof(modelId) != 'undefined' ? '/'+modelId : ''),
                type: "POST",
                dataType: "json",
                data: {'selectedCategory':catVal}
            })
            .done(function(data) {
                $('#filter_holder').html(data.filters != '' ?  data.filters : '')
                setParentFiltersChangeHandler();
                setLabels();
                setStyle();
            });
        }
        else{
            $('#filter_holder').html('');
        }
    }

    function loadSubCat(){
        var catVal = $('#catMain').val();
        if(catVal > 0){
            $.ajax({
                url: base_uri + "ajax_subcats",
                type: "POST",
                dataType: "json",
                data: {'selectedCategory':catVal}
            })
            .done(function(data) {
                $('#subCategory').html(data.categories != '' ?  data.categories     : '')
                $('#filter_holder').html(data.filters != '' ?  data.filters     : '')
                setLabels();
                setPrice();
                setStyle();
                $('#mainCategory').val(null);
            });
        }
        else{
            $('#subCategory').html('');
            $('#mainCategory').val(null);
        }

    }

    function loadRegionCities(){
        var val = $('#region').val();
        if(val  > 0){
            $.ajax({
                url: base_uri + "ajax_cities",
                type: "POST",
                dataType: "json",
                data: {'selectedRegion':val }
            })
            .done(function(data) {
                $('#subRegion').html(data.cities != '' ?  data.cities : '');
                $('#city_id').val( null );
                setStyle();
            });
        }
        else{
            $('#subRegion').html('');
            $('#city_id').val( null );
        }

    }

    /**
     * Loading child filter
     * @param id
     * @param parent
     * @param value
     */
    function loadSubFilter(id, parent, value){
        if(!value){
            $('#subfilter_'+ id +'').html('<select></select>');
            setStyle();
        }else{
            $.ajax({
                url: base_uri + "sub_filter/" + id,
                type: "POST",
                dataType: "json",
                data: {
                    parent: parent,
                    value: value
                }
            })
            .done(function(data){
                if(data.content)
                    $('#filter_'+ id +':hidden').show();
                else
                    $('#filter_'+ id +':visible').hide();
                $('#subfilter_'+ id +'').html(data.content);
                setStyle();
            });
        }
    }

    /**
     * Setup parent fillter change handler
     */
    function setParentFiltersChangeHandler(){
        $('#filter_holder select[data-parent]').each(function(){
            var filter_id = $(this).data('id');
            var parent_id = $(this).data('parent');
            $('#filter_holder select[data-id=' +$(this).data('parent')+ ']').change(function(){
                loadSubFilter(filter_id, parent_id, $(this).val());
            });
        });
    }

    /**
     * Set adtype and price labels (depends to category)
     */
    function setLabels(){
        catVal = $('#catChild').val();
        if(!catVal){
            catVal = $('#catMain').val();
        }
        common_labels = {
            0: 'Частное',
            1: 'Коммерческое'
        };
        job_labels = {
            0: 'Резюме',
            1: 'Вакансия'
        };
        if(typeof job_ids != 'undefined'){
            if(catVal in job_ids){
                $('#eventPriceLabel').text('Ставка');
                $('#eventChangeLabel').hide();
                $('#eventFreeLabel').hide();
                $('#jobType').show();
                $('#eventType option').each(function(){
                    $(this).text( job_labels[ $(this).val() ] );
                });
                $('#eventType').trigger('refresh');
            }
            else{
                $('#eventPriceLabel').text('Цена');
                $('#eventChangeLabel').show();
                $('#eventFreeLabel').show();
                $('#jobType').hide();
                $('#eventType option').each(function(){
                    $(this).text( common_labels[ $(this).val() ] );
                });
                $('#eventType').trigger('refresh');
            }
        }
    }

    function setPrice(){
        catVal = $('#catChild').val();
        if(!catVal)
            catVal = $('#catMain').val();
        if(typeof noprice_ids != 'undefined'){
            if(catVal in noprice_ids)
                $('#price_holder').hide();
            else
                $('#price_holder').show();
        }
    }

    function setStyle(){
        if ($.fn.styler) {
            $('input, select').styler({});
        }
    }

    /* Показать / скрыть карту */
    $('#toggleMap').click(function(e){
        e.preventDefault();
        if($('#region').val() && $('#city').val() && $('#addressInput').val()){
            address = $(this).attr('rel') + ', '+ $("#region option:selected").text() +', '+ $("#city option:selected").text() +', '+  $('#addressInput').val();
        }
        else{
            alert('Невозможно отобразить карту, Вы не указали адрес.');
            return false;
        }
        if(!initMap){
            initMap = true;
            show_address(address);
            $('#showAddress').show();
            $(this).text('Обновить карту');
        }else{
            reshow_address(address);
        }
    });

    function show_address(showAddr){
        ymaps.geocode(showAddr, { results: 1 }).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            myMap = new ymaps.Map("showAddress", {
                center: firstGeoObject.geometry.getCoordinates(),
                zoom: 15,
                behaviors:['default', 'scrollZoom']
            });

            // Метка на карте
            myMap.balloon.open(
                firstGeoObject.geometry.getCoordinates(), {
                    contentHeader: 'Ваш адрес на карте',
                    contentFooter: showAddr
                }
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
            myMap.balloon.open(
                firstGeoObject.geometry.getCoordinates(), {
                    contentHeader: 'Ваш адрес на карте',
                    contentFooter: showAddr
                }
            );
        });
    }
});
