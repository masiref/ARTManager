var ProjectManager = {
    init: function() {
        $("#modal-add-project").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-project").click(function() {
            $("#modal-add-project").modal('show');
        });
        $("#save-project").click(function() {
            ProjectManager.save();
        });
        $("[id^=delete-project-]").click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            ProjectManager.delete(id, name);
        });
    },
    initItem: function(id) {
        $("#open-project-" + id).tooltip();
        $('#delete-project-' + id).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            ProjectManager.delete(id, name);
        }).tooltip();
        $('#add-application-' + id).click(function(event) {
            event.preventDefault();
            var projectId = $(this).data('project-id');
            var projectName = $(this).data('project-name');
            var projectDescription = $(this).data('project-description');
            ApplicationManager.openAddFormModal(projectId, projectName, projectDescription);
        }).tooltip();
        ProjectManager.initApplicationsTable(id);
    },
    initApplicationsTable: function(id) {
        $('#applications-' + id).DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [ 1, 'asc' ]
            ],
            "columnDefs": [
                { "orderable": false, "targets": 0 }
            ]
        });
    },
    initEditableData: function() {
        $( "#name" ).editable({
            success: function(response, newValue) {
                $("#breadcrumb-active-item").html(response);
            },
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            }
        });
        $( "#description" ).editable({
            emptytext: 'Add description'
        });
    },
    resetAddForm: function() {
        $("#form-add-project")[0].reset();
    },
    closeAddFormModal: function() {
        $("#modal-add-project").modal('hide');
    },
    refreshSummary: function() {
        var count = ProjectManager.getCount();
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' project' + (count > 1 ? "s" : "");
        $('#projects-count').html(subtitle);
    },
    refreshProjectSummary: function(id) {
        var count = ProjectManager.getApplicationCount(id);
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' application' + (count > 1 ? "s" : "") + ' in the project';
        $('#application-count-' + id).html(subtitle);
        if (count === 0) {
            ProjectManager.hideApplicationTable(id);
        }
    },
    getCount: function() {
        return $("[id^=panel-project-]").length;
    },
    getApplicationCount: function(id) {
        var table = $("#applications-" + id).DataTable();
        return table.data().length;
    },
    add: function(panel) {
        $("#projects-row").prepend(panel);
    },
    remove: function(id) {
        $('#panel-project-' + id).css('visibility', 'hidden').animate({
            height: 0,
            width: 0
        }, 300, function () {
            var container = $(this).parent();
            $(this).remove();
            container.remove();
            ProjectManager.refreshSummary();
        });
    },
    save: function() {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_project_ajax'),
            data: $("#form-add-project").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Project not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var project = data.project;
                var id = project.id;
                var name = project.name;
                var panel = data.panel;
                ProjectManager.add(panel);
                ProjectManager.initItem(id);
                ProjectManager.refreshProjectSummary(id);
                ProjectManager.refreshSummary();
                ProjectManager.resetAddForm();
                ProjectManager.closeAddFormModal();
                var message = name + " added !";
                Base.showSuccessMessage(message);
            }
        });
    },
    delete: function(id, name) {
        swal({
            title: "Delete " + name + " ?",
            text: "You will not be able to recover this project !",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it !",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_delete_project_ajax', {
                    'id': id
                })
            }).done(function(data) {
                if (data.error) {
                    var message = name + " not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    ProjectManager.remove(id);
                    var message = name + " deleted !";
                    Base.showSuccessMessage(message);
                }
            });
        });
    },
    showApplicationTable: function(id) {
        $("#applications-" + id).show();
    },
    hideApplicationTable: function(id) {
        $("#applications-" + id).hide();
    }
};