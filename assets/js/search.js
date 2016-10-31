$(function(){
    var base_uri = '/';
/**
 * Подсказки в поиске
 */
$('#serchformQuery').autocomplete({
    source: function( request, response ) {
        $.getJSON( "/autocomplete", {
            term: $('#serchformQuery').val(),
            category: $('#categoryAlias').val()
        }, response );
    },
    minLength: 2
});

/**
* Подгрузка картинок
*/
$(".list_img img").lazyload({
    threshold : 300
});

/**
 * Города показать все или только крупные
 */
    $('#showAllCity').click(function(e){
        e.preventDefault();
        $(this).addClass('active');
        $('#showBigCity').removeClass('active');
        $('#big_city_list').hide();
        $('#all_city_list').show();
    });
    $('#showBigCity').click(function(e){
        e.preventDefault();
        $(this).addClass('active');
        $('#showAllCity').removeClass('active');
        $('#big_city_list').show();
        $('#all_city_list').hide();
    });
    //$('#filtersList').slideDown(500);

/**
 * Regions selector
 */
    $('#regionLabel input').on('click', function(){
        if(!$('#regionsList').length){
            $.ajax({
                type: "POST",
                url: base_uri+ 'boardSearch/regions',
                dataType: 'json',
                success: function(data){
                    $('#regionLabel').append(data.content);
                    showList('regionsList');
                }
            });
        }
        else
            showList('regionsList');
    });
    $(document).on('click', '#regionLabel li', function(){
        if($(this).data('action') == 'go'){
            $('#regionAlias').val( $(this).data('alias') );
            $('#regionTopInput').val( $(this).data('title') );
            $(this).parents('.selectorWrapper').slideUp(50);
            $('#boardTopForm').submit();
        }
        else if($(this).data('action') == 'back'){
            $('#regionLabel .selectorWrapper:visible').slideUp(50);
            $('#regionLabel .st-level').slideDown(50);
            addMouseUpEvent('#regionsList');
        }
        else{
            $('#regionsList').slideUp(50);
            var regionId = $(this).data('id');
            if(!$('#regionsCities_'+ regionId).length){
                $.ajax({
                    type: "POST",
                    url: base_uri+ 'boardSearch/cities',
                    data: {'region_id':$(this).data('id')},
                    dataType: 'json',
                    success: function(data){
                        $('#regionsList').after(data.content);
                        showList('regionsCities_' + regionId);
                    }
                });
            }
            else
                showList('regionsCities_'+ regionId);
        }
    });

/**
 * Category selector
 */
    $('#categoryLabel input').on('click', function(){
        if(!$('#categoriesList').length){
            $.ajax({
                type: "POST",
                url: base_uri+ 'boardSearch/parts',
                dataType: 'json',
                success: function(data){
                    $('#categoryLabel').append(data.content);
                    showList('categoriesList');
                }
            });
        }
        else
            showList('categoriesList');
    });
    $(document).on('click', '#categoryLabel li', function(){
        if($(this).data('action') == 'go'){
            $('#categoryAlias').val( $(this).data('alias') );
            $('#categoryTopInput').val( $(this).text() );
            $(this).parents('.selectorWrapper').slideUp(50);
            $('#filtersList').html('');
            $('#boardTopForm').submit();
        }
        else if($(this).data('action') == 'back'){
            $('#categoryLabel .selectorWrapper:visible').slideUp(50);
            $('#categoryLabel .st-level').slideDown(50);
            addMouseUpEvent('#categoriesList');
        }
        else{
            $('#categoriesList').slideUp(50);
            var partId = $(this).data('id');
            if(!$('#categoriesSubcats_'+ partId).length){
                $.ajax({
                    type: "POST",
                    url: base_uri+ 'boardSearch/categories',
                    data: {'part_id':partId},
                    dataType: 'json',
                    success: function(data){
                        $('#categoriesList').after(data.content);
                        showList('categoriesSubcats_'+ partId);
                    }
                });
            }
            else
                showList('categoriesSubcats_'+ partId);
        }
    });

/**
 * Click out of list of close button event handler
 * @param target_id
 */
    function addMouseUpEvent(target_id){
        $(document).unbind('mouseup');
        $(document).mouseup(function (e){
            var container = $(target_id);
            if (!container.is(e.target) && container.has(e.target).length === 0){
                $(target_id).hide(50);
                $(document).unbind('mouseup');
            }
        });
    }

/**
 * Show list layer
 * @param index
 */
    function showList(index){
        $('.selectorWrapper:visible').hide(0);
        $('#'+index).slideDown(50);
        addMouseUpEvent('#'+index);
    }
/**
 * Submit form
 */
    $('#boardTopForm').submit(function(e){
        e.preventDefault();
        $(this).unbind('submit');
        disableEmptyFilters();
        generateFormInputs();
        $(this).attr('action', generateFormUri()).submit();
    });

/**
 *  Disable empty fields
 */
    function disableEmptyFilters(){
        $('#filtersList input[type=text], #filtersList select').each(function(){
            if( !$(this).val() || $(this).data('main'))
                $(this).attr('disabled', 'disabled');
        });
        if(!$('#serchformQuery').val())
            $('#serchformQuery').attr('disabled', 'disabled');
    }

/**
 * Generate hidden inputs by GET query when filter list invisible
 */
    function generateFormInputs(){
        if($('#filtersList div.filter').length || window.location.href.indexOf('?')<=0)
            return;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var key in hashes)
        {
            var hash = hashes[key].split('=');
            var input = $('<input>').attr('type', 'hidden').attr('name', decodeURI(hash[0])).val(hash[1]);
            $('#filtersList').append(input);
        }
    }

/**
 * Form action generator
 * @returns {string}
 */
    function generateFormUri(){
        var uri = '/';
        var region = $('#regionAlias').val();
        var category = $('#categoryAlias').val();
        var mainfilter = $('#filtersList select[data-main="1"]').val();
        uri += region ? region : 'all';
        if(category)
            uri += '/'+category;
        if(typeof subcat_selected != 'undefined' && subcat_options[subcat_selected])
            uri += '/'+subcat_options[subcat_selected];
        uri += '.html';
    alert
        if(!$('#filtersList div.filter').length && window.location.href.indexOf('?')>0)
            uri += '?'+window.location.href.slice(window.location.href.indexOf('?') + 1);
        return uri;
    }

/**
 * Переключение main-фильтра
 */
    $(document).on('change', '#filtersList select[data-main="1"]', function(){
        if(typeof subcat_options != 'undefined' && typeof subcat_options[$(this).val()] != 'undefined'){
            var alias = subcat_options[$(this).val()];
            var uri = decodeURI(basecat_uri).replace('{{ALIAS}}', alias);
        }
        else{
            var uri = decodeURI(basecat_uri).replace('/{{ALIAS}}', '');
            $("#filtersList select[data-parent="+$(this).data('id')+"]").val(null);
        }
        disableEmptyFilters();
        $('#boardTopForm').unbind('submit');
        $('#boardTopForm').attr('action', uri).submit();
    });

/**
 * Open advanced search (filters panel)
 */
$('#openAdvanced').on('click', function(e){
    e.preventDefault();
    if($('#filtersList:visible').length){
        $('#filtersList').toggle(200);
    }
    else{
        if(!$('#filtersList div.filter').length){
            loadCategoryFilters();
        }
        else
            $('#filtersList').toggle(200);
    }
});

/**
 * Category filters loading
 */
    function loadCategoryFilters(){
        $.ajax({
            type: "POST",
            url: base_uri+ 'boardSearch/filters',
            data: {
                query: window.location.href.indexOf('?')>0 ? window.location.href.slice(window.location.href.indexOf('?') + 1) : null,
                category: $('#categoryAlias').val(),
                mainfilter: typeof subcat_selected != 'undefined' ? subcat_selected : null
            },
            dataType: 'json',
            success: function(data){
                $('#filtersList').html(data.content);
                $('#filtersList').slideDown(50);
            }
        });
    }

/**
 * Loading child filter
 * @param id
 * @param parent
 * @param value
 */
    function loadSubFilter(id, parent, value){
        if(!value){
            $('#filtersList select[data-id='+ id +']').attr("disabled", "disabled").html('');
            return;
        }
        $.ajax({
            url: base_uri+ "boardSearch/sub_filter/" + id,
            type: "POST",
            dataType: "json",
            data: {
                parent: parent,
                value: value
            },
            success: function(data){
                if(data.content)
                    $('#filtersList select[data-id='+ id +']').replaceWith(data.content).attr("disabled", false);
                else
                    $('#filtersList select[data-id='+ id +']').attr("disabled", "disabled").html('');
            }
        })
    }
});