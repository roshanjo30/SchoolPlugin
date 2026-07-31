import template from './school-list.html.twig';

const { Component } = Shopware;

Component.register('school-list', {
    template,

    inject: [
        'repositoryFactory',
    ],

    data() {
        return {
            schools: [],
            isLoading: false,
    
            columns: [
                {
                    property: 'schoolName',
                    label: 'School Name',
                },
                {
                    property: 'contactPerson',
                    label: 'Contact Person',
                },
                {
                    property: 'email',
                    label: 'Email',
                },
                {
                    property: 'city',
                    label: 'City',
                },
                {
                    property: 'status',
                    label: 'Status',
                },
            ],
        };
    },

    computed: {
        schoolRepository() {
            return this.repositoryFactory.create('school');
        },
    },

    created() {
        this.loadSchools();
    },

    methods: {

        async loadSchools() {
            this.isLoading = true;
    
            const criteria = new Shopware.Data.Criteria();


            criteria.addAssociation('logoMedia');
    
            this.schools = await this.schoolRepository.search(
                criteria,
                Shopware.Context.api
            );
    
            this.isLoading = false;
        },
    
    
        onRowClick(school) {
            this.$router.push({
                name: 'school.plugin.detail',
                params: {
                    id: school.id,
                },
            });
        },
    
    },
});