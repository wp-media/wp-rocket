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

        if (t.total < 0) {
            clearInterval(timeinterval);

            return;
        }

        daysSpan.innerHTML = t.days;
        hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
        minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
        secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
    }

    updateClock();
    const timeinterval = setInterval(updateClock, 1000);
}

function rucssTimer(id, endtime) {
	const timer = document.getElementById(id);
	const notice = document.getElementById('rocket-notice-rucss-processing');
	const success = document.getElementById('rocket-notice-rucss-success');

	if (timer === null) {
		return;
	}

	function updateTimer() {
		const start = Date.now();
		const remaining = Math.floor( ( (endtime * 1000) - start ) / 1000 );

		if (remaining <= 0) {
			clearInterval(timerInterval);

			if (notice !== null) {
				notice.classList.add('hidden');
			}

			if (success !== null) {
				success.classList.remove('hidden');
			}

			if ( rocket_ajax_data.cron_disabled ) {
				return;
			}

			const data = new FormData();

			data.append( 'action', 'rocket_spawn_cron' );
			data.append( 'nonce', rocket_ajax_data.nonce );

			fetch( ajaxurl, {
				method: 'POST',
				credentials: 'same-origin',
				body: data
			} );

			return;
		}

		timer.innerHTML = remaining;
	}

	updateTimer();
	const timerInterval = setInterval( updateTimer, 1000);
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

if (typeof rocket_ajax_data.notice_end_time !== 'undefined') {
    rucssTimer('rocket-rucss-timer', rocket_ajax_data.notice_end_time);
}