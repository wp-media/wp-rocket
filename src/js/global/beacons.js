var $ = jQuery;
$(document).ready(function(){


    /**
     * Show beacons on button "help" click
     */
    HS.beacon.ready( function(){
        var $help = $('.wpr-infoAction--help');
        $help.on('click', function(e){
            var ids = $(this).data('beacon-id');
            wprCallBeacon(ids);
            return false;
        });

        function wprCallBeacon(aID){
            aID = aID.split(',');
            if ( aID.length === 0 ) {
                return;
            }
            
                var refThis = HS.beacon;

                if ( aID.length > 1 ) {
                    refThis.suggest(aID);

                    setTimeout(function() {
                        refThis.open();
                    }, 200);
                } else {
                    refThis.show(aID);
                }
            
        }

        // Ask support
        var $askSupport = $('.wpr-js-askSupport');
        $askSupport.on('click', function(){
            HS.beacon.open();
            return false;
        });

    });

});
