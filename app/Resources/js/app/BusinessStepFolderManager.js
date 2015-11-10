var BusinessStepFolderManager = {
    init: function() {
        $("#modal-add-business-step-folder").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-business-step-folder").click(function() {
            $("#modal-add-business-step-folder").modal('show');
        });
        $("#save-business-step-folder").click(function() {
            var applicationId = $(this).data('application-id');
            BusinessStepFolderManager.save(applicationId);
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
            Routing.generate('app_application_business_step_folder_update_name_ajax', {
                'id': id
            })
        );
        $('#entity-description').editable('option', 'pk', id);
        $('#entity-description').editable(
            'option',
            'url',
            Routing.generate('app_application_business_step_folder_update_description_ajax', {
                'id': id
            })
        );
        $('#entity-name').editable('setValue', name, false);
        $('#entity-description').editable('setValue', description, false);
        $('#entity-creation-date').html(createdAt);  
    },
    resetAddForm: function() {
        $("#form-add-business-step-folder")[0].reset();
    },
    closeAddFormModal: function() {
        $("#modal-add-business-step-folder").modal('hide');
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
        var businessStepPlannerTreeCssSelector = BusinessStepPlanner.getTreeCssSelector(applicationId);
        var selectedFolder = $(businessStepPlannerTreeCssSelector).treeview('getSelected');
        var parentFolderId = BusinessStepFolderManager.getParentFolderId(selectedFolder);
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_business_step_folder_ajax', {
                'id': applicationId,
                'parentId': parentFolderId
            }),
            data: $("#form-add-business-step-folder").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Scenario folder not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var name = data.name;
                var foldersCount = data.businessStepFoldersCount;
                BusinessStepPlanner.hideTree(applicationId);
                BusinessStepPlanner.tree(applicationId, businessStepPlannerTreeCssSelector, data.treeBusinessSteps, true);
                TreeManager.collapse(businessStepPlannerTreeCssSelector);
                var selectedNode = $(businessStepPlannerTreeCssSelector).treeview("getSelected")[0];
                TreeManager.revealNode(businessStepPlannerTreeCssSelector, selectedNode);
                TreeManager.expandNode(businessStepPlannerTreeCssSelector, selectedNode);
                BusinessStepPlanner.showTree(applicationId);
                BusinessStepPlanner.showEntityProperties(selectedNode);
                BusinessStepPlanner.refreshBusinessStepPlannerFolderSummary(foldersCount);
                BusinessStepFolderManager.resetAddForm();
                BusinessStepFolderManager.closeAddFormModal();
                var message = name + " added !";
                Base.showSuccessMessage(message);
            }
        });
    },
    showProperties: function(id) {
        BusinessStepPlanner.hideEntityActions();
        $('#entity-icon').removeClass().addClass("fontello-icon-folder");
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_business_step_folder_ajax', {
                'id': id
            })
        }).done(function(data) {
            BusinessStepPlanner.hideEntityPropertiesLoader();
            var folder = data.businessStepFolder;
            BusinessStepFolderManager.updateEditableData(folder);
            BusinessStepManager.setAddButtonDataAttributes(id, folder.name, folder.description);
        });
    }
};