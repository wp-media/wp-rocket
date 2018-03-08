var $ = jQuery;
$(document).ready(function(){


    /***
    * Show beacons on button "help" click
    ***/

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
        HS.beacon.ready( function(){
            var refThis = this;

            if ( aID.length > 1 ) {
                this.suggest(aID);

                setTimeout(function() {
                    refThis.open();
                }, 200);
            } else {
                this.show(aID);
            }
        });
    }

});
