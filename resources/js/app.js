import Vue                      from 'vue';
import PullAdvertiserPlanData   from './components/actions/PullAdvertiserPlanData';
import ExportAdvertiserPlanData from './components/actions/ExportAdvertiserPlanData';

window.Vue = Vue;


// Vue.use(ElementUI);
Vue.component('action-pull-advertiser-plan-data', PullAdvertiserPlanData);
Vue.component('action-export-advertiser-plan-data', ExportAdvertiserPlanData);
