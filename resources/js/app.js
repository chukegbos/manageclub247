require('./bootstrap');

window.Vue = require('vue');

import moment from 'moment';
import { Form, HasError, AlertError, AlertSuccess } from 'vform';
import VueProgressBar from 'vue-progressbar';
import JsonCSV from 'vue-json-csv';

Vue.component('downloadCsv', JsonCSV);

Vue.use(VueProgressBar, {
	color: 'rgb(143, 255, 199)',
	failedColor: 'red',
	height: '4px'
});

window.Form = Form;

Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);
Vue.component(AlertSuccess.name, AlertSuccess)

import VueFormWizard from 'vue-form-wizard'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
Vue.use(VueFormWizard)

Vue.component('pagination', require('laravel-vue-pagination'));
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';
// Install BootstrapVue
Vue.use(BootstrapVue);
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin);

import Swal from 'sweetalert2';
window.Swal = Swal;

const Toast = Swal.mixin({
	toast: true,
	position: 'top-end',
	showConfirmButton: false,
	timer: 7000,
	timerProgressBar: true,
	onOpen: (toast) => {
		toast.addEventListener('mouseenter', Swal.stopTimer);
		toast.addEventListener('mouseleave', Swal.resumeTimer);
	}
});

window.Toast = Toast;

let Fuse = new Vue();
window.Fuse = Fuse;

import CKEditor from 'ckeditor4-vue';
Vue.use(CKEditor);

import Vue from 'vue'
import vSelect from 'vue-select'
Vue.component('v-select', vSelect)
import 'vue-select/dist/vue-select.css';

Vue.component('vue-typeahead-bootstrap', VueTypeaheadBootstrap)
import VueHtmlToPaper from 'vue-html-to-paper';

const options = {
  name: '_blank',
  specs: [
    'fullscreen=yes',
    'titlebar=yes',
    'scrollbars=yes'
  ],
  styles: [
    'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
    'https://unpkg.com/kidlat-css/css/kidlat.css',
    '/css/print.css'
  ]
}

Vue.use(VueHtmlToPaper, options);


import DashboardComponent from './components/DashboardComponent.vue';
import PinComponent from './components/PinComponent.vue';
import PasswordComponent from './components/PasswordComponent.vue';
import CustomerComponent from './components/CustomerComponent.vue';
import CustomerSalesComponent from './components/CustomerSalesComponent.vue';
import CustomerCreditComponent from './components/CustomerCreditComponent.vue';
import CustomerFundComponent from './components/CustomerFundComponent.vue';
import CustomerReportComponent from './components/CustomerReportComponent.vue';
import CustomerStatementComponent from './components/CustomerStatementComponent.vue';
import CustomerCreateComponent from './components/CustomerCreateComponent.vue';
import CustomerViewComponent from './components/CustomerViewComponent.vue';
import StaffComponent from './components/StaffComponent.vue';
import InventoryComponent from './components/InventoryComponent.vue';
import InventoryCategoryComponent from './components/InventoryCategoryComponent.vue';
import SettingComponent from './components/SettingComponent.vue';
import ProfileComponent from './components/ProfileComponent.vue';


import ShoppingCartComponent from './components/ShoppingCartComponent.vue';
import InvoiceComponent from './components/InvoiceComponent.vue';
import OrderComponent from './components/OrderComponent.vue';
import ReceiptComponent from './components/ReceiptComponent.vue';
import FundReceiptComponent from './components/FundReceiptComponent.vue';


import OrderViewComponent from './components/OrderViewComponent.vue';
import QuoteComponent from './components/QuoteComponent.vue';
import SuppliersComponent from './components/SuppliersComponent.vue';
import ProductionComponent from './components/ProductionComponent.vue';
import ProductionUsedComponent from './components/ProductionUsedComponent.vue';

import ProductionFormComponent from './components/ProductionFormComponent.vue';
import ProductionUsedFormComponent from './components/ProductionUsedFormComponent.vue';
import ViewProductionComponent from './components/ViewProductionComponent.vue';
import ViewProcessComponent from './components/ViewProcessComponent.vue';
import BalanceComponent from './components/BalanceComponent.vue';
import ChartAccountComponent from './components/ChartAccountComponent.vue';
import TypeAccountComponent from './components/TypeAccountComponent.vue';
import TaxAccountComponent from './components/TaxAccountComponent.vue';
import LedgerComponent from './components/LedgerComponent.vue';
import WalletFundingComponent from './components/WalletFundingComponent.vue';
import ReportComponent from './components/ReportComponent.vue';
import TrialBalanceComponent from './components/TrialBalanceComponent.vue';
import BalanceSheetComponent from './components/BalanceSheetComponent.vue';
import ProfitLossComponent from './components/ProfitLossComponent.vue';
import OrderDebtorComponent from './components/OrderDebtorComponent.vue';
import SupplierDebtorComponent from './components/SupplierDebtorComponent.vue';
import DebtorViewComponent from './components/DebtorViewComponent.vue';
import PurchasesComponent from './components/PurchasesComponent.vue';
import AllpurchasesComponent from './components/AllpurchasesComponent.vue';
import PurchaseFormComponent from './components/PurchaseFormComponent.vue';
import PurchaseFormNonComponent from './components/PurchaseFormNonComponent.vue';
import PurchaseFormCreditComponent from './components/PurchaseFormCreditComponent.vue';
import ViewPurchaseComponent from './components/ViewPurchaseComponent.vue';
import OpeningBalanceComponent from './components/OpeningBalanceComponent.vue';
import CreditUnitComponent from './components/CreditUnitComponent.vue';

let routes = [
    {
        path: '/home',
        component: DashboardComponent
    },

    {
        path: '/pin',
        component: PinComponent
    },

    {
        path: '/password',
        component: PasswordComponent
    },


    {
        name: 'Customers',
        path: '/admin/customers',
        component: CustomerComponent
    },

    {
        name: 'allpurchases',
        path: '/admin/allpurchases',
        component: AllpurchasesComponent
    },

    {
        path: '/admin/customers/create',
        component: CustomerCreateComponent
    },

    {
        path: '/admin/customers/credit',
        component: CustomerCreditComponent
    },

    {
        path: '/admin/customers/fund',
        component: CustomerFundComponent
    },

    {
        name: 'wallet-funding',
        path: '/admin/customers/fund-history',
        component: WalletFundingComponent
    },

    {
        path: '/admin/customers/reports',
        component: CustomerReportComponent
    },

    {
        path: '/admin/funding/:ref_id',
        component: FundReceiptComponent
    },

    {
        path: '/debtors',
        component: OrderDebtorComponent
    },

    {
        path: '/store/debtors/:sale_id',
        component: DebtorViewComponent
    },

    {
        path: '/admin/customers/create/:id',
        component: CustomerCreateComponent
    },

    {
        name: 'statement',
        path: '/customer/statement',
        component: CustomerStatementComponent
    },

    {
        path: '/customer/statement/:id',
        component: CustomerStatementComponent
    },

    {
        name: 'sales',
        path: '/customer/sales',
        component: CustomerSalesComponent
    },

    {
        path: '/customer/sales/:id',
        component: CustomerSalesComponent
    },

    {
        path: '/admin/customers/:unique_id',
        component: CustomerViewComponent
    },

    {
        path: '/admin/staff',
        component: StaffComponent
    },

    {
        name: 'Inventory',
        path: '/admin/inventory',
        component: InventoryComponent
    },

    {
        path: '/admin/inventory/category',
        component: InventoryCategoryComponent
    },

    {
        path: '/admin/inventory/:id',
        component: InventoryComponent
    },
    
    {
        path: '/admin/setting',
        component: SettingComponent
    },

    {
        name: 'purchases',
        path: '/admin/purchases',
        component: PurchasesComponent
    },

    {
        path: '/admin/purchase/create',
        component: PurchaseFormComponent
    },

    {
        path: '/admin/purchase/:purchase_id',
        component: PurchaseFormComponent
    },

    {
        path: '/admin/purchase/nonitem',
        component: PurchaseFormNonComponent
    },

    {
        path: '/admin/purchase/createcredit',
        component: PurchaseFormCreditComponent
    },

    {
        path: '/admin/purchases/:id',
        component: ViewPurchaseComponent
    },

    {
        path: '/admin/user/:id',
        component: ProfileComponent
    },

    {
        name: 'sell',
        path: '/sale/shopping-cart',
        component: ShoppingCartComponent
    },

    {
        path: '/sale/shopping-cart/:sale_id',
        component: ShoppingCartComponent
    },

    {
        name: 'Invoice',
        path: '/sale/invoice',
        component: InvoiceComponent
    },

    {
        path: '/sale/quote',
        component: QuoteComponent
    },

    {
        name: 'Transactions',
        path: '/sale/orders',
        component: OrderComponent
    },

    {
        path: '/sale/orders/:buyer',
        component: OrderComponent
    },

    {
        path: '/sale/orders/:store_id',
        component: OrderComponent
    },

    {
        path: '/orderview/:sale_id',
        component: OrderViewComponent
    },

    {
        path: '/receipt/:sale_id',
        component: ReceiptComponent
    },

    {
        path: '/production',
        component: ProductionComponent
    }, 

    {
        path: '/production/used',
        component: ProductionUsedComponent
    },   

    {
        path: '/production/used/create',
        component: ProductionUsedFormComponent
    },

    {
        path: '/production/create',
        component: ProductionFormComponent
    },

    {
        path: '/production/:prod_id',
        component: ViewProductionComponent
    },


    {
        path: '/process/:prod_id',
        component: ViewProcessComponent
    },

    {
        path: '/account/balance',
        component: BalanceComponent
    },
    {
        path: '/account/chart-of-account',
        component: ChartAccountComponent
    },

    {
        path: '/account/wallet-funding',
        component: WalletFundingComponent
    },

    {
        path: '/account/trial-balance',
        component: TrialBalanceComponent
    },
    {
        path: '/account/profit-and-loss',
        component: ProfitLossComponent
    },

    {
        path: '/account/balance-sheet',
        component: BalanceSheetComponent
    },

    {
        path: '/account/type',
        component: TypeAccountComponent
    },

    {
        path: '/account/tax',
        component: TaxAccountComponent
    },

    {
        path: '/account/reports',
        component: ReportComponent
    },

    {
        path: '/account/ledger',
        component: LedgerComponent
    },

    {
        path: '/account/ledger/:ref_id',
        component: LedgerComponent
    },

    {
        path: '/suppliers',
        component: SuppliersComponent
    },

    {
        path: '/suppliers/debt',
        component: SupplierDebtorComponent
    },

    {
        path: '/company/opening-balance',
        component: OpeningBalanceComponent
    },

    {
        path: '/company/creditunit',
        component: CreditUnitComponent
    },

    { path: '/404', component: require('./components/404.vue').default },
    { path: '/outlets', component: require('./components/OutletComponent.vue').default },
    { path: '/outlets/:code', component: require('./components/OutletStoreComponent.vue').default },
    { path: '/rooms/:code', component: require('./components/OutletRoomComponent.vue').default },
    { path: '/account/company-fund', component: require('./components/CompanyFundComponent.vue').default },
    { path: '/account/journal', component: require('./components/JournalComponent.vue').default },
    { path: '/sales/:trans_id', component: require('./components/SalesComponent.vue').default },
    { path: '/movements/outlets', component: require('./components/OutletMovementComponent.vue').default },
    { path: '/movements/outletmovement', component: require('./components/AllOutletMovementComponent.vue').default },
    { path: '/movements/outletRequest', component: require('./components/AllOutletRequestComponent.vue').default },
    { path: '/movements/myoutlet', name: 'mymovement', component: require('./components/MyOutletMovementComponent.vue').default },
    { path: '/movements/myrequest', name: 'myrequest', component: require('./components/MyRequestMovementComponent.vue').default },
    { path: '/movements/intra-outlets', component: require('./components/IntraOutletComponent.vue').default },
    { path: '/store-movements/:ref_id', component: require('./components/CompleteMoveComponent.vue').default },
    { path: '/storerequest/:ref_id', component: require('./components/CompleteRequestComponent.vue').default },
    { path: '/inventory/breakdown/:code', component: require('./components/BreakdownComponent.vue').default },
    { path: '/payment', component: require('./components/PaymentComponent.vue').default },
    { path: '/payment/products', component: require('./components/PaymentProductsComponent.vue').default },
    { path: '/payment/channels', component: require('./components/PaymentChannelComponent.vue').default },
    { path: '/payment/banks', component: require('./components/PaymentBankComponent.vue').default },
    { path: '/payment/pos', component: require('./components/PaymentPosComponent.vue').default },
    { path: '/payment/debits', component: require('./components/PaymentDebitComponent.vue').default },
    { path: '/payment/reciept/:id', component: require('./components/PaymentReceiptComponent.vue').default },
    { path: '/members/types', component: require('./components/MemberTypeComponent.vue').default },
    { path: '/members/sections', component: require('./components/MemberSectionComponent.vue').default },
    { path: '/admin/logins', component: require('./components/LoginComponent.vue').default },

    { path: '/messages', component: require('./components/MessageComponent.vue').default },
    { path: '/messages/compose', component: require('./components/MessageComposeComponent.vue').default },
];

const router = new VueRouter({
	mode: 'history',
	routes // short for `routes: routes`
});

Vue.filter('capitalize', function(value) {
    if (!value) return '';
    value = value.toString();
    return value.charAt(0).toUpperCase() + value.slice(1);
});

Vue.filter('formatPrice', function(value) {
    if (!value) return '';
    let val = (value).toFixed(2).replace(',', '.')
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
});



Vue.filter('myDate', function(created) {
    if (!created) return '';
    return moment(created).format('MMMM Do YYYY');
});

Vue.filter('setDate', function(created) {
    if (!created) return '';
    return moment(created).format('MMMM Do YYYY, h:mm:ss a'); // May 3rd 2021, 12:45:50 pm
   // return moment(created).format('MMMM Do YYYY');
});

Vue.filter('ageDate', function(created) {
    if (!created) return '';
    return moment(created, "YYYYMMDD").fromNow();

});

Vue.filter('toCurrency', function(value) {
	if (typeof value !== 'number') {
		return value;
	}
	var formatter = new Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
		minimumFractionDigits: 0
	});
	return formatter.format(value);
});

const app = new Vue({
	el: '#app',
	router,
	data: {
		search: ''
	},
	methods: {
		searchit() {
			Fuse.$emit('searching');
		}
	}
});