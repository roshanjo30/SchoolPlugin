import template from './school-detail.html.twig';
import './school-detail.scss';

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
            schoolRepository: null,

            searchTerm: '',
            isLoading: false
        };
    },


    computed: {

        columns() {
            return [
                {
                    property: 'name',
                    label: 'Product',
                    primary: true
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
        },


        filteredProducts() {

            if (!this.searchTerm) {
                return this.products;
            }


            const term = this.searchTerm.toLowerCase();


            return this.products.filter(product => {

                return (
                    product.name?.toLowerCase().includes(term)
                    ||
                    product.productNumber?.toLowerCase().includes(term)
                );

            });

        }

    },


    created() {

        this.schoolRepository =
            this.repositoryFactory.create('school');


        this.loadSchool();

    },


    methods: {


        onSearch(value) {

            this.searchTerm = value;

        },


        async loadSchool() {

            try {

                const criteria = new Criteria();

                criteria.addAssociation('category');


                const school = await this.schoolRepository.get(
                    this.$route.params.id,
                    Shopware.Context.api,
                    criteria
                );


                this.school = school;


                await this.loadProducts();


            } catch (error) {

                console.error(error);


                this.createNotificationError({
                    title: 'Error',
                    message: 'Could not load school.'
                });

            }

        },

        increasePrice(product) {

            let price = Number(product.schoolPrice ?? 0);
        
            product.schoolPrice = price + 1;
        
        },
        
        
        decreasePrice(product) {
        
            let price = Number(product.schoolPrice ?? 0);
        
            price = price - 1;
        
            if (price < 0) {
                price = 0;
            }
        
            product.schoolPrice = price;
        
        },


        async loadProducts() {

            if (!this.school) {
                return;
            }


            this.isLoading = true;


            try {

                const httpClient =
                    Shopware.Application
                        .getContainer('init')
                        .httpClient;



                const response = await httpClient.get(
                    `_action/school-product-price/products/${this.school.id}`,
                    {
                        headers: {
                            Authorization:
                                `Bearer ${this.loginService.getToken()}`
                        }
                    }
                );


                this.products = response.data;


            } catch (error) {

                console.error(error);

                this.products = [];


                this.createNotificationError({
                    title: 'Error',
                    message: 'Could not load products.'
                });


            } finally {

                this.isLoading = false;

            }

        },


        async savePrices() {


            const prices = this.products.map(product => {

                return {
                    productId: product.id,
                    price: Number(product.schoolPrice)
                };

            });



            try {


                const httpClient =
                    Shopware.Application
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
                            Authorization:
                                `Bearer ${this.loginService.getToken()}`,
                            'Content-Type': 'application/json'
                        }
                    }
                );



                this.createNotificationSuccess({
                    title: 'Success',
                    message: 'School prices saved successfully.'
                });



                await this.loadProducts();



            } catch (error) {


                console.error(error);


                this.createNotificationError({
                    title: 'Error',
                    message: 'Could not save school prices.'
                });


            }

        }

    }
});