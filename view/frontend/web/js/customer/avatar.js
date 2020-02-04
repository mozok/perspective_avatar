define([
    'ko',
    'jquery',
    'mage/url'
], function(ko, $, urlBuilder) {
    'use strict';

    let imageUrl = ko.observable();
    let url = urlBuilder.build('avatar/customer/avatar');

    $.ajax({
        url: url,
        dataType: 'json',
        type: 'get'
    }).done(function(res){
        imageUrl(res.url);
    });

    return imageUrl;
});