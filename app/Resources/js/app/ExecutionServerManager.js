var ExecutionServerManager = {
    init: function() {
        $("#modal-add-execution-server").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-execution-server").click(function() {
            $("#modal-add-execution-server").modal('show');
        });
        $("#save-execution-server").click(function() {
            ExecutionServerManager.save();
        });
        $("[id^=delete-execution-server-]").click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            ExecutionServerManager.delete(id, name);
        });
    },
    initItem: function(id) {
        $("#open-execution-server-" + id).tooltip();
        $('#delete-execution-server-' + id).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            ExecutionServerManager.delete(id, name);
        }).tooltip();
    },
    initEditableData: function(serverId) {
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
        $("#server").editable({
            value: serverId,
            emptytext: 'Select a server'
        });
        $("#art-runner-path").editable();
    },
    resetAddForm: function() {
        $("#form-add-execution-server")[0].reset();
    },
    closeAddFormModal: function() {
        $("#modal-add-execution-server").modal('hide');
    },
    refreshSummary: function() {
        var count = ExecutionServerManager.getCount();
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' execution server' + (count > 1 ? "s" : "");
        $('#execution-servers-count').html(subtitle);
    },
    getCount: function() {
        return $("[id^=panel-execution-server-]").length;
    },
    add: function(panel) {
        $("#execution-servers-row").prepend(panel);
    },
    remove: function(id) {
        $('#panel-execution-server-' + id).css('visibility', 'hidden').animate({
            height: 0,
            width: 0
        }, 300, function () {
            var container = $(this).parent();
            $(this).remove();
            container.remove();
            ExecutionServerManager.refreshSummary();
        });
    },
    save: function() {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_configuration_execution_server_ajax'),
            data: $("#form-add-execution-server").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Execution server not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var executionServer = data.executionServer;
                var id = executionServer.id;
                var name = executionServer.name;
                var panel = data.panel;
                ExecutionServerManager.add(panel);
                ExecutionServerManager.initItem(id);
                ExecutionServerManager.refreshSummary();
                ExecutionServerManager.resetAddForm();
                ExecutionServerManager.closeAddFormModal();
                var message = name + " added !";
                Base.showSuccessMessage(message);
            }
        });
    },
    delete: function(id, name) {
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
                    var message = "Execution server not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    ExecutionServerManager.remove(id);
                    var message = name + " deleted !";
                    Base.showSuccessMessage(message);
                }
            });
        });
    }
};