var TestSetFolderManager = {
    init: function() {
        $("#modal-add-test-set-folder").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-test-set-folder").click(function() {
            $("#modal-add-test-set-folder").modal('show');
        });
        $("#save-test-set-folder").click(function() {
            var applicationId = $(this).data('application-id');
            TestSetFolderManager.save(applicationId);
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
            Routing.generate('app_application_test_set_folder_update_name_ajax', {
                'id': id
            })
        );
        $('#entity-description').editable('option', 'pk', id);
        $('#entity-description').editable(
            'option',
            'url',
            Routing.generate('app_application_test_set_folder_update_description_ajax', {
                'id': id
            })
        );
        $('#entity-name').editable('setValue', name, false);
        $('#entity-description').editable('setValue', description, false);
        $('#entity-creation-date').html(createdAt);
    },
    resetAddForm: function() {
        $("#form-add-test-set-folder")[0].reset();
    },
    closeAddFormModal: function() {
        $("#modal-add-test-set-folder").modal('hide');
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
        var testSetPlannerTreeCssSelector = TestSetPlanner.getTreeCssSelector(applicationId);
        var selectedFolder = $(testSetPlannerTreeCssSelector).treeview('getSelected');
        var parentFolderId = TestSetFolderManager.getParentFolderId(selectedFolder);
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_test_set_folder_ajax', {
                'id': applicationId,
                'parentId': parentFolderId
            }),
            data: $("#form-add-test-set-folder").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Feature folder not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var name = data.name;
                var foldersCount = data.testSetFoldersCount;
                TestSetPlanner.hideTree(applicationId);
                TestSetPlanner.tree(applicationId, testSetPlannerTreeCssSelector, data.treeTestSets, true);
                TreeManager.collapse(testSetPlannerTreeCssSelector);
                var selectedNode = $(testSetPlannerTreeCssSelector).treeview("getSelected")[0];
                TreeManager.revealNode(testSetPlannerTreeCssSelector, selectedNode);
                TreeManager.expandNode(testSetPlannerTreeCssSelector, selectedNode);
                TestSetPlanner.showTree(applicationId);
                TestSetPlanner.showEntityProperties(selectedNode);
                TestSetPlanner.refreshTestSetPlannerFolderSummary(foldersCount);
                TestSetFolderManager.resetAddForm();
                TestSetFolderManager.closeAddFormModal();
                var message = name + " added !";
                Base.showSuccessMessage(message);
            }
        });
    },
    showProperties: function(id) {
        TestSetPlanner.hideEntityActions();
        $('#entity-icon').removeClass().addClass("fontello-icon-folder");
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_test_set_folder_ajax', {
                'id': id
            })
        }).done(function(data) {
            TestSetPlanner.hideEntityPropertiesLoader();
            var folder = data.testSetFolder;
            var chart = folder.chart;
            TestSetFolderManager.updateEditableData(folder);
            TestSetPlanner.refreshChart(chart);
            TestSetManager.setAddButtonDataAttributes(id, folder.name, folder.description);
        });
    }
};