define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'ko'
], function (Component, customerData, ko) {
    'use strict';

    let imgVisible = ko.observable(false);
    let lim = 1;
    let reload = 0;

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
         * Invalidate picture 404 error after admin manipulation
         */
        invalidatePicture: ko.computed(function(){
            let data = customerData.get('customer')();
            if(data.hasOwnProperty('avatar_url')) {
                let url = data.avatar_url;
                fetch(url, {method: 'post'}).then(function(res) {
                    if(res.status === 404) {
                        if(reload < lim) {
                            customerData.reload('customer');
                            reload++;
                        }else {
                            imgVisible(true);
                        }
                    }else {
                        imgVisible(true);
                    }
                });
            }
        })
    });
});
