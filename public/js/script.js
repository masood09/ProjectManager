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

    if ($("#project_manage").get(0)) {
        $('.people-checkbox').change(function() {
            subscribeUrl = $(this).attr('data-url');

            $.getJSON(subscribeUrl, function(data) {

            });
        });
    }

    if ($('#fullCalendar').get(0)) {
        $('#fullCalendar').fullCalendar({
            events: $('#fullCalendarEvenUrl').val(),
            eventClick: function(calEvent, jsEvent, view) {
                if ($('#editLeave').get(0)) {
                    $('#editLeaveId').val(calEvent.leaveId);
                    $('#editLeave').modal();
                }
            },
            selectable: true,
            select: function(start, end, allDay) {
                startYear = start.getFullYear();

                if (parseFloat(start.getMonth() + 1) < 10) {
                    startMonth = '0' + parseFloat(start.getMonth() + 1);
                }
                else {
                    startMonth = parseFloat(start.getMonth() + 1);
                }

                if (parseFloat(start.getDate()) < 10) {
                    startDay = '0' + start.getDate();
                }
                else {
                    startDay = start.getDate();
                }

                endYear = end.getFullYear();

                if (parseFloat(end.getMonth() + 1) < 10) {
                    endMonth = '0' + parseFloat(end.getMonth() + 1);
                }
                else {
                    endMonth = parseFloat(end.getMonth() + 1);
                }

                if (parseFloat(end.getDate()) < 10) {
                    endDay = '0' + end.getDate();
                }
                else {
                    endDay = end.getDate();
                }

                formattedStart = startYear + '-' + startMonth + '-' + startDay;
                formattedEnd = endYear + '-' + endMonth + '-' + endDay;

                $('#leavesFrom').datepicker('setValue', start);
                $('#leavesTo').datepicker('setValue', end);
                $('#leavesReason').val('');

                if ($('#admin_leaves').get(0)) {
                    $('#leavesUser').val(0);
                }

                $('#newLeave').modal();
            }
        });

        $('#applyLeaveFormSave').click(function(e) {
            e.stopPropagation();
            e.preventDefault();

            if ($('#leavesUser').val() === "0") {
                return false;
            }

            $.ajax({
                type: "POST",
                url: $('#applyLeaveForm').attr('action'),
                data: {
                    'leavesUser': $('#leavesUser').val(),
                    'leavesFrom': $('#leavesFrom').val(),
                    'leavesTo': $('#leavesTo').val(),
                    'leavesApproved': '1',
                    'leavesReason': $('#leavesReason').val()
                }
            }).done (function (data) {
                $('#fullCalendar').fullCalendar('refetchEvents');
                $('#newLeave').modal('hide');
            });
        });

        if ($('#editLeave').get(0)) {
            $('#editLeaveFormDecline').click(function (e) {
                e.stopPropagation();
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: $('#editLeaveForm').attr('action'),
                    data: {
                        'leave_id': $('#editLeaveId').val(),
                        'approved': '0'
                    }
                }).done (function (data) {
                    $('#fullCalendar').fullCalendar('refetchEvents');
                    $('#editLeave').modal('hide');
                });
            });

            $('#editLeaveFormApprove').click(function (e) {
                e.stopPropagation();
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: $('#editLeaveForm').attr('action'),
                    data: {
                        'leave_id': $('#editLeaveId').val(),
                        'approved': '1'
                    }
                }).done (function (data) {
                    $('#fullCalendar').fullCalendar('refetchEvents');
                    $('#editLeave').modal('hide');
                });
            });
        }
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

    $('.datepicker').datepicker();

    $(".date").easydate({ 'live': false });
    $(".date").show();
});
