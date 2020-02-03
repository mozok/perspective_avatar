define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'ko',
    'avatarLoader'
], function (Component, customerData, ko, avatarLoader) {
    'use strict';

    let imgVisible = ko.observable(false);

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.customer = customerData.get('customer');
        },

        /**
         * Invalidated image visibility
         */
        imgVisible: imgVisible,

        /**
         * Loaded customer's image
         */
        image: avatarLoader,

        /**
         * Invalidate picture 404 error after admin manipulation
         */
        invalidatePicture: ko.computed(function(){
            if(avatarLoader()) {
                imgVisible(true);
            }
        })
    });
});
