import template from './school-detail.html.twig';

const { Criteria } = Shopware.Data;
const { Mixin } = Shopware;

Shopware.Component.register('school-product-price-detail', {
    template,

    inject: [
        'repositoryFactory',
        'loginService'
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            school: null,
            products: [],
            schoolRepository: null
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'name',
                    label: 'Product'
                },
                {
                    property: 'productNumber',
                    label: 'Product Number'
                },
                {
                    property: 'defaultPrice',
                    label: 'Shopware Price'
                },
                {
                    property: 'schoolPrice',
                    label: 'School Price'
                }
            ];
        }
    },

    created() {
        this.schoolRepository = this.repositoryFactory.create('school');
        this.loadSchool();
    },

    methods: {
        async loadSchool() {
            const criteria = new Criteria();
            criteria.addAssociation('category');

            const school = await this.schoolRepository.get(
                this.$route.params.id,
                Shopware.Context.api,
                criteria
            );

            this.school = school;
            this.loadProducts();
        },

        async loadProducts() {
            try {
                const httpClient = Shopware.Application
                    .getContainer('init')
                    .httpClient;

                const response = await httpClient.get(
                    `_action/school-product-price/products/${this.school.id}`,
                    {
                        headers: {
                            Authorization: `Bearer ${this.loginService.getToken()}`
                        }
                    }
                );

                this.products = response.data;
            } catch (error) {
                console.error(error);
                this.products = [];
            }
        },

        async savePrices() {
            const prices = this.products.map(product => {
                return {
                    productId: product.id,
                    price: Number(product.schoolPrice)
                };
            });

            const httpClient = Shopware.Application
                .getContainer('init')
                .httpClient;

            await httpClient.post(
                '_action/school-product-price/save',
                {
                    schoolId: this.school.id,
                    prices
                },
                {
                    headers: {
                        Authorization: `Bearer ${this.loginService.getToken()}`,
                        'Content-Type': 'application/json'
                    }
                }
            );

            this.createNotificationSuccess({
                title: 'Success',
                message: 'School prices saved successfully.'
            });

            this.loadProducts();
        }
    }
});