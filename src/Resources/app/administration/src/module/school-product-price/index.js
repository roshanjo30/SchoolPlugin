import './page/school-list';
import './page/school-detail';

const { Module } = Shopware;
Module.register('school-product-price', {
    type: 'plugin',
    name: 'school-product-price',
    title: 'School Product Prices',
    description:'Manage school specific product prices',
    color: '#189eff',
    routes: {
        list: {
            component:
                'school-product-price-list',
            path: 'list'
        },
        detail: {
            component: 'school-product-price-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'school-product-price.list'
            }
        }
    },

    navigation: [
        {
            id: 'school-product-price',
            label: 'School Prices',
            color: '#189eff',
            path:'school.product.price.list',
            parent:'sw-catalogue',
            position: 100
        }
    ]
});