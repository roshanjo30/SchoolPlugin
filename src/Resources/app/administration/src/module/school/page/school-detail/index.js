import template from './school-detail.html.twig';

const { Component } = Shopware;

Component.register('school-detail', {
    template,

    inject: [
        'repositoryFactory',
    ],

    data() {
        return {
            school: null,
            isLoading: false,
            actionMessage: '',
            actionVariant: 'success',
        };
    },

    computed: {
        schoolRepository() {
            return this.repositoryFactory.create('school');
        },

        schoolId() {
            return this.$route.params.id;
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

        notify(variant, title, message) {
            this.actionMessage = message;
            this.actionVariant = variant;

            try {
                Shopware.State.dispatch('notification/createNotification', {
                    variant,
                    title,
                    message,
                });
            } catch (error) {
                console.warn('Toast notification failed, using inline message only', error);
            }
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
            } catch (error) {
                console.error('Could not load school', error);
                this.notify('error', 'Error', 'Could not load school.');
            } finally {
                this.isLoading = false;
            }
        },

        async approveSchool() {
            this.isLoading = true;
            try {
                await this.getHttpClient().post(
                    `_action/school/${this.school.id}/approve`,
                    {},
                    { headers: this.getAuthHeaders() }
                );
                this.notify('success', 'Success', 'School approved successfully.');
                await this.loadSchool();
            } catch (error) {
                console.error('Could not approve school', error.response?.data || error);
                this.notify('error', 'Error', 'Could not approve school.');
            } finally {
                this.isLoading = false;
            }
        },

        async disableSchool() {
            this.isLoading = true;
            try {
                await this.getHttpClient().post(
                    `_action/school/${this.school.id}/disable`,
                    {},
                    { headers: this.getAuthHeaders() }
                );
                this.notify('success', 'Success', 'School disabled successfully.');
                await this.loadSchool();
            } catch (error) {
                console.error('Could not disable school', error.response?.data || error);
                this.notify('error', 'Error', 'Could not disable school.');
            } finally {
                this.isLoading = false;
            }
        },
    },
});