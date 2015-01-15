$(function () {

    var formLocked = false;

    $('form').on('submit', function(e){
        if(formLocked){
            e.preventDefault();
            alert('Внимание: Вы не закончили обработку фотографий!');
            return;
        }
    });

    /**
     * File fields change handler
     */
    $('#photoInputs input:file').on('change', function () {
        formLocked = true;

        var photoId = $(this).attr('id');
        var fieldId = photoId.replace('photos_','');
        var templates = {
            crop_title: '<h4 id="cropTitle"></h4>',
            marginline: '<br><br>',
            next_button: '<button id="nextButton">Дальше</button>',
            done_button: '<button id="doneButton">Готово</button>'
        };
        var areaImg = $('<img />').data('id', fieldId);
        var areaInstance = null;

        var oFReader = new FileReader();
        oFReader.readAsDataURL(document.getElementById(photoId).files[0]);
        oFReader.onload = function (oFREvent) {

            /* Redering crop zone */
            areaImg.attr('src', oFREvent.target.result);
            $('#previewZone')
                .append(templates.crop_title)
                .append(areaImg)
                .append(templates.marginline)
                .append(templates.next_button)
                .removeClass('hide');

            areaInstance = setCropArea(areaImg, 100, 100, 'thumb');

            /* Scroll to crop AND handling NEXT & DONE buttons */
            $('body').animate({scrollTop: $('#photoInputs').offset().top});
            $('#nextButton').on('click', function(e){
                e.preventDefault();
                areaInstance.cancelSelection();
                areaInstance = setCropArea(areaImg, 300, 200, 'prev');
                $(this).replaceWith(templates.done_button);
                $('#doneButton').on('click', function(e){
                    e.preventDefault();
//                    areaInstance.remove();
                    areaInstance.cancelSelection();
                    areaInstance = null;
                    formLocked = false;
                    $('#previewZone').html('').addClass('hide');
                });
            });
        };

    });

    /**
     * Setting up crop area on image
     * returns areaSelect instance
     * @param elem
     * @param width
     * @param height
     * @returns {*}
     */
    function setCropArea(elem, width, height, idPrefix){
        var fieldId = elem.data('id');
        $('#cropTitle').html('Укажите область обрезки '+ width+'x'+height);

        /* Calculating area coords for thumb */
        var imgWidth = elem.width();
        var imgHeight = elem.height();
        var imgRatio = imgWidth / imgHeight;
        var cropRatio = width / height;

        if(imgRatio >= cropRatio){
            var w = Math.round(imgHeight * cropRatio);
            var h = imgHeight;
            var x1 = Math.round((imgWidth - w) / 2);
            var y1 = 0;
        }
        else{
            var w = imgWidth;
            var h = Math.round(imgWidth / cropRatio);
            var x1 = 0;
            var y1 = 0;
        }

        /* SettingUP start values */
        $('#'+idPrefix+'_x_'+fieldId).val(x1);
        $('#'+idPrefix+'_y_'+fieldId).val(y1);
        $('#'+idPrefix+'_w_'+fieldId).val(w);
        $('#'+idPrefix+'_h_'+fieldId).val(h);

        /* Create areaSelect */
        return elem.imgAreaSelect({
            x1: x1,
            y1: y1,
            x2: x1+w,
            y2: y1+h,
            aspectRatio: width + ':' + height,
            zIndex: '1100',
            instance: true,
            onSelectEnd: function(i, e) {
                $('#'+idPrefix+'_x_'+fieldId).val(e.x1);
                $('#'+idPrefix+'_y_'+fieldId).val(e.y1);
                $('#'+idPrefix+'_w_'+fieldId).val(e.width);
                $('#'+idPrefix+'_h_'+fieldId).val(e.height);
            }
        });
    }
});