$(function(){
    var base_uri = '/';
/**
 * Regions selector
 */
    $('#regionLabel input').on('click', function(){
        $('.selectorWrapper:visible').hide(0);
        $('#regionsList').show(50);
        $('#regionsList').mouseleave(function(){
            $(this).hide(50);
        });
    });
    $(document).on('click', '#regionLabel li', function(){
        if($(this).data('action') == 'go'){
            $('#regionAlias').val( $(this).data('alias') );
            $('#regionTopInput').val( $(this).data('title') );
            $(this).parents('.selectorWrapper').slideUp(50);
            $('#boardTopForm').attr('action', generateFormUri()).submit();
        }
        else if($(this).data('action') == 'back'){
            $('#regionLabel .selectorWrapper:visible').slideUp(50);
            $('#regionLabel .st-level').slideDown(50);
        }
        else{
            $('#regionsList').slideUp(50);
            var regionId = $(this).data('id');
            $.ajax({
                type: "POST",
                url: base_uri+ 'boardSearch/cities',
                data: {'region_id':$(this).data('id')},
                dataType: 'json',
                success: function(data){
                    $('#regionsList').after(data.content);
                    $('#regionsCities_'+ regionId).slideDown(50);
                    $('#regionsCities_'+ regionId).mouseleave(function(){
                        $(this).slideUp(50);
                    });
                }
            });
        }
    });

/**
 * Category selector
 */
    $('#categoryLabel input').on('click', function(){
        $('.selectorWrapper:visible').hide(0);
        var selector = $('#categoriesList');
        selector.show(50).mouseleave(function(){
            $(this).hide(50);
        });
    });
    $('#categoryLabel li').on('click', function(){
        if($(this).data('action') == 'go'){
            $('#categoryAlias').val( $(this).data('alias') );
            $('#categoryTopInput').val( $(this).text() );
            $(this).parents('.selectorWrapper').slideUp(50);
            $('#boardTopForm').attr('action', generateFormUri()).submit();
        }
        else if($(this).data('action') == 'back'){
            $('#categoryLabel .selectorWrapper:visible').slideUp(50);
            $('#categoryLabel .st-level').slideDown(50);
        }
        else{
            $('#categoriesList').slideUp(50);
            $('#categoriesSubcats_'+ $(this).data('id')).slideDown(50);
            $('#categoriesSubcats_'+ $(this).data('id')).mouseleave(function(){
                $(this).slideUp(50);
            });
        }
    });

/**
 * Parent filter change event handlers
 */
    $('#filtersList select[data-parent]').each(function(){
        var filter_id = $(this).data('id');
        var parent_id = $(this).data('parent');
        $('#filtersList select[data-id=' + parent_id + ']').change(function(){
            $('#filter_holder select[data-id='+ filter_id +']').attr('disabled', true);
            loadSubFilter(filter_id, parent_id, $(this).val());
        });
    });

    /**
     * Form action generator
     * @returns {string}
     */
    function generateFormUri(){
        var uri = '/';
        var region = $('#regionAlias').val();
        var category = $('#categoryAlias').val();
        uri += region ? region : 'all';
        if(category)
            uri += '/'+category;
        uri += '.html';
        return uri;
    }

    /**
     * Category filters loading
     */
    function loadCategoryFilters(){
        $.ajax({
            type: "POST",
            url: base_uri+ 'boardSearch/filters',
            data: {'region_id':$(this).data('id')},
            dataType: 'json',
            success: function(data){
                $('#filtersList').html(data.content);
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
            $('#filtersList select[data-id='+ id +']').replaceWith(data.content);
        });
    }
});