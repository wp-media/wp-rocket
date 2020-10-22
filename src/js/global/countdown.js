function getTimeRemaining(endtime){
    const start = Date.now();
    const total = (endtime * 1000) - start;
    const seconds = Math.floor( (total/1000) % 60 );
    const minutes = Math.floor( (total/1000/60) % 60 );
    const hours = Math.floor( (total/(1000*60*60)) % 24 );
    const days = Math.floor( total/(1000*60*60*24) );

    return {
        total,
        days,
        hours,
        minutes,
        seconds
    };
}

function initializeClock(id, endtime) {
    const clock = document.getElementById(id);

    if (clock === null) {
        return;
    }

    const daysSpan = clock.querySelector('.rocket-countdown-days');
    const hoursSpan = clock.querySelector('.rocket-countdown-hours');
    const minutesSpan = clock.querySelector('.rocket-countdown-minutes');
    const secondsSpan = clock.querySelector('.rocket-countdown-seconds');
  
    function updateClock() {
        const t = getTimeRemaining(endtime);
        daysSpan.innerHTML = t.days;
        hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
        minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
        secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

        if (t.total <= 0) {
            clearInterval(timeinterval);
        }
    }
  
    updateClock();
    const timeinterval = setInterval(updateClock, 1000);
}

if (!Date.now) {
    Date.now = function now() {
      return new Date().getTime();
    };
}

if (typeof rocket_ajax_data.promo_end !== 'undefined') {
    initializeClock('rocket-promo-countdown', rocket_ajax_data.promo_end);
}

if (typeof rocket_ajax_data.license_expiration !== 'undefined') {
    initializeClock('rocket-renew-countdown', rocket_ajax_data.license_expiration);
}