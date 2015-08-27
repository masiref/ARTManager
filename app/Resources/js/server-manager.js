/* server triggers */
$( "#modal-add-server" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-server" ).click(function() {
    $("#modal-add-server").modal('show');
});

$( "#save-server" ).click(function() {
    saveServer();
});

$( "[id^=delete-server-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    deleteServer(id, name);
});

$( "[id^=check-connection-server-]").click(function() {
    var id = $(this).data('id');
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_index_configuration_server_check_connection_ajax', {
            'id': id
        })
    }).done(function(data) {
        swal("Result", data.result, "info");
    });
});

/* server methods */
function refreshServerSubtitle(count) {
    var subtitle = '';
    if (count <= 1) {
        subtitle += 'There is ';
    } else {
        subtitle += 'There are ';
    }
    if (count === 0) {
        subtitle += 'no ';
    } else {
        subtitle += '<span class="badge">' + count + '</span>';
    }
    subtitle += ' server' + (count > 1 ? "s" : "");
    $('#servers-count').html(subtitle);
}

function saveServer() {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_configuration_server_ajax'),
        data: $("#form-add-server").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Server not added !", data.error, "error");
        } else {
            var server = data.server;
            var id = server.id;
            var name = server.name;
            var panel = data.panel;
            $("#servers-row").prepend(panel);
            $('#delete-server-' + id).click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                deleteServer(id, name);
            }).tooltip();
            var serversCount = $("[id^=panel-server-]").length;
            refreshServerSubtitle(serversCount);
            swal(name + " added !", "Your server has been added.", "success");
            $("#form-add-server")[0].reset();
            $("#modal-add-server").modal('hide');
        }
    });
}

function deleteServer(id, name) {  
    swal({
        title: "Delete " + name + " ?",
        text: "You will not be able to recover this server !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !",
        closeOnConfirm: false
    },
    function(){
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_delete_configuration_server_ajax', {
                'id': id
            })
        }).done(function(data) {
            if (data.error) {
                swal(name + " not deleted !", data.error, "error");
            } else {
                $('#panel-server-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    var container = $(this).parent();
                    $(this).remove();
                    container.remove();
                    var serversCount = $("[id^=panel-server-]").length;
                    refreshServerSubtitle(serversCount);
                });
                swal(name + " deleted !", "Your server has been deleted.", "success");
            }
        });
    });
}