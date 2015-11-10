var BusinessStepManager = {
    init: function() {
        $("#modal-add-business-step").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-business-step").click(function() {
            var folderId = $(this).data('business-step-folder-id');
            var folderName = $(this).data('business-step-folder-name');
            var folderDescription = $(this).data('business-step-folder-description');
            BusinessStepManager.openAddFormModal(folderId, folderName, folderDescription);
        });
        $("#save-business-step").click(function() {
            var folderId = $(this).data('business-step-folder-id');
            var folderName = $(this).data('business-step-folder-name');
            BusinessStepManager.save(folderId, folderName);
        });
    },
    initItem: function(id) {
        $('#edit-entity').attr("href",
            Routing.generate('app_index_application_business_step_editor', {
                'id': id
            })
        );
    },
    updateEditableData: function(businessStep) {
        var id = businessStep.id;
        var name = businessStep.name;
        var description = businessStep.description;
        var createdAt = businessStep.createdAt;
        $('#entity-name').editable('option', 'pk', id);
        $('#entity-name').editable(
            'option',
            'url',
            Routing.generate('app_application_business_step_folder_business_step_update_name_ajax', {
                'id': id
            })
        );
        $('#entity-description').editable('option', 'pk', id);
        $('#entity-description').editable(
            'option',
            'url',
            Routing.generate('app_application_business_step_folder_business_step_update_description_ajax', {
                'id': id
            })
        );
        $('#entity-name').editable('setValue', name, false);
        $('#entity-description').editable('setValue', description, false);
        $('#entity-creation-date').html(createdAt);
    },
    resetAddForm: function() {
        $("#form-add-business-step")[0].reset();
    },
    openAddFormModal: function(folderId, folderName, folderDescription)  {
        if (folderId) {
            $('#new-business-step-business-step-folder-name').html(folderName);
            $('#new-business-step-business-step-folder-description').html(folderDescription);
            $('#save-business-step').data('business-step-folder-id', folderId);
            $('#save-business-step').data('business-step-folder-name', folderName);
            $("#modal-add-business-step").modal('show');
        } else {
            Base.showErrorMessage("Please select a folder !");
        }
    },
    closeAddFormModal: function() {
        $("#modal-add-business-step").modal('hide');
    },
    save: function(folderId, folderName) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_business_step_folder_business_step_ajax', {
                'id': folderId
            }),
            data: $("#form-add-business-step").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Business step not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var name = data.name;
                var businessStepsCount = data.businessStepsCount;
                var applicationId = data.applicationId;
                var businessStepPlannerTreeCssSelector = BusinessStepPlanner.getTreeCssSelector(applicationId);
                BusinessStepPlanner.hideTree(applicationId);
                BusinessStepPlanner.tree(applicationId, businessStepPlannerTreeCssSelector, data.treeBusinessSteps, true);
                TreeManager.collapse(businessStepPlannerTreeCssSelector);
                var selectedNode = $(businessStepPlannerTreeCssSelector).treeview("getSelected")[0];
                TreeManager.revealNode(businessStepPlannerTreeCssSelector, selectedNode);
                TreeManager.expandNode(businessStepPlannerTreeCssSelector, selectedNode);
                BusinessStepPlanner.showTree(applicationId);
                BusinessStepPlanner.showEntityProperties(selectedNode);
                BusinessStepPlanner.refreshBusinessStepPlannerBusinessStepSummary(businessStepsCount);
                BusinessStepManager.resetAddForm();
                BusinessStepManager.closeAddFormModal();
                var message = name + " added to " + folderName + " !";
                Base.showSuccessMessage(message);
            }
        });
    },
    setAddButtonDataAttributes: function(folderId, folderName, folderDescription) {
        $('#add-business-step').data('business-step-folder-id', folderId);
        $('#add-business-step').data('business-step-folder-name', folderName);
        $('#add-business-step').data('business-step-folder-description', folderDescription);
    },
    unsetAddButtonDataAttributes: function() {
        $('#add-business-step').removeData('business-step-folder-id');
        $('#add-business-step').removeData('business-step-folder-name');
        $('#add-business-step').removeData('business-step-folder-description');
    },
    showProperties: function(id) {
        BusinessStepPlanner.showEntityActions();
        $('#entity-icon').removeClass().addClass('fontello-icon-level-up');
        BusinessStepManager.initItem(id);
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_business_step_folder_business_step_ajax', {
                'id': id
            })
        }).done(function(data) {
            BusinessStepPlanner.hideEntityPropertiesLoader();
            var businessStep = data.businessStep;
            BusinessStepManager.updateEditableData(businessStep);
        });
    }
};