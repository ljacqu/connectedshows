$(function () {
    var IMDB_URL = 'http://www.imdb.com/find?s=tt&q=';

    var SEARCH_BUTTON = $('.js-search-button');
    var SEARCH_FIELD = $('.js-search-field');

    SEARCH_BUTTON.click(function (event) {
        event.preventDefault();
        var searchTerms = SEARCH_FIELD.val();
        var url = IMDB_URL + encodeURI(searchTerms);

        window.open(url, 'imdb_search', 'location=yes,resizable=yes,scrollbars=yes,status=yes,width=800,height=600');
    });
    SEARCH_FIELD.keyup(function () {
        if ($(this).val() === '') {
            SEARCH_BUTTON.prop('disabled', true);
        } else {
            SEARCH_BUTTON.prop('disabled', false);
        }
    });
    if (SEARCH_FIELD.val() === '') {
        SEARCH_BUTTON.prop('disabled', true);
    }
});
