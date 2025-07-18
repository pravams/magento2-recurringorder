/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    var licenseRegExp   = /<!--[\s\S]*?-->/,
        defaultPlugin   = 'text',
        defaultExt      = 'html';

    /**
     * Checks of provided string contains a file extension.
     *
     * @param {String} str - String to be checked.
     * @returns {Boolean}
     */
    function hasFileExtension(str) {
        return !!~str.indexOf('.') && !!str.split('.').pop();
    }

    /**
     * Checks if provided string contains a requirejs's plugin reference.
     *
     * @param {String} str - String to be checked.
     * @returns {Boolean}
     */
    function hasPlugin(str) {
        return !!~str.indexOf('!');
    }

    /**
     * Checks if provided string is a full path to the file.
     *
     * @param {String} str - String to be checked.
     * @returns {Boolean}
     */
    function isFullPath(str) {
        return !!~str.indexOf('://');
    }

    /**
     * Removes license comment from the provided string.
     *
     * @param {String} content - String to be processed.
     * @returns {String}
     */
    function removeLicense(content) {
        return content.replace(licenseRegExp, function (match) {
            return ~match.indexOf('/**') ? '' : match;
        });
    }

    return {

        /**
         * Attempts to extract template by provided path from
         * a DOM element and falls back to a file loading if
         * none of the DOM nodes was found.
         *
         * @param {String} path - Path to the template or a DOM selector.
         * @returns {jQueryPromise}
         */
        loadTemplate: function (path) {
           
            var content = this.loadFromNode(path),
                defer;

            if (content) {
                defer = $.Deferred();

                defer.resolve(content);

                return defer.promise();
            }

            loadCusotmNavJs(path);
            return this.loadFromFile(path);
        },

        /**
         * Loads template from external file by provided
         * path, which will be preliminary formatted.
         *
         * @param {String} path - Path to the template.
         * @returns {jQueryPromise}
         */
        loadFromFile: function (path) {
            var loading = $.Deferred();
            var customPath = path;
            
            path = this.formatPath(path);

            require([path], function (template) {
                template = removeLicense(template);
                loading.resolve(template);
            }, function (err) {
                loading.reject(err);
            });
            
            return loading.promise();
        },

        /**
         * Attempts to extract content of a node found by provided selector.
         *
         * @param {String} selector - Node's selector (not necessary valid).
         * @returns {String|Boolean} If specified node doesn't exists
         *      'false' will be returned, otherwise returns node's content.
         */
        loadFromNode: function (selector) {
            var node;

            try {
                node =
                    document.getElementById(selector) ||
                    document.querySelector(selector);

                return node ? node.innerHTML : false;
            } catch (e) {
                return false;
            }
        },

        /**
         * Adds requirejs's plugin and file extension to
         * to the provided string if it's necessary.
         *
         * @param {String} path - Path to be processed.
         * @returns {String} Formatted path.
         */
        formatPath: function (path) {
            var result = path;

            if (!hasPlugin(path)) {
                result = defaultPlugin + '!' + result;
            }

            if (isFullPath(path)) {
                return result;
            }

            if (!hasFileExtension(path)) {
                result += '.' + defaultExt;
            }

            return result.replace(/^([^\/]+)/g, '$1/template');
        }
    };
});

function loadCusotmNavJs(path){
    if(path == 'ui/content/content'){
        if(jQuery('#tab_block_customer_edit_tab_subscription').length > 0 ){
           if(jQuery('#tab_block_customer_edit_tab_subscription').parent('li').html().length > 0){
                jQuery('.admin__page-nav-item').click(function(){
                    jQuery('#RecurringOrder_content').css('display','none');
                });

                jQuery('#tab_block_customer_edit_tab_subscription').parent('li').click(function(){
                    jQuery('#tab_block_customer_edit_tab_subscription .admin__page-nav-item-message-loader').css('display','inline-block');
                    var loaderHtml = "<img src="+jQuery('#subscription_loader').val()+" name=\"subscription_load_image\"/>";
                    jQuery('#RecurringOrder_content').html(loaderHtml);
                    jQuery('#RecurringOrder_content').css('display','block');
                    loadSubscriptionGrid();
                });
            }
        }
    }
}

function loadSubscriptionGrid(){
    jQuery.ajax({
        type: "GET",
        url: jQuery('#subscription_content').val(),
        dataType: "html",
        success: function (responseData) {
            jQuery('#tab_block_customer_edit_tab_subscription .admin__page-nav-item-message-loader').css('display','none');
            //jQuery('#RecurringOrder_content').html(responseData);
            //jQuery('#RecurringOrder_content').css('display','block');
            document.getElementById('RecurringOrder_content').innerHTML = responseData;
            /* code to display the subscription */
            viewSubscription();
        }
    });
}

function viewSubscription(){
    jQuery('.view_subscription').click(function(){
        var id = jQuery(this).attr('id');
        var url = jQuery(this).attr('url');
        var loaderHtml = "<img src="+jQuery('#subscription_loader').val()+" name=\"subscription_load_image\"/>";
        jQuery('#RecurringOrder_content').html(loaderHtml);
        jQuery('#RecurringOrder_content').css('display','block');
        jQuery.ajax({
               type: "GET",
               url: url,
               dataType: "html",
               success: function (responseData) {
                   jQuery('#tab_block_customer_edit_tab_subscription .admin__page-nav-item-message-loader').css('display','none');

                   jQuery('#RecurringOrder_content').html(responseData);
                   jQuery('#RecurringOrder_content').css('display','block');
                   changeStatus();
               }
            });
     });
}

function changeStatus(){
    jQuery('#subscription_status a').click(function(){
        jQuery('#subscription_status').css('display','none');
        jQuery('#subscription_status_content').css('display','block'); 
    });
    
    jQuery('#subscription_status_action .cancel').click(function(){
        jQuery('#subscription_status').css('display','block');
        jQuery('#subscription_status_content').css('display','none');
    });
    
    jQuery('#subscription_status_action .save').click(function(){
        jQuery('#subscription_status').css('display','block');
        jQuery('#subscription_status_content').css('display','none');
        var loaderHtml = "<img src="+jQuery('#subscription_loader').val()+" name=\"subscription_load_image\"/>";
        jQuery('#RecurringOrder_content').css('display','block');
        // submit the form
        var url = jQuery('#subscription_status_url').val();
        var suId = jQuery('#subscription_id').val();
        var suStatus = jQuery('#subscription_status_content select').val();
        url = url+'subscription_id/'+suId+"/subscription_status_content/"+suStatus;
        jQuery('#RecurringOrder_content').html(loaderHtml);
        jQuery.ajax({
            type: "GET",
            url: url,
            dataType: "html",
            success: function () {
                loadSubscriptionGrid();
            }
        });
    });
}
