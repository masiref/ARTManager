var TestInstanceManager = {
    init: function() {
        $("#modal-add-test-instance").modal({
            backdrop: 'static',
            show: false
        });
        $("#modal-details-test-instance").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-test-instance").click(function() {
            TestInstanceManager.openAddFormModal();
        });
        $("#save-test-instances").click(function() {
            var testSetId = $(this).data('test-set-id');
            var applicationId = $(this).data('application-id');
            TestInstanceManager.save(testSetId, applicationId);
        });
        $("[id^=delete-test-instance-]").click(function() {
            var id = $(this).data('id');
            var name = $(this).data('test-name');
            TestInstanceManager.delete(id, name);
        });
        $("[id^=details-test-instance-]").click(function() {
            var name = $(this).data('test-name');
            var description = $(this).data('test-description');
            TestInstanceManager.openDetailsFormModal(name, description);
        });
    },
    initItems: function() {
        $("[id^=delete-test-instance-]").click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('test-name');
            TestInstanceManager.delete(id, name);
        }).tooltip();
        $("[id^=details-test-instance-]").click(function(event) {
            event.preventDefault();
            var name = $(this).data('test-name');
            var description = $(this).data('test-description');
            TestInstanceManager.openDetailsFormModal(name, description);
        }).tooltip();
        $("#add-test-instance").click(function(event) {
            event.preventDefault();
            TestInstanceManager.openAddFormModal();
        }).tooltip();
    },
    openAddFormModal: function() {
        $("#modal-add-test-instance").modal('show');
    },
    closeAddFormModal: function() {
        $("#modal-add-test-instance").modal('hide');
    },
    openDetailsFormModal: function(name, description) {
        $("#test-instance-test-name").html(name);
        $("#test-instance-test-description").html(description);
        $("#modal-details-test-instance").modal('show');
    },
    save: function(testSetId, applicationId) {
        var testPlannerTreeCssSelector = TestPlanner.getTreeCssSelector(applicationId);
        var objects = $(testPlannerTreeCssSelector).treeview('getChecked');
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
                var message = "Selected test instances were not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var count = data.count;
                TestSetEditor.updateExecutionGrid(data.executionGrid);
                TestSetEditor.initExecutionGrid(testSetId);
                TestSetEditor.refreshBehatFeature(testSetId);
                TestInstanceManager.closeAddFormModal();
                var message = "You have added " + count + " test instance" + (count > 1 ? "s" : "");
                Base.showSuccessMessage(message);
            }
        });
    },
    delete: function(id, name) {
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
                    var message = "Test instance was not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    TestSetEditor.updateExecutionGrid(data.executionGrid);
                    TestSetEditor.initItem(data.testSetId);
                    TestSetEditor.refreshBehatFeature(data.testSetId);
                    Base.showSuccessMessage("Test instance deleted !");
                }
            });
        });
    }
};