jQuery(document).ready(function($) {
	let progressTimerId = null;
	let timeLeft = pvb_fetch_stats.interval / 1000;

	function pvb_fetch_stats_handler() {
		if (document.hidden) return;

		$.ajax({
			url: pvb_fetch_stats.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'pvb_refresh_stats',
				nonce: pvb_fetch_stats.nonce,
			},
			success: function(response) {
				if (response.success) {
					const data = response.data;
					$('.api-info-queries-used strong').text(data.queries_today);
					$('.api-info-bursts').first().html(`<strong>${data.burst_used}</strong> / ${data.burst_total} <small>Used</small>`);
					$('.api-info-bursts').last().text(data.queries_lifetime);
				}
			},
			error: function(xhr) {
				console.error('Error:', xhr.status, xhr.responseText);
			}
		});
	}

	function startUnifiedTimer() {
		clearInterval(progressTimerId);

		const progressPath = document.querySelector('#pvb-refresh-timer .progress');
		const timerDisplay = document.querySelector('#pvb-refresh-timer .pvb-timer-count');

		if (!progressPath) {
			console.warn('Progress path not found.');
			return;
		}

		const totalLength = 100;
		progressPath.style.strokeDasharray = totalLength;
		progressPath.style.strokeDashoffset = totalLength;

		let elapsed = 0;
		const interval = 1000;

		if (timerDisplay) {
			timerDisplay.textContent = timeLeft;
		}

		progressTimerId = setInterval(() => {
			if (document.hidden) return;

			elapsed += interval;
			timeLeft = Math.ceil((pvb_fetch_stats.interval - elapsed) / 1000);

			const progress = Math.min(elapsed / pvb_fetch_stats.interval, 1);
			progressPath.style.strokeDashoffset = totalLength * (1 - progress);

			if (timerDisplay) {
				timerDisplay.textContent = timeLeft;
			}

			if (progress >= 1) {
				clearInterval(progressTimerId);
				pvb_fetch_stats_handler();
				document.dispatchEvent(new CustomEvent("pvbUnifiedTick"));
				timeLeft = pvb_fetch_stats.interval / 1000;
				startUnifiedTimer(); // restart
			}
		}, interval);
	}

	function handleVisibilityChange() {
		if (document.hidden) {
			clearInterval(progressTimerId);
			progressTimerId = null;
		} else if (!progressTimerId) {
			pvb_fetch_stats_handler();
			timeLeft = pvb_fetch_stats.interval / 1000;
			startUnifiedTimer();
		}
	}

	document.addEventListener('visibilitychange', handleVisibilityChange);

	// Initial run
	pvb_fetch_stats_handler();
	startUnifiedTimer();
});
