var ServerManager = {
    init: function() {
        $("#modal-add-server").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-server").click(function() {
            $("#modal-add-server").modal('show');
        });
        $("#save-server").click(function() {
            ServerManager.save();
        });
        $("[id^=delete-server-]").click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            ServerManager.delete(id, name);
        });
        $("[id^=check-connection-server-]").click(function() {
            var id = $(this).data('id');
            ServerManager.checkConnection(id);
        });
    },
    initItem: function(id) {
        $("#open-server-" + id).tooltip();
        $('#delete-server-' + id).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            ServerManager.delete(id, name);
        }).tooltip();
    },
    initEditableData: function() {
        $("#name").editable({
            success: function(response, newValue) {
                $("#breadcrumb-active-item").html(response);
            },
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            }
        });
        $("#description").editable({
            emptytext: 'Add description'
        });
        $("#host").editable({
            success: function(response, newValue) {
                $("#breadcrumb-active-item").html(response);
            }
        });
        $("#port").editable();
        $("#username").editable();
        $("#password").editable();
    },
    resetAddForm: function() {
        $("#form-add-server")[0].reset();
    },
    closeAddFormModal: function() {
        $("#modal-add-server").modal('hide');
    },
    refreshSummary: function() {
        var count = ServerManager.getCount();
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' server' + (count > 1 ? "s" : "");
        $('#servers-count').html(subtitle);
    },
    getCount: function() {
        return $("[id^=panel-server-]").length;
    },
    add: function(panel) {
        $("#servers-row").prepend(panel);
    },
    remove: function(id) {
        $('#panel-server-' + id).css('visibility', 'hidden').animate({
            height: 0,
            width: 0
        }, 300, function () {
            var container = $(this).parent();
            $(this).remove();
            container.remove();
            ServerManager.refreshSummary();
        });
    },
    save: function() {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_configuration_server_ajax'),
            data: $("#form-add-server").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Server not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var server = data.server;
                var id = server.id;
                var name = server.name;
                var panel = data.panel;
                ServerManager.add(panel);
                ServerManager.initItem(id);
                ServerManager.refreshSummary();
                ServerManager.resetAddForm();
                ServerManager.closeAddFormModal();
                var message = name + " added !";
                Base.showSuccessMessage(message);
            }
        });
    },
    delete: function(id, name) {
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
                    var message = "Server not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    ServerManager.remove(id);
                    var message = name + " deleted !";
                    Base.showSuccessMessage(message);
                }
            });
        });
    },
    checkConnection: function(id) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_index_configuration_server_check_connection_ajax', {
                'id': id
            })
        }).done(function(data) {
            Base.showInfoMessage(data.result);
        });
    }
};