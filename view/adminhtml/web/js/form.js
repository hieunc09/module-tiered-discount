define([
    'jquery',
    'uiRegistry',
    'mage/translate'
], function ($, uiRegistry) {
    var tieredDiscountForm = {
        update: function () {
            var selector = $('[data-index="simple_action"] select');
            var action = selector.val();
            var discountQty = $('[data-index="discount_qty"]');
            var discountStep = $('[data-index="discount_step"]');
            var firstTieredDiscountStep = $('[data-index="tdrule[spent_x]"]');
            var firstTieredDiscountAmount = $('[data-index="tdrule[get_y]"]');
            var secondTieredDiscountStep = $('[data-index="tdrule[spent_w]"]');
            var secondTieredDiscountAmount = $('[data-index="tdrule[get_z]"]');

            this.checkFieldsValue();

            if (action === 'tiered_discount') {
                this.hideElement(discountQty);
                this.hideElement(discountStep);
                this.showElement(firstTieredDiscountStep);
                this.showElement(firstTieredDiscountAmount);
                this.showElement(secondTieredDiscountStep);
                this.showElement(secondTieredDiscountAmount);
            } else {
                this.showElement(discountQty);
                this.showElement(discountStep);
                this.hideElement(firstTieredDiscountStep);
                this.hideElement(firstTieredDiscountAmount);
                this.hideElement(secondTieredDiscountStep);
                this.hideElement(secondTieredDiscountAmount);
            }
        },

        checkFieldsValue: function () {
            var discountQty = uiRegistry.get('sales_rule_form.sales_rule_form.actions.discount_qty'),
                discountStep = uiRegistry.get('sales_rule_form.sales_rule_form.actions.discount_step');

            if (discountQty.value() < 0) {
                discountQty.value(0);
            }

            if (discountStep.value() == 0 || discountStep.value() == '') {
                discountStep.value(1);
            }
        },

        showElement: function (name) {
            name.show();
        },

        hideElement: function (name) {
            name.hide();
        }
    };

    return tieredDiscountForm;
});
