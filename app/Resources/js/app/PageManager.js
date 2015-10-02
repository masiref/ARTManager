var PageManager = {
    init: function() {
        $("#modal-add-page").modal({
            backdrop: 'static',
            show: false
        });
        $("[id^=add-page-]").click(function() {
            var objectMapId = $(this).data('object-map-id');
            var objectMapName = $(this).data('object-map-name');
            var objectMapDescription = $(this).data('object-map-description');
            PageManager.openAddFormModal(objectMapId, objectMapName, objectMapDescription);
        });
        $("#save-page").click(function() {
            var objectMapId = $(this).data('object-map-id');
            var objectMapName = $(this).data('object-map-name');
            PageManager.save(objectMapId, objectMapName);
        });
    },
    initEditableData: function(objectMapId) {
        $("#page-type").editable({
            emptytext: 'Select a type',
            success: function(response, newValue) {
                ObjectMapManager.hideTree(objectMapId);
                ObjectMapManager.refreshTreeWithSelectedPage(response.pageId);
                ObjectMapManager.refreshObjectTypeIcon(response.pageTypeIcon);
                $('#add-object').data('page-name', newValue);
            }
        });
        $("#page-path").editable({
            emptytext: 'Add path',
            defaultValue: ''
        });
    },
    updateEditableData: function(page) {
        var id = page.id;
        var name = page.name;
        var description = page.description;
        var type = page.pageType;
        var path = page.path;
        $('#object-icon').removeClass().addClass(type.icon);
        $('#object-name').editable('option', 'pk', id);
        $('#object-name').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_update_page_name_ajax', {
                'id': id
            })
        );
        $('#object-description').editable('option', 'pk', id);
        $('#object-description').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_update_page_description_ajax', {
                'id': id
            })
        );
        $('#page-type').editable('option', 'pk', id);
        $('#page-type').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_update_page_type_ajax', {
                'id': id
            })
        );
        $('#page-path').editable('option', 'pk', id);
        $('#page-path').editable(
            'option',
            'url',
            Routing.generate('app_application_object_map_update_page_path_ajax', {
                'id': id
            })
        );
        $('#object-name').editable('setValue', name, false);
        $('#object-description').editable('setValue', description, false);
        $('#page-type').editable('setValue', type.id, false);
        $('#page-path').editable('setValue', path, false);
    },
    resetAddForm: function() {
        $("#form-add-page")[0].reset();
    },
    openAddFormModal: function(objectMapId, objectMapName, objectMapDescription) {
        $('#new-page-object-map-name').html(objectMapName);
        $('#save-page').data('object-map-id', objectMapId);
        $('#save-page').data('object-map-name', objectMapName);
        $('#new-page-object-map-description').html(objectMapDescription);
        $('#save-page').data('object-map-description', objectMapDescription);
        $("#modal-add-page").modal('show');
    },
    closeAddFormModal: function() {
        $("#modal-add-page").modal('hide');
    },
    getParentPageId: function(selectedPage) {
        var parentPageId = -1;
        if (selectedPage.length === 1) {
            var parentPage = selectedPage[0];
            var href = parentPage.href;
            var id = href.substring(href.lastIndexOf("-") + 1);
            var type = href.substring(href.indexOf("-") + 1, href.lastIndexOf("-"));
            if (type === "page") {
                parentPageId = id;
            }
        }
        return parentPageId;
    },
    save: function(objectMapId, objectMapName) {
        var objectMapTreeCssSelector = ObjectMapManager.getTreeCssSelector(objectMapId);
        var selectedPage = $(objectMapTreeCssSelector).treeview('getSelected');
        var parentPageId = PageManager.getParentPageId(selectedPage);
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_object_map_page_ajax', {
                'id': objectMapId,
                'parentId': parentPageId
            }),
            data: $("#form-add-page").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Page not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var name = data.name;
                var pagesCount = data.pagesCount;
                ObjectMapManager.hideTree(objectMapId);
                ObjectMapManager.tree(objectMapId, objectMapTreeCssSelector, data.treeObjectMap, true);
                TreeManager.collapse(objectMapTreeCssSelector);
                var selectedNode = $(objectMapTreeCssSelector).treeview("getSelected")[0];
                TreeManager.revealNode(objectMapTreeCssSelector, selectedNode);
                TreeManager.expandNode(objectMapTreeCssSelector, selectedNode);
                ObjectMapManager.showTree(objectMapId);
                ObjectMapManager.showObjectProperties(selectedNode);
                ObjectMapManager.refreshObjectMapPageSummary(objectMapId, pagesCount);
                PageManager.resetAddForm();
                PageManager.closeAddFormModal();
                var message = name + " added to " + objectMapName + " !";
                Base.showSuccessMessage(message);
            }
        });
    },
    setAddButtonDataAttributes: function(objectMapId, objectMapName, objectMapDescription) {
        $('#add-page').data('object-map-id', objectMapId);
        $('#add-page').data('object-map-name', objectMapName);
        $('#add-page').data('object-map-description', objectMapDescription);
    },
    unsetAddButtonDataAttributes: function() {
        $('#add-page').removeData('object-map-id');
        $('#add-page').removeData('object-map-name');
        $('#add-page').removeData('object-map-description');
    },
    showTypeBlock: function() {
        $('#page-type-block').show();
    },
    hideTypeBlock: function() {
        $('#page-type-block').hide();
    },
    showPathBlock: function() {
        $('#page-path-block').show();
    },
    hidePathBlock: function() {
        $('#page-path-block').hide();
    },
    showProperties: function(id) {
        ObjectManager.hideTypeBlock();
        ObjectManager.hideIdentifierBlock();
        PageManager.showTypeBlock();
        PageManager.showPathBlock();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_object_map_page_ajax', {
                'id': id
            })
        }).done(function(data) {
            ObjectMapManager.hideObjectPropertiesLoader();
            var page = data.page;
            PageManager.updateEditableData(page);
            ObjectManager.setAddButtonDataAttributes(id, page.name, page.description);
        });
    }
};