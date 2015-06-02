/**
 * TipComplete Jquery plugin
 * Show hints list to text input field, when focused on it
 * @author Butch Stine
 * @package TipComplete
 */
(function( $ ){

    var settings = {
        values : [100,1000,10000,100000],
        suffix : '',
        prefix: ''
    };

    var methods = {
        'init' : function( options ){
            options = $.extend(settings, options || {});

            var $this = $(this),
                data = $this.data('TipComplete');
            if ( ! data ) {
                var tipBody = $("<div/>",{
                    'class' : 'tipcomplete',
                    'id' : 'tipcomplete_' + $(this).attr('id')
                });

                $(tipBody).append( $("<span/>", {
                    text : $(this).val()>0 ? methods.spanValue.call(tipBody, $(this).val()) : $(this).attr('placeholder')
                }) );
                $(tipBody).append( $(this).clone() );
                var tipList = $("<ul/>");
                for(k in settings.values)
                    $(tipList).append( $("<li/>", {
                        text : methods.spanValue( settings.values[k] ),
                        'data-value' : settings.values[k]
                    }) );
                $(tipBody).append( tipList );
                $(this).replaceWith(tipBody);

                /* Event handlers */
                $(tipBody).on('click', methods.open);
                $(tipBody).on('mouseleave', methods.close);
                $("ul li", tipBody).on('click', methods.choose);

                tipBody.data('TipComplete', {
                    prefix: options.prefix,
                    suffix: options.suffix
                });
            }
        },
        'open' : function(e){
            if($(e.target).is("span")){
                $('span', this).hide();
                $('input', this).show().focus();
                $('ul', this).show().focus();
            }
        },
        'close' : function(){
            if($('span', this).is(":hidden")){
                var value = $('input',this).val()>0 ? methods.spanValue.call( this, $('input',this).val() ) : $('input',this).attr('placeholder');
                $('input', this).hide();
                $('ul', this).hide();
                $('span', this).text( value ).show();
            }
        },
        'choose' : function(parent){
            parent = $(this).parents('.tipcomplete');
            $('ul', parent).hide();
            $('input', parent).val( $(this).data('value') ).hide();
            $('span', parent).text( methods.spanValue.call( parent, $(this).data('value')) ).show();
        },
        'spanValue' : function(value){
            if(settings.prefix != '')
                value = settings.prefix + ' ' + value;
            if(settings.suffix!= '')
                value = value + ' ' + settings.suffix;
            return value;
        }
    };

    $.fn.TipComplete = function( method ){
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Метод ' +  method + ' не существует в jQuery.TipComplete' );
        }
    }
})( jQuery );
