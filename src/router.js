import home from './components/page_home.vue'
import search from './components/page_search.vue'
import upload from './components/page_upload.vue'
import share from './components/page_share.vue'
import author from './components/page_author.vue'
import diy from './components/page_diy.vue'
import diys from './components/page_diy_status.vue'
import donate from './components/page_donate.vue'
import nanoQuery from './components/page_nano_query.vue'
const routers = [
    { path: '/', component: nanoQuery },
    { path: '/search', component: search },
    { path: '/search/:package', component: search },
    { path: '/share/:id', component: share },
    { path: '/author/:id', component: author },
    { path: '/upload', component: upload },
    { path: '/nano/query', component: nanoQuery },
    { path: '/diy', component: diy },
    { path: '/diy/:p/:c/:id', component: diys },
    { path: '/donate', component: donate },
];
export default routers;