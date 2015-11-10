var BusinessStepControlStepManager = {
    init: function() {
        $("#modal-control-step").modal({
            backdrop: 'static',
            show: false
        });
        $("[id^=add-control-step-step-]").click(function() {
            var stepId = $(this).data('step-id');
            BusinessStepControlStepManager.openAddFormModal(stepId);
        });
        $("#save-control-step").click(function() {
            var parentStepId = $(this).data('step-id');
            if (parentStepId !== '') {
                BusinessStepControlStepManager.save(parentStepId);
            }
            var stepId = $(this).data('control-step-id');
            if (stepId !== '') {
                BusinessStepControlStepManager.update(stepId);
            }
        });
        $("[id^=delete-control-step-]").click(function() {
            var id = $(this).data('id');
            var order = $(this).data('order');
            var stepId = $(this).data('step-id');
            var stepOrder = $(this).data('step-order');
            BusinessStepControlStepManager.delete(id, order, stepId, stepOrder);
        });
        $("[id^=edit-control-step-]").click(function() {
            var id = $(this).data('id');
            var stepId = $(this).data('step-id');
            BusinessStepControlStepManager.openEditFormModal(id, stepId);
        });
    },
    initItem: function(id) {
        $("#delete-control-step-" + id).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var order = $(this).data('order');
            var stepId = $(this).data('step-id');
            var stepOrder = $(this).data('step-order');
            BusinessStepControlStepManager.delete(id, order, stepId, stepOrder);
        }).tooltip();

        $("#edit-control-step-" + id).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var stepId = $(this).data('step-id');
            BusinessStepControlStepManager.openEditFormModal(id, stepId);
        }).tooltip();
    },
    resetForm: function() {
        $("#form-control-step")[0].reset();
        $("#control_step_action").parent().remove();
        $("#control_step_parameterDatas").parent().remove();
        $("#control_step_object").val($("#execute_step_object option:first").val());
    },
    openAddFormModal: function(stepId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_business_step_step_control_step_form_ajax', {
                'stepId': stepId
            })
        }).done(function(data) {
            $('#save-control-step').data('control-step-id', '');
            $('#save-control-step').data('step-id', stepId);
            $("#modal-control-step-body").html(data.form);
            $("#modal-control-step-title").html(data.modalTitle);
            $("#modal-control-step").modal('show');
            BusinessStepControlStepManager.triggerFormEventListeners();
        });
    },
    openEditFormModal: function(id, stepId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_business_step_step_control_step_form_ajax', {
                'id': id,
                'stepId': stepId
            })
        }).done(function(data) {
            $('#save-control-step').data('control-step-id', id);
            $('#save-control-step').data('step-id', '');
            $("#modal-control-step-body").html(data.form);
            $("#modal-control-step-title").html(data.modalTitle);
            $("#modal-control-step").modal('show');
            BusinessStepControlStepManager.triggerFormEventListeners();
        });
    },
    closeFormModal: function() {
        $("#modal-control-step").modal('hide');
    },
    add: function(id, stepId, row) {
        $(row).appendTo($('#control-step-rows-' + stepId));
        $(row).sortable({
            connectWith: "#control-step-rows-" + stepId
        });
        BusinessStepControlStepManager.initItem(id);
    },
    remove: function(id, stepId, testId) {
        $('#control-step-row-' + id).css('visibility', 'hidden').animate({
            height: 0,
            width: 0
        }, 300, function () {
            $(this).remove();
            BusinessStepControlStepManager.refreshOrders(stepId);
            //TestEditor.refreshBehatScenario(testId);
        });
    },
    refresh: function(id, row) {
        $("#control-step-row-" + id).replaceWith($(row));
        BusinessStepControlStepManager.initItem(id);
    },
    save: function(stepId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_business_step_step_control_step_ajax', {
                'id': stepId
            }),
            data: $("#form-control-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-control-step").replaceWith($(data.form));
                BusinessStepControlStepManager.triggerFormEventListeners();
            } else {
                BusinessStepControlStepManager.add(data.id, stepId, data.row);
                BusinessStepControlStepManager.closeFormModal();
                BusinessStepControlStepManager.resetForm();
                //TestEditor.refreshBehatScenario(data.testId);
                Base.showSuccessMessage("Step added with success !");
            }
        });
    },
    update: function(id) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_update_application_business_step_step_control_step_ajax', {
                'id': id
            }),
            data: $("#form-control-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-control-step").replaceWith($(data.form));
                BusinessStepControlStepManager.triggerFormEventListeners();
            } else {
                BusinessStepControlStepManager.refresh(data.id, data.row);
                BusinessStepControlStepManager.closeFormModal();
                BusinessStepControlStepManager.resetForm();
                //TestEditor.refreshBehatScenario(data.testId);
                Base.showSuccessMessage("Step updated with success !");
            }
        });
    },
    updateOrders: function(testId, steps) {
        $.ajax({
            url: Routing.generate('app_update_application_business_step_step_control_step_orders_ajax'),
            method: 'POST',
            data: {
                'steps': steps
            }
        }).done(function(data) {
            jQuery.each(data.stepsAndOrders, function(id, order) {
                $("#control-step-order-" + id).html(order);
                $("#delete-control-step-" + id).data("order", order);
            });
            //TestEditor.refreshBehatScenario(testId);
        });
    },
    delete: function(id, order, stepId, stepOrder) {
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
                url: Routing.generate('app_delete_application_business_step_step_control_step_ajax', {
                    'id': id
                })
            }).done(function(data) {
                if (data.error) {
                    var message = "Verification step #" + order + " of step #" + stepOrder + " not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    BusinessStepControlStepManager.remove(id, stepId, data.testId);
                    Base.showSuccessMessage("Step deleted with success !");
                }
            });
        });
    },
    triggerFormEventListeners: function() {
        $("#control_step_page").change(function() {
            var stepId = $(this).data('step-id');
            BusinessStepControlStepManager.updateFormAfterPageObjectSelection(stepId);
        });
        $("#control_step_object").change(function() {
            var stepId = $(this).data('step-id');
            BusinessStepControlStepManager.updateFormAfterPageObjectSelection(stepId);
        });
        $("#control_step_action").change(function() {
            var stepId = $(this).data('step-id');
            BusinessStepControlStepManager.updateFormAfterActionSelection(stepId);
        });
    },
    updateFormAfterPageObjectSelection: function(stepId) {
        $("#control_step_action").parent().remove();
        $("#control_step_parameterDatas").parent().remove();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_check_application_business_step_step_control_step_ajax', {
                'id': stepId
            }),
            data: $("#form-control-step").serialize()
        }).done(function(data) {
            $("#form-control-step").replaceWith($(data.form));
            BusinessStepControlStepManager.triggerFormEventListeners();
        });
    },
    updateFormAfterActionSelection: function(stepId) {
        $("#control_step_parameterDatas").parent().remove();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_check_application_business_step_step_control_step_ajax', {
                'id': stepId
            }),
            data: $("#form-control-step").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-control-step").replaceWith($(data.form));
                BusinessStepControlStepManager.triggerFormEventListeners();
            }
        });
    },
    refreshOrders: function(stepId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_business_step_step_control_step_orders_ajax', {
                'id': stepId
            })
        }).done(function(data) {
            jQuery.each(data, function(id, order) {
                $("#control-step-order-" + id).html(order);
                $("#delete-control-step-" + id).data("order", order);
            });
        });
    }
};