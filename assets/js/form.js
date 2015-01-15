$(function(){
    $('#mainCategory').change(function(){
        loadFilters($(this));
    });
    $('#addForm').on('change', 'select[id^=subcategory]', function(){
        loadFilters($(this));
    });
    setParentFiltersChangeHandler();

    /**
     * Loading filters
     * @param selectEl
     */
    function loadFilters(selectEl){
        var catVal = selectEl.val();
        if(catVal > 0){
            $.ajax({
                url: "/board/ajax_filters" + (typeof(modelId) != 'undefined' ? '/'+modelId : ''),
                type: "POST",
                dataType: "json",
                data: {'selectedCategory':catVal}
            })
            .done(function(data) {
//                $(data.holder).html( data.categories != '' ? data.categories : '')
                $('#filter_holder').html(data.filters != '' ?  data.filters : '')
                setParentFiltersChangeHandler();
            });
        }
        else{
            selectEl.next().html('');
            $('#filter_holder').html('');
        }
    }

    /**
     * Loading child filter
     * @param id
     * @param parent
     * @param value
     */
    function loadSubFilter(id, parent, value){
        $.ajax({
            url: "/board/sub_filter/" + id,
            type: "POST",
            dataType: "json",
            data: {
                parent: parent,
                value: value
            }
        })
        .done(function(data){
            $('#filter_holder select[data-id='+ id +']').replaceWith(data.content);
        });
    }

    /**
     * Setup parent fillter change handler
     */
    function setParentFiltersChangeHandler(){
        $('#filter_holder select[data-parent]').each(function(){
            var filter_id = $(this).data('id');
            var parent_id = $(this).data('parent');
            $('#filter_holder select[data-id=' +$(this).data('parent')+ ']').change(function(){
                $('#filter_holder select[data-id='+ filter_id +']').attr('disabled', true);
                loadSubFilter(filter_id, parent_id, $(this).val());
            });
        });
    }
});
