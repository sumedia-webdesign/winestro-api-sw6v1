!function(t){var i={};function e(n){if(i[n])return i[n].exports;var r=i[n]={i:n,l:!1,exports:{}};return t[n].call(r.exports,r,r.exports,e),r.l=!0,r.exports}e.m=t,e.c=i,e.d=function(t,i,n){e.o(t,i)||Object.defineProperty(t,i,{enumerable:!0,get:n})},e.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},e.t=function(t,i){if(1&i&&(t=e(t)),8&i)return t;if(4&i&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(e.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&i&&"string"!=typeof t)for(var r in t)e.d(n,r,function(i){return t[i]}.bind(null,r));return n},e.n=function(t){var i=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(i,"a",i),i},e.o=function(t,i){return Object.prototype.hasOwnProperty.call(t,i)},e.p=(window.__sw__.assetPath + '/bundles/sumediawbo/'),e(e.s="18cw")}({"18cw":function(t,i){Shopware.Component.override("sw-settings-shipping-price-matrix",{data:function(){return{calculationTypes:[{label:this.$tc("sw-settings-shipping.priceMatrix.calculationLineItemCount"),value:1},{label:this.$tc("sw-settings-shipping.priceMatrix.calculationPrice"),value:2},{label:this.$tc("sw-settings-shipping.priceMatrix.calculationWeight"),value:3},{label:this.$tc("sw-settings-shipping.priceMatrix.calculationVolume"),value:4},{label:"Flaschen",value:5}],showDeleteModal:!1,isLoading:!1}},computed:{wboArticlesRepository:function(){return this.repositoryFactory.create("wbo_articles")},labelQuantityStart:function(){return{1:"sw-settings-shipping.priceMatrix.columnQuantityStart",2:"sw-settings-shipping.priceMatrix.columnPriceStart",3:"sw-settings-shipping.priceMatrix.columnWeightStart",4:"sw-settings-shipping.priceMatrix.columnVolumeStart",5:"Von"}[this.priceGroup.calculation]||"sw-settings-shipping.priceMatrix.columnQuantityStart"},labelQuantityEnd:function(){return{1:"sw-settings-shipping.priceMatrix.columnQuantityEnd",2:"sw-settings-shipping.priceMatrix.columnPriceEnd",3:"sw-settings-shipping.priceMatrix.columnWeightEnd",4:"sw-settings-shipping.priceMatrix.columnVolumeEnd",5:"Bis"}[this.priceGroup.calculation]||"sw-settings-shipping.priceMatrix.columnQuantityEnd"},numberFieldType:function(){return{1:"int",2:"float",3:"float",4:"float",5:"int"}[this.priceGroup.calculation]||"float"}}})}});