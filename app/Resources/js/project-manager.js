/* project triggers */
$( "#modal-add-project" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-project" ).click(function() {
    $("#modal-add-project").modal('show');
});

$( "#save-project" ).click(function() {
    saveProject();
});

$( "[id^=delete-project-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    deleteProject(id, name);
});

/* application triggers */
$( "[id^=modal-add-application-]" ).modal({
    backdrop: 'static',
    show: false
});

$( "[id^=add-application-]" ).click(function() {
    var id = $(this).data('project-id');
    var name = $(this).data('project-name');
    var description = $(this).data('project-description');
    showAddApplicationForm(id, name, description);
});

$( "[id^=delete-application-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    var projectId = $(this).data('projectId');
    var projectName = $(this).data('projectName');
    deleteApplication(id, name, projectId, projectName);
});

/* project methods */
function refreshProjectSubtitle(count) {
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
    subtitle += ' project' + (count > 1 ? "s" : "");
    $('#projects-count').html(subtitle);;
}

function saveProject() {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_project_ajax'),
        data: $("#form-add-project").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Project not added !", data.error, "error");
        } else {
            var project = JSON.parse(data.project);
            var id = project.id;
            var name = project.name;
            var panel = data.panel;
            $("#projects-row").prepend(panel);
            $('#delete-project-' + id).click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                deleteProject(id, name);
            }).tooltip();
            $('#add-application-' + id).tooltip();
            $('#add-application-' + id).click(function(event) {
                event.preventDefault();
                var id = $(this).data('project-id');
                var name = $(this).data('project-name');
                var description = $(this).data('project-description');
                showAddApplicationForm(id, name, description);
            });
            refreshApplicationSubtitle(0, id);
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
            var projectsCount = $("[id^=panel-project-]").length;
            refreshProjectSubtitle(projectsCount);
            swal(name + " added !", "Your project has been added.", "success");
            $("#form-add-project")[0].reset();
            $("#modal-add-project").modal('hide');
        }
    });
}

function deleteProject(id, name) {  
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
                swal(name + " not deleted !", data.error, "error");
            } else {
                $('#panel-project-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    var container = $(this).parent();
                    $(this).remove();
                    container.remove();
                    var projectsCount = $("[id^=panel-project-]").length;
                    refreshProjectSubtitle(projectsCount);
                });
                swal(name + " deleted !", "Your project has been deleted.", "success");
            }
        });
    });
}

/* application methods */
function refreshApplicationSubtitle(count, projectId) {
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
    subtitle += ' application' + (count > 1 ? "s" : "") + ' in the project';
    $('#application-count-' + projectId).html(subtitle);
}

function showAddApplicationForm(id, name, description) {
    $('#new-application-project-name').html(name);
    $('#new-application-project-description').html(description);
    $('#save-application').data('project-id', id);
    $('#save-application').data('project-name', name);
    $('#save-application').data('project-description', description);
    $('#save-application').unbind('click');
    $('#save-application').click(function(event) {
        event.preventDefault();
        var projectId = $(this).data('project-id');
        var projectName = $(this).data('project-name');
        saveApplication(projectId, projectName);
    });
    $("#modal-add-application").modal('show');
}

function saveApplication(projectId, projectName) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_ajax', {
            'id': projectId
        }),
        data: $("#form-add-application").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Application not added !", data.error, "error");
        } else {
            var id = data.id;
            var name = data.name;
            $("#applications-" + projectId).show();
            var table = $('#applications-' + projectId).dataTable();
            var rowNode = $(data.row);
            table.fnAddTr(rowNode[0]);
            $("#form-add-application")[0].reset();
            $("#modal-add-application").modal('hide');
            $("#delete-application-" + id).click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                var projectId = $(this).data('projectId');
                var projectName = $(this).data('projectName');
                deleteApplication(id, name, projectId, projectName);
            });
            var applicationsCount = $('#applications-' + projectId + ' tr').length - 1;
            refreshApplicationSubtitle(applicationsCount, projectId);
            swal(name + " added to " + projectName + " !", "Your application has been added.", "success");
        }
    });
}

function deleteApplication(id, name, projectId, projectName) {
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
                swal(name + " not deleted !", data.error, "error");
            } else {
                var oTable = $('#applications-' + projectId).DataTable();
                oTable.row("#row-application-" + id).remove().draw();
                var applicationsCount = oTable.data().length;
                refreshApplicationSubtitle(applicationsCount, projectId);
                if (applicationsCount === 0) {
                    $('#applications-' + projectId).hide();
                }
                swal(name + " deleted !", "Your application has been deleted.", "success");
            }
        });
    });
}