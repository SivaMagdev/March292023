define([
    'jquery',
    'underscore',
    'mage/template',
    'jquery/ui',
    'mage/translate'
], function ($, _, mageTemplate) {
    'use strict';

    function isEmpty(value) {
        return (value.length === 0) || (value == null) || /^\s+$/.test(value);
    }

    $.widget('mage.ajaxsearch', {
        options: {
            autocomplete: 'off',
            minSearchLength: 2,
            responseFieldElements: 'ul li',
            selectClass: 'selected',
            template:
                '<div class="ajaxsearch__product col-8-8">' +

                    '<a class="ajaxsearch__image col-2-8" href="<%- data.url %>">' +
                        '<img src="<%- data.image %>">' +
                    '</a>' +

                    '<div class="ajaxsearch__description col-6-8 last">' +

                    '<div class="ajaxsearch__product__title">' +
                        '<a class="h4" href="<%- data.url %>">' +
                            '<%- data.title %>' + $.mage.__(" | ") + '<%- data.sku %>' +
                        '</a>' +
                    '</div>' +

                    '<% if (data.loggedin && data.customerApproved) { %>' +
                        '<% if (data.stock_status) { %>' +
                            '<div class="ajaxsearch__availabity">' +
                                '<span>' + $.mage.__("In stock") + '</span>' +
                            '</div>' +
                        '<% } else { %>'+
                            '<div class="ajaxsearch__unavailabity">' +
                                '<span>' + $.mage.__("Out of stock") + '</span>' +
                            '</div>' +
                        '<% } %>'+
                    '<% } %>'+

                    '<% if (data.strength || data.pack_size || data.case_pack) { %>' +
                        '<div class="ajaxsearch__attributes">' +
                            '<% if (data.strength) { %>' +
                                '<span>' + $.mage.__("Total Content:") + '<%- data.strength %>' + $.mage.__(" | ") + '</span>' +
                            '<% } %>'+
                            '<% if (data.pack_size) { %>' +
                                '<span>' + $.mage.__("Pack:") + '<%- data.pack_size %>' + $.mage.__(" | ") + '</span>' +
                            '<% } %>'+
                            '<% if (data.case_pack) { %>' +
                                '<span>' + $.mage.__("Case:") + '<%- data.case_pack %>' + '</span>' +
                            '<% } %>'+
                        '</div>' +
                    '<% } %>'+

                    '<% if (data.loggedin && data.customerApproved) { %>' +
                        '<% if (data.final_price != 0) { %>' +
                            '<div class="ajaxsearch__price">' +
                                '<span>' + $.mage.__("Price:") + ' <span class="<% if (data.has_special_price) { %> u-strike-through <% } %> ">' + '<%- data.price %></span></span>' +
                            '</div>' +

                            '<% if (data.has_special_price) { %>' +
                                '<div class="ajaxsearch__sale">' +
                                    '<span class="ajaxsearch__sale--accent"> <%- data.special_price %></span>' +
                                '</div>' +
                            '<% } %>'+
                        '<% } %>'+
                    '<% } %>'+

                '</div> <!-- .ajaxsearch__product col-8-8 -->'
            ,
            submitBtn: 'button[type="submit"]',
            searchLabel: '[data-role=minisearch-label]'
        },
        _create: function () {
            this.responseList = {
                indexList: null,
                selected: null
            };
            this.autoComplete = $(this.options.destinationSelector);
            this.searchForm = $(this.options.formSelector);
            this.submitBtn = this.searchForm.find(this.options.submitBtn)[0];
            this.searchLabel = $(this.options.searchLabel);

            _.bindAll(this, '_onKeyDown', '_onPropertyChange', '_onSubmit');

            this.submitBtn.disabled = true;

            this.element.attr('autocomplete', this.options.autocomplete);

            this.element.on('blur', $.proxy(function () {

                setTimeout($.proxy(function () {
                    if (this.autoComplete.is(':hidden')) {
                        this.searchLabel.removeClass('active');
                    }
                    this.autoComplete.hide();
                    this._updateAriaHasPopup(false);
                }, this), 250);
            }, this));

            this.element.trigger('blur');

            this.element.on('focus', $.proxy(function () {
                this.searchLabel.addClass('active');

                if($('#search').val().length >= 2) {
                    $('#ajaxsearchpopup').css('display', 'block');
                }
            }, this));
            this.element.on('keydown', this._onKeyDown);
            this.element.on('input propertychange', this._onPropertyChange);

            this.searchForm.on('submit', $.proxy(function () {
                this._onSubmit();
                this._updateAriaHasPopup(false);
            }, this));
        },

        _getFirstVisibleElement: function () {
            return this.responseList.indexList ? this.responseList.indexList.first() : false;
        },

        _getLastElement: function () {
            return this.responseList.indexList ? this.responseList.indexList.last() : false;
        },

        _updateAriaHasPopup: function (show) {
            if (show) {
                this.element.attr('aria-haspopup', 'true');
            } else {
                this.element.attr('aria-haspopup', 'false');
            }
        },

        _resetResponseList: function (all) {
            this.responseList.selected = null;

            if (all === true) {
                this.responseList.indexList = null;
            }
        },

        _onSubmit: function (e) {
            var value = this.element.val();

            if (isEmpty(value)) {
                e.preventDefault();
            }

            if (this.responseList.selected) {
                this.element.val(this.responseList.selected.find('.qs-option-name').text());
            }
        },

        _onKeyDown: function (e) {
            var keyCode = e.keyCode || e.which;

            switch (keyCode) {
                case $.ui.keyCode.HOME:
                    this._getFirstVisibleElement().addClass(this.options.selectClass);
                    this.responseList.selected = this._getFirstVisibleElement();
                    break;
                case $.ui.keyCode.END:
                    this._getLastElement().addClass(this.options.selectClass);
                    this.responseList.selected = this._getLastElement();
                    break;
                case $.ui.keyCode.ESCAPE:
                    this._resetResponseList(true);
                    this.autoComplete.hide();
                    break;
                case $.ui.keyCode.ENTER:
                    this.searchForm.trigger('submit');
                    break;
                case $.ui.keyCode.DOWN:

                    if (this.responseList.indexList) {
                        if (!this.responseList.selected) {
                            this._getFirstVisibleElement().addClass(this.options.selectClass);
                            this.responseList.selected = this._getFirstVisibleElement();
                        }
                        else if (!this._getLastElement().hasClass(this.options.selectClass)) {
                            this.responseList.selected = this.responseList.selected.removeClass(this.options.selectClass).next().addClass(this.options.selectClass);
                        } else {
                            this.responseList.selected.removeClass(this.options.selectClass);
                            this._getFirstVisibleElement().addClass(this.options.selectClass);
                            this.responseList.selected = this._getFirstVisibleElement();
                        }
                        this.element.val(this.responseList.selected.find('.qs-option-name').text());
                        this.element.attr('aria-activedescendant', this.responseList.selected.attr('id'));
                    }
                    break;
                case $.ui.keyCode.UP:

                    if (this.responseList.indexList !== null) {
                        if (!this._getFirstVisibleElement().hasClass(this.options.selectClass)) {
                            this.responseList.selected = this.responseList.selected.removeClass(this.options.selectClass).prev().addClass(this.options.selectClass);

                        } else {
                            this.responseList.selected.removeClass(this.options.selectClass);
                            this._getLastElement().addClass(this.options.selectClass);
                            this.responseList.selected = this._getLastElement();
                        }
                        this.element.val(this.responseList.selected.find('.qs-option-name').text());
                        this.element.attr('aria-activedescendant', this.responseList.selected.attr('id'));
                    }
                    break;
                default:
                    return true;
            }
        },

        _onPropertyChange: function () {
            var searchField = this.element,
                clonePosition = {
                    position: 'absolute',
                    width: '100%'
                },
                source = this.options.template,
                template = mageTemplate(source),
                dropdown = $('<div class="ajaxsearch"><div class="ajaxsearch__overlay"></div><div class="ajaxsearch__wrapper"><div class="ajaxsearch__wrapper--inner"><div class="ajaxsearch__content clearfix"><button type="submit" class="view-all" value="Submit">View all search result</button></div>'),
                value = this.element.val();

            this.submitBtn.disabled = isEmpty(value);

            if (value.length >= parseInt(this.options.minSearchLength, 10)) {
                $('#search_mini_form').addClass('is-loading');
                if(this.request) {
                    this.request.abort();
                }
                var custom_attribute = $('#custom_attribute').val();
                this.request = $.get(this.options.url, {q: value,custom_attribute:custom_attribute}, $.proxy(function (data) {
                    if( ! data.length)
                        dropdown.find('.ajaxsearch__content').replaceWith('<div class="ajaxsearch__product ajax__noresults">' + $.mage.__('Your search returned no results.') + '</div>');

                    $.each(data, function (index, element) {
                        element.index = index;
                        var html = template({
                            data: element
                        });
                        dropdown.find('.ajaxsearch__content').prepend(html);
                    });

                    this.responseList.indexList = this.autoComplete.html(dropdown)
                        .css(clonePosition)
                        .show()
                        .find(this.options.responseFieldElements + ':visible');

                    $('#search_mini_form').removeClass('is-loading');

                    this._resetResponseList(false);
                    this.element.removeAttr('aria-activedescendant');

                    if (this.responseList.indexList.length) {
                        this._updateAriaHasPopup(true);
                    } else {
                        this._updateAriaHasPopup(false);
                    }

                    this.responseList.indexList
                        .on('click', function (e) {
                            this.responseList.selected = $(e.target);
                            this.searchForm.trigger('submit');
                        }.bind(this))
                        .on('mouseenter mouseleave', function (e) {
                            this.responseList.indexList.removeClass(this.options.selectClass);
                            $(e.target).addClass(this.options.selectClass);
                            this.responseList.selected = $(e.target);
                            this.element.attr('aria-activedescendant', $(e.target).attr('id'));
                        }.bind(this))
                        .on('mouseout', function (e) {
                            if (!this._getLastElement() && this._getLastElement().hasClass(this.options.selectClass)) {
                                $(e.target).removeClass(this.options.selectClass);
                                this._resetResponseList(false);
                            }
                        }.bind(this));
                }, this));
            } else {
                this._resetResponseList(true);
                this.autoComplete.hide();
                this._updateAriaHasPopup(false);
                this.element.removeAttr('aria-activedescendant');
            }
        }
    });
    return $.mage.ajaxsearch;
});