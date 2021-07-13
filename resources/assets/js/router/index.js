import VueRouter from 'vue-router'

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'index',
            component: () => import('../pages')
        },
    ],
});

export default router;
