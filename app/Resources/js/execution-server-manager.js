/* server triggers */
$( "#modal-add-execution-server" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-execution-server" ).click(function() {
    $("#modal-add-execution-server").modal('show');
});

$( "#save-execution-server" ).click(function() {
    saveExecutionServer();
});

$( "[id^=delete-execution-server-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    deleteExecutionServer(id, name);
});

/* server methods */
function refreshExecutionServerSubtitle(count) {
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
    subtitle += ' execution server' + (count > 1 ? "s" : "");
    $('#execution-servers-count').html(subtitle);
}

function saveExecutionServer() {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_configuration_execution_server_ajax'),
        data: $("#form-add-execution-server").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Execution server not added !", data.error, "error");
        } else {
            var executionServer = data.executionServer;
            var id = executionServer.id;
            var name = executionServer.name;
            var panel = data.panel;
            $("#execution-servers-row").prepend(panel);
            $('#delete-execution-server-' + id).click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                deleteExecutionServer(id, name);
            }).tooltip();
            var executionServersCount = $("[id^=panel-execution-server-]").length;
            refreshExecutionServerSubtitle(executionServersCount);
            swal(name + " added !", "Your execution server has been added.", "success");
            $("#form-add-execution-server")[0].reset();
            $("#modal-add-execution-server").modal('hide');
        }
    });
}

function deleteExecutionServer(id, name) {  
    swal({
        title: "Delete " + name + " ?",
        text: "You will not be able to recover this execution server !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !",
        closeOnConfirm: false
    },
    function(){
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_delete_configuration_execution_server_ajax', {
                'id': id
            })
        }).done(function(data) {
            if (data.error) {
                swal(name + " not deleted !", data.error, "error");
            } else {
                $('#panel-execution-server-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    var container = $(this).parent();
                    $(this).remove();
                    container.remove();
                    var executionServersCount = $("[id^=panel-execution-server-]").length;
                    refreshExecutionServerSubtitle(executionServersCount);
                });
                swal(name + " deleted !", "Your execution server has been deleted.", "success");
            }
        });
    });
}