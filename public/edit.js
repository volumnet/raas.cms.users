jQuery(document).ready(function($) {
    var notifications = {};

    var fillText = function(subject, text)
    {
        $('#notifyUserModal form').show();
        $('#notifyUserModal [data-role="notifications"]').hide();
        $('#notifyUserModal #notifySubject').val(subject);
        var $textarea = $('<textarea id="notification_text" name="notification_text"></textarea>');
        $textarea.val(text)
        $('#notifyUserModal [data-role="text-container"]').empty().append($textarea);
        $textarea.ckeditor(ckEditorConfig);
    }

    var checkVis = function(vis)
    {
        if (vis) {
            $('#notify_about_activation').click();
        } else {
            $('#notify_about_block').click();
        }
    }

    var url = 'ajax.php?p=cms&m=users&action=get_notifications&id=1';
    $.getJSON(url, function(data) {
        notifications = data;
        $('#notify_about_activation').click(function() { fillText(notifications.activate_subject, notifications.activate); });
        $('#notify_about_block').click(function() { fillText(notifications.block_subject, notifications.block); });
        checkVis($('#vis').is(':checked'));
        $('#vis').click(function() { checkVis($(this).is(':checked')) })

        $('#notifyUserModal form').submit(function() {
            $(this).ajaxForm();
            $(this).ajaxSubmit({ 
                dataType: 'json', 
                'url': $(this).attr('action'), 
                success: function() {
                    $('#notifyUserModal form').hide();
                    $('#notifyUserModal [data-role="notifications"]').show();
                }
            });
            return false;
        })
    })
});