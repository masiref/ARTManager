var TestSetEditor = {
    init: function() {
        $("#modal-run-test-set").modal({
            backdrop: 'static',
            show: false
        });
        $("#modal-run-test-sets").modal({
            backdrop: 'static',
            show: false
        });
        $("#modal-test-set-history").modal({
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
        $("#refresh-execution-grid").click(function() {
            var id = $(this).data('id');
            TestSetEditor.refreshExecutionGrid(id);
        });
        $("#show-test-set-history").click(function() {
            TestSetEditor.openRunHistoryModal();
        });
        $("[id^=details-test-set-run-]").click(function() {
            var id = $(this).data('id');
            var testSetId = $(this).data('test-set-id');
            TestSetEditor.highlightSelectedRunRow(id, testSetId);
            TestSetEditor.showSelectedRunExecutionGrid(id);
            TestSetEditor.showSelectedRunExecutionReport(id);
        });
    },
    initItem: function(id) {
        TestSetEditor.initExecutionGrid(id);
        $('#history-grid-' + id).dataTable({
            "searching": false,
            "paging": true,
            "info": false,
            "order": [],
            "columnDefs": [
                { "orderable": false, "targets": [-1, 0] }
            ]
        });
        $("[id^=details-test-set-run-]").click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var testSetId = $(this).data('test-set-id');
            TestSetEditor.highlightSelectedRunRow(id, testSetId);
            TestSetEditor.showSelectedRunExecutionGrid(id);
            TestSetEditor.showSelectedRunExecutionReport(id);
        }).tooltip();
    },
    initExecutionGrid: function(id) {
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
    openMultipleRunFormModal: function(applicationId) {
        var treeCssSelector = TestSetPlanner.getTreeCssSelector(applicationId);
        var objects = $(treeCssSelector).treeview('getChecked');
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_test_set_entities_run_ajax', {
                id: applicationId
            }),
            data: {
                objects: objects
            }
        }).done(function(data) {
            $("#modal-run-test-sets-body").html($(data.modalContent));
            $("#modal-run-test-sets").modal('show');
        });
    },
    closeMultipleRunFormModal: function() {
        $("#modal-run-test-sets").modal('hide');
    },
    openRunHistoryModal: function() {
        $("#modal-test-set-history").modal('show');
    },
    closeRunHistoryModal: function() {
        $("#modal-test-set-history").modal('hide');
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
                    TestSetEditor.updateExecutionGrid(data.executionGrid);
                    TestSetEditor.updateHistoryGrid(data.historyGrid);
                    TestSetEditor.initItem(testSetId);
                    Base.refreshSidebar(true);
                });
                TestSetEditor.resetRunForm();
                TestSetEditor.closeRunFormModal();
            }
        });
    },
    saveRuns: function(applicationId) {
        var treeCssSelector = TestSetPlanner.getTreeCssSelector(applicationId);
        var objects = $(treeCssSelector).treeview('getChecked');
        var data = $("#form-add-test-set-run").serializeArray();
        data.push({ name: "objects", value: JSON.stringify(objects) });
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_test_sets_run_ajax'),
            data: $.param(data)
        }).done(function(data) {
            if (data.error) {
                var message = "Runs not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                swal({
                    title: "Success",
                    text: "Your runs have been added to the queue. You can follow their execution in the sidebar.",
                    type: "success"
                }, function() {
                    Base.refreshSidebar(true);
                });
                TestSetEditor.closeMultipleRunFormModal();
            }
        });
    },
    updateExecutionGrid: function(grid) {
        $("#execution-grid").replaceWith($(grid));
        TestInstanceManager.initItems();
    },
    updateHistoryGrid: function(grid) {
        $("#history-grid-wrapper").html($(grid));
        TestSetEditor.hideSelectedRunExecutionGrid();
        TestSetEditor.hideSelectedRunExecutionReport();
    },
    refreshExecutionGrid: function(id) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_set_execution_grid_ajax', {
                'id': id
            })
        }).done(function(data) {
            TestSetEditor.updateExecutionGrid(data.executionGrid);
            TestSetEditor.updateHistoryGrid(data.historyGrid);
            TestSetEditor.initItem(id);
        });
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
    },
    showSelectedRunExecutionGrid: function(id) {
        TestSetEditor.hideSelectedRunExecutionGrid();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_set_run_execution_grid_ajax', {
                'id': id
            })
        }).done(function(data) {
            TestSetEditor.updateSelectedRunExecutionGrid(data.executionGrid);
            $("#selected-run-execution-grid").show();
        });
    },
    hideSelectedRunExecutionGrid: function() {
        $("#selected-run-execution-grid").hide();
    },
    updateSelectedRunExecutionGrid: function(grid) {
        $("#selected-run-execution-grid-table").html($(grid));
    },
    showSelectedRunExecutionReport: function(id) {
        TestSetEditor.hideSelectedRunExecutionReport();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_set_run_execution_report_ajax', {
                'id': id
            })
        }).done(function(data) {
            if (data.report && data.report !== "") {
                TestSetEditor.updateSelectedRunExecutionReport(data.report);
                $("#selected-run-execution-report").show();
            }
        });
    },
    hideSelectedRunExecutionReport: function() {
        $("#selected-run-execution-report").hide();
    },
    updateSelectedRunExecutionReport: function(report) {
        $("#selected-run-execution-report-content").html(report);
    },
    highlightSelectedRunRow: function(id, testSetId) {
        $('#history-grid-' + testSetId + ' tbody tr').removeClass("selected");
        $('#row-test-set-run-' + id).addClass("selected");
    }
};