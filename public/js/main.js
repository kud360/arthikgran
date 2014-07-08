var defaultCorrect = [[
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D', 'A'],
    [
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D', 'A'],
    [
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D', 'A'],
    [
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D', 'A'],
    [
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D',
        'A', 'B', 'C', 'D', 'A', 'B', 'C', 'D', 'B']];

var correct = [];

/*jslint unparam: true */
/*global window, $ */
$(function() {
    'use strict';

    $('#fileupload').fileupload({
        url: 'parse',
        dataType: 'json',
        sequentialUploads: true,
        done: function(e, data) {
            console.log(data);
            $("#statusText").text('File ' + data.files[0].name + " interpreted Successfully");
            publishResult(data.result.grid);
            var progress = $('#fileupload').fileupload('progress');
            if (progress.loaded == progress.total) {
                $(".progress-bar").removeClass('progress-bar-warning').addClass('progress-bar-success')
            }
        },
        start: function(e, data) {
            $('#answers label.active').addClass('btn-success')
            $('#answers label').addClass('disabled');
            $('#answers label').each(function(index, elem) {
                $(this).addClass('disabled');
                if ($(this).hasClass('active')) {
                    $(this).addClass('btn-success')
                    var set = $(this).closest('.well-lg').prop('id').charCodeAt(3) - 49;
                    if (correct.length === set) {
                        correct.push(new Array());
                    }
                    correct[set].push($(this).text().trim());
                }
            });
        },
        progress: function(e, data) {
            if (data.loaded == data.total) {
                $("#statusText").text("Interpreting " + data.files[0].name + ". This can take upto a minute ...");
                $(".progress-bar").removeClass('progress-bar-info').addClass('progress-bar-warning')
            } else {
                $("#statusText").text("Uploading " + data.files[0].name);
                $(".progress-bar").removeClass('progress-bar-warning').addClass('progress-bar-info')
            }

        },
        progressall: function(e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css('width', progress + '%');
            $('#progress .progress-bar').text(progress + ' %');
        }
    }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');

    $("#answers .hidden").each(function() {
        var $entry = $('.well', this).clone();
        $('.well', this).remove();
        var setNo = this.id.charCodeAt(3) - 49;
        for (var i = 0; i < defaultCorrect[setNo].length; i++) {
            if (i % 5 === 0) {
                $(this).append('<div class="row">');
            }
            $(this).children('.row:last').append($entry.clone());
            $('.row:last .well:last .badge', this).text(i + 1);
            $('.row:last .well:last input[value=' + defaultCorrect[setNo][i] + ']', this).click();

        }
        $(this).removeClass('hidden');
    });
});

function getName(grid) {
    var name = "";
    var startRow = 2;
    var EndRow = startRow + 26
    var startColumn = 0;
    var EndColumn = grid.length - 1;
    var i, j

    for (j = startColumn; j < EndColumn; j++) {
        for (i = startRow; i < EndRow; i++) {
            if (grid[j][i]) {
                name += String.fromCharCode(65 + i - startRow);
                break;
            }
        }
        if (i === EndRow) {
            name += ' ';
        }
    }

    return name.trim();
}

function getSet(grid) {
    var startRow = 32;
    var startColumn = 20;
    var sets = 5;

    for (var i = 0; i < sets; i++) {
        if (grid[startColumn][startRow + i]) {
            return i;
        }
    }
    return 1;
}

function getRollNo(grid) {
    var startRow = 32;
    var startColumn = 1;
    var digits = 3;
    var roll = 0;

    for (var i = 0; i < digits; i++) {

        var faceValue = 0;

        for (var j = 0; j < 5; j++) {
            if (grid[ startColumn + i * 2 ][startRow + j]) {
                faceValue = j;
            } else if (grid[ startColumn + i * 2 + 1][startRow + j]) {
                faceValue = j + 5;
            }
            if (faceValue) {
                break;
            }
        }
        roll = roll * 10 + faceValue;
    }
    return roll;
}

function getDiv(grid) {
    var startRow = 32;
    var startColumn = 10;
    for (i = 0; i < 5; i++) {
        for (j = 0; j < 6; j++) {
            var alpha = i + (j * 5)
            if (j < 26) {
                if (grid[startColumn + j][startRow + i]) {
                    return String.fromCharCode(65 + alpha)
                }
            }
        }
    }
    return ' ';
}

function getMarks(grid) {

    var set = getSet(grid)
    var noOfAnswers = correct[set].length;
    var answersInARow = 5;
    var answersInAColumn = Math.ceil(noOfAnswers / answersInARow);
    var rowsPerAnswer = 1;
    var columnsPerAnswer = 6;
    var startColumn = 1;
    var startRow = 39;
    var marks = 0;

    for (var i = startRow; i < startRow + answersInAColumn * rowsPerAnswer; i += rowsPerAnswer) {

        for (var j = startColumn; j < columnsPerAnswer * answersInARow; j += columnsPerAnswer) {

            var questionNo = ((j - startColumn) / columnsPerAnswer) * answersInAColumn + ((i - startRow) / rowsPerAnswer);

            if (questionNo <= noOfAnswers) {
                var option = '';

                option += (grid[j][i]) ? 'A' : '';
                option += (grid[j + 1][i]) ? 'B' : '';
                option += grid[j + 2][i] ? 'C' : '';
                option += grid[j + 3][i] ? 'D' : '';

                console.log(questionNo + ' ( ' + i + ',' + j + ' ) : ' + option);

                if (option.length == 1) {
                    if (correct[set][questionNo] == option)
                        marks += 1;
                }
            }
        }
    }
    return marks;
}

function publishResult(grid) {
    var name = getName(grid);
    var marks = getMarks(grid);
    var roll = getRollNo(grid);
    var div = getDiv(grid);
    var srno = $("#resultTable tr").length;
    var pass = (marks > 19) ? 1 : 0;
    var $row = $('<tr></tr>');
    $row.append($('<td></td>').append(srno));
    $row.append($('<td></td>').append('<b>' + name + '</b>'));
    $row.append($('<td></td>').append(div));
    $row.append($('<td></td>').append(roll));
    $row.append($('<td></td>').append(marks));
    if (pass) {
        $row.addClass('success');
        $row.append($('<td></td>').append('<span class="label label-success">Pass</span>'));

    } else {
        $row.addClass('danger')
        $row.append($('<td></td>').append('<span class="label label-danger">Fail</span>'));
    }

    $("#resultTable").append($row);
}