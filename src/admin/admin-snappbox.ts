import './admin.css';

/**
 * Enterprise Admin Management for SnappBox Elite.
 * Ported from legacy jQuery to modern TypeScript.
 */

declare const SNAPPBOX_GLOBAL: {
    ajaxUrl: string;
    nonce: string;
    rtlPluginUrl?: string;
    mapStyleUrl?: string;
    i18n?: Record<string, string>;
};

declare const maplibregl: any;
declare const jQuery: any;

(function ($) {
    'use strict';

    $(function () {
        const $map = $('#admin-osm-map');
        if ($map.length && typeof maplibregl !== 'undefined') {
            initMap($map);
        }

        const $ctx = $('#snappbox-admin-context');
        if (!$ctx.length) return;

        const ctx = {
            ajaxUrl: SNAPPBOX_GLOBAL.ajaxUrl,
            nonce: SNAPPBOX_GLOBAL.nonce,
            wooOrderId: parseInt($ctx.data('woo-order-id'), 10)
        };

        const $modal = $('#sb-pricing-modal');
        const $pricingMsg = $('#pricing-message');
        const $createBtn = $('#snappbox-create-order');

        $('#snappbox-pricing-order').on('click', function (e: Event) {
            e.preventDefault();
            fetchPricing(ctx, $pricingMsg, $createBtn, $modal);
        });

        $('#snappbox-cancel-order').on('click', function (e: Event) {
            e.preventDefault();
            cancelOrder(ctx);
        });
    });

    function initMap($el: any) {
        const lat = parseFloat($el.data('lat'));
        const lng = parseFloat($el.data('lng'));

        const map = new maplibregl.Map({
            container: $el.attr('id'),
            style: SNAPPBOX_GLOBAL.mapStyleUrl || 'https://tile.snappmaps.ir/styles/snapp-style-v4.1.2/style.json',
            center: [lng, lat],
            zoom: 15
        });

        new maplibregl.Popup()
            .setLngLat([lng, lat])
            .setHTML('Customer Location')
            .addTo(map);
    }

    async function fetchPricing(ctx: any, $msg: any, $btn: any, $modal: any) {
        $msg.text('Fetching pricing...');
        try {
            const response = await $.post(ctx.ajaxUrl, {
                action: 'snappb_get_pricing',
                order_id: ctx.wooOrderId,
                nonce: ctx.nonce
            });

            if (response.success) {
                $msg.html(`Elite Price: ${response.data.finalCustomerFare} IRT`);
                $btn.show();
                $modal.show();
            } else {
                $msg.text(response.data || 'Error fetching price');
            }
        } catch (e) {
            $msg.text('Connection error');
        }
    }

    async function cancelOrder(ctx: any) {
        if (!confirm('Are you sure you want to cancel this SnappBox order?')) return;

        try {
            const response = await $.post(ctx.ajaxUrl, {
                action: 'snappb_cancel_order',
                woo_order_id: ctx.wooOrderId,
                nonce: ctx.nonce
            });

            if (response.success) {
                alert('Order cancelled successfully.');
                window.location.reload();
            } else {
                alert('Cancellation failed: ' + (response.data || 'Unknown error'));
            }
        } catch (e) {
            alert('Request failed');
        }
    }

})(jQuery);
