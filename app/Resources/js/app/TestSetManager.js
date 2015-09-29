var TestSetManager = {
    init: function() {
        $("#modal-add-test-set").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-test-set").click(function() {
            var folderId = $(this).data('test-set-folder-id');
            var folderName = $(this).data('test-set-folder-name');
            var folderDescription = $(this).data('test-set-folder-description');
            TestSetManager.openAddFormModal(folderId, folderName, folderDescription);
        });
        $("#save-test-set").click(function() {
            var folderId = $(this).data('test-set-folder-id');
            var folderName = $(this).data('test-set-folder-name');
            TestSetManager.save(folderId, folderName);
        });
    },
    initItem: function(id) {
        $('#edit-entity').attr("href",
            Routing.generate('app_index_application_test_set_editor', {
                'id': id
            })
        );  
    },
    updateEditableData: function(testSet) {
        var id = testSet.id;
        var name = testSet.name;
        var description = testSet.description;
        var createdAt = testSet.createdAt;
        $('#entity-name').editable('option', 'pk', id);
        $('#entity-name').editable(
            'option',
            'url',
            Routing.generate('app_application_test_set_folder_test_set_update_name_ajax', {
                'id': id
            })
        );
        $('#entity-description').editable('option', 'pk', id);
        $('#entity-description').editable(
            'option',
            'url',
            Routing.generate('app_application_test_set_folder_test_set_update_description_ajax', {
                'id': id
            })
        );
        $('#entity-name').editable('setValue', name, false);
        $('#entity-description').editable('setValue', description, false);
        $('#entity-creation-date').html(createdAt);
    },
    resetAddForm: function() {
        $("#form-add-test-set")[0].reset();
    },
    openAddFormModal: function(folderId, folderName, folderDescription)  {
        if (folderId) {
            $('#new-test-set-test-set-folder-name').html(folderName);
            $('#new-test-set-test-set-folder-description').html(folderDescription);
            $('#save-test-set').data('test-set-folder-id', folderId);
            $('#save-test-set').data('test-set-folder-name', folderName);
            $("#modal-add-test-set").modal('show');
        } else {
            Base.showErrorMessage("Please select a folder !");
        }
    },
    closeAddFormModal: function() {
        $("#modal-add-test-set").modal('hide');
    },
    save: function(folderId, folderName) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_test_set_folder_test_set_ajax', {
                'id': folderId
            }),
            data: $("#form-add-test-set").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Feature not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var name = data.name;
                var testSetsCount = data.testSetsCount;
                var applicationId = data.applicationId;
                var testSetPlannerTreeCssSelector = TestSetPlanner.getTreeCssSelector(applicationId);
                TestSetPlanner.hideTree(applicationId);
                TestSetPlanner.tree(applicationId, testSetPlannerTreeCssSelector, data.treeTestSets, true);
                TreeManager.collapse(testSetPlannerTreeCssSelector);
                var selectedNode = $(testSetPlannerTreeCssSelector).treeview("getSelected")[0];
                TreeManager.revealNode(testSetPlannerTreeCssSelector, selectedNode);
                TreeManager.expandNode(testSetPlannerTreeCssSelector, selectedNode);
                TestSetPlanner.showTree(applicationId);
                TestSetPlanner.showEntityProperties(selectedNode);
                TestSetPlanner.refreshTestSetPlannerTestSetSummary(testSetsCount);
                TestSetManager.resetAddForm();
                TestSetManager.closeAddFormModal();
                var message = name + " added to " + folderName + " !";
                Base.showSuccessMessage(message);
            }
        });
    },
    setAddButtonDataAttributes: function(folderId, folderName, folderDescription) {
        $('#add-test-set').data('test-set-folder-id', folderId);
        $('#add-test-set').data('test-set-folder-name', folderName);
        $('#add-test-set').data('test-set-folder-description', folderDescription);
    },
    unsetAddButtonDataAttributes: function() {
        $('#add-test-set').removeData('test-set-folder-id');
        $('#add-test-set').removeData('test-set-folder-name');
        $('#add-test-set').removeData('test-set-folder-description');
    },
    showProperties: function(id) {
        TestSetPlanner.showEntityActions();
        $('#entity-icon').removeClass().addClass('fontello-icon-beaker');
        TestSetManager.initItem(id);
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_test_set_folder_test_set_ajax', {
                'id': id
            })
        }).done(function(data) {
            TestSetPlanner.hideEntityPropertiesLoader();
            var testSet = data.testSet;
            var chart = testSet.chart;
            TestSetManager.updateEditableData(testSet);
            TestSetPlanner.refreshChart(chart);
        });
    }
};