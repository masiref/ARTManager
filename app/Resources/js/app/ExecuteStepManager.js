var ExecuteStepManager = {
    init: function() {
        $("#modal-execute-step").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-execute-step").click(function() {
            var testId = $(this).data('test-id');
            ExecuteStepManager.openAddFormModal(testId);
        });
        $("#save-execute-step").click(function() {
            var testId = $(this).data('test-id');
            if (testId !== '') {
                ExecuteStepManager.save(testId);
            }
            var stepId = $(this).data('step-id');
            if (stepId !== '') {
                ExecuteStepManager.update(stepId);
            }
        });
        $("[id^=delete-execute-step-]").click(function() {
            var id = $(this).data('id');
            var order = $(this).data('order');
            var testId = $(this).data('test-id');
            ExecuteStepManager.delete(id, order, testId);
        });
        $("[id^=edit-execute-step-]").click(function() {
            var id = $(this).data('id');
            var testId = $(this).data('test-id');
            ExecuteStepManager.openEditFormModal(id, testId);
        });
    },
    initItem: function(id) {
        $("#delete-execute-step-" + id ).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var order = $(this).data('order');
            var testId = $(this).data('test-id');
            ExecuteStepManager.delete(id, order, testId);
        }).tooltip();
        $("#add-control-step-step-" + id).click(function(event) {
            event.preventDefault();
            var stepId = $(this).data('step-id');
            ControlStepManager.openAddFormModal(stepId);
        }).tooltip();
        $("#edit-execute-step-" + id ).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var testId = $(this).data('test-id');
            ExecuteStepManager.openEditFormModal(id, testId);
        }).tooltip();
    },
    resetForm: function() {
        $("#execute_step_action").parent().remove();
        $("#execute_step_parameterDatas").parent().remove();
        $("#execute_step_object").val($("#execute_step_object option:first").val());
    },
    openAddFormModal: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_step_form_ajax', {
                'testId': testId
            })
        }).done(function(data) {
            $('#save-execute-step').data('step-id', '');
            $('#save-execute-step').data('test-id', testId);
            $("#modal-execute-step-body").html(data.form);
            $("#modal-execute-step-title").html(data.modalTitle);
            $("#modal-execute-step").modal('show');
            ExecuteStepManager.triggerFormEventListeners();
        });
    },
    openEditFormModal: function(id, testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_step_form_ajax', {
                'id': id,
                'testId': testId
            })
        }).done(function(data) {
            $('#save-execute-step').data('test-id', '');
            $('#save-execute-step').data('step-id', id);
            $("#modal-execute-step-body").html(data.form);
            $("#modal-execute-step-title").html(data.modalTitle);
            $("#modal-execute-step").modal('show');
            ExecuteStepManager.triggerFormEventListeners();
        });
    },
    closeFormModal: function() {
        $("#modal-execute-step").modal('hide');
    },
    add: function(id, row) {
        $(row).appendTo($('#step-rows'));
        ExecuteStepManager.initItem(id);
    },
    remove: function(id, testId) {
        $('#step-row-' + id).css('visibility', 'hidden').animate({
            height: 0,
            width: 0
        }, 300, function () {
            $(this).remove();
            ExecuteStepManager.refreshOrders(testId);
            TestEditor.refreshBehatScenario(testId);
        });
    },
    refresh: function(id, row) {
        $("#step-row-" + id).replaceWith($(row));
        ExecuteStepManager.initItem(id);
    },
    save: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_test_execute_step_ajax', {
                'id': testId
            }),
            data: $("#form-execute-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                ExecuteStepManager.triggerFormEventListeners();
            } else {
                ExecuteStepManager.add(data.id, data.row);
                ExecuteStepManager.closeFormModal();
                ExecuteStepManager.resetForm();
                TestEditor.refreshBehatScenario(testId);
                Base.showSuccessMessage("Step added with success !");
            }
        });
    },
    update: function(id) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_update_application_test_execute_step_ajax', {
                'id': id
            }),
            data: $("#form-execute-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                ExecuteStepManager.triggerFormEventListeners();
            } else {
                ExecuteStepManager.refresh(data.id, data.row);
                ExecuteStepManager.closeFormModal();
                ExecuteStepManager.resetForm();
                TestEditor.refreshBehatScenario(data.testId);
                Base.showSuccessMessage("Step updated with success !");
            }
        });
    },
    updateOrders: function(testId, steps) {
        $.ajax({
            url: Routing.generate('app_update_application_test_execute_step_orders_ajax'),
            method: 'POST',
            data: {
                'steps': steps
            }
        }).done(function(data) {
            jQuery.each(data.stepsAndOrders, function(id, order) {
                $("#step-order-" + id).html(order);
                $("#delete-execute-step-" + id).data("order", order);
                $("#step-right-" + id).find("[id^=delete-control-step-]").data("step-order", order);
            });
            TestEditor.refreshBehatScenario(testId);
        });
    },
    delete: function(id, order, testId) {
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
                    var message = "Step #" + order + " not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    ExecuteStepManager.remove(id, testId);
                    Base.showSuccessMessage("Step deleted with success !");
                }
            });
        });
    },
    triggerFormEventListeners: function() {
        $("#execute_step_object").change(function() {
            var testId = $(this).data('test-id');
            ExecuteStepManager.updateFormAfterObjectSelection(testId);
        });
        $("#execute_step_action").change(function() {
            var testId = $(this).data('test-id');
            ExecuteStepManager.updateFormAfterActionSelection(testId);
        });
    },
    updateFormAfterObjectSelection: function(testId) {
        $("#execute_step_action").parent().remove();
        $("#execute_step_parameterDatas").parent().remove();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_check_application_test_execute_step_ajax', {
                'id': testId
            }),
            data: $("#form-execute-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                ExecuteStepManager.triggerFormEventListeners();
            }
        });
    },
    updateFormAfterActionSelection: function(testId) {
        $("#execute_step_parameterDatas").parent().remove();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_check_application_test_execute_step_ajax', {
                'id': testId
            }),
            data: $("#form-execute-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                ExecuteStepManager.triggerFormEventListeners();
            }
        });
    },
    refreshOrders: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_execute_step_orders_ajax', {
                'id': testId
            })
        }).done(function(data) {
            jQuery.each(data, function(id, order) {
                $("#step-order-" + id).html(order);
                $("#delete-execute-step-" + id).data("order", order);
                $("#step-right-" + id).find("[id^=delete-control-step-]").data("step-order", order);
            });
        });
    }
};