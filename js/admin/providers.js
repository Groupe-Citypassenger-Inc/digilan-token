(function ($) {
    $(document).ready(function () {
        var savingMessage = provider_ajax.savingMessage;
        var errorMessage = provider_ajax.errorMessage;
        var successMessage = provider_ajax.successMessage;
        $('.dlt-dashboard-providers').sortable({
            handle: '.dlt-dashboard-provider-sortable-handle',
            items: ' > .dlt-dashboard-provider',
            tolerance: 'pointer',
            stop: function (event, ui) {
                var $providers = $('.dlt-dashboard-providers > .dlt-dashboard-provider'),
                    providerList = [];
                for (var i = 0; i < $providers.length; i++) {
                    providerList.push($providers.eq(i).data('provider'));
                }

                ui.item.find('.dlt-provider-notice').remove();

                var $notice = $('<div class="dlt-provider-notice">' + savingMessage + '</div>')
                    .appendTo(ui.item);

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: ajaxurl,
                    data: {
                        '_ajax_nonce': provider_ajax._ajax_nonce,
                        'action': 'digilan-token-plugin',
                        'view': 'orderProviders',
                        'ordering': providerList
                    },
                    success: function () {
                        $notice.html(successMessage);
                        setTimeout(function () {
                            $notice.fadeOut(300, function () {
                                $notice.remove();
                            });
                        }, 2000);
                    },
                    error: function () {
                        $notice.html(errorMessage);
                        setTimeout(function () {
                            $notice.fadeOut(300, function () {
                                $notice.remove();
                            });
                        }, 3000);
                    }
                });
            }
        });
    });
})(jQuery);