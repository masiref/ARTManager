var BusinessStepExecuteStepManager = {
    init: function() {
        $("#modal-execute-step").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-execute-step").click(function() {
            var testId = $(this).data('test-id');
            var startingPageId = $(this).data('starting-page-id');
            if (startingPageId !== "") {
                BusinessStepExecuteStepManager.openAddFormModal(testId);
            } else {
                Base.showErrorMessage("Please select a starting page !")
            }
        });
        $("#save-execute-step").click(function() {
            var testId = $(this).data('test-id');
            if (testId !== '') {
                BusinessStepExecuteStepManager.save(testId);
            }
            var stepId = $(this).data('step-id');
            if (stepId !== '') {
                BusinessStepExecuteStepManager.update(stepId);
            }
        });
        $("[id^=delete-execute-step-]").click(function() {
            var id = $(this).data('id');
            var order = $(this).data('order');
            var testId = $(this).data('test-id');
            BusinessStepExecuteStepManager.delete(id, order, testId);
        });
        $("[id^=edit-execute-step-]").click(function() {
            var id = $(this).data('id');
            var testId = $(this).data('test-id');
            BusinessStepExecuteStepManager.openEditFormModal(id, testId);
        });
    },
    initItem: function(id) {
        $("#delete-execute-step-" + id).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var order = $(this).data('order');
            var testId = $(this).data('test-id');
            BusinessStepExecuteStepManager.delete(id, order, testId);
        }).tooltip();
        $("#add-control-step-step-" + id).click(function(event) {
            event.preventDefault();
            var stepId = $(this).data('step-id');
            ControlStepManager.openAddFormModal(stepId);
        }).tooltip();
        $("#edit-execute-step-" + id).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var testId = $(this).data('test-id');
            BusinessStepExecuteStepManager.openEditFormModal(id, testId);
        }).tooltip();
        $("#control-step-rows-" + id).filter("[id^=delete-control-step-]").click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var order = $(this).data('order');
            var stepId = $(this).data('step-id');
            var stepOrder = $(this).data('step-order');
            BusinessStepControlStepManager.delete(id, order, stepId, stepOrder);
        }).tooltip();
        $("#control-step-rows-" + id).filter("[id^=edit-control-step-]").click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var stepId = $(this).data('step-id');
            BusinessStepControlStepManager.openEditFormModal(id, stepId);
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
            url: Routing.generate('app_get_application_business_step_step_form_ajax', {
                'testId': testId
            })
        }).done(function(data) {
            $('#save-execute-step').data('step-id', '');
            $('#save-execute-step').data('test-id', testId);
            $("#modal-execute-step-body").html(data.form);
            $("#modal-execute-step-title").html(data.modalTitle);
            $("#modal-execute-step").modal('show');
            BusinessStepExecuteStepManager.triggerFormEventListeners();
        });
    },
    openEditFormModal: function(id, testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_business_step_step_form_ajax', {
                'id': id,
                'testId': testId
            })
        }).done(function(data) {
            $('#save-execute-step').data('test-id', '');
            $('#save-execute-step').data('step-id', id);
            $("#modal-execute-step-body").html(data.form);
            $("#modal-execute-step-title").html(data.modalTitle);
            $("#modal-execute-step").modal('show');
            BusinessStepExecuteStepManager.triggerFormEventListeners();
        });
    },
    closeFormModal: function() {
        $("#modal-execute-step").modal('hide');
    },
    add: function(id, row) {
        $(row).appendTo($('#step-rows'));
        BusinessStepExecuteStepManager.initItem(id);
    },
    remove: function(id, testId) {
        $('#step-row-' + id).css('visibility', 'hidden').animate({
            height: 0,
            width: 0
        }, 300, function () {
            $(this).remove();
            BusinessStepExecuteStepManager.refreshOrders(testId);
            //TestEditor.refreshBehatScenario(testId);
        });
    },
    refresh: function(id, row) {
        $("#step-row-" + id).replaceWith($(row));
        BusinessStepExecuteStepManager.initItem(id);
    },
    save: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_business_step_execute_step_ajax', {
                'id': testId
            }),
            data: $("#form-execute-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                BusinessStepExecuteStepManager.triggerFormEventListeners();
            } else {
                BusinessStepExecuteStepManager.add(data.id, data.row);
                BusinessStepExecuteStepManager.closeFormModal();
                BusinessStepExecuteStepManager.resetForm();
                //TestEditor.refreshBehatScenario(testId);
                Base.showSuccessMessage("Step added with success !");
            }
        });
    },
    update: function(id) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_update_application_business_step_execute_step_ajax', {
                'id': id
            }),
            data: $("#form-execute-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                BusinessStepExecuteStepManager.triggerFormEventListeners();
            } else {
                BusinessStepExecuteStepManager.refresh(data.id, data.row);
                BusinessStepExecuteStepManager.closeFormModal();
                BusinessStepExecuteStepManager.resetForm();
                //TestEditor.refreshBehatScenario(data.testId);
                Base.showSuccessMessage("Step updated with success !");
            }
        });
    },
    updateOrders: function(testId, steps) {
        $.ajax({
            url: Routing.generate('app_update_application_business_step_execute_step_orders_ajax'),
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
            //TestEditor.refreshBehatScenario(testId);
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
                url: Routing.generate('app_delete_application_business_step_execute_step_ajax', {
                    'id': id
                })
            }).done(function(data) {
                if (data.error) {
                    var message = "Step #" + order + " not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    BusinessStepExecuteStepManager.remove(id, testId);
                    Base.showSuccessMessage("Step deleted with success !");
                }
            });
        });
    },
    triggerFormEventListeners: function() {
        $("#execute_step_object").change(function() {
            var testId = $(this).data('test-id');
            BusinessStepExecuteStepManager.updateFormAfterObjectSelection(testId);
        });
        $("#execute_step_action").change(function() {
            var testId = $(this).data('test-id');
            BusinessStepExecuteStepManager.updateFormAfterActionSelection(testId);
        });
    },
    updateFormAfterObjectSelection: function(testId) {
        $("#execute_step_action").parent().remove();
        $("#execute_step_parameterDatas").parent().remove();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_check_application_business_step_execute_step_ajax', {
                'id': testId
            }),
            data: $("#form-execute-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                BusinessStepExecuteStepManager.triggerFormEventListeners();
            }
        });
    },
    updateFormAfterActionSelection: function(testId) {
        $("#execute_step_parameterDatas").parent().remove();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_check_application_business_step_execute_step_ajax', {
                'id': testId
            }),
            data: $("#form-execute-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-execute-step").replaceWith($(data.form));
                BusinessStepExecuteStepManager.triggerFormEventListeners();
            }
        });
    },
    refreshOrders: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_business_step_execute_step_orders_ajax', {
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