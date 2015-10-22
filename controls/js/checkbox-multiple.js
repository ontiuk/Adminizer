// Checkbox multiple JS
(function( $ ) {
  'use strict';

  $(function() {

    var api = wp.customize;

    $( '.customize-control-checkbox-multiple' ).live( 'change', 'input:checkbox', function(e) {
        e.preventDefault();

        var that     = $( this ),
            hidden   = that.find( '.checkbox-multiple-hidden' ).prop('id'),   
            chx_val  = that.find( 'input:checkbox:checked' ).map( 
                function() {
                    return this.value;
                }
            ).get().join( ',' );

        var chx_str = api.instance(hidden).get();
        api.instance(hidden).set( chx_val );
        return;
    });
  });  
})( jQuery );
