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
			animate: 2000
		});

		var ajax_call = function() {
			$.getJSON('/ProjectManager/ajax/dashboard', function(data) {
				$('#openTasksCount').html(data.openTasksCount);
				$('#allTasksCount').html(data.allTasksCount);
				$('#userTodaysProductivityText').html(data.userTodaysProductivity);
				$('#userTodaysTime').html(data.userTodaysTime);
				$('#userMonthsTime').html(data.userMonthsTime);
				$('#header-notification').html(data.notificationsHtml);

				$('#taskPercent').data('easyPieChart').update(data.taskPercent);
				$('#userTodaysProductivity').data('easyPieChart').update(data.userTodaysProductivity);
				$('#userTodaysTimePercent').data('easyPieChart').update(data.userTodaysTimePercent);
				$('#userMonthsTimePercent').data('easyPieChart').update(data.userMonthsTimePercent);

				$(".date").easydate({ 'live': false });
			});
		};

		setInterval(ajax_call, 5 * 1000);
	}
	else if (body_id == 'project_tasks') {
		$('#task_title').editable();
		$('#task_job_id').editable();
		$('#task_hours').editable();
		$('#task_assigned_to').editable();
		$('#task_status').editable();

		var ajax_call_project_tasks = function() {
			$.getJSON('/ProjectManager/ajax/projecttasks', function(data) {
				$('#header-notification').html(data.notificationsHtml);

				$(".date").easydate({ 'live': false });
			});
		};

		setInterval(ajax_call_project_tasks, 5 * 1000);
	}

	$(".date").easydate({ 'live': false });
	$(".date").show();
});
