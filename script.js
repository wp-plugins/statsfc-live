var $j = jQuery;

setInterval(function() {
	$j.getJSON('https://api.statsfc.com/live-updates.json?callback=?', function(data) {
		$j.each(data, function(match_id, score) {
			$j('#statsfc_' + match_id + ' .statsfc_homeScore').text(score[0]);
			$j('#statsfc_' + match_id + ' .statsfc_awayScore').text(score[1]);
			$j('#statsfc_' + match_id + ' .statsfc_status').text(score[2]);
		});
	});
}, 60000);