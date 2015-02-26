$(function(){
    var base_uri = '/';

/**
 * Города показать все или только крупные
 */
    $('#showAllCity').click(function(e){
        $(this).addClass('active');
        $('#showBigCity').removeClass('active');
        $('#city_list li.smallcity').show();
        e.preventDefault();
    });
    $('#showBigCity').click(function(e){
        $(this).addClass('active');
        $('#showAllCity').removeClass('active');
        $('#city_list li.smallcity').hide();
        e.preventDefault();
    });

/**
 * Regions selector
 */
    $('#regionLabel input').on('click', function(){
        $('.selectorWrapper:visible').hide(0);
        $('#regionsList').show(50);
        addMouseUpEvent('#regionsList');
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
            $.ajax({
                type: "POST",
                url: base_uri+ 'boardSearch/cities',
                data: {'region_id':$(this).data('id')},
                dataType: 'json',
                success: function(data){
                    $('#regionsList').after(data.content);
                    $('#regionsCities_'+ regionId).slideDown(50);
                    addMouseUpEvent('#regionsCities_'+ regionId);
                }
            });
        }
    });

/**
 * Category selector
 */
    $('#categoryLabel input').on('click', function(){
        $('.selectorWrapper:visible').hide(0);
        $('#categoriesList').show(50);;
        addMouseUpEvent('#categoriesList');
    });
    $('#categoryLabel li').on('click', function(){
        if($(this).data('action') == 'go'){
            $('#categoryAlias').val( $(this).data('alias') );
            $('#categoryTopInput').val( $(this).text() );
            $(this).parents('.selectorWrapper').slideUp(50);
            $('#boardTopForm').submit();
        }
        else if($(this).data('action') == 'back'){
            $('#categoryLabel .selectorWrapper:visible').slideUp(50);
            $('#categoryLabel .st-level').slideDown(50);
            addMouseUpEvent('#categoriesList');
        }
        else{
            $('#categoriesList').slideUp(50);
            $('#categoriesSubcats_'+ $(this).data('id')).slideDown(50);
            addMouseUpEvent('#categoriesSubcats_'+ $(this).data('id'));
        }
    });

$('#boardTopForm').submit(function(e){
    e.preventDefault();
    $(this).unbind('submit');
    $(this).attr('action', generateFormUri()).submit();
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
//        uri += '?' + $('#boardTopForm').serialize();
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