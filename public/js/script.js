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
    body_id = $('body').attr('id');

    if (body_id == 'dashboard') {
        ajaxUrl = $('#dashboard_ajax_url').val();

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
            $.getJSON(ajaxUrl, function(data) {
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
                $(".date").show();
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

        ajaxUrl = $('#project_tasks_ajax_url').val();

        var ajax_call_project_tasks = function() {
            $.getJSON(ajaxUrl, function(data) {
                $('#header-notification').html(data.notificationsHtml);

                $(".date").easydate({ 'live': false });
                $(".date").show();
            });
        };

        setInterval(ajax_call_project_tasks, 5 * 1000);
    }
    else if (body_id == 'project_notes') {
        $('#note_title').editable();
        $('#note-content').editable();

        $('#content-edit').click(function(e) {
            e.stopPropagation();
            e.preventDefault();
            $('#note-content').editable('toggle');
        });

        ajaxUrl = $('#project_notes_ajax_url').val();

        var ajax_call_project_notes = function() {
            $.getJSON(ajaxUrl, function(data) {
                $('#header-notification').html(data.notificationsHtml);

                $(".date").easydate({ 'live': false });
                $(".date").show();
            });
        };

        setInterval(ajax_call_project_notes, 5 * 1000);
    }
    else if (body_id == 'project_files') {
        ajaxUrl = $('#project_files_ajax_url').val();
        var ajax_call_project_files = function() {
            $.getJSON(ajaxUrl, function(data) {
                $('#header-notification').html(data.notificationsHtml);

                $(".date").easydate({ 'live': false });
                $(".date").show();
            });
        };

        setInterval(ajax_call_project_files, 5 * 1000);
    }

    $(".date").easydate({ 'live': false });
    $(".date").show();
});
