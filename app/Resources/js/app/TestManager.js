var TestManager = {
    init: function() {
        $( "#modal-add-test" ).modal({
            backdrop: 'static',
            show: false
        });
        $( "#add-test" ).click(function() {
            var folderId = $(this).data('test-folder-id');
            var folderName = $(this).data('test-folder-name');
            var folderDescription = $(this).data('test-folder-description');
            TestManager.openAddFormModal(folderId, folderName, folderDescription);
        });
        $( "#save-test" ).click(function() {
            var folderId = $(this).data('test-folder-id');
            var folderName = $(this).data('test-folder-name');
            TestManager.save(folderId, folderName);
        });
    },
    initItem: function(id) {
        $('#edit-entity').attr("href",
            Routing.generate('app_index_application_test_editor', {
                'id': id
            })
        );
    },
    updateEditableData: function(test) {
        var id = test.id;
        var name = test.name;
        var description = test.description;
        var createdAt = test.createdAt;
        $('#entity-name').editable('option', 'pk', id);
        $('#entity-name').editable(
            'option',
            'url',
            Routing.generate('app_application_test_folder_test_update_name_ajax', {
                'id': id
            })
        );
        $('#entity-description').editable('option', 'pk', id);
        $('#entity-description').editable(
            'option',
            'url',
            Routing.generate('app_application_test_folder_test_update_description_ajax', {
                'id': id
            })
        );
        $('#entity-name').editable('setValue', name, false);
        $('#entity-description').editable('setValue', description, false);
        $('#entity-creation-date').html(createdAt);
    },
    resetAddForm: function() {
        $("#form-add-test")[0].reset();
    },
    openAddFormModal: function(folderId, folderName, folderDescription)  {
        if (folderId) {
            $('#new-test-test-folder-name').html(folderName);
            $('#new-test-test-folder-description').html(folderDescription);
            $('#save-test').data('test-folder-id', folderId);
            $('#save-test').data('test-folder-name', folderName);
            $("#modal-add-test").modal('show');
        } else {
            Base.showErrorMessage("Please select a folder !");
        }
    },
    closeAddFormModal: function() {
        $("#modal-add-test").modal('hide');
    },
    save: function(folderId, folderName) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_test_folder_test_ajax', {
                'id': folderId
            }),
            data: $("#form-add-test").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Test not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var name = data.name;
                var testsCount = data.testsCount;
                var applicationId = data.applicationId;
                var testPlannerTreeCssSelector = TestPlanner.getTreeCssSelector(applicationId);
                TestPlanner.hideTree(applicationId);
                TestPlanner.tree(applicationId, testPlannerTreeCssSelector, data.treeTests, true);
                TreeManager.collapse(testPlannerTreeCssSelector);
                var selectedNode = $(testPlannerTreeCssSelector).treeview("getSelected")[0];
                TreeManager.revealNode(testPlannerTreeCssSelector, selectedNode);
                TreeManager.expandNode(testPlannerTreeCssSelector, selectedNode);
                TestPlanner.showTree(applicationId);
                TestPlanner.showEntityProperties(selectedNode);
                TestPlanner.refreshTestPlannerTestSummary(testsCount);
                TestManager.resetAddForm();
                TestManager.closeAddFormModal();
                var message = name + " added to " + folderName + " !";
                Base.showSuccessMessage(message);
            }
        });
    },
    setAddButtonDataAttributes: function(folderId, folderName, folderDescription) {
        $('#add-test').data('test-folder-id', folderId);
        $('#add-test').data('test-folder-name', folderName);
        $('#add-test').data('test-folder-description', folderDescription);
    },
    unsetAddButtonDataAttributes: function() {
        $('#add-test').removeData('test-folder-id');
        $('#add-test').removeData('test-folder-name');
        $('#add-test').removeData('test-folder-description');
    },
    showProperties: function(id) {
        TestPlanner.showEntityActions();
        $('#entity-icon').removeClass().addClass('fontello-icon-tasks');
        TestManager.initItem(id);
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_test_folder_test_ajax', {
                'id': id
            })
        }).done(function(data) {
            TestPlanner.hideEntityPropertiesLoader();
            var test = data.test;
            TestManager.updateEditableData(test);
        });
    }
};