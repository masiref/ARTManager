/* execute step triggers */
$( "#modal-execute-step" ).modal({
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

$( "[id^=delete-execute-step-]" ).click(function() {
    var id = $(this).data('id');
    var order = $(this).data('order');
    var testId = $(this).data('test-id');
    deleteExecuteStep(id, order, testId);
});

$( "[id^=edit-execute-step-]" ).click(function() {
    var id = $(this).data('id');
    var testId = $(this).data('test-id');
    showEditStepForm(id, testId);
});

/* control step triggers */
$( "#modal-control-step" ).modal({
    backdrop: 'static',
    show: false
});

$( "[id^=add-control-step-step-]").click(function() {
    var id = $(this).data('step-id');
    showAddControlStepForm(id);
});

$( "#save-control-step" ).click(function() {
    var parentStepId = $(this).data('step-id');
    if (parentStepId !== '') {
        saveControlStep(parentStepId);
    }
    var stepId = $(this).data('control-step-id');
    if (stepId !== '') {
        updateControlStep(stepId);
    }
});

$( "[id^=delete-control-step-]" ).click(function() {
    var id = $(this).data('id');
    var order = $(this).data('order');
    var stepId = $(this).data('step-id');
    var stepOrder = $(this).data('step-order');
    deleteControlStep(id, order, stepId, stepOrder);
});

$( "[id^=edit-control-step-]" ).click(function() {
    var id = $(this).data('id');
    var stepId = $(this).data('step-id');
    showEditControlStepForm(id, stepId);
});

/* test methods */
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

/* execute step methods */
function triggerStepFormEventListeners() {
    $( "#execute_step_object" ).change(function() {
        var testId = $(this).data('test-id');
        updateStepFormAfterObjectSelection(testId);
    });

    $( "#execute_step_action" ).change(function() {
        var testId = $(this).data('test-id');
        updateStepFormAfterActionSelection(testId);
    });
}

function showStepFormModal(form, title) {
    $("#modal-execute-step-body").html(form);
    $("#modal-execute-step-title").html(title);
    $("#modal-execute-step").modal('show');
}

function triggerStepEventListeners(id) {
    $( "#delete-execute-step-" + id ).click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var order = $(this).data('order');
        var testId = $(this).data('test-id');
        deleteExecuteStep(id, order, testId);
    }).tooltip();
    $( "#add-control-step-step-" + id).click(function(event) {
        event.preventDefault();
        var id = $(this).data('step-id');
        showAddControlStepForm(id);
    }).tooltip();
    $( "#edit-execute-step-" + id ).click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var testId = $(this).data('test-id');
        showEditStepForm(id, testId);
    }).tooltip();
}

function resetStepFormAndCloseModal() {
    $( "#execute_step_action" ).parent().remove();
    $( "#execute_step_parameterDatas" ).parent().remove();
    $("#execute_step_object").val($("#execute_step_object option:first").val());
    $("#modal-execute-step").modal('hide');
}

function showAddStepForm(id) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_step_form_ajax', {
            'testId': id
        })
    }).done(function(data) {
        $('#save-execute-step').data('step-id', '');
        $('#save-execute-step').data('test-id', id);
        showStepFormModal(data.form, data.modalTitle);
        triggerStepFormEventListeners();
    });
}

function showEditStepForm(id, testId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_step_form_ajax', {
            'id': id,
            'testId': testId
        })
    }).done(function(data) {
        $('#save-execute-step').data('test-id', '');
        $('#save-execute-step').data('step-id', id);
        showStepFormModal(data.form, data.modalTitle);
        triggerStepFormEventListeners();
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
            triggerStepFormEventListeners();
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
                triggerStepFormEventListeners();
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
            triggerStepFormEventListeners();
        } else {
            showStepAndCloseStepForm(data);
        }
    });
}

function showStepAndCloseStepForm(data) {
    var id = data.id;
    var row = data.row;
    $(row).insertBefore($('#step-footer'));
    triggerStepEventListeners(id);
    resetStepFormAndCloseModal();
    swal("Step saved with success !", "", "success");
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
            triggerStepFormEventListeners();
        } else {
            updateStepAndCloseStepForm(data);
        }
    });
}

function updateStepAndCloseStepForm(data) {
    var id = data.id;
    var $row = $(data.row);
    $("#step-row-" + id).replaceWith($row);
    triggerStepEventListeners(id);
    resetStepFormAndCloseModal();
    swal("Step updated with success !", "", "success");
}

function deleteExecuteStep(id, order, testId) {  
    swal({
        title: "Delete step #" + order + " ?",
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
                swal("Step #" + order + " not deleted !", data.error, "error");
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

/* control step methods */
function triggerControlStepFormEventListeners() {
    $( "#control_step_page" ).change(function() {
        var stepId = $(this).data('step-id');
        updateControlStepFormAfterPageOrObjectSelection(stepId);
    });

    $( "#control_step_object" ).change(function() {
        var stepId = $(this).data('step-id');
        updateControlStepFormAfterPageOrObjectSelection(stepId);
    });

    $( "#control_step_action" ).change(function() {
        var stepId = $(this).data('step-id');
        updateControlStepFormAfterActionSelection(stepId);
    });
}

function showControlStepFormModal(form, title) {
    $("#modal-control-step-body").html(form);
    $("#modal-control-step-title").html(title);
    $("#modal-control-step").modal('show');
}

function triggerControlStepEventListeners(id) {
    $( "#delete-control-step-" + id).click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var order = $(this).data('order');
        var stepId = $(this).data('step-id');
        var stepOrder = $(this).data('step-order');
        deleteControlStep(id, order, stepId, stepOrder);
    }).tooltip();

    $( "#edit-control-step-" + id ).click(function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var stepId = $(this).data('step-id');
        showEditControlStepForm(id, stepId);
    }).tooltip();
}

function resetControlStepFormAndCloseModal() {
    $("#form-control-step")[0].reset();
    $( "#control_step_action" ).parent().remove();
    $( "#control_step_parameterDatas" ).parent().remove();
    $("#control_step_object").val($("#execute_step_object option:first").val());
    $("#modal-control-step").modal('hide');
}

function showAddControlStepForm(id) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_step_control_step_form_ajax', {
            'stepId': id
        })
    }).done(function(data) {
        $('#save-control-step').data('control-step-id', '');
        $('#save-control-step').data('step-id', id);
        showControlStepFormModal(data.form, data.modalTitle);
        triggerControlStepFormEventListeners();
    });
}

function showEditControlStepForm(id, stepId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_step_control_step_form_ajax', {
            'id': id,
            'stepId': stepId
        })
    }).done(function(data) {
        $('#save-control-step').data('control-step-id', id);
        $('#save-control-step').data('step-id', '');
        showControlStepFormModal(data.form, data.modalTitle);
        triggerControlStepFormEventListeners();
    });
}

function updateControlStepFormAfterPageOrObjectSelection(stepId) {
    $( "#control_step_action" ).parent().remove();
    $( "#control_step_parameterDatas" ).parent().remove();
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_check_application_test_step_control_step_ajax', {
            'id': stepId
        }),
        data: $("#form-control-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not updated !", data.error, "error");
        } else {
            $("#form-control-step").replaceWith($(data.form));
            $( "#control_step_object" ).change(function() {
                var stepId = $(this).data('step-id');
                updateControlStepFormAfterPageOrObjectSelection(stepId);
            });
            $( "#control_step_action" ).change(function() {
                var stepId = $(this).data('step-id');
                updateControlStepFormAfterActionSelection(stepId);
            });
        }
    });
}

function updateControlStepFormAfterActionSelection(stepId) {
    $( "#control_step_parameterDatas" ).parent().remove();
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_check_application_test_step_control_step_ajax', {
            'id': stepId
        }),
        data: $("#form-control-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not updated !", data.error, "error");
        } else {
            if (data.form) {
                $("#form-control-step").replaceWith($(data.form));
                $( "#control_step_object" ).change(function() {
                    var stepId = $(this).data('step-id');
                    updateControlStepFormAfterPageOrObjectSelection(stepId);
                });
                $( "#control_step_action" ).change(function() {
                    var stepId = $(this).data('step-id');
                    updateControlStepFormAfterActionSelection(stepId);
                });
            }
        }
    });
}

function saveControlStep(stepId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_test_step_control_step_ajax', {
            'id': stepId
        }),
        data: $("#form-control-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not added !", data.error, "error");
        } else {
            showControlStepAndCloseControlStepForm(data, stepId);
        }
    });
}

function showControlStepAndCloseControlStepForm(data, stepId) {
    var id = data.id;
    var row = data.row;
    $(row).insertBefore($('#control-step-footer-' + stepId));
    triggerControlStepEventListeners(id);
    resetControlStepFormAndCloseModal();
    swal("Step added with success !", "", "success");
}

function updateControlStep(id) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_update_application_test_step_control_step_ajax', {
            'id': id
        }),
        data: $("#form-control-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not added !", data.error, "error");
        } else {
            updateControlStepAndCloseControlStepForm(data, id);
        }
    });
}

function updateControlStepAndCloseControlStepForm(data) {
    var id = data.id;
    var $row = $(data.row);
    $("#control-step-row-" + id).replaceWith($row);
    triggerControlStepEventListeners(id);
    resetControlStepFormAndCloseModal();
    swal("Step updated with success !", "", "success");
}

function deleteControlStep(id, order, stepId, stepOrder) {  
    swal({
        title: "Delete verification step #" + order + " of step #" + stepOrder + " ?",
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
                swal("Verification step #" + order + " of step #" + stepOrder + " not deleted !", data.error, "error");
            } else {
                $('#control-step-row-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    $(this).remove();
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