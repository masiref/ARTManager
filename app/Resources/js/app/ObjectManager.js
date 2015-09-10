var ObjectManager = {
    init: function() {
        $("#modal-add-object").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-object").click(function() {
            var pageId = $(this).data('page-id');
            var pageName = $(this).data('page-name');
            var pageDescription = $(this).data('page-description');
            ObjectManager.openAddFormModal(pageId, pageName, pageDescription);
        });
        $("#save-object").click(function() {
            var pageId = $(this).data('page-id');
            var pageName = $(this).data('page-name');
            ObjectManager.save(pageId, pageName);
        });
    },
    initEditableData: function(objectMapId) {
        $("#object-type").editable({
            emptytext: 'Select a type',
            success: function(response, newValue) {
                ObjectMapManager.hideTree(objectMapId);
                ObjectMapManager.refreshTreeWithSelectedObject(response.objectId);
                ObjectMapManager.refreshObjectTypeIcon(response.objectTypeIcon);
            }
        });
        $("#object-identifier-type").editable({
            emptytext: 'Select a type'
        });
        $("#object-identifier-value").editable({
            emptytext: 'Enter a value',
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            }
        });
    },
    updateEditableData: function(object) {
        var id = object.id;
        var name = object.name;
        var description = object.description;
        var type = object.objectType;
        var identifier = object.objectIdentifier;
        var identifierType = null;
        if (identifier !== null) {
            identifierType = identifier.objectIdentifierType;
        }
        $('#object-icon').removeClass().addClass(object.objectType.icon);
        $('#object-name').editable('option', 'pk', id);
        $('#object-name').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_page_update_object_name_ajax', {
                'id': id
            })
        );
        $('#object-description').editable('option', 'pk', id);
        $('#object-description').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_page_update_object_description_ajax', {
                'id': id
            })
        );
        $('#object-type').editable('option', 'pk', id);
        $('#object-type').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_page_update_object_type_ajax', {
                'id': id
            })
        );
        $('#object-identifier-type').editable('option', 'pk', id);
        $('#object-identifier-type').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_page_update_object_identifier_type_ajax', {
                'id': id
            })
        );
        $('#object-identifier-value').editable('option', 'pk', id);
        $('#object-identifier-value').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_page_update_object_identifier_value_ajax', {
                'id': id
            })
        );
        $('#object-name').editable('setValue', name, false);
        $('#object-description').editable('setValue', description, false);
        $('#object-type').editable('setValue', type.id, false);
        if (identifier !== null) {
            $('#object-identifier-value').editable('setValue', identifier.value, false);
            if (identifierType !== null) {
                $('#object-identifier-type').editable('setValue', identifierType.id, false);
            } else {
                $('#object-identifier-type').editable('setValue', '', false);
            }
        } else {
            $('#object-identifier-type').editable('setValue', '', false);
            $('#object-identifier-value').editable('setValue', '', false);
        }
    },
    resetAddForm: function() {
        $("#form-add-object")[0].reset();
    },
    openAddFormModal: function(pageId, pageName, pageDescription) {
        if (pageId) {
            $('#new-object-page-name').html(pageName);
            $('#new-object-page-description').html(pageDescription);
            $('#save-object').data('page-id', pageId);
            $('#save-object').data('page-name', pageName);
            $('#save-object').data('page-description', pageDescription);
            $("#modal-add-object").modal('show');
        } else {
            Base.showErrorMessage("Please select a page !");
        }
    },
    closeAddFormModal: function() {
        $("#modal-add-object").modal('hide');
    },
    save: function(pageId, pageName) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_object_map_page_object_ajax', {
                'id': pageId
            }),
            data: $("#form-add-object").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Object not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var name = data.name;
                var objectsCount = data.objectsCount;
                var objectMapId = data.objectMapId;
                var objectMapTreeCssSelector = ObjectMapManager.getTreeCssSelector(objectMapId);
                ObjectMapManager.hideTree(objectMapId);
                ObjectMapManager.tree(objectMapId, objectMapTreeCssSelector, data.treeObjectMap, true);
                TreeManager.collapse(objectMapTreeCssSelector);
                var selectedNode = $(objectMapTreeCssSelector).treeview("getSelected")[0];
                TreeManager.revealNode(objectMapTreeCssSelector, selectedNode);
                TreeManager.expandNode(objectMapTreeCssSelector, selectedNode);
                ObjectMapManager.showTree(objectMapId);
                ObjectMapManager.showObjectProperties(selectedNode);
                ObjectMapManager.refreshObjectMapObjectSummary(objectMapId, objectsCount);
                ObjectManager.resetAddForm();
                ObjectManager.closeAddFormModal();
                var message = name + " added to " + pageName + " !";
                Base.showSuccessMessage(message);
            }
        });
    },
    setAddButtonDataAttributes: function(pageId, pageName, pageDescription) {
        $('#add-object').data('page-id', pageId);
        $('#add-object').data('page-name', pageName);
        $('#add-object').data('page-description', pageDescription);
    },
    unsetAddButtonDataAttributes: function() {
        $('#add-object').removeData('page-id');
        $('#add-object').removeData('page-name');
        $('#add-object').removeData('page-description');
    },
    showTypeBlock: function() {
        $('#object-type-block').show();
    },
    hideTypeBlock: function() {
        $('#object-type-block').hide();
    },
    showIdentifierBlock: function() {
        $('#object-identifier-block').show();
    },
    hideIdentifierBlock: function() {
        $('#object-identifier-block').hide();
    },
    showProperties: function(id) {
        PageManager.hideTypeBlock();
        PageManager.hidePathBlock();
        ObjectManager.showTypeBlock();
        ObjectManager.showIdentifierBlock();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_object_map_page_object_ajax', {
                'id': id
            })
        }).done(function(data) {
            ObjectMapManager.hideObjectPropertiesLoader();
            var object = data.object;
            ObjectManager.updateEditableData(object);
        });
    }
};

