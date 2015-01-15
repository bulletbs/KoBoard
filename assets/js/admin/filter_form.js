var optionHtml = null;
var deletedOptionHtml = null;

var optionsWrapper = null;
var optionsList = null;
var deletedList = null;

$(document).ready(function(){
    optionsWrapper = $('#filterOptions');
    optionsList = $('#optionsList');
    deletedList = $('#deletedOptions');

    /**
     * Type Selector
     * @type {*|jQuery|HTMLElement}
     */
    $('#form_select_type').change(function(){
        var type_id = $(this).val();
        $.ajax({
            url: '/admin/boardFilters/getoptions',
            dataType: 'json',
            method: 'post',
            data: {
                type_id: type_id ,
                model_id: model_id
            }
        }).done(function(data){
            $('#filterOptions').html(data.content);
            if(data.content)
                $('#control_group_options').show();
            else
                $('#control_group_options').hide();

        });

    });


    /**
     * Type Selector
     * @type {*|jQuery|HTMLElement}
     */
    $('#filterOptions').on('change', '#parentFilter', function(){
        $.ajax({
            url: '/admin/boardFilters/parentoptions',
            method: 'post',
            dataType: 'json',
            data: {
                type_id: $('#form_select_type').val(),
                model_id: model_id,
                parent_id: $('#parentFilter').val()
            }
        }).done(function(data){
            $('#filterOptions').html(data.content);
        });
    });


    /**
     * Options List events
     * @type {*|jQuery|HTMLElement}
     */
    $('#filterOptions').on('click', '.addButton',function(){
        var content = optionHtml;
        if($(this).data('id') > 0)
            content = content.replace('PARENT_ID', $(this).data('id'));
        $(this).before(content);
    });
    $('#filterOptions').on('click', '.del', function(){
        var id = $(this).data('id');
        if(id)
            deletedList.append(deletedOptionHtml.replace('optionKey', id));
        $(this).parent().remove();
    });

});