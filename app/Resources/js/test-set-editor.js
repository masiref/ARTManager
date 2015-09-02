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
    var name = $(this).data('test-name');
    deleteTestInstance(id, name);
});

/* test set triggers */
$( "#modal-run-test-set" ).modal({
    backdrop: 'static',
    show: false
});

$( "#run-test-set" ).click(function() {
    showRunTestSetModal();
});

$( "#save-test-set-run").click(function() {
    var testSetId = $(this).data('test-set-id');
    saveTestSetRun(testSetId);
});

/* execution grid methods */
function triggerExecutionGridEventListeners(id) {
    $('#execution-grid-' + id).dataTable({
        "searching": false,
        "paging": false,
        "info": false
    }).rowReordering({
        sURL: Routing.generate('app_update_application_test_set_test_instance_orders_ajax'),
        sRequestType: "POST",
        callback: function() {
            refreshBehatFeature(id);
        }
    });

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
            refreshBehatFeature(testSetId);
            swal("Selected test instances added !", "You have added " + count + " test instance" + (count > 1 ? "s" : ""), "success");
            $("#modal-add-test-instance").modal('hide');
        }
    });
}

function deleteTestInstance(id, name) {
    swal({
        title: "Delete test instance " + name + " ?",
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
                refreshBehatFeature(data.testSetId);
                swal("Test instance deleted !", "", "success");
            }
        });
    });
}

/* test set methods */
function showRunTestSetModal() {
    $("#modal-run-test-set").modal('show');
}

function saveTestSetRun(testSetId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_test_set_run_ajax', {
            'id': testSetId
        }),
        data: $("#form-add-test-set-run").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Run not added !", data.error, "error");
        } else {
            swal("Run added !", "Job handle: " + data.handle, "success");
        }
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

function refreshBehatFeature(testSetId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_set_behat_feature_ajax', {
            'id': testSetId
        })
    }).done(function(data) {
        $("#behat-feature").html(data.feature);
    });
}