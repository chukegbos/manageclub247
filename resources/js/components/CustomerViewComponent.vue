<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid mt-2">
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>{{ user.name }} </strong></h2>
                </div>

                <div class="col-md-6">
                    <vue-typeahead-bootstrap
                        v-model="userQuery"
                        :ieCloseFix="false"
                        :data="users"
                        :serializer="data => data.name"
                        @hit="getUserID($event)"
                        placeholder="Search for Member"
                        @input="lookUser"
                    />
                </div>
            </div>
         
            <b-card no-body>
                <b-tabs card>
                    <b-tab title="Overview" active>
                        <b-card-text>
                            <b-row>
                                <b-col cols="12" md="9">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tr>
                                                        <th>Membership Wallet</th>
                                                        <th>Bar/Kitchen Wallet</th>
                                                        <th>Debts</th>
                                                        <th>Total Sales</th>
                                                        <th>Value of total sales</th>
                                                        <th>Quotes</th>
                                                        <th>Invoices</th>
                                                        <th>Transactions</th>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <span v-html="nairaSign"></span>{{ formatPrice(user.wallet_balance) }}
                                                            <!--<p><a href="javascript:void(0)" @click="statement(user)" style="color:blue;">View Account</a></p>-->
                                                        </td>

                                                        <td>
                                                            <span v-html="nairaSign"></span>{{ formatPrice(user.bar_wallet) }}
                                                        </td>

                                                        <td>
                                                            <span v-html="nairaSign"></span>{{ formatPrice(payment_debts_sum) }}
                                                        </td>
                                                        
                                                        <td>{{ order_count }}</td> 
                                                        <td><span v-html="nairaSign"></span>{{ formatPrice( value_order_count) }}</td>
                                                        <td>{{ quotes.length }}</td>
                                                        <td>{{ invoices.length }}</td> 
                                                        <td>{{ orders.length }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <span v-if="user.image">
                                                <img class="img-fluid img-responsive" :src="'/img/members/'+ user.image" alt="Profile picture" style="height:100px; display: block; margin-left: auto; margin-right: auto; width: 120px; border-radius:10px">
                                            </span>
                                            <span v-else-if="user.gender=='male'">
                                                <img class="img-fluid img-responsive" :src="'/img/avatar/male.jpg'" alt="Profile picture" style="height:100px; display: block; margin-left: auto; margin-right: auto; width: 120px; border-radius:10px">
                                            </span>

                                            <span v-else>
                                                <img class="img-fluid img-responsive" :src="'/img/avatar/female.jpg'" alt="Profile picture" style="height:100px; display: block; margin-left: auto; margin-right: auto; width: 120px; border-radius:10px">
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-center">User Information</h6>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tr>
                                                        <th>Membership ID</th>
                                                        <td>{{ user.unique_id }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Member Type</th>
                                                        <td>{{ member.get_member_type }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Admission Date</th>
                                                        <td>{{ user.entrance_date | myDate }}</td>
                                                    </tr>
                                                   
                                                    <tr>
                                                        <th>Gender</th>
                                                        <td>{{ user.gender | capitalize }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Date of Birth</th>
                                                        <td>{{ user.dob | myDate}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Phone</th>
                                                        <td>{{ member.phone_1 }}, {{ member.phone_2 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Email</th>
                                                        <td>{{ user.email }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Residential Address</th>
                                                        <td>{{ user.address }}, {{ member.get_city }} LGA of {{ member.get_state }} State, Nigeria</td>
                                                    </tr>

                                                    <tr>
                                                        <th>Home of Origin</th>
                                                        <td>{{ member.home_town }}, {{ member.get_lga }} LGA of {{ member.get_state_of_origin }} State, Nigeria</td>
                                                    </tr>

                                                    <tr>
                                                        <th>Office Address</th>
                                                        <td>{{ member.office_address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Marital Status</th>
                                                        <td>{{ member.marital_status }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h6 class="text-center">Additional Information</h6>
                                            <div class="table-responsive" v-if="additional">
                                                <table class="table table-hover">
                                                    <tr>
                                                        <th>Next of Kin</th>
                                                        <td>{{ additional.kin_name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Next of Kin Relationship</th>
                                                        <td>{{ additional.kin_relationship }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Next of Kin Address</th>
                                                        <td>{{ additional.kin_address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Next of Kin Phone</th>
                                                        <td>{{ additional.kin_phone_1 }}, {{ additional.kin_phone_2 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Beneficiary Name</th>
                                                        <td>{{ additional.beneficiary_name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Beneficiary Relationship</th>
                                                        <td>{{ additional.beneficiary_relationship }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Beneficiary Address</th>
                                                        <td>{{ additional.beneficiary_address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Beneficiary Phone</th>
                                                        <td>{{ additional.beneficiary_phone_1 }}, {{ additional.beneficiary_phone_2 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Sponsors</th>
                                                        <td>{{ additional.sponsor_1 }}, {{ additional.sponsor_2 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Spouse Name</th>
                                                        <td>{{ member.spouse_name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Children</th>
                                                        <td>{{ member.children }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <h6 class="text-center">Cards</h6>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tr>
                                                        <th>Cards</th>
                                                    </tr>

                                                    <tr v-for="card in card_numbers" :key="card.id">
                                                        <td>{{ card.card_number }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <!--<div class="col-md-9">
                                            <h6 class="text-center">Academic Information</h6>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tr>
                                                        <th>Level</th>
                                                        <th>Institution</th>
                                                        <th>Degree</th>
                                                    </tr>

                                                    <tr v-for="edu in educations" :key="edu.id">
                                                        <td>{{ edu.level }}</td>
                                                        <td>{{ edu.institution }}</td> 
                                                        <td>{{ edu.degree }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>-->
                                    </div>
                                </b-col>

                                <b-col cols="12" md="3">
                                    <div class="mb-2">
                                        <h6 class="text-center">Register Sections</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <tr v-for="section in sections">
                                                    <th>{{ section.title }}</th>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <!--<div class="mb-2">
                                        <h6 class="text-center">Quick Links</h6>
                                        <ul>
                                            <li>
                                                <p><a href="javascript:void(0)" @click="statement(user)" style="color:blue;">Member Sale Statement</a></p>
                                            </li>
                                            <li>
                                                <p><a href="javascript:void(0)" @click="sales(user)" style="color:blue;">Member's Sales</a></p>
                                            </li>
                                        </ul>
                                    </div>-->
                                </b-col>
                            </b-row>
                        </b-card-text>
                    </b-tab>

                    <b-tab title="Debts" id="debt">
                        <b-card-text>
                            <div class="table-responsive" v-if="payment_debts.length">
                                <table class="table table-hover">
                                    <tr>
                                        <th>S/N</th>
                                        <th>Payment</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Date Created</th>
                                        <th>Grace Period</th>
                                        <th>Status</th>
                                        <th v-if="admin.role==1 || admin.role==5">Action</th>
                                    </tr>

                                    <tr  v-for="(debt, index) in payment_debts">
                                        <td>{{ index + 1 }}</td>
                                        <td><span v-if="debt.product">{{ debt.product.payment_name }}</span></td>
                                        <td>{{ debt.description }}</td>

                                        <td>
                                            <span v-html="nairaSign"></span>{{ formatPrice(debt.amount)  }} 
                                            <br> 
                                            <span class="badge badge-danger btn-sm">Unpaid</span>
                                        </td>
                                        <td>{{ debt.start_date | myDate }}</td> 
                                        <td>
                                            {{ debt.grace_period }} Days 
                                            <span class="badge badge-danger" v-if="debt.period==0">Expired</span><br>
                                            {{ startDateMoment(debt.start_date, debt.grace_period) }}
                                        </td> 
                                        
                                        <td><span class="badge badge-danger btn-sm">Unpaid</span></td>
                                        <td v-if="admin.role==1 || admin.role==5">
                                            <a href="javascript:void(0)" @click="extendPeriod(debt)" class="btn btn-info btn-sm">Extend Period</a> 

                                            <a href="javascript:void(0)" @click="onPay(debt)" class="btn btn-success btn-sm">Pay</a>
                                            
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div v-else class="alert alert-danger text-center">
                                No debt found
                            </div>
                        </b-card-text>
                    </b-tab>

                    <b-tab title="Quotes">
                        <b-card-text>
                            <div style="border; height: 20em; overflow-y: auto; white-space: nowrap; padding:5px">
                                <div  v-if="quotes.length">
                                    <table class="table table-hover">
                                        <tr>
                                            <th>S/N</th>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                                            <th>Sales Rep</th>
                                            <th>Action</th>
                                        </tr>

                                        <tr  v-for="(order, index) in quotes">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ order.sale_id }}</td>
                                            <td>{{ order.main_date | myDate }}</td> 
                                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                                            <td>{{ order.market_id }}</td>
                                            <td>
                                                <span v-if="order.status!='cancel'">
                                                    <a href="javascript:void(0)" @click=viewItems(order) style="color:blue">View</a> <span v-if="user.role==3">| <a href="javascript:void(0)" @click=cancel(order) style="color:blue">Cancel</a></span>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div v-else class="alert alert-danger text-center">
                                    No record of any quote found
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>

                    <b-tab title="Invoices">
                        <b-card-text>
                            <div style="border; height: 20em; overflow-y: auto; white-space: nowrap; padding:5px">
                                <div  v-if="invoices.length">
                                    <table class="table table-hover">
                                        <tr>
                                            <th>S/N</th>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                                            <th>Sales Rep</th>
                                            <th>Action</th>
                                        </tr>

                                        <tr  v-for="(order, index) in invoices">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ order.sale_id }}</td>
                                            <td>{{ order.main_date | myDate }}</td> 
                                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                                            <td>{{ order.market_id }}</td>
                                            <td>
                                                <span v-if="order.status!='cancel'">
                                                    <a href="javascript:void(0)" @click=viewItems(order) style="color:blue">View</a> <span v-if="user.role==3">| <a href="javascript:void(0)" @click=cancel(order) style="color:blue">Cancel</a></span>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div v-else class="alert alert-danger text-center">
                                    No record of any invoice found
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>

                    <b-tab title="All Transactions">
                        <b-card-text>
                            <div style="border; height: 20em; overflow-y: auto; white-space: nowrap; padding:5px">
                                <div  v-if="orders.length>0">
                                    <table class="table table-hover">
                                        <tr>
                                            <th>S/N</th>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                                            <th>Sales Rep</th>
                                            <th>Action</th>
                                        </tr>

                                        <tr  v-for="(order, index) in orders">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ order.sale_id }}</td>
                                            <td>{{ order.main_date | myDate }}</td> 
                                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                                            <td>{{ order.market_id }}</td>
                                            <td>
                                                <span v-if="order.status!='cancel'">
                                                    <a href="javascript:void(0)" @click=viewItems(order) style="color:blue">View</a> <span v-if="user.role==3">| <a href="javascript:void(0)" @click=cancel(order) style="color:blue">Cancel</a></span>
                                                </span>
                                            </td>
                                        </tr>
                                        
                                    </table>
                                </div>
                                <div v-else class="alert alert-danger text-center">
                                    No record of any transaction found
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>
                </b-tabs>
            </b-card>

            <div class="modal fade" id="extend" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Increase Grace Period
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="updateExtend()">
                            <div class="modal-body">
                               
                                <div class="form-group">
                                    <label>How many more days do you want to add?</label>
                                    <input v-model="extend.grace_period" type="number" class="form-control"/>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>

                                <button type="submit" class="btn btn-success">
                                    Increase
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addNewCredit" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Increase Credit Level
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="updateCredit()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Member Name</label>
                                    <input v-model="credit.name" type="text" readonly="true" class="form-control"/>
                                </div>

                               
                                <div class="form-group">
                                    <label>Member Credit Unit</label>
                                    <input v-model="credit.credit_unit" type="number" class="form-control"/>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>

                                <button type="submit" class="btn btn-success">
                                    Increase
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

      

            <div class="modal fade" id="addpay" tabindex="-1" role="dialog" aria-labelledby="addNewstoreLabel" aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Payment</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="updatePay()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Description</label>
                                    <input v-model="formPay.description" type="text" readonly="true" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label>Amount</label>
                                    <input v-model="formPay.amount" type="number" required class="form-control"/>
                                </div>
                              
                                <div class="form-group">
                                    <label>Method of Payment</label>
                                    <b-form-select
                                        v-model="formPay.payment_channel"
                                        :options="channels"
                                        value-field="id"
                                        text-field="title">

                                        <template v-slot:first>
                                            <b-form-select-option :value="0">
                                                ---Select Payment Method---
                                            </b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>

                                

                                <div class="form-group" v-if="formPay.payment_channel==2">
                                    <label>Select POS</label>
                                    <select v-model="formPay.pos" class="form-control">
                                        <option v-for="po in pos" v-bind:value="po.id">
                                            {{ po.name }} ({{ po.code }})
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group" v-else-if="formPay.payment_channel==3">
                                    <label>Select Bank</label>
                                    <select v-model="formPay.bank" class="form-control">
                                        <option v-for="bank in banks" v-bind:value="bank.id">
                                            {{ bank.account_number }} ({{ bank.account_name }} - {{ bank.bank_name }})
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group" v-else-if="formPay.payment_channel==6">
                                    <label>Member Balance</label>
                                    <input v-model="user.wallet_balance" type="text" readonly="true" class="form-control"/>
                                </div>

                                <!--<div class="form-group" v-else>
                                    <label>Receipt Numberr</label>
                                    <input v-model="formPay.receipt_number" type="text" class="form-control" required/>
                                </div>-->
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">
                                    Pay
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import {debounce} from 'lodash';
    import moment from 'moment';

    export default {
        data(){
            return {
                is_busy: false,
                member: '',
                user: '',
                educations: '',
                card_numbers: [],
                orders: [],
                invoices: [],
                quotes: [],
                nairaSign: "&#x20A6;",
                unique_id: '',
                order_count: '',
                payment_debts_sum: '',
                payment_debts: [],
                value_order_count: '',
                funds: {},
                users: [],
                channels: [],
                banks: [],
                pos: [],
                userQuery: '',
                additional: '',
                sections: '',
                today: moment().format("YYYY-MM-DD"),
                credit: new Form({
                    name: '',
                    payer_id: '',
                    credit_unit: '',
                }),

                extend: new Form({
                    id: '',
                    grace_period: '',
                }),

                form: new Form({
                    user_id: '',
                }),

                wallet: new Form({
                    name: '',
                    payer_id: '',
                    mop: 'Cash',
                    amount: '',
                }),

                options: [
                    { text: 'Wallet', value: 'Wallet' },
                    { text: 'Credit Unit', value: 'Credit Unit' }
                ],

                options2: [
                    { text: 'Cash', value: 'Cash' },
                    { text: 'Transfer', value: 'Transfer' },
                ],
                admin: "",
                formPay: {
                    amount: "",
                    debit_id: "",
                    receipt_number: "",
                    member_id: "",
                    payment_channel: 0,
                    description: '',
                    receipt_number: '',
                    bank: '',
                    pos: '',
                },
            }
        },

        created(){
            this.getUser();
            this.loadPage();
        },

        methods: {
            lookUser(){
                debounce(() => {

                    fetch('/api/searchcustomer', {params: this.userQuery})
                    .then(response => {
                        return response.json();
                    })
                    .then(data => {
                        this.users = data;

                    })
                }, 500)();
            },

            onPay(debt) {
                console.log(debt)
                this.formPay.debit_id = debt.id;
                this.formPay.amount = debt.amount;
                this.formPay.member_id = debt.member_id;
                this.formPay.description = debt.description;
                $('#addpay').modal('show');
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
            },

            getUserID(data){

                if(this.is_busy) return;
                this.is_busy = true;

                this.unique_id = data.unique_id;
               
                axios.get('/api/customer/' + this.unique_id)
                .then((response) => {
                    if(response.data.error){
                        Swal.fire(
                            "Failed!",
                            response.data.error,
                            "warning"
                        );
                        this.$router.push({ path: "/admin/customers"});
                    }
                    else{
                        this.educations = response.data.educations;
                        this.additional = response.data.additional;
                        this.member = response.data.member;
                        this.user = response.data.user;
                        this.card_numbers = response.data.card_numbers;
                        this.orders = response.data.orders;
                        this.invoices = response.data.invoices;
                        this.quotes = response.data.quotes;
                        this.funds = response.data.funds;
                        this.order_count = response.data.order_count;
                        this.value_order_count = response.data.value_order_count;
                        this.sections = response.data.sections;
                        this.payment_debts = response.data.payment_debts;
                        this.payment_debts_sum = response.data.payment_debts_sum;
                        this.channels = response.data.channels;
                        this.banks = response.data.banks;
                        this.pos = response.data.pos;
                    }
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            getResultsFund(page = 1) {

                axios.get('/api/customer/' + this.unique_id +'?page=' + page)
                .then(response => {
                    this.funds = response.data.funds;
                })
                .catch((err) => {
                        console.log(err);
                });
            },

            getResults(page = 1) {

                axios.get('/api/customer/' + this.unique_id +'?page=' + page)
                .then(response => {
                    this.orders = response.data.orders;
                })
                .catch((err) => {
                        console.log(err);
                });
            },

            loadPage(){
                if(this.is_busy) return;
                this.is_busy = true;

                this.unique_id = this.$route.params.unique_id;
                
                axios.get('/api/customer/' + this.unique_id)
                .then((response) => {
                    if(response.data.error){
                        Swal.fire(
                            "Failed!",
                            response.data.error,
                            "warning"
                        );
                        this.$router.push({ path: "/admin/customers"});
                    }
                    else{
                        console.log(response.data)
                        this.user = response.data.user;
                        this.orders = response.data.orders;
                        this.invoices = response.data.invoices;
                        this.quotes = response.data.quotes;
                        this.funds = response.data.funds;
                        this.order_count = response.data.order_count;
                        this.value_order_count = response.data.value_order_count;
                        this.card_numbers = response.data.card_numbers;
                        this.additional = response.data.additional;
                        this.educations = response.data.educations;
                        this.member = response.data.member;
                        this.sections = response.data.sections;
                        this.payment_debts = response.data.payment_debts;
                        this.payment_debts_sum = response.data.payment_debts_sum;
                        this.channels = response.data.channels;
                        this.banks = response.data.banks;
                        this.pos = response.data.pos;
                    }
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            startDateMoment(value, grace_period) {
                return moment(value).add(grace_period, 'days').format('MMMM Do YYYY');
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            viewItems(order) {
                this.$router.push({ path: "/orderview/" + order.sale_id });
            },

            statement(user) {
                this.$router.push({ name: 'statement', params: { unique_id: user.unique_id} });
                //this.$router.push({ path: "/customer/statement", params: { unique_id: user.unique_id } });
            },

            sales(user) {
                this.$router.push({ name: 'sales', params: { unique_id: user.unique_id} });
                //this.$router.push({ path: "/customer/sales/" + user.unique_id });
            },

            viewAll() {
                this.$router.push({ path: "/sale/orders/" + this.user.id });
            },

            CreditModal(customer) {
                this.credit.payer_id = customer.id;
                this.credit.name = customer.name;
                this.credit.credit_unit = customer.credit_unit;
                $("#addNewCredit").modal("show");
            },

            extendPeriod(debt) {
                this.extend.id = debt.id;
                this.extend.grace_period = debt.grace_period;
                $("#extend").modal("show");
            },

            WalletModal(customer) {
                this.wallet.payer_id = customer.id;
                this.wallet.name = customer.name;
                $("#addNewWallet").modal("show");
            },

            updateCredit() {
                axios.post("/api/payment/pay", this.formPay)

                .then((data) => {
                    $("#addNewCredit").modal("hide");
                    Swal.fire("Updated!", "Payment Registered Successfully.", "success");
                    this.getUser();
                    this.loadPage();
                })

                .catch();
            },

            updatePay() {
                axios.post("/api/payment/pay", this.formPay)

                .then((data) => {
                    $("#addpay").modal("hide");

                    if(data.data.error) {
                        Swal.fire(
                            "Failed!",
                            data.data.error,
                            "warning"
                        );
                    }
                    else {
                        Swal.fire("Updated!", "Payment Registered Successfully.", "success");
                    }
                    this.getUser();
                    this.loadPage();
                })

                .catch();
            },

            updateExtend() {
                axios.post("/api/payment/graceperiod", this.extend)

                .then((data) => {
                    $("#extend").modal("hide");
                    Swal.fire("Updated!", "Grace Period Extended Successfully.", "success");
                    this.getUser();
                    this.loadPage();
                })

                .catch();
            },

            updateWallet() {
                axios.post("/api/user/walletuser", this.wallet)

                .then((data) => {
                    $("#addNewWallet").modal("hide");
                    Swal.fire("Updated!", "Customer Credited Successfully.", "success");
                    this.getUser();
                    this.loadPage();
                })

                .catch();
            },
        },  
    }
</script>
<style>
    .active{
        color: #000 !important;
    }

    nav-link, a{
        color: #000 !important;
    }
<style>