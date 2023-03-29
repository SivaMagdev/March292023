require(
    [
        'Magento_Ui/js/lib/validation/validator',
        'jquery',
        'mage/translate'
], function(validator, $){

        validator.addRule(
            'video-validation',
            function (value) {
                if(value == ''){
                    return false;
                }else{
                    return true;
                }
            }
            ,$.mage.__('Please Enter the value')
        );
});