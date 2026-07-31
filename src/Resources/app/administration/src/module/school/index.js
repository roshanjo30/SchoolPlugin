import './page/school-list';
import './page/school-detail';

import enGB from '../../snippet/en-GB.json';
import deDE from '../../snippet/de-DE.json';

const { Module } = Shopware;

Module.register('school-plugin', {
    type: 'plugin',

    name: 'school-plugin',

    title: 'school.general.mainMenuItem',

    description: 'school.general.mainMenuItem',

    color: '#189eff',

    icon: 'regular-school',

    snippets: {
        'en-GB': enGB,
        'de-DE': deDE,
    },

    routes: {
        listing: {
            component: 'school-list',
            path: 'listing',
        },

        detail: {
            component: 'school-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'school.plugin.listing',
            },
        },

        create: {
            component: 'school-detail',
            path: 'create',
            meta: {
                parentPath: 'school-plugin.listing',
            },
        },
    },

    settingsItem: [
        {
            group: 'plugins',
            to: 'school.plugin.listing',
            icon: 'regular-school',
            label: 'school.general.mainMenuItem',
        },
    ],
});