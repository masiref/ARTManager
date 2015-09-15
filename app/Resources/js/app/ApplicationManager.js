var ApplicationManager = {
    init: function() {
        $("[id^=modal-add-application-]").modal({
            backdrop: 'static',
            show: false
        });
        $("[id^=add-application-]").click(function() {
            var projectId = $(this).data('project-id');
            var projectName = $(this).data('project-name');
            var projectDescription = $(this).data('project-description');
            ApplicationManager.openAddFormModal(projectId, projectName, projectDescription);
        });
        $("[id^=delete-application-]").click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var projectId = $(this).data('projectId');
            var projectName = $(this).data('projectName');
            ApplicationManager.delete(id, name, projectId, projectName);
        });
        $("#save-application").click(function() {
            var projectId = $(this).data('project-id');
            var projectName = $(this).data('project-name');
            ApplicationManager.save(projectId, projectName);
        });
    },
    initItem: function(id) {
        $("#open-application-" + id).tooltip();
        $("#delete-application-" + id).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            var projectId = $(this).data('projectId');
            var projectName = $(this).data('projectName');
            ApplicationManager.delete(id, name, projectId, projectName);
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
            emptytext: 'Add a description'
        });
        $("#url").editable({
            emptytext: 'Add an URL',
            success: function(response, newValue) {
                var gotoApplicationLink = $("#goto-application-link");
                if (newValue === "") {
                    gotoApplicationLink.hide();
                } else {
                    gotoApplicationLink.show();
                    gotoApplicationLink.attr("href", newValue);
                }
            },
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            }
        });
    },
    resetAddForm: function() {
        $("#form-add-application")[0].reset();
    },
    openAddFormModal: function(projectId, projectName, projectDescription) {
        $('#new-application-project-name').html(projectName);
        $('#new-application-project-description').html(projectDescription);
        $('#save-application').data('project-id', projectId);
        $('#save-application').data('project-name', projectName);
        $('#save-application').data('project-description', projectDescription);
        $("#modal-add-application").modal('show');
    },
    closeAddFormModal: function() {
        $("#modal-add-application").modal('hide');
    },
    refreshSummary: function(projectId) {
        ProjectManager.refreshProjectSummary(projectId);
    },
    add: function(projectId, row) {
        var table = $('#applications-' + projectId).dataTable();
        var rowNode = $(row);
        table.fnAddTr(rowNode[0]);
    },
    remove: function(id, projectId) {
        var oTable = $('#applications-' + projectId).dataTable();
        oTable.row("#row-application-" + id).remove().draw();
    },
    save: function(projectId, projectName) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_ajax', {
                'id': projectId
            }),
            data: $("#form-add-application").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Application not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var id = data.id;
                var name = data.name;
                ProjectManager.showApplicationTable(projectId);
                ApplicationManager.add(projectId, data.row);
                ApplicationManager.resetAddForm();
                ApplicationManager.closeAddFormModal();
                ApplicationManager.initItem(id);
                ApplicationManager.refreshSummary(projectId);
                var message = name + " added to " + projectName + " !";
                Base.showSuccessMessage(message);
            }
        });
        
    },
    delete: function(id, name, projectId, projectName) {
        swal({
            title: "Delete " + name + " from " + projectName + " ?",
            text: "You will not be able to recover this application !",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it !",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_delete_application_ajax', {
                    'id': id
                })
            }).done(function(data) {
                if (data.error) {
                    var message = name + " not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    ApplicationManager.remove(id, projectId);
                    ApplicationManager.refreshSummary(projectId);
                    var message = name + " deleted !";
                    Base.showSuccessMessage(message);
                }
            });
        });
    }
};