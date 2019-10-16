import Vue from 'vue'
import VueRouter from 'vue-router'
import iview from 'iview'
import 'iview/dist/styles/iview.css'
import App from './App.vue'
import routes from './router.js'
Vue.use(VueRouter);
Vue.use(iview);
const router = new VueRouter({
  routes :routes
})
window.app=new Vue({
    router:router,
    data:{e:new Vue(),server:"https://gxicon.e123.pw/api.php?",passport:"https://passport.e123.pw/"},
    render: h => h(App),
}).$mount('#app');
router.afterEach(function(route){
    try{
      _hmt.push(['_trackPageview',route.path]);
    }catch(e){}
});