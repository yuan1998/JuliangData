import Vue                      from 'vue';
import VueClipboard             from 'vue-clipboard2'
import MyBus                    from './Utils/Vue-Bus';
import PullAdvertiserPlanData   from './components/actions/PullAdvertiserPlanData';
import ExportAdvertiserPlanData from './components/actions/ExportAdvertiserPlanData';
import ModelGenerateAuthurl     from './components/Modal/ModelGenerateAuthurl';
import ButtonGenerateAuthUrl    from './components/Buttons/ButtonGenerateAuthUrl';
import PageAccountPlanSum       from './components/Pages/AccountPlanSum';

MyBus(Vue);
window.Vue                           = Vue;
VueClipboard.config.autoSetContainer = true; // add this line
Vue.use(VueClipboard);

// Vue.use(ElementUI);
Vue.component('action-pull-advertiser-plan-data', PullAdvertiserPlanData);
Vue.component('action-export-advertiser-plan-data', ExportAdvertiserPlanData);
Vue.component('modal-generate-auth-url', ModelGenerateAuthurl);
Vue.component('button-generate-auth-url', ButtonGenerateAuthUrl);
Vue.component('page-account-plan-sum', PageAccountPlanSum);
