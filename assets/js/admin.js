jQuery(document).ready(function ($) {
    $('.aipc-settings__disabledsuggestionsEnable').click(function () {
        var $item = $(this).closest('.aipc-settings__disabledsuggestionsItem');
        var productId = $item.data('id');
        $item.remove();
        if ( $('.aipc-settings__disabledsuggestionsItem').length == 0 ) {
            $('.aipc-settings__disabledsuggestionsList').remove();
        }
        var url = aipcplugin.adminUrl;
        const xhttp = new XMLHttpRequest();
        xhttp.open("GET", url + '?aipc-product-remove-skip-list=' + productId, true);
        xhttp.send();
    });

    $('.aipc-settings__gatherdataButton').click(function () {
        var url = aipcplugin.adminUrl;
        const xhttp = new XMLHttpRequest();
        xhttp.open("GET", url + '?aipc-gather-data=1', true);
        xhttp.send();
        setTimeout(
            function(){
                location.reload();
            },
            1000
        );
    });
});