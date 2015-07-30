$(function(){
    var base_uri = '/board/';

    $(document).ajaxStart(function() { $('#loading_layer').show(); });
    $(document).ajaxStop(function() { $('#loading_layer').hide(); });

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
                $('#eventType option').each(function(){
                    $(this).text( job_labels[ $(this).val() ] );
                });
            }
            else{
                $('#eventPriceLabel').text('Цена');
                $('#eventType option').each(function(){
                    $(this).text( common_labels[ $(this).val() ] );
                });
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
        $('input, select').styler({});
    }
});
