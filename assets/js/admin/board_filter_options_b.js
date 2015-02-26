function loadFilters(selectedCategory){
    $.ajax({
        url: "/admin/board/get_filters",
        type: "POST",
        dataType: "json",
        data: {
            'modelId' : adId,
            'modelCategoryId' : categoryId,
            'selectedCategory':selectedCategory
        }
    }).done(function(data) {
        $('#filterOptions').hide(50,function(){
            $(this).html(data.message).show(50)
        });
    });
}

$(document).ready(function(){
    $('#loading').ajaxStart(function(){$(this).show()});
    $('#loading').ajaxStop(function(){$(this).hide()});
    $('#form_select_category_id').change(function(){
        loadFilters($(this).val());
    });
    loadFilters($('#form_select_category_id').val());
});