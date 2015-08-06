$( "#modal-add-step" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-execute-step" ).click(function() {
    var id = $(this).data('test-id');
    showAddStepForm(id);
});
 
$( "#save-execute-step" ).click(function() {
    var testId = $(this).data('test-id');
    if (testId !== '') {
        saveStep(testId);
    }
    var stepId = $(this).data('step-id');
    if (stepId !== '') {
        updateStep(stepId);
    }
});

$( "[id^=add-control-step-step-]").click(function() {
    var id = $(this).data('step-id');
    var name = $(this).data('step-name');
    showAddControlStepForm(id, name);
});

$( "#save-control-step" ).click(function() {
    var stepId = $(this).data('step-id');
    saveControlStep(stepId);
});

$( "[id^=delete-execute-step-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    var order = $(this).data('order');
    var testId = $(this).data('test-id');
    deleteExecuteStep(id, name, order, testId);
});

$( "[id^=delete-control-step-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    var order = $(this).data('order');
    var stepId = $(this).data('step-id');
    deleteControlStep(id, name, order, stepId);
});

$( "[id^=edit-execute-step-]" ).click(function() {
    var id = $(this).data('id');
    var testId = $(this).data('test-id');
    showEditStepForm(id, testId);
});

function showAddStepForm(id) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_step_form_ajax', {
            'testId': id
        })
    }).done(function(data) {
        $("#modal-execute-step-body").html(data.form);
        $("#modal-execute-step-title").html(data.modalTitle);
        $('#save-execute-step').data('step-id', '');
        $('#save-execute-step').data('test-id', id);
        $("#modal-execute-step").modal('show');

        $( "#execute_step_object" ).change(function() {
            var testId = $(this).data('test-id');
            updateStepFormAfterObjectSelection(testId);
        });

        $( "#execute_step_action" ).change(function() {
            var testId = $(this).data('test-id');
            updateStepFormAfterActionSelection(testId);
        });
    });
}

function updateStepFormAfterObjectSelection(testId) {
    $( "#execute_step_action" ).parent().remove();
    $( "#execute_step_parameterDatas" ).parent().remove();
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_check_application_test_execute_step_ajax', {
            'id': testId
        }),
        data: $("#form-execute-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not updated !", data.error, "error");
        } else {
            $("#form-execute-step").replaceWith($(data.form));
            $( "#execute_step_object" ).change(function() {
                var testId = $(this).data('test-id');
                updateStepFormAfterObjectSelection(testId);
            });
            $( "#execute_step_action" ).change(function() {
                var testId = $(this).data('test-id');
                updateStepFormAfterActionSelection(testId);
            });
        }
    });
}

function updateStepFormAfterActionSelection(testId) {
    $( "#execute_step_parameterDatas" ).parent().remove();
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_check_application_test_execute_step_ajax', {
            'id': testId
        }),
        data: $("#form-execute-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not updated !", data.error, "error");
        } else {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                $( "#execute_step_object" ).change(function() {
                    var testId = $(this).data('test-id');
                    updateStepFormAfterObjectSelection(testId);
                });
                $( "#execute_step_action" ).change(function() {
                    var testId = $(this).data('test-id');
                    updateStepFormAfterActionSelection(testId);
                });
            }
        }
    });
}

function saveStep(testId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_test_execute_step_ajax', {
            'id': testId
        }),
        data: $("#form-execute-step").serialize()
    }).done(function(data) {
        if (data.form) {
            $("#form-execute-step").replaceWith($(data.form));
            $( "#execute_step_object" ).change(function() {
                var testId = $(this).data('test-id');
                updateStepFormAfterObjectSelection(testId);
            });
            $( "#execute_step_action" ).change(function() {
                var testId = $(this).data('test-id');
                updateStepFormAfterActionSelection(testId);
            });
        } else {
            showStepAndCloseAddStepForm(data);
        }
    });
}

function showAddControlStepForm(id, name) {
    $('#new-step-step-name').html(name);
    $('#save-control-step').data('step-id', id);
    $("#modal-add-control-step").modal('show');
}

function saveControlStep(stepId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_test_step_control_step_ajax', {
            'id': stepId
        }),
        data: $("#form-add-control-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not added !", data.error, "error");
        } else {
            var id = data.id;
            var row = data.row;
            $(row).insertBefore($('#control-step-footer-' + stepId));
            $( "#delete-control-step-" + id).click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                var order = $(this).data('order');
                var stepId = $(this).data('step-id');
                deleteControlStep(id, name, order, stepId);
            });
            swal("Step added with success !", "", "success");
            $("#form-add-control-step")[0].reset();
            $("#modal-add-control-step").modal('hide');
        }
    });
}

function deleteExecuteStep(id, name, order, testId) {  
    swal({
        title: "Delete #" + order + " " + name + " ?",
        text: "You will not be able to recover this step !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !",
        closeOnConfirm: false
    },
    function(){
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_delete_application_test_execute_step_ajax', {
                'id': id
            })
        }).done(function(data) {
            if (data.error) {
                swal("#" + order + " " + name + " not deleted !", data.error, "error");
            } else {
                $('#step-row-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    $(this).remove();
                    updateExecuteStepsOrders(testId);
                });
                swal("Step deleted with success !", "", "success");
            }
        });
    });
}

function updateExecuteStepsOrders(testId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_execute_step_orders_ajax', {
            'id': testId
        })
    }).done(function(data) {
        jQuery.each(data, function(id, order) {
            $("#step-order-" + id).html(order);
            $("#delete-execute-step-" + id).data("order", order);
        });
    });
}

function deleteControlStep(id, name, order, stepId) {  
    swal({
        title: "Delete #" + order + " " + name + " ?",
        text: "You will not be able to recover this step !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !",
        closeOnConfirm: false
    },
    function(){
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_delete_application_test_step_control_step_ajax', {
                'id': id
            })
        }).done(function(data) {
            if (data.error) {
                swal("#" + order + " " + name + " not deleted !", data.error, "error");
            } else {
                $('#control-step-row-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    $(this).remove();
                    // TODO: update execute steps orders
                    updateControlStepsOrders(stepId);
                });
                swal("Step deleted with success !", "", "success");
            }
        });
    });
}

function updateControlStepsOrders(stepId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_step_control_step_orders_ajax', {
            'id': stepId
        })
    }).done(function(data) {
        jQuery.each(data, function(id, order) {
            $("#control-step-order-" + id).html(order);
            $("#delete-control-step-" + id).data("order", order);
        });
    });
}

function updateStartingPage(testId, pageId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_test_update_starting_page_ajax', {
            id: testId
        }),
        data: {
            pageId: pageId
        }
    }).done(function(data) {
        if (data.error) {
            swal("Starting page not updated !", data.error, "error");
        }
    });
}

function showStepAndCloseAddStepForm(data) {
    var id = data.id;
    var row = data.row;
    $(row).insertBefore($('#step-footer'));
    $( "#delete-execute-step-" + id ).click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        var order = $(this).data('order');
        var testId = $(this).data('test-id');
        deleteExecuteStep(id, name, order, testId);
    });
    $( "#add-control-step-step-" + id).click(function(event) {
        event.preventDefault();
        var id = $(this).data('step-id');
        var name = $(this).data('step-name');
        showAddControlStepForm(id, name);
    });
    $( "#edit-execute-step-" + id ).click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var testId = $(this).data('test-id');
        showEditStepForm(id, testId);
    });
    swal("Step saved with success !", "", "success");
    $( "#execute_step_action" ).parent().remove();
    $( "#execute_step_parameterDatas" ).parent().remove();
    $("#execute_step_object").val($("#execute_step_object option:first").val());
    $("#modal-execute-step").modal('hide');
}

function showEditStepForm(id, testId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_step_form_ajax', {
            'id': id,
            'testId': testId
        })
    }).done(function(data) {
        $("#modal-execute-step-body").html(data.form);
        $("#modal-execute-step-title").html(data.modalTitle);
        $('#save-execute-step').data('test-id', '');
        $('#save-execute-step').data('step-id', id);
        $("#modal-execute-step").modal('show');

        $( "#execute_step_object" ).change(function() {
            var testId = $(this).data('test-id');
            updateStepFormAfterObjectSelection(testId);
        });

        $( "#execute_step_action" ).change(function() {
            var testId = $(this).data('test-id');
            updateStepFormAfterActionSelection(testId);
        });
    });
}

function updateStep(id) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_update_application_test_execute_step_ajax', {
            'id': id
        }),
        data: $("#form-execute-step").serialize()
    }).done(function(data) {
        if (data.form) {
            $("#form-execute-step").replaceWith($(data.form));
            $( "#execute_step_object" ).change(function() {
                var testId = $(this).data('test-id');
                updateStepFormAfterObjectSelection(testId);
            });
            $( "#execute_step_action" ).change(function() {
                var testId = $(this).data('test-id');
                updateStepFormAfterActionSelection(testId);
            });
        } else {
            updateStepAndCloseAddStepForm(data);
        }
    });
}

function updateStepAndCloseAddStepForm(data) {
    var id = data.id;
    var $row = $(data.row);
    $("#step-row-" + id).replaceWith($row);
    $( "#delete-execute-step-" + id ).click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        var order = $(this).data('order');
        var testId = $(this).data('test-id');
        deleteExecuteStep(id, name, order, testId);
    });
    $( "#add-control-step-step-" + id).click(function(event) {
        event.preventDefault();
        var id = $(this).data('step-id');
        var name = $(this).data('step-name');
        showAddControlStepForm(id, name);
    });
    $( "#edit-execute-step-" + id ).click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var testId = $(this).data('test-id');
        showEditStepForm(id, testId);
    });
    swal("Step updated with success !", "", "success");
    $( "#execute_step_action" ).parent().remove();
    $( "#execute_step_parameterDatas" ).parent().remove();
    $("#execute_step_object").val($("#execute_step_object option:first").val());
    $("#modal-execute-step").modal('hide');
}
