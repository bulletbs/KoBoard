/**
 * TipComplete Jquery plugin
 * Show hints list to text input field, when focused on it
 * @author Butch Stine
 * @package TipComplete
 */
(function( $ ){

    var defaults = {
        values : [100,1000,10000,100000],
        suffix : '',
        prefix: '',
        no_digits: false,
        clean_mask: /[^\d]/g
    };

    var methods = {
        'init' : function( opts ){
            var options = {};
            $.extend(options, defaults, opts || {});

            var $this = $(this),
                data = $this.data('TipComplete');
            if ( ! data ) {
                var tipBody = $("<div/>",{
                    class : 'tipcomplete',
                    id : 'tipcomplete_' + $(this).attr('id'),
                    data : options
                });
                $(tipBody).append( $("<span/>", {
                    html : $(this).val() != '' ? methods.spanValue(tipBody, $(this).val()) : $(this).attr('placeholder')
                }) );
                $(tipBody).append( $(this).clone() );
                var tipList = $("<ul/>");
                for(k in options.values)
                    $(tipList).append( $("<li/>", {
                        html : methods.spanValue(tipBody, options.values[k] ),
                        'data-value' : options.values[k]
                    }) );
                $(tipBody).append( tipList );
                $(this).replaceWith(tipBody);

                /* Event handlers */
                if(options.clean_mask)
                    $("input", tipBody).on('keyup', function(){
                        var value = $(this).val().toString().replace(options.clean_mask, '');
                        $(this).val( value );
                    });
                $(tipBody).on('click', {base:tipBody}, methods.open);
                $(tipBody).on('mouseleave', {base:tipBody}, methods.close);
                $("ul li", tipBody).on('click', {base:tipBody}, methods.choose);
            }
        },
        'open' : function(e){
            var base = e.data.base;
            if($(e.target).is("span")){
                $('span', base).hide();
                $('input', base).show().focus();
                $('ul', base).show();
            }
        },
        'close' : function(e){
            var base = e.data.base;
            var value = $("input", base).val();
            if($('span', this).is(":hidden")){
                var value = value>0 ? methods.spanValue( base, value ) : $('input',this).attr('placeholder');
                $('input', base).hide();
                $('ul', base).hide();
                $('span', base).html( value ).show();
            }
        },
        'choose' : function(e){
            var base = e.data.base;
            var value = $(this).data('value');
            $('ul', base).hide();
            $('input', base).val( value ).hide();
            $('span', base).html( methods.spanValue( base, value) ).show();
        },
        'spanValue' : function(base, value){
            var data = $(base).data();
            if(!parseInt(data.no_digits))
                value = value.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
            if(data.prefix != '')
                value = data.prefix + ' ' + value;
            if(data.suffix!= '')
                value = value + ' ' + data.suffix;
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
