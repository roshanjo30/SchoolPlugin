import template from './school-list.html.twig';


const { Criteria } = Shopware.Data;


Shopware.Component.register(
    'school-product-price-list',
{

    template,


    inject: [
        'repositoryFactory'
    ],


    data() {

        return {

            schools: [],

            repository: null

        };

    },


    created() {

        this.repository =
            this.repositoryFactory.create(
                'school'
            );


        this.loadSchools();

    },


    methods: {


        loadSchools() {

            const criteria =
                new Criteria();


            this.repository
                .search(
                    criteria,
                    Shopware.Context.api
                )
                .then(result => {

                    this.schools = result;

                });

        },


        openSchool(school) {

            this.$router.push({
                name: 'school.product.price.detail',
                params: {
                    id: school.id
                }
            });
        
        }
    }


});