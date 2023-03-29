define([
  'jquery',
  'jquery/ui',
  'magento-swatch.renderer'
], function($){

  $.widget('ecomm.SwatchRenderer', $.mage.SwatchRenderer, {
        /**
         * @private
         */
        _init: function () {
            // Don't render the same set of swatches twice
            if ($(this.element).attr('data-rendered')) {
                return;
            }
            $(this.element).attr('data-rendered', true);

            if (_.isEmpty(this.options.jsonConfig.images)) {
                this.options.useAjax = true;
                // creates debounced variant of _LoadProductMedia()
                // to use it in events handlers instead of _LoadProductMedia()
                this._debouncedLoadProductMedia = _.debounce(this._LoadProductMedia.bind(this), 500);
            }

            if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                // store unsorted attributes
                this.options.jsonConfig.mappedAttributes = _.clone(this.options.jsonConfig.attributes);
                this._sortAttributes();
                this._RenderControls();

                //console.table(this.options);

                //this is additional code for select first attribute value
                if (this.options.jsonConfig.attributes.length > 0) {
                    //console.log('Product ID:'+this.getProduct());
                    var selectswatch = this.element.find('.' + this.options.classes.attributeClass + ' .' + this.options.classes.attributeOptionsWrapper);
                    //console.log('.' + this.options.classes.attributeClass + ' .' + this.options.classes.attributeOptionsWrapper);

                    $.each(selectswatch, function (index, item) {
                        //console.log(index);

                        /*if(index == 0){
                            console.log($(item).find('.swatch-select option:first').val());
                            //$(item).find('.swatch-select').val($(item).find('.swatch-select option:first').val()).trigger('change');
                            console.log('.swatch-select option[value="'+$(item).find('.swatch-select option:first').val()+'"]');
                            $(item).find('.swatch-select option[value="'+$(item).find('.swatch-select option:first').val()+'"]').prop('selected', true);
                        }*/

                        //if(index == 1){
                            var swatchOption = $(item).find('div.swatch-option').first();
                            if (swatchOption.length && !$(item).find('div.swatch-option').hasClass('selected')) {
                                swatchOption.trigger('click');
                            }
                        //}
                    });

                    $.each(selectswatch, function (index, item) {
                        //console.log(index);

                        if(index == 0){
                            //console.log($(item).find('.swatch-select option:first').val());

                            $(item).find('.swatch-select').trigger('change');
                            ///$(item).find('.swatch-select').val($(item).find('.swatch-select option:first').val()).trigger('change');
                            //console.log('.swatch-select option[value="'+$(item).find('.swatch-select option:first').val()+'"]');
                            //$(item).find('.swatch-select option[value="'+$(item).find('.swatch-select option:first').val()+'"]').prop('selected', true);
                        }
                    });

                    //console.log(this.options.classes.selectClass);
                    //console.log($("."+this.options.classes.selectClass+" option:first").val());

                    //$("."+this.options.classes.selectClass).val($("."+this.options.classes.selectClass+" option:first").val()).trigger('change');
                }

                //this is additional code for select first attribute value

                this._setPreSelectedGallery();
                $(this.element).trigger('swatch.initialized');
            } else {
                console.log('SwatchRenderer: No input data received');
            }
            this.options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();
        },

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.data('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput),
                checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']),
                $priceBox = $widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr('data-option-selected', $this.data('option-id')).find('.selected').removeClass('selected');
                $label.text($this.data('option-label'));
                $input.val($this.data('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                $widget._toggleCheckedAttributes($this, $wrapper);
            }

            $widget._Rebuild();

            //console.log($widget.options.jsonConfig);

            var iparentid = $widget.options.jsonConfig.sparentid[this.getProduct()];
            var product_id = this.getProduct();
            var iname = $widget.options.jsonConfig.sname[this.getProduct()];
            var idescription = $widget.options.jsonConfig.sdescription[this.getProduct()];
            var indcupc = $widget.options.jsonConfig.sndcupc[this.getProduct()];
            var iquantity = $widget.options.jsonConfig.squantity[this.getProduct()];
            var icustomprice = $widget.options.jsonConfig.scustomprice[this.getProduct()];
            var iaddtocarturl = $widget.options.jsonConfig.saddtocarturl[this.getProduct()];
            var icustomoptions = $widget.options.jsonConfig.scustomoptions[this.getProduct()];
            var ishortdatedlable = $widget.options.jsonConfig.sshortdatedlable[this.getProduct()];
            var icasepack = $widget.options.jsonConfig.scasepack[this.getProduct()];
            var ibrand = $widget.options.jsonConfig.sbrand[this.getProduct()];
            var icoldchain = $widget.options.jsonConfig.scoldchain[this.getProduct()];
            var iglutenfree = $widget.options.jsonConfig.sglutenfree[this.getProduct()];
            var ilatexfree = $widget.options.jsonConfig.slatexfree[this.getProduct()];
            var ipreservativefree = $widget.options.jsonConfig.spreservativefree[this.getProduct()];
            var idryfree = $widget.options.jsonConfig.sdryfree[this.getProduct()];
            var ibarcoded = $widget.options.jsonConfig.sbarcoded[this.getProduct()];
            var iconcentration = $widget.options.jsonConfig.sconcentration[this.getProduct()];
            var itotalcontent = $widget.options.jsonConfig.stotalcontent[this.getProduct()];
            var ishotdesc = $widget.options.jsonConfig.sshotdesc[this.getProduct()];
            var ishs = $widget.options.jsonConfig.sshs[this.getProduct()];
            var itheraputiccat = $widget.options.jsonConfig.stheraputiccat[this.getProduct()];
            var ifdarating = $widget.options.jsonConfig.sfdarating[this.getProduct()];
            var iadditioninfos = $widget.options.jsonConfig.sadditioninfos[this.getProduct()];
            var iwholesalerinfos = $widget.options.jsonConfig.swholesalerinfos[this.getProduct()];
            var isupportivedocs = $widget.options.jsonConfig.ssupportivedocs[this.getProduct()];
            var iwishlist = $widget.options.jsonConfig.swishlist[this.getProduct()];
            var iwishlisturl = $widget.options.jsonConfig.swishlisturl[this.getProduct()];


            if(iname != ''){
                $('.product-info-main [data-ui-id="page-title-wrapper"]').html(iname);
                var pname_class='.ajax-product-item-name-' + iparentid;
                if($(pname_class).length > 0) {
                    $(pname_class).html(iname);
                }
            }

            if(indcupc != ''){
                $('[itemprop="sku"]').html(indcupc);
                var ndc_class='.product-attribute-sku-' + iparentid;
                if($(ndc_class).length > 0) {
                    $(ndc_class).html(indcupc);
                }
            }

            if(iquantity != ''){
                var qty_class='#product-available-qty-value-' + iparentid;
                if($(qty_class).length > 0) {
                    $(qty_class).html(iquantity);
                }
            }

            if(iwishlist == 1){
                var wishlist_id='#product-wishlist-' + iparentid;
                if($(wishlist_id).length > 0) {
                    $(wishlist_id).addClass("active");
                }
            } else {
                var wishlist_id='#product-wishlist-' + iparentid;
                if($(wishlist_id).length > 0) {
                    $(wishlist_id).removeClass("active");
                }
            }

            if(iwishlisturl != ''){

                var wishlist_id='#product-wishlist-' + iparentid;
                if($(wishlist_id).length > 0) {
                    $(wishlist_id).attr('data-post', iwishlisturl);
                }
            }

            if(iaddtocarturl != ''){

                var form_id='#product_addtocart_form_' + iparentid;
                if($(form_id).length > 0) {
                    $(form_id).attr('action', iaddtocarturl);
                    $(form_id).attr('data-product-sku', indcupc);

                    $(form_id+' input[name=product]').val(product_id);
                }

                if($('#product_addtocart_form').length > 0) {
                    $('#product_addtocart_form').attr('action', iaddtocarturl);
                    $('#product_addtocart_form').attr('data-product-sku', indcupc);

                    $('#product_addtocart_form input[name=product]').val(product_id);
                }
            }



            if(ibrand != ''){
                var brand_class='.product-attribute-brand-' + iparentid;
                if($(brand_class).length > 0) {
                    $(brand_class).html(ibrand);
                }
            }

            if(icoldchain != ''){
                var coldchain_class='.product-attribute-cold-chain-' + iparentid;
                if($(coldchain_class).length > 0) {
                    $(coldchain_class).html(icoldchain);
                }
            }

            if(iglutenfree != ''){
                var glutenfree_class='.product-attribute-gluten-free-' + iparentid;
                if($(glutenfree_class).length > 0) {
                    $(glutenfree_class).html(iglutenfree);
                }
            }

            if(ilatexfree != ''){
                var latexfree_class='.product-attribute-latex-free-' + iparentid;
                if($(latexfree_class).length > 0) {
                    $(latexfree_class).html(ilatexfree);
                }
            }

            if(ipreservativefree != ''){
                var preservativefree_class='.product-attribute-preservative-free-' + iparentid;
                if($(preservativefree_class).length > 0) {
                    $(preservativefree_class).html(ipreservativefree);
                }
            }

            if(idryfree != ''){
                var dryfree_class='.product-attribute-dry-free-' + iparentid;
                if($(dryfree_class).length > 0) {
                    $(dryfree_class).html(idryfree);
                }
            }

            if(ibarcoded != ''){
                var barcoded_class='.product-attribute-barcoded-' + iparentid;
                if($(barcoded_class).length > 0) {
                    $(barcoded_class).html(ibarcoded);
                }
            }

            if(iconcentration != ''){
                var concentration_class='.product-attribute-concentration-' + iparentid;
                if($(concentration_class).length > 0) {
                    $(concentration_class).html(iconcentration);
                }
            }

            if(itotalcontent != ''){
                var totalcontent_class='.product-attribute-total-content-' + iparentid;
                if($(totalcontent_class).length > 0) {
                    $(totalcontent_class).html(itotalcontent);
                }
            }

            if(ishotdesc != ''){
                var shotdesc_class='.product-attribute-short-desc-' + iparentid;
                if($(shotdesc_class).length > 0) {
                    $(shotdesc_class).html(ishotdesc);
                }
            }

            if(ishs != ''){
                var shs_class='.product-attribute-shs-' + iparentid;
                if($(shs_class).length > 0) {
                    $(shs_class).html(ishs);
                }
            }

            if(itheraputiccat != ''){
                var theraputiccat_class='.product-attribute-tcat-' + iparentid;
                if($(theraputiccat_class).length > 0) {
                    $(theraputiccat_class).html(itheraputiccat);
                }
            }

            if(ifdarating != ''){
                var fdarating_class='.product-attribute-fda-' + iparentid;
                if($(fdarating_class).length > 0) {
                    $(fdarating_class).html(ifdarating);
                }
            }

            if(icustomprice != ''){
                //var main_price_class='#product-price-' + iparentid;
                var main_price_class='.product-info-main .product-info-price';
                var price_class='#product-final-general-price-' + iparentid;

                $(main_price_class).html(icustomprice);

                if($(price_class).length > 0) {
                    $(price_class).html(icustomprice);
                }
            }

            if(icasepack != ''){
                var casepack_class='#product-case-pack-' + iparentid;
                if($(casepack_class).length > 0) {
                    $(casepack_class).html(icasepack);
                }
            }

            if(iadditioninfos != ''){
                var additionalproductinfo_class='#additionalproductinfo';
                if($(additionalproductinfo_class).length > 0) {
                    $(additionalproductinfo_class+' .additional-links dd').html(iadditioninfos);
                }
            }

            if(iwholesalerinfos != ''){
                var wholesaleritem_class='#wholesaleritem';
                if($(wholesaleritem_class).length > 0) {
                    $(wholesaleritem_class+' .drl-wholesale').html(iwholesalerinfos);
                }
            }

            if(isupportivedocs != ''){
                var supportive_class='#supportive';
                if($(supportive_class).length > 0) {
                    $(supportive_class).html(isupportivedocs);
                }
            }

            var customoption_class='#customattributesshortdated-' + iparentid;
            if($(customoption_class).length > 0) {
                $(customoption_class).html(icustomoptions);
            }

            var shortdated_class='#ajax-short-dated-' + iparentid;
            if($(shortdated_class).length > 0) {
                if(ishortdatedlable == 1) {
                    $(shortdated_class).show();
                } else {
                    $(shortdated_class).hide();
                }
            }


            /*if(idescription != ''){
                $('[data-role="content"]').find('.description .value').html(idescription);
            }*/

            if ($priceBox.is(':data(mage-priceBox)')) {
                $widget._UpdatePrice();
            }

            $(document).trigger('updateMsrpPriceBlock',
                [
                    this._getSelectedOptionPriceIndex(),
                    $widget.options.jsonConfig.optionPrices,
                    $priceBox
                ]);

            if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                $widget._loadMedia();
            }

            $input.trigger('change');
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.data('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.productForm.length > 0) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.val() > 0) {
                $parent.attr('data-option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('data-option-selected');
                $input.val('');
            }

            $widget._Rebuild();


            //console.log($widget.options.jsonConfig);

            var iparentid = $widget.options.jsonConfig.sparentid[this.getProduct()];
            var product_id = this.getProduct();
            var iname = $widget.options.jsonConfig.sname[this.getProduct()];
            var idescription = $widget.options.jsonConfig.sdescription[this.getProduct()];
            var indcupc = $widget.options.jsonConfig.sndcupc[this.getProduct()];
            var iquantity = $widget.options.jsonConfig.squantity[this.getProduct()];
            var icustomprice = $widget.options.jsonConfig.scustomprice[this.getProduct()];
            var iaddtocarturl = $widget.options.jsonConfig.saddtocarturl[this.getProduct()];
            var icustomoptions = $widget.options.jsonConfig.scustomoptions[this.getProduct()];
            var ishortdatedlable = $widget.options.jsonConfig.sshortdatedlable[this.getProduct()];
            var icasepack = $widget.options.jsonConfig.scasepack[this.getProduct()];
            var ibrand = $widget.options.jsonConfig.sbrand[this.getProduct()];
            var icoldchain = $widget.options.jsonConfig.scoldchain[this.getProduct()];
            var iglutenfree = $widget.options.jsonConfig.sglutenfree[this.getProduct()];
            var ilatexfree = $widget.options.jsonConfig.slatexfree[this.getProduct()];
            var ipreservativefree = $widget.options.jsonConfig.spreservativefree[this.getProduct()];
            var idryfree = $widget.options.jsonConfig.sdryfree[this.getProduct()];
            var ibarcoded = $widget.options.jsonConfig.sbarcoded[this.getProduct()];
            var iconcentration = $widget.options.jsonConfig.sconcentration[this.getProduct()];
            var itotalcontent = $widget.options.jsonConfig.stotalcontent[this.getProduct()];
            var ishotdesc = $widget.options.jsonConfig.sshotdesc[this.getProduct()];
            var ishs = $widget.options.jsonConfig.sshs[this.getProduct()];
            var itheraputiccat = $widget.options.jsonConfig.stheraputiccat[this.getProduct()];
            var ifdarating = $widget.options.jsonConfig.sfdarating[this.getProduct()];
            var iadditioninfos = $widget.options.jsonConfig.sadditioninfos[this.getProduct()];
            var iwholesalerinfos = $widget.options.jsonConfig.swholesalerinfos[this.getProduct()];
            var isupportivedocs = $widget.options.jsonConfig.ssupportivedocs[this.getProduct()];
            var iwishlist = $widget.options.jsonConfig.swishlist[this.getProduct()];
            var iwishlisturl = $widget.options.jsonConfig.swishlisturl[this.getProduct()];


            if(iname != ''){
                $('.product-info-main [data-ui-id="page-title-wrapper"]').html(iname);
                var pname_class='.ajax-product-item-name-' + iparentid;
                if($(pname_class).length > 0) {
                    $(pname_class).html(iname);
                }
            }

            if(indcupc != ''){
                $('[itemprop="sku"]').html(indcupc);
                var ndc_class='.product-attribute-sku-' + iparentid;
                if($(ndc_class).length > 0) {
                    $(ndc_class).html(indcupc);
                }
            }

            if(iquantity != ''){
                var qty_class='#product-available-qty-value-' + iparentid;
                if($(qty_class).length > 0) {
                    $(qty_class).html(iquantity);
                }
            }

            if(iwishlist == 1){
                var wishlist_id='#product-wishlist-' + iparentid;
                if($(wishlist_id).length > 0) {
                    $(wishlist_id).addClass("active");
                }
            } else {
                var wishlist_id='#product-wishlist-' + iparentid;
                if($(wishlist_id).length > 0) {
                    $(wishlist_id).removeClass("active");
                }
            }

            if(iwishlisturl != ''){

                var wishlist_id='#product-wishlist-' + iparentid;
                if($(wishlist_id).length > 0) {
                    $(wishlist_id).attr('data-post', iwishlisturl);
                }
            }

            if(iaddtocarturl != ''){

                var form_id='#product_addtocart_form_' + iparentid;
                if($(form_id).length > 0) {
                    $(form_id).attr('action', iaddtocarturl);
                    $(form_id).attr('data-product-sku', indcupc);

                    $(form_id+' input[name=product]').val(product_id);
                }

                if($('#product_addtocart_form').length > 0) {
                    $('#product_addtocart_form').attr('action', iaddtocarturl);
                    $('#product_addtocart_form').attr('data-product-sku', indcupc);

                    $('#product_addtocart_form input[name=product]').val(product_id);
                }
            }



            if(ibrand != ''){
                var brand_class='.product-attribute-brand-' + iparentid;
                if($(brand_class).length > 0) {
                    $(brand_class).html(ibrand);
                }
            }

            if(icoldchain != ''){
                var coldchain_class='.product-attribute-cold-chain-' + iparentid;
                if($(coldchain_class).length > 0) {
                    $(coldchain_class).html(icoldchain);
                }
            }

            if(iglutenfree != ''){
                var glutenfree_class='.product-attribute-gluten-free-' + iparentid;
                if($(glutenfree_class).length > 0) {
                    $(glutenfree_class).html(iglutenfree);
                }
            }

            if(ilatexfree != ''){
                var latexfree_class='.product-attribute-latex-free-' + iparentid;
                if($(latexfree_class).length > 0) {
                    $(latexfree_class).html(ilatexfree);
                }
            }

            if(ipreservativefree != ''){
                var preservativefree_class='.product-attribute-preservative-free-' + iparentid;
                if($(preservativefree_class).length > 0) {
                    $(preservativefree_class).html(ipreservativefree);
                }
            }

            if(idryfree != ''){
                var dryfree_class='.product-attribute-dry-free-' + iparentid;
                if($(dryfree_class).length > 0) {
                    $(dryfree_class).html(idryfree);
                }
            }

            if(ibarcoded != ''){
                var barcoded_class='.product-attribute-barcoded-' + iparentid;
                if($(barcoded_class).length > 0) {
                    $(barcoded_class).html(ibarcoded);
                }
            }

            if(iconcentration != ''){
                var concentration_class='.product-attribute-concentration-' + iparentid;
                if($(concentration_class).length > 0) {
                    $(concentration_class).html(iconcentration);
                }
            }

            if(itotalcontent != ''){
                var totalcontent_class='.product-attribute-total-content-' + iparentid;
                if($(totalcontent_class).length > 0) {
                    $(totalcontent_class).html(itotalcontent);
                }
            }

            if(ishotdesc != ''){
                var shotdesc_class='.product-attribute-short-desc-' + iparentid;
                if($(shotdesc_class).length > 0) {
                    $(shotdesc_class).html(ishotdesc);
                }
            }

            if(ishs != ''){
                var shs_class='.product-attribute-shs-' + iparentid;
                if($(shs_class).length > 0) {
                    $(shs_class).html(ishs);
                }
            }

            if(itheraputiccat != ''){
                var theraputiccat_class='.product-attribute-tcat-' + iparentid;
                if($(theraputiccat_class).length > 0) {
                    $(theraputiccat_class).html(itheraputiccat);
                }
            }

            if(ifdarating != ''){
                var fdarating_class='.product-attribute-fda-' + iparentid;
                if($(fdarating_class).length > 0) {
                    $(fdarating_class).html(ifdarating);
                }
            }

            if(icustomprice != ''){
                //var main_price_class='#product-price-' + iparentid;
                var main_price_class='.product-info-main .product-info-price';
                var price_class='#product-final-general-price-' + iparentid;

                $(main_price_class).html(icustomprice);

                if($(price_class).length > 0) {
                    $(price_class).html(icustomprice);
                }
            }

            if(icasepack != ''){
                var casepack_class='#product-case-pack-' + iparentid;
                if($(casepack_class).length > 0) {
                    $(casepack_class).html(icasepack);
                }
            }

            if(iadditioninfos != ''){
                var additionalproductinfo_class='#additionalproductinfo';
                if($(additionalproductinfo_class).length > 0) {
                    $(additionalproductinfo_class+' .additional-links dd').html(iadditioninfos);
                }
            }

            if(iwholesalerinfos != ''){
                var wholesaleritem_class='#wholesaleritem';
                if($(wholesaleritem_class).length > 0) {
                    $(wholesaleritem_class+' .drl-wholesale').html(iwholesalerinfos);
                }
            }

            if(isupportivedocs != ''){
                var supportive_class='#supportive';
                if($(supportive_class).length > 0) {
                    $(supportive_class).html(isupportivedocs);
                }
            }

            var customoption_class='#customattributesshortdated-' + iparentid;
            if($(customoption_class).length > 0) {
                $(customoption_class).html(icustomoptions);
            }

            var shortdated_class='#ajax-short-dated-' + iparentid;
            if($(shortdated_class).length > 0) {
                if(ishortdatedlable == 1) {
                    $(shortdated_class).show();
                } else {
                    $(shortdated_class).hide();
                }
            }

            $widget._UpdatePrice();
            $widget._loadMedia();
            $input.trigger('change');
        },

        /**
         * Render select by part of config
         *
         * @param {Object} config
         * @param {String} chooseText
         * @returns {String}
         * @private
         */
        _RenderSwatchSelect: function (config, chooseText) {
            var html;

            if (this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            html =
                '<select class="' + this.options.classes.selectClass + ' ' + config.code + '">';
                /* +
                '<option value="0" data-option-id="0">' + chooseText + '</option>';*/
            //console.log(config.options.length);

            var inc = 1;

            $.each(config.options, function () {
                if(inc == config.options.length){
                    var label = this.label,
                        attr = ' value="' + this.id + '" selected="selected" data-option-id="' + this.id + '"';
                } else {
                    /*if(inc == 1){
                         var label = this.label,
                            attr = ' value="' + this.id + '" selected="selected" data-option-id="' + this.id + '"';

                    } else {*/
                        var label = this.label,
                            attr = ' value="' + this.id + '" data-option-id="' + this.id + '"';
                    //}
                }

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' data-option-empty="true"';
                }

                html += '<option ' + attr + '>' + label + '</option>';

                inc++;
            });

            html += '</select>';

            return html;
        }

    });

  return $.ecomm.SwatchRenderer;
});