$(document).ready(function() {
	body_id = $('body').attr('id');

	if (body_id == 'dashboard') {
		$('.chart').easyPieChart({
			barColor: function(percent) {
				percent /= 100;
				return "rgb(" + Math.round(255 * (1-percent)) + ", " + Math.round(255 * percent) + ", 0)";
			},
			trackColor: '#5c5c5c',
			scaleColor: false,
			lineCap: 'butt',
			lineWidth: 15,
			animate: 200
		});

		var ajax_call = function() {
			$.getJSON('/ProjectManager/ajax/dashboard', function(data) {
				$('#openTasksCount').html(data.openTasksCount);
				$('#allTasksCount').html(data.allTasksCount);
				$('#userTodaysProductivityText').html(data.userTodaysProductivity);
				$('#userTodaysTime').html(data.userTodaysTime);
				$('#userMonthsTime').html(data.userMonthsTime);

				$('#taskPercent').data('easyPieChart').update(data.taskPercent);
				$('#userTodaysProductivity').data('easyPieChart').update(data.userTodaysProductivity);
				$('#userTodaysTimePercent').data('easyPieChart').update(data.userTodaysTimePercent);
				$('#userMonthsTimePercent').data('easyPieChart').update(data.userMonthsTimePercent);
			});
		};

		setInterval(ajax_call, 1000 * 60 * 1);
	}
});
