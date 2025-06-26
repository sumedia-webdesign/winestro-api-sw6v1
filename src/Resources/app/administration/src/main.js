//let priceMatrix = Shopware.Component.('sw-settings-shipping-price-matrix');
Shopware.Component.override('sw-settings-shipping-price-matrix', {
    data() {
        return {
            calculationTypes: [
                { label: this.$tc('sw-settings-shipping.priceMatrix.calculationLineItemCount'), value: 1 },
                { label: this.$tc('sw-settings-shipping.priceMatrix.calculationPrice'), value: 2 },
                { label: this.$tc('sw-settings-shipping.priceMatrix.calculationWeight'), value: 3 },
                { label: this.$tc('sw-settings-shipping.priceMatrix.calculationVolume'), value: 4 },
                { label: 'Flaschen', value: 5 }
            ],
            showDeleteModal: false,
            isLoading: false,
        };
    },

    computed: {
        wboArticlesRepository() {
            return this.repositoryFactory.create('wbo_articles');
        },

        labelQuantityStart() {
            const calculationType = {
                1: 'sw-settings-shipping.priceMatrix.columnQuantityStart',
                2: 'sw-settings-shipping.priceMatrix.columnPriceStart',
                3: 'sw-settings-shipping.priceMatrix.columnWeightStart',
                4: 'sw-settings-shipping.priceMatrix.columnVolumeStart',
                5: 'Von'
            };

            return calculationType[this.priceGroup.calculation]
                || 'sw-settings-shipping.priceMatrix.columnQuantityStart';
        },

        labelQuantityEnd() {
            const calculationType = {
                1: 'sw-settings-shipping.priceMatrix.columnQuantityEnd',
                2: 'sw-settings-shipping.priceMatrix.columnPriceEnd',
                3: 'sw-settings-shipping.priceMatrix.columnWeightEnd',
                4: 'sw-settings-shipping.priceMatrix.columnVolumeEnd',
                5: 'Bis'
            };

            return calculationType[this.priceGroup.calculation]
                || 'sw-settings-shipping.priceMatrix.columnQuantityEnd';
        },

        numberFieldType() {
            const calculationType = {
                1: 'int',
                2: 'float',
                3: 'float',
                4: 'float',
                5: 'int'
            };

            return calculationType[this.priceGroup.calculation]
                || 'float';
        },
    }
});