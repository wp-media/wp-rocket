document.addEventListener( 'DOMContentLoaded', function() {
    document.querySelectorAll( '.wpr-rocketcdn-open' ).forEach( function(el) {
        el.addEventListener( 'click', function(e) {
            e.preventDefault();
        });
    });

    MicroModal.init({
        disableScroll: true
    });
});