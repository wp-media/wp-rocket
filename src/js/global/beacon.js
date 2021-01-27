var $ = jQuery;
$(document).ready(function(){
    if ('Beacon' in window) {
        /**
         * Show beacons on button "help" click
         */
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

                if ( aID.length > 1 ) {
                    window.Beacon("suggest", aID);

                    setTimeout(function() {
                        window.Beacon("open");
                    }, 200);
                } else {
                    window.Beacon("article", aID.toString());
                }

        }
    }
});
