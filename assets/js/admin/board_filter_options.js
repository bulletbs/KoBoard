$(function(){
    $('#form_select_category_id').change(function(){
        loadFilters();
    });
    setParentFiltersChangeHandler();

    /**
     * Loading filters
     * @param selectEl
     */
    function loadFilters(){
        var categoryId = $('#form_select_category_id').val();
        if(categoryId > 0){
            $.ajax({
                url: "/admin/board/get_filters",
                type: "POST",
                dataType: "json",
                data: {model_id:adId, category_id:categoryId}
            })
            .done(function(data) {
                $('#filterOptions').html(data.filters != '' ?  data.filters : '')
                setParentFiltersChangeHandler();
            });
        }
        else{
            $('#filterOptions').html('');
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
            data: { parent: parent, value: value }
        })
        .done(function(data){
            $('#filterOptions select[data-id='+ id +']').replaceWith(data.content);
        });
    }

    /**
     * Setup parent filter change handler
     */
    function setParentFiltersChangeHandler(){
        $('#filterOptions select[data-parent]').each(function(){
            var filter_id = $(this).data('id');
            var parent_id = $(this).data('parent');
            $('#filterOptions select[data-id=' +$(this).data('parent')+ ']').change(function(){
                $('#filterOptions select[data-id='+ filter_id +']').attr('disabled', true);
                loadSubFilter(filter_id, parent_id, $(this).val());
            });
        });
    }
});
