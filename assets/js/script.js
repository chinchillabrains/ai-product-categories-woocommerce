jQuery(document).ready(function ($) {
    var categorySelectHtml = formatCategoriesForHtml(aipcplugin);
    $('#product_catdiv .inside').prepend($(categorySelectHtml));

    function formatCategoriesForHtml (pluginData) {
        var ret_html = '<div class="aipc-category-select">';
        ret_html += '<h4>' + pluginData.listTitle + '</h4>';
        ret_html += '<ul class="aipc-category-select__list">';
        for ( var i = 0; i < pluginData.categories.length; i++ ) {
            ret_html += '<li>';
                ret_html += '<label class="aipc-category-select__item"><input value="' + pluginData.categories[i].term_id + '" type="checkbox" name="aipc_input_product_cat[]" id="aipc-product_cat-' + pluginData.categories[i].term_id + '"> ' + pluginData.categories[i].hierarchical_cat_name + '</label>';
            ret_html += '</li>';
        }
        ret_html += '</ul>';
        ret_html += '</div>';
        return ret_html;
    }
});