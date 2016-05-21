$(function () {
    'use strict';

    var configs = {};

    var CONFIGS_SELECT = '.js-config-select';
    var SHOW_CHECKBOX = '.js-show-checkbox';
    var DELETE_CONFIG_BTN = '.js-config-deletebtn';

    var createOptionTag = function (id, name) {
        return '<option value="' + id + '">' + name + '</option>';
    };

    var saveNewConfig = function () {
        var name = window.prompt('Enter name of config to save');
        if (name === null) {
            return; // prompt was canceled
        }
        var selectedShowIds = $(SHOW_CHECKBOX).filter(':checked')
            .map(function () { return $(this).val(); }).get();
        var config = {
            shows: selectedShowIds,
            name: name,
            fileName: $('input[name="file"]').val(),
            threshold: $('input[name="threshold"]').val(),
            type: $('input[name="type"]:checked').val(),
            unit: $('input[name="unit"]:checked').val()
        };

        $.post('./js/saved_configs/edit_config.php', {config: config}, function (data) {
            configs[data.id] = config;
            $(CONFIGS_SELECT).append(createOptionTag(data.id, config.name));
        });
    };

    var updateShowCheckboxes = function (showIds) {
        $('.js-show-checkbox:checked').prop('checked', false);
        $.each($(SHOW_CHECKBOX), function () {
            if ($.inArray($(this).val(), showIds) !== -1) {
                $(this).prop('checked', true);
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
        });
    };

    var handleSelectChange = function () {
        var deleteButton = $(DELETE_CONFIG_BTN);
        if ($(this).val() === 'create') {
            deleteButton.hide();
            saveNewConfig();
        } else if ($(this).val() !== 'blank') {
            applyConfig($(this).val());
            deleteButton.show();
        }
    };

    /**
     * Populates the set element for configs with the available configurations.
     */
    var populateDropdown = function () {
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
        populateDropdown();
        $(DELETE_CONFIG_BTN).click(deleteConfig);
    });

});

