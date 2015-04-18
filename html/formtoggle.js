function toggleboxes(source, boxesname) {
    var checkboxes = document.getElementsByName(boxesname);
    var totalboxes = checkboxes.length;
    for (var i = 0; i < totalboxes; ++i) {
        checkboxes[i].checked = source.checked;
    }
}

function checkallbox(name, boxesname) {
    var checkboxes = document.getElementsByName(boxesname);
    var totalboxes = checkboxes.length;
    var allchecked = true;

    for (var i = 0; i < totalboxes; ++i) {
        if (!checkboxes[i].checked) {
            allchecked = false;
            break;
        }
    }

    document.getElementById(name).checked = allchecked;
}