var TestPlanner = {
    init: function() {
        $("#delete-checked-entities").click(function() {
            var applicationId = $(this).data('application-id');
            TestPlanner.deleteCheckedEntities(applicationId);
        });
    },
    initEditableData: function(applicationId) {
        $("#entity-name").editable({
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            },
            success: function(response, newValue) {
                TestPlanner.hideTree(applicationId);
                switch(response.entityType) {
                    case "folder":
                        TestPlanner.refreshTreeWithSelectedFolder(response.testFolderId);
                        $('#add-test').data('test-folder-name', newValue);
                        break;
                    case "test":
                        TestPlanner.refreshTreeWithSelectedTest(response.testId);
                        break;
                }
            }
        });
        $("#entity-description").editable({
            emptytext: 'Add description',
            defaultValue: '',
            success: function(response, newValue) {
                TestPlanner.hideTree(applicationId);
                switch(response.entityType) {
                    case "folder":
                        TestPlanner.refreshTreeWithSelectedFolder(response.testFolderId);
                        $('#add-test').data('test-folder-description', newValue);
                        break;
                    case "test":
                        TestPlanner.refreshTreeWithSelectedTest(response.testId);
                        break;
                }
            }
        });
    },
    refresh: function(applicationId, collapse, checkbox) { 
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_tests_tree_ajax', {
                'id': applicationId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                TestPlanner.refreshTree(applicationId, "#" + treeCssSelector, treeData, collapse, checkbox);
            });
        });
    },
    refreshTestPlannerFolderSummary: function(count) {
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' folder' + (count > 1 ? "s" : "");
        $('#test-folders-count').html(subtitle);
    },
    refreshTestPlannerTestSummary: function(count) {
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' scenario' + (count > 1 ? "s" : "");
        $('#tests-count').html(subtitle);
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
            TestPlanner.showEntityPropertiesPanel();
            TestPlanner.showEntityPropertiesLoader();
            var href = node.href;
            var id = TreeManager.getIdFromNodeHref(href);
            var type = TreeManager.getTypeFromNodeHref(href);
            $('#entity-icon').removeClass();
            switch(type) {
                case "folder":
                    TestFolderManager.showProperties(id);
                    break;
                case "test":
                    TestManager.showProperties(id);
                    break;
                default:
                    $('#entity-icon').removeClass().addClass("fontello-icon-puzzle");
            }
        } else {
            TestPlanner.hideEntityPropertiesPanel();
        }
    },
    refreshEntityTypeIcon: function(icon) {
        $('#object-icon').removeClass().addClass(icon);
    },
    refreshCheckedEntityCount: function(applicationId) {
        var treeCssSelector = TestPlanner.getTreeCssSelector(applicationId);
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
        var treeCssSelector = TestPlanner.getTreeCssSelector(applicationId);
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
                url: Routing.generate('app_application_test_entities_delete_ajax', {
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
                    var foldersCount = data.testFoldersCount;
                    var testsCount = data.testsCount;
                    TestPlanner.hideTree(applicationId);
                    TestPlanner.hideEntityPropertiesPanel();
                    TestManager.unsetAddButtonDataAttributes();
                    TestPlanner.clearEntityActions();
                    TestPlanner.tree(applicationId, treeCssSelector, data.treeTests, true);
                    TreeManager.collapse(treeCssSelector);
                    TestPlanner.showTree(applicationId);
                    TestPlanner.refreshTestPlannerFolderSummary(foldersCount);
                    TestPlanner.refreshTestPlannerTestSummary(testsCount);
                    TestPlanner.refreshCheckedEntityCount(applicationId);
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
                TestPlanner.showEntityProperties(node);
            },
            onNodeUnselected: function(event, node) {
                TestPlanner.hideEntityPropertiesPanel();
                TestManager.unsetAddButtonDataAttributes();
                TestPlanner.clearEntityActions();
            },
            onNodeChecked: function(event, node) {
                TreeManager.checkChildNodes(treeCssSelector, node);
                TestPlanner.refreshCheckedEntityCount(applicationId);
            },
            onNodeUnchecked: function(event, node) {
                TreeManager.uncheckChildNodes(treeCssSelector, node);
                TreeManager.uncheckParentNode(treeCssSelector, node);
                TestPlanner.refreshCheckedEntityCount(applicationId);
            }             
        });
    },
    refreshTree: function(id, treeCssSelector, data, collapse, checkbox) {
        TestPlanner.tree(id, treeCssSelector, data, checkbox);
        if (collapse) {
            TreeManager.collapse(treeCssSelector);
        }
        var selectedNode = $(treeCssSelector).treeview("getSelected")[0];
        TreeManager.revealNode(treeCssSelector, selectedNode);
        TestPlanner.showTree(id);
    },
    refreshTreeWithSelectedFolder: function(folderId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_tests_tree_with_selected_folder_ajax', {
                'id': folderId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                TestPlanner.refreshTree(id, "#" + treeCssSelector, treeData, true, true);
            });
        });
    },
    refreshTreeWithSelectedTest: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_tests_tree_with_selected_test_ajax', {
                'id': testId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                TestPlanner.refreshTree(id, "#" + treeCssSelector, treeData, true, true);
            });
        });
    },
    getTreeCssSelector: function(id) {
        return "#tree-tests-" + id;
    },
    showTreeLoader: function(id) {
        $(TestPlanner.getTreeCssSelector(id) + "-loader").addClass("three-quarters-loader");
    },
    hideTreeLoader: function(id) {
        $(TestPlanner.getTreeCssSelector(id) + "-loader").removeClass("three-quarters-loader");
    },
    showTree: function(id) {
        var treeCssSelector = TestPlanner.getTreeCssSelector(id);
        TestPlanner.hideTreeLoader(id);
        $(treeCssSelector).show();
    },
    hideTree: function(id) {
        var treeCssSelector = TestPlanner.getTreeCssSelector(id);
        TestPlanner.showTreeLoader(id);
        $(treeCssSelector).hide();
    }
};