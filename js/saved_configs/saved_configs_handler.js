$(function () {
    'use strict';

    var configs = {};

    var CONFIGS_SELECT = '.js-config-select';
    var SHOW_CHECKBOX = '.js-show-checkbox';
    var DELETE_CONFIG_BTN = '.js-config-deletebtn';
    var UPDATE_CONFIG_BTN = '.js-config-updatebtn';
    var REAPPLY_CONFIG_BTN = '.js-config-reapplybtn';

    var createOptionTag = function (id, name) {
        return '<option value="' + id + '">' + name + '</option>';
    };

    var updateActionButtonsVisibility = function (selectedItemValue) {
        var actionButtons = $(UPDATE_CONFIG_BTN + ', ' + DELETE_CONFIG_BTN + ', ' + REAPPLY_CONFIG_BTN);
        if (selectedItemValue === 'create' || selectedItemValue === 'blank') {
            actionButtons.hide();
        } else {
            actionButtons.show();
        }
    };

    var selectItemInDropdown = function (itemValue) {
        var dropdown = $(CONFIGS_SELECT);
        dropdown.find(':selected').prop('selected', false);
        dropdown.find('[value="' + itemValue + '"]').prop('selected', true);
        updateActionButtonsVisibility(itemValue);
    };

    var saveConfig = function (id) {
        var config = {};
        if (id) {
            config.id = id;
            config.name = $(CONFIGS_SELECT).find(':checked').text();
        } else {
            var name = window.prompt('Enter name of config to save');
            if (name === null) {
                return; // prompt was canceled
            }
            config.name = name;
        }
        var selectedShowIds = $(SHOW_CHECKBOX).filter(':checked')
            .map(function () { return $(this).val(); }).get();
        $.extend(config, {
            shows: selectedShowIds,
            fileName: $('input[name="file"]').val(),
            threshold: $('input[name="threshold"]').val(),
            type: $('input[name="type"]:checked').val(),
            unit: $('input[name="unit"]:checked').val()
        });

        $.post('./js/saved_configs/edit_config.php', {config: config}, function (data) {
            configs[data.id] = config;
            if (!id) {
                $(CONFIGS_SELECT).append(createOptionTag(data.id, config.name));
                selectItemInDropdown(data.id);
            }
        });
    };

    var updateConfig = function () {
        saveConfig($(CONFIGS_SELECT).find(':selected').val());
    };

    var updateShowCheckboxes = function (showIds) {
        $.each($(SHOW_CHECKBOX), function () {
            if ($.inArray($(this).val(), showIds) !== -1) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    };

    var applyRadioIfValid = function (radioName, value) {
        if (value !== undefined && value !== null) {
            var radios = $('input[name="' + radioName + '"]');
            radios.prop('checked', false);
            radios.filter('[value="' + value + '"]').prop('checked', true);

        }
    };

    var applyInputValueIfValid = function (inputName, value) {
        if (value !== undefined && value !== null) {
            $('input[name="' + inputName + '"]').val(value);
        }
    };

    var applyConfig = function (configId) {
        var config = configs[configId];
        updateShowCheckboxes(config.shows);
        applyInputValueIfValid('file', config.fileName);
        applyInputValueIfValid('threshold', config.threshold);
        applyRadioIfValid('type', config.type);
        applyRadioIfValid('unit', config.unit);
    };

    var deleteConfig = function () {
        var selectedOption = $(CONFIGS_SELECT).find('option:selected');
        var showId = selectedOption.val();
        $.post('./js/saved_configs/delete_config.php', {id: showId}, function () {
            selectedOption.remove();
            delete configs[showId];
            selectItemInDropdown('blank');
        });
    };

    var handleSelectChange = function () {
        updateActionButtonsVisibility($(this).val());
        if ($(this).val() === 'create') {
            saveConfig();
        } else if ($(this).val() !== 'blank') {
            applyConfig($(this).val());
        }
    };

    var applySelectedConfig = function () {
        var configId = $(CONFIGS_SELECT).find(':selected').val();
        applyConfig(configId);
    };

    /**
     * Populates the set element for configs with the available configurations.
     */
    var buildDropdownContent = function () {
        var selectElem = $(CONFIGS_SELECT);
        var result = '<option value="blank" selected="selected">Select&hellip;</option>' +
            '<option value="create" style="border-bottom: 2px solid #fc9; font-style: italic">Create</option>';
        for (var configId in configs) {
            if (configs.hasOwnProperty(configId)) {
                result += createOptionTag(configId, configs[configId].name);
            }
        }
        selectElem.append(result);
        selectElem.change(handleSelectChange);
    };

    // Execution
    $.get('./js/saved_configs/get_configs.php', function (data) {
        configs = data;
        buildDropdownContent();
        $(REAPPLY_CONFIG_BTN).click(applySelectedConfig);
        $(UPDATE_CONFIG_BTN).click(updateConfig);
        $(DELETE_CONFIG_BTN).click(deleteConfig);
    });

});

