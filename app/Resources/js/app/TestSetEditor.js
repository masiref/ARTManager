var TestSetEditor = {
    init: function() {
        $("#modal-run-test-set").modal({
            backdrop: 'static',
            show: false
        });
        $("#run-test-set").click(function() {
            TestSetEditor.openRunFormModal();
        });
        $("#save-test-set-run").click(function() {
            var testSetId = $(this).data('test-set-id');
            TestSetEditor.saveRun(testSetId);
        });
        $("#execution-grid-collapse").on('shown.bs.collapse', function() {
            $(".execution-grid-collapse-toggle-icon").removeClass("fontello-icon-down-open")
                    .addClass("fontello-icon-up-open");
        });
        $("#execution-grid-collapse").on('hidden.bs.collapse', function() {
            $(".execution-grid-collapse-toggle-icon").removeClass("fontello-icon-up-open")
                    .addClass("fontello-icon-down-open");
        });
    },
    initItem: function(id) {
        $('#execution-grid-' + id).dataTable({
            "searching": false,
            "paging": false,
            "info": false
        }).rowReordering({
            sURL: Routing.generate('app_update_application_test_set_test_instance_orders_ajax'),
            sRequestType: "POST",
            callback: function() {
                TestSetEditor.refreshBehatFeature(id);
            }
        });
    },
    resetRunForm: function() {
        $("#form-add-test-set-run")[0].reset();
    },
    openRunFormModal: function() {
        $("#modal-run-test-set").modal('show');
    },
    closeRunFormModal: function() {
        $("#modal-run-test-set").modal('hide');
    },
    saveRun: function(testSetId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_test_set_run_ajax', {
                'id': testSetId
            }),
            data: $("#form-add-test-set-run").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Run not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                swal({
                    title: "Success",
                    text: "Your run has been added to the queue. You can follow its execution in the sidebar.",
                    type: "success"
                }, function() {
                    TestSetEditor.refreshExecutionGrid(data.executionGrid);
                    Base.refreshSidebar(true);
                });
                TestSetEditor.resetRunForm();
                TestSetEditor.closeRunFormModal();
            }
        });
    },
    refreshExecutionGrid: function(grid) {
        $("#execution-grid").replaceWith($(grid));
        TestInstanceManager.initItems();
    },
    refreshBehatFeature: function(id) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_set_behat_feature_ajax', {
                'id': id
            })
        }).done(function(data) {
            $("#behat-feature").html(data.feature);
        });
    }
};