/*jslint browser: true white:true*/
/*global jQuery,gmosso_endpoint_config_data*/

(function ($, configData) {
    "use strict";

    var $detailsDiv = $("<div class=\"table-cell details last\"></div>");

    var removeItemDetails = function() {
        if (!$detailsDiv.is(":visible")) {
            return;
        }
        var $prev = $detailsDiv.prev();
        if ($prev.hasClass("table-cell")) {
            $prev.toggleClass("last", true);
        }
        $detailsDiv.hide();
    };

    var displayItemDetails = function (itemId, itemData) {
        var rowSelector = ".table-cell a[data-userid=\"" +
            itemId.toString() + "\"]";
        var $lastDivOfRow = $("#table-items").find(rowSelector).last().parent();

        $lastDivOfRow.removeClass("last");

        $detailsDiv.removeClass("dark");
        if ($lastDivOfRow.hasClass("dark")) {
            $detailsDiv.addClass("dark");
        }

        $detailsDiv.html(itemData);
        $detailsDiv.data("userid", itemId);
        $lastDivOfRow.after($detailsDiv);
        $detailsDiv.slideDown(300);
    };

    var toggleItemDetails = function() {
        if ($detailsDiv.is(":visible")) {
            removeItemDetails();
            return;
        }
        $detailsDiv.show();
    };

    $(".gmosso-endpoint-template-general " +
        "#table-items .table-cell a").on("click", function (event) {
        event.preventDefault();

        var $target = $(event.target);
        var itemId = $target.data("userid") || null;

        if (itemId === null) {
            return;
        }

        if ($detailsDiv.data("userid") === itemId) {
            toggleItemDetails();
            return;
        }

        $.get(configData.ajaxUrl, {
            action: configData.action,
            endpoint: configData.endpoint,
            itemId: Number(itemId)
        })
        .done(function (data) {
            removeItemDetails();
            displayItemDetails(itemId, data.html);
        })
        .fail(function (error) {
            var errorHtml = (
                (error.responseJSON !== undefined) ?
                error.responseJSON.data.hmtl : error.responseText
            );
            removeItemDetails();
            displayItemDetails(itemId, errorHtml);
        });
    });

}(jQuery, gmosso_endpoint_config_data));
