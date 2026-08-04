import template from './school-detail.html.twig';

const { Component, Mixin } = Shopware;

Component.register('school-detail', {
    template,

    inject: [
        'repositoryFactory',
    ],
    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            school: null,
            originalStatus: null,
            selectedStatus: null,
            isLoading: false,
        };
    },

    computed: {
        schoolRepository() {
            return this.repositoryFactory.create('school');
        },

        schoolId() {
            return this.$route.params.id;
        },

        statusOptions() {
            return [
                {
                    label: this.$tc('school.status.disabled'),
                    value: 'disabled',
                },
                {
                    label: this.$tc('school.status.approved'),
                    value: 'approved',
                },
            ];
        },
    },

    created() {
        this.loadSchool();
    },

    methods: {
        getHttpClient() {
            return Shopware.Application.getContainer('init').httpClient;
        },

        getAuthHeaders() {
            const token = Shopware.Context.api.authToken?.access;
            return {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json',
            };
        },

        async loadSchool() {
            this.isLoading = true;
            try {
                const criteria = new Shopware.Data.Criteria();
                criteria.addAssociation('logoMedia');
                this.school = await this.schoolRepository.get(
                    this.schoolId,
                    Shopware.Context.api,
                    criteria
                );

                this.selectedStatus = this.school.status;
                this.originalStatus = this.school.status;
            } catch (error) {
                console.error('Could not load school', error);
                this.createNotificationError({
                    title: 'Error',
                    message: 'Could not load school.',
                });
            } finally {
                this.isLoading = false;
            }
        },

        async saveSchool() {
            this.isLoading = true;

            try {
                if (this.selectedStatus !== this.originalStatus) {
                    if (this.selectedStatus === 'approved') {
                        await this.getHttpClient().post(
                            `_action/school/${this.school.id}/approve`,
                            {},
                            { headers: this.getAuthHeaders() }
                        );
                    } else if (this.selectedStatus === 'disabled') {
                        await this.getHttpClient().post(
                            `_action/school/${this.school.id}/disable`,
                            {},
                            { headers: this.getAuthHeaders() }
                        );
                    }

                    await this.loadSchool();
                }

                this.createNotificationSuccess({
                    title: this.$tc('school.notifications.successTitle'),
                    message: this.$tc('school.notifications.saveSuccess'),
                });
            } catch (error) {
                console.error(error);
                this.createNotificationError({
                    title: this.$tc('school.notifications.errorTitle'),
                    message: this.$tc('school.notifications.saveError'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        cancelEdit() {
            this.$router.push({ name: 'school.plugin.listing' });
        },
    },
});