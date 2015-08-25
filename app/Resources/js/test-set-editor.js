/* test instance triggers */
$( "#modal-add-test-instance" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-test-instance" ).click(function() {
    showAddTestInstanceFormModal();
});

$( "#save-test-instances").click(function() {
    var testSetId = $(this).data('test-set-id');
    var applicationId = $(this).data('application-id');
    addSelectedTestsInstancesToTestSet(testSetId, applicationId);
});

$("[id^=delete-test-instance-]").click(function() {
    var id = $(this).data('id');
    var order = $(this).data('order');
    var name = $(this).data('test-name');
    deleteTestInstance(id, order, name);
});

/* execution grid methods */
function triggerExecutionGridEventListeners(id) {
    $('#execution-grid-' + id).dataTable({
        "searching": false,
        "paging": false,
        "info": false
    }).rowReordering();

    $("[id^=delete-test-instance-]").click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var order = $(this).data('order');
        var name = $(this).data('test-name');
        deleteTestInstance(id, order, name);
    }).tooltip();

    $( "#add-test-instance" ).click(function(event) {
        event.preventDefault();
        showAddTestInstanceFormModal();
    }).tooltip();
}

function refreshExecutionGrid(data) {
    $("#execution-grid").replaceWith($(data));
}

/* test instance methods */
function showAddTestInstanceFormModal() {
    $("#modal-add-test-instance").modal('show');
}

function addSelectedTestsInstancesToTestSet(testSetId, applicationId) {
    var testsTreeHtmlId = "#tree-tests-" + applicationId;
    var objects = $(testsTreeHtmlId).treeview('getChecked');
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_test_set_instances_ajax', {
            'id': testSetId
        }),
        data: {
            objects: objects
        }
    }).done(function(data) {
        if (data.error) {
            swal("Selected test instances were not added !", data.error, "error");
        } else {
            var count = data.count;
            refreshExecutionGrid(data.executionGrid);
            triggerExecutionGridEventListeners(testSetId);
            swal("Selected test instances added !", "You have added " + count + " test instance" + (count > 1 ? "s" : ""), "success");
            $("#modal-add-test-instance").modal('hide');
        }
    });
}

function deleteTestInstance(id, order, name) {
    swal({
        title: "Delete test instance #" + order + " " + name + " ?",
        text: "You will not be able to recover it !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !",
        closeOnConfirm: false
    }, function() {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_delete_application_test_set_instance_ajax', {
                id: id
            })
        }).done(function(data) {
            if (data.error) {
                swal("Test instance was not deleted !", data.error, "error");
            } else {
                refreshExecutionGrid(data.executionGrid);
                triggerExecutionGridEventListeners(data.testSetId);
                swal("Test instance deleted !", "", "success");
            }
        });
    });
}

/* hybrid methods */
function triggerTestSetCollapsibleElementsEventListeners() {
    $("#execution-grid-collapse").on('shown.bs.collapse', function() {
        $(".execution-grid-collapse-toggle-icon").removeClass("fontello-icon-down-open")
                .addClass("fontello-icon-up-open");
    });

    $("#execution-grid-collapse").on('hidden.bs.collapse', function() {
        $(".execution-grid-collapse-toggle-icon").removeClass("fontello-icon-up-open")
                .addClass("fontello-icon-down-open");
    });
}