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
        if($(this).val()==0)
            $('#control_group_options').show(50);
        else
            $('#control_group_options').hide(50);
    });


    /**
     * Options List events
     * @type {*|jQuery|HTMLElement}
     */
    $('#addButton').click(function(){
        optionsList.append(optionHtml);
    });
    $('#optionsList input[type=button]').on('click',function(){
        var id = parseInt($(this).prev().attr('name').replace(/options\[|\]/g,''));
        if(id)
            deletedList.append(deletedOptionHtml.replace('optionKey', id));
        $(this).parent().remove();
    });

});