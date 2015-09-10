var TestSetPlanner = {
    init: function() {
        $( "#delete-checked-entities").click(function() {
            var applicationId = $(this).data('application-id');
            TestSetPlanner.deleteCheckedEntities(applicationId);
        });
        $("#run-checked-entities").click(function() {
            TestSetManager.openMultipleRunFormModal();
        });
        $("#run-entity").click(function() {
            TestSetManager.openRunFormModal()
        });
    },
    initEditableData: function(applicationId) {
        $("#entity-name").editable({
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            },
            success: function(response, newValue) {
                TestSetPlanner.hideTree(applicationId);
                switch(response.entityType) {
                    case "folder":
                        TestSetPlanner.refreshTreeWithSelectedFolder(response.testSetFolderId);
                        $('#add-test-set').data('test-set-folder-name', newValue);
                        break;
                    case "test-set":
                        TestSetPlanner.refreshTreeWithSelectedTestSet(response.testSetId);
                        break;
                }
            }
        });
        $("#entity-description").editable({
            emptytext: 'Add description',
            defaultValue: '',
            success: function(response, newValue) {
                TestSetPlanner.hideTree(applicationId);
                switch(response.entityType) {
                    case "folder":
                        TestSetPlanner.refreshTreeWithSelectedFolder(response.testSetFolderId);
                        $('#add-test-set').data('test-set-folder-description', newValue);
                        break;
                    case "test-set":
                        TestSetPlanner.refreshTreeWithSelectedTestSet(response.testSetId);
                        break;
                }
            }
        });
    },
    refresh: function(applicationId, collapse, checkbox) { 
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_test_sets_tree_ajax', {
                'id': applicationId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                TestSetPlanner.refreshTree(applicationId, "#" + treeCssSelector, treeData, collapse, checkbox);
            });
        });
    },
    refreshTestSetPlannerFolderSummary: function(count) {
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' folder' + (count > 1 ? "s" : "");
        $('#test-set-folders-count').html(subtitle);
    },
    refreshTestSetPlannerTestSetSummary: function(count) {
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' test set' + (count > 1 ? "s" : "");
        $('#test-sets-count').html(subtitle);
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
            TestSetPlanner.showEntityPropertiesPanel();
            TestSetPlanner.showEntityPropertiesLoader();
            var href = node.href;
            var id = TreeManager.getIdFromNodeHref(href);
            var type = TreeManager.getTypeFromNodeHref(href);
            $('#entity-icon').removeClass();
            switch(type) {
                case "folder":
                    TestSetFolderManager.showProperties(id);
                    break;
                case "test-set":
                    TestSetManager.showProperties(id);
                    break;
                default:
                    $('#entity-icon').removeClass().addClass("fontello-icon-puzzle");
            }
        } else {
            TestSetPlanner.hideEntityPropertiesPanel();
        }
    },
    refreshEntityTypeIcon: function(icon) {
        $('#object-icon').removeClass().addClass(icon);
    },
    refreshCheckedEntityCount: function(applicationId) {
        var treeCssSelector = TestSetPlanner.getTreeCssSelector(applicationId);
        var checkedEntities = $(treeCssSelector).treeview('getChecked');
        var checkedEntitiesCount = checkedEntities.length;
        $("[id^=checked-entities-count-]").html(checkedEntitiesCount);
        if (checkedEntitiesCount > 0) {
            $("#delete-checked-entities").removeClass("disabled");
            $("#run-checked-entities").removeClass("disabled");
        } else {
            $("#delete-checked-entities").addClass("disabled");
            $("#run-checked-entities").addClass("disabled");
        }
    },
    deleteCheckedEntities: function(applicationId) {
        var treeCssSelector = TestSetPlanner.getTreeCssSelector(applicationId);
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
                url: Routing.generate('app_application_test_set_entities_delete_ajax', {
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
                    var foldersCount = data.testSetFoldersCount;
                    var testSetsCount = data.testSetsCount;
                    TestSetPlanner.hideTree(applicationId);
                    TestSetPlanner.hideEntityPropertiesPanel();
                    TestSetManager.unsetAddButtonDataAttributes();
                    TestSetPlanner.clearEntityActions();
                    TestSetPlanner.tree(applicationId, treeCssSelector, data.treeTestSets, true);
                    TreeManager.collapse(treeCssSelector);
                    TestSetPlanner.showTree(applicationId);
                    TestSetPlanner.refreshTestSetPlannerFolderSummary(foldersCount);
                    TestSetPlanner.refreshTestSetPlannerTestSetSummary(testSetsCount);
                    TestSetPlanner.refreshCheckedEntityCount(applicationId);
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
                TestSetPlanner.showEntityProperties(node);
            },
            onNodeUnselected: function(event, node) {
                TestSetPlanner.hideEntityPropertiesPanel();
                TestSetManager.unsetAddButtonDataAttributes();
                TestSetPlanner.clearEntityActions();
            },
            onNodeChecked: function(event, node) {
                TreeManager.checkChildNodes(treeCssSelector, node);
                TestSetPlanner.refreshCheckedEntityCount(applicationId);
            },
            onNodeUnchecked: function(event, node) {
                TreeManager.uncheckChildNodes(treeCssSelector, node);
                TreeManager.uncheckParentNode(treeCssSelector, node);
                TestSetPlanner.refreshCheckedEntityCount(applicationId);
            }             
        });
    },
    refreshTree: function(id, treeCssSelector, data, collapse, checkbox) {
        TestSetPlanner.tree(id, treeCssSelector, data, checkbox);
        if (collapse) {
            TreeManager.collapse(treeCssSelector);
        }
        var selectedNode = $(treeCssSelector).treeview("getSelected")[0];
        TreeManager.revealNode(treeCssSelector, selectedNode);
        TestSetPlanner.showTree(id);
    },
    refreshTreeWithSelectedFolder: function(folderId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_test_sets_tree_with_selected_folder_ajax', {
                'id': folderId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                TestSetPlanner.refreshTree(id, "#" + treeCssSelector, treeData, true, true);
            });
        });
    },
    refreshTreeWithSelectedTestSet: function(testSetId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_test_sets_tree_with_selected_test_set_ajax', {
                'id': testSetId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                TestSetPlanner.refreshTree(id, "#" + treeCssSelector, treeData, true, true);
            });
        });
    },
    getTreeCssSelector: function(id) {
        return "#tree-test-sets-" + id;
    },
    showTreeLoader: function(id) {
        $(TestSetPlanner.getTreeCssSelector(id) + "-loader").addClass("three-quarters-loader");
    },
    hideTreeLoader: function(id) {
        $(TestSetPlanner.getTreeCssSelector(id) + "-loader").removeClass("three-quarters-loader");
    },
    showTree: function(id) {
        var treeCssSelector = TestSetPlanner.getTreeCssSelector(id);
        TestSetPlanner.hideTreeLoader(id);
        $(treeCssSelector).show();
    },
    hideTree: function(id) {
        var treeCssSelector = TestSetPlanner.getTreeCssSelector(id);
        TestSetPlanner.showTreeLoader(id);
        $(treeCssSelector).hide();
    },
    refreshChart: function(data) {
        var chartContext = $("#entity-chart").get(0).getContext("2d");
        new Chart(chartContext).Doughnut(data, {
            scaleFontSize: 8,
            tooltipFontSize: 10,
            percentageInnerCutout : 70
        });
    }
};