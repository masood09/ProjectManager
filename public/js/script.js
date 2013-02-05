// Copyright (C) 2013 Masood Ahmed

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.

$(document).ready(function() {
    $.fn.editable.defaults.mode = 'inline';

    if ($("#openTasksCount").get(0)) {
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
    }

    if ($("#project_tasks").get(0)) {
        $('#task_title').editable();
        $('#task_job_id').editable();
        $('#task_hours').editable();
        $('#task_assigned_to').editable();
        $('#task_status').editable();
        $('.comment_content').editable();
        $('#comment-textarea').wysihtml5();

        $('.comment-edit').click(function(e) {
            e.stopPropagation();
            e.preventDefault();
            $("#" + $(this).attr("data-id")).editable('toggle');
        });

        $('#post-comment').click(function(e) {
            e.stopPropagation();
            e.preventDefault();

            $(this).attr('disabled', 'disabled');
            $('#new-comment-comment').val($('#comment-textarea').val());
            $('#new-comment-form').submit();
        });

        $('#new-comment-form .file-plus').click(function(e) {
            e.stopPropagation();
            e.preventDefault();
            $('.file-plus').each(function(index) {
                fileInputName = 'file' + (index + 1);
            });

            fileInputHtml = '<p><input type="file" name="' + fileInputName + '"></p>';

            $('#new-comment-file-upload').append(fileInputHtml);
        });

        $('.people-checkbox').change(function() {
            subscribeUrl = $(this).attr('data-url');

            $.getJSON(subscribeUrl, function(data) {

            });
        });
    }

    if ($("#project_notes").get(0)) {
        $('#note_title').editable();
        $('#note-content').editable();

        $('#content-edit').click(function(e) {
            e.stopPropagation();
            e.preventDefault();
            $('#note-content').editable('toggle');
        });
    }

    var ajax_call = function () {
        $.getJSON($('#ajaxUrl').val() + '/' + $('#lastUpdate').val(), function(data) {

            $('#timer-userTodaysTime').html(data.userTodaysTime);

            if ($("#timer-currentTaskTime").get(0)) {
                $("#timer-currentTaskTime").html(data.currentTaskTime);
            }

            if ($("#openTasksCount").get(0)) {
                $('#openTasksCount').html(data.openTasksCount);
                $('#allTasksCount').html(data.allTasksCount);
                $('#userTodaysProductivityText').html(data.userTodaysProductivity);
                $('#userTodaysTime').html(data.userTodaysTime);
                $('#userMonthsTime').html(data.userMonthsTime);
                $('#taskPercent').data('easyPieChart').update(data.taskPercent);
                $('#userTodaysProductivity').data('easyPieChart').update(data.userTodaysProductivity);
                $('#userTodaysTimePercent').data('easyPieChart').update(data.userTodaysTimePercent);
                $('#userMonthsTimePercent').data('easyPieChart').update(data.userMonthsTimePercent);
                $('#dashboardActivityBlock').prepend(data.notificationDropdown);
            }

            if ($('#notificationsCount').html() === "0" && data.notificationDropdown !== "") {
                $('#notificationDropdown').html(data.notificationDropdown);
            }
            else if (data.notificationDropdown !== "") {
                $('#notificationDropdown').prepend(data.notificationDropdown);
            }

            if (data.notificationDropdown !== "") {
                $('#notificationsCount').html(parseFloat($('#notificationsCount').html()) + parseFloat(data.notificationsCount));
            }

            $('#lastUpdate').val(data.lastUpdate);

            $(".date").easydate({ 'live': false });
            $(".date").show();
        });
    };

    setInterval(ajax_call, 5 * 1000);

    $('.timer-task-a').click(function (e) {
        e.stopPropagation();
        e.preventDefault();

        task_id = $(this).attr('data-task-id');
        $('#timer-form-task-id').val(task_id);
        $('#timer-form').submit();
    });

    $(".date").easydate({ 'live': false });
    $(".date").show();
});
