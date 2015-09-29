var TestFolderManager = {
    init: function() {
        $("#modal-add-test-folder").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-test-folder").click(function() {
            $("#modal-add-test-folder").modal('show');
        });
        $("#save-test-folder").click(function() {
            var applicationId = $(this).data('application-id');
            TestFolderManager.save(applicationId);
        });
    },
    updateEditableData: function(folder) {
        var id = folder.id;
        var name = folder.name;
        var description = folder.description;
        var createdAt = folder.createdAt;
        $('#entity-name').editable('option', 'pk', id);
        $('#entity-name').editable(
            'option',
            'url',
            Routing.generate('app_application_test_folder_update_name_ajax', {
                'id': id
            })
        );
        $('#entity-description').editable('option', 'pk', id);
        $('#entity-description').editable(
            'option',
            'url',
            Routing.generate('app_application_test_folder_update_description_ajax', {
                'id': id
            })
        );
        $('#entity-name').editable('setValue', name, false);
        $('#entity-description').editable('setValue', description, false);
        $('#entity-creation-date').html(createdAt);  
    },
    resetAddForm: function() {
        $("#form-add-test-folder")[0].reset();
    },
    closeAddFormModal: function() {
        $("#modal-add-test-folder").modal('hide');
    },
    getParentFolderId: function(selectedFolder) {
        var parentFolderId = -1;
        if (selectedFolder.length === 1) {
            var parentFolder = selectedFolder[0];
            var href = parentFolder.href;
            var id = href.substring(href.lastIndexOf("-") + 1);
            var type = href.substring(href.indexOf("-") + 1, href.lastIndexOf("-"));
            if (type === "folder") {
                parentFolderId = id;
            }
        }
        return parentFolderId;
    },
    save: function(applicationId) {
        var testPlannerTreeCssSelector = TestPlanner.getTreeCssSelector(applicationId);
        var selectedFolder = $(testPlannerTreeCssSelector).treeview('getSelected');
        var parentFolderId = TestFolderManager.getParentFolderId(selectedFolder);
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_test_folder_ajax', {
                'id': applicationId,
                'parentId': parentFolderId
            }),
            data: $("#form-add-test-folder").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Scenario folder not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var name = data.name;
                var foldersCount = data.testFoldersCount;
                TestPlanner.hideTree(applicationId);
                TestPlanner.tree(applicationId, testPlannerTreeCssSelector, data.treeTests, true);
                TreeManager.collapse(testPlannerTreeCssSelector);
                var selectedNode = $(testPlannerTreeCssSelector).treeview("getSelected")[0];
                TreeManager.revealNode(testPlannerTreeCssSelector, selectedNode);
                TreeManager.expandNode(testPlannerTreeCssSelector, selectedNode);
                TestPlanner.showTree(applicationId);
                TestPlanner.showEntityProperties(selectedNode);
                TestPlanner.refreshTestPlannerFolderSummary(foldersCount);
                TestFolderManager.resetAddForm();
                TestFolderManager.closeAddFormModal();
                var message = name + " added !";
                Base.showSuccessMessage(message);
            }
        });
    },
    showProperties: function(id) {
        TestPlanner.hideEntityActions();
        $('#entity-icon').removeClass().addClass("fontello-icon-folder");
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_test_folder_ajax', {
                'id': id
            })
        }).done(function(data) {
            TestPlanner.hideEntityPropertiesLoader();
            var folder = data.testFolder;
            TestFolderManager.updateEditableData(folder);
            TestManager.setAddButtonDataAttributes(id, folder.name, folder.description);
        });
    }
};