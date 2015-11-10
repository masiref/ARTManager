var BusinessStepPlanner = {
    init: function() {
        $("#delete-checked-entities").click(function() {
            var applicationId = $(this).data('application-id');
            BusinessStepPlanner.deleteCheckedEntities(applicationId);
        });
    },
    initEditableData: function(applicationId) {
        $("#entity-name").editable({
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            },
            success: function(response, newValue) {
                BusinessStepPlanner.hideTree(applicationId);
                switch(response.entityType) {
                    case "folder":
                        BusinessStepPlanner.refreshTreeWithSelectedFolder(response.businessStepFolderId);
                        $('#add-business-step').data('business-step-folder-name', newValue);
                        break;
                    case "business-step":
                        BusinessStepPlanner.refreshTreeWithSelectedBusinessStep(response.businessStepId);
                        break;
                }
            }
        });
        $("#entity-description").editable({
            emptytext: 'Add description',
            defaultValue: '',
            success: function(response, newValue) {
                BusinessStepPlanner.hideTree(applicationId);
                switch(response.entityType) {
                    case "folder":
                        BusinessStepPlanner.refreshTreeWithSelectedFolder(response.businessStepFolderId);
                        $('#add-business-step').data('business-step-folder-description', newValue);
                        break;
                    case "business-step":
                        BusinessStepPlanner.refreshTreeWithSelectedBusinessStep(response.businessStepId);
                        break;
                }
            }
        });
    },
    refresh: function(applicationId, collapse, checkbox) { 
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_business_steps_tree_ajax', {
                'id': applicationId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                BusinessStepPlanner.refreshTree(applicationId, "#" + treeCssSelector, treeData, collapse, checkbox);
            });
        });
    },
    refreshBusinessStepPlannerFolderSummary: function(count) {
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' folder' + (count > 1 ? "s" : "");
        $('#business-step-folders-count').html(subtitle);
    },
    refreshBusinessStepPlannerBusinessStepSummary: function(count) {
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' scenario' + (count > 1 ? "s" : "");
        $('#business-steps-count').html(subtitle);
    },
    showEntityPropertiesPanel: function() {
        $("#panel-body-entity-properties").show();
        $("#panel-footer-entity-properties").show();
    },
    hideEntityPropertiesPanel: function() {
        $("#panel-body-entity-properties").hide();
        $("#panel-footer-entity-properties").hide();
    },
    showEntityPropertiesLoader: function() {
        $("#entity-properties-loader").show();
    },
    hideEntityPropertiesLoader: function() {
        $("#entity-properties-loader").hide();
    },
    showEntityActions: function() {
        $("#panel-body-content-entity-actions").show();
    },
    hideEntityActions: function() {
        $("#panel-body-content-entity-actions").hide();
    },
    clearEntityActions: function() {
        $("#edit-entity").removeAttr("href");
    },
    showEntityProperties: function(node) {
        if (node) {
            BusinessStepPlanner.showEntityPropertiesPanel();
            BusinessStepPlanner.showEntityPropertiesLoader();
            var href = node.href;
            var id = TreeManager.getIdFromNodeHref(href);
            var type = TreeManager.getTypeFromNodeHref(href);
            $('#entity-icon').removeClass();
            switch(type) {
                case "folder":
                    BusinessStepFolderManager.showProperties(id);
                    break;
                case "business-step":
                    BusinessStepManager.showProperties(id);
                    break;
                default:
                    $('#entity-icon').removeClass().addClass("fontello-icon-puzzle");
            }
        } else {
            BusinessStepPlanner.hideEntityPropertiesPanel();
        }
    },
    refreshEntityTypeIcon: function(icon) {
        $('#object-icon').removeClass().addClass(icon);
    },
    refreshCheckedEntityCount: function(applicationId) {
        var treeCssSelector = BusinessStepPlanner.getTreeCssSelector(applicationId);
        var checkedEntities = $(treeCssSelector).treeview('getChecked');
        var checkedEntitiesCount = checkedEntities.length;
        $("#checked-entities-count").html(checkedEntitiesCount);
        if (checkedEntitiesCount > 0) {
            $("#delete-checked-entities").removeClass("disabled");
        } else {
            $("#delete-checked-entities").addClass("disabled");
        }
    },
    deleteCheckedEntities: function(applicationId) {
        var treeCssSelector = BusinessStepPlanner.getTreeCssSelector(applicationId);
        swal({
            title: "Delete selected entities ?",
            text: "You will not be able to recover them !",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete them !",
            closeOnConfirm: false
        }, function() {
            var objects = $(treeCssSelector).treeview('getChecked');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_application_business_step_entities_delete_ajax', {
                    id: applicationId
                }),
                data: {
                    objects: objects,
                    selectedNode: $(treeCssSelector).treeview("getSelected")[0]
                }
            }).done(function(data) {
                if (data.error) {
                    var message = "Selected entities were not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    var count = data.count;
                    var foldersCount = data.businessStepFoldersCount;
                    var businessStepsCount = data.businessStepsCount;
                    BusinessStepPlanner.hideTree(applicationId);
                    BusinessStepPlanner.hideEntityPropertiesPanel();
                    BusinessStepManager.unsetAddButtonDataAttributes();
                    BusinessStepPlanner.clearEntityActions();
                    BusinessStepPlanner.tree(applicationId, treeCssSelector, data.treeBusinessSteps, true);
                    TreeManager.collapse(treeCssSelector);
                    BusinessStepPlanner.showTree(applicationId);
                    BusinessStepPlanner.refreshBusinessStepPlannerFolderSummary(foldersCount);
                    BusinessStepPlanner.refreshBusinessStepPlannerBusinessStepSummary(businessStepsCount);
                    BusinessStepPlanner.refreshCheckedEntityCount(applicationId);
                    var message = "You have deleted " + count + " entit" + (count > 1 ? "ies" : "y");
                    Base.showSuccessMessage(message);
                }
            });
        });
    },
    tree: function(applicationId, treeCssSelector, data, checkbox) {
        $(treeCssSelector).treeview({
            data: data,
            showBorder: false,
            showCheckbox: checkbox,
            onNodeSelected: function(event, node) {
                BusinessStepPlanner.showEntityProperties(node);
            },
            onNodeUnselected: function(event, node) {
                BusinessStepPlanner.hideEntityPropertiesPanel();
                BusinessStepManager.unsetAddButtonDataAttributes();
                BusinessStepPlanner.clearEntityActions();
            },
            onNodeChecked: function(event, node) {
                TreeManager.checkChildNodes(treeCssSelector, node);
                BusinessStepPlanner.refreshCheckedEntityCount(applicationId);
            },
            onNodeUnchecked: function(event, node) {
                TreeManager.uncheckChildNodes(treeCssSelector, node);
                TreeManager.uncheckParentNode(treeCssSelector, node);
                BusinessStepPlanner.refreshCheckedEntityCount(applicationId);
            }             
        });
    },
    refreshTree: function(id, treeCssSelector, data, collapse, checkbox) {
        BusinessStepPlanner.tree(id, treeCssSelector, data, checkbox);
        if (collapse) {
            TreeManager.collapse(treeCssSelector);
        }
        var selectedNode = $(treeCssSelector).treeview("getSelected")[0];
        TreeManager.revealNode(treeCssSelector, selectedNode);
        BusinessStepPlanner.showTree(id);
    },
    refreshTreeWithSelectedFolder: function(folderId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_business_steps_tree_with_selected_folder_ajax', {
                'id': folderId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                BusinessStepPlanner.refreshTree(id, "#" + treeCssSelector, treeData, true, true);
            });
        });
    },
    refreshTreeWithSelectedBusinessStep: function(businessStepId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_business_steps_tree_with_selected_business_step_ajax', {
                'id': businessStepId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                BusinessStepPlanner.refreshTree(id, "#" + treeCssSelector, treeData, true, true);
            });
        });
    },
    getTreeCssSelector: function(id) {
        return "#tree-business-steps-" + id;
    },
    showTreeLoader: function(id) {
        $(BusinessStepPlanner.getTreeCssSelector(id) + "-loader").addClass("three-quarters-loader");
    },
    hideTreeLoader: function(id) {
        $(BusinessStepPlanner.getTreeCssSelector(id) + "-loader").removeClass("three-quarters-loader");
    },
    showTree: function(id) {
        var treeCssSelector = BusinessStepPlanner.getTreeCssSelector(id);
        BusinessStepPlanner.hideTreeLoader(id);
        $(treeCssSelector).show();
    },
    hideTree: function(id) {
        var treeCssSelector = BusinessStepPlanner.getTreeCssSelector(id);
        BusinessStepPlanner.showTreeLoader(id);
        $(treeCssSelector).hide();
    }
};