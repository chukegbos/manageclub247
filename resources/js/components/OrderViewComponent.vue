<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mb-2">
            <div class="row mt-2">
                <div class="col-md-2"></div>
                <div class="col-md-8" id="printMe">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h2><b>{{ store.name }}</b></h2>

                                <u><h4 v-if="sale.status=='pending' && sale.approved==0">
                                    <strong>
                                        <span v-if="sale.mop==0"> Credit Sales</span>
                                        <span v-else> Cash Sales</span>
                                        Quote
                                    </strong>
                                </h4>

                                <h4 v-else-if="sale.status=='pending' && sale.approved==1">
                                    <strong>
                                        <span v-if="sale.mop==0"> Credit Sale</span>
                                        <span v-else> Cash Sale</span>
                                        Invoice
                                    </strong>
                                </h4>
                                
                                <h4 v-else-if="sale.status=='concluded' && sale.approved==1">
                                    <strong>
                                        <span v-if="sale.mop==0"> Credit Sales</span>
                                        <span v-else> Cash Sales</span>
                                        Receipt
                                    </strong>
                                </h4>

                                <h4 v-else>
                                    <strong>
                                        <span v-if="sale.mop==0"> Credit Sales</span>
                                        <span v-else> Cash Sales</span>
                                        Items
                                    </strong>
                                </h4></u>
                            </div>
                            
                            <div class="table-responsive border">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>
                                                <span v-if="sale.status=='concluded'">Sold to</span>

                                                <span v-else>Biiled To</span>
                                            </th>

                                            <th>Date</th>

                                            <th>
                                                <span v-if="sale.status=='pending' && sale.approved==1">Invoice No</span>

                                                <span v-else-if="sale.status=='concluded'">Sale No</span>

                                                <span v-else-if="sale.status=='returned'">Invoice No</span>

                                                <span v-else>Quote No</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>

                                                <span v-if="customer">
                                                    <span v-if="customer.first_name">
                                                        {{ customer.get_member }}<br>
                                                        {{ customer.phone_1 }}<br>
                                                        {{ customer.address }}
                                                    </span>
                                                    <span v-else>
                                                        {{ customer }}
                                                    </span>
                                                </span>
                                                

                                            </td>
                                            <td>{{ sale.created_at | myDate }}</td>
                                            <td>{{ sale.sale_id }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                    
                            <div class="table-responsive border">
                                <table class="table table-bordered">
                                    <tr>
                                        <th><h5>Drink List</h5></th>
                                    </tr>
                                </table>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Qty</th>
                                            <th>Price (<span v-html="nairaSign"></span>)</th>
                                         
                                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                                            <!--<th>Dis. Amount (<span v-html="nairaSign"></span>)</th>-->
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-for="(item, index) in items">
                                            <!--<td>{{ index + 1}}</td>-->
                                            <td>{{ item.product_name}}</td>
                                            <td>{{ item.qty }}</td>
                                            <td>{{ formatPrice(item.price) }}</td>
                                            <!--<td>{{ formatPrice(item.qty*item.price) }}</td>-->
                                            <td>{{ formatPrice((item.qty * item.price) - ((item.discount/100)*(item.qty * item.price))) }}</td>
                                        </tr>                                 
                                    </tbody>
                                </table>

                                <table class="table table-bordered">
                                    <tr>
                                        <th><h5>Food List</h5></th>
                                    </tr>
                                </table>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Qty</th>
                                            <th>Unit Amount (<span v-html="nairaSign"></span>)</th>
                                         
                                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                                            
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-for="(item, index) in services">
                                            <!--<td>{{ index + 1}}</td>-->
                                            <td>{{ item.food }}</td>
                                            <td>{{ item.qty }}</td>
                                            <td>{{ formatPrice(item.amount) }}</td>
                                          
                                            <td>{{ formatPrice(item.qty * item.amount) }}</td>
                                        </tr>                                 
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 row">
                                <div class="col-md-6 text-center">
                                    <i v-if="sale.status=='concluded'">Thanks and please call again</i><br><br>
                                    <p>
                                        .
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <div class="border p-2">
                                        <h6><b>Total :  <span class="pull-right"><span v-html="nairaSign"></span>{{ formatPrice(this.sale.totalPrice) }}</span></b></h6>
                                    </div>
                                    <div class="border p-2" v-if="sale.status!='concluded'">
                                        <h6><b>Bal Due :  <span class="pull-right"><span v-html="nairaSign"></span>{{ formatPrice(0) }}</span></b></h6>
                                    </div>
                                    <div class="border p-2" v-if="sale.status!='concluded'">
                                        <p><b>Balance :  <span class="pull-right"><span v-html="nairaSign"></span>{{ formatPrice(0) }}</span></b></p>
                                    </div>
                                    <br>
                                    <p class="text-center"><u>{{ sale.marketer }}</u></p>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div> 

            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <button v-if="sale.status=='pending'" class="btn btn-info btn-sm mb-2"  @click="newModal">Complete Transaction</button>

                   
                    <button v-if="sale.status=='pending'" class="btn btn-primary btn-sm mb-2"  @click="editInvoice">Edit</button>

                    <button @click=onPrint class="btn btn-success btn-sm mb-2">Print</button>
                </div>
            </div>
        </div>

        
            <div class="modal fade" id="addNewUser" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewUserLabel">
                                Conclude Transaction
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="updateDeal()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input v-model="form.totalPrice" type="text" readonly="true" class="form-control"/>
                                    <input v-model="form.sale_id" type="hidden" readonly="true" class="form-control"/>
                                </div>

                                <!--<div class="form-group">
                                    <label>Customer Name</label>
                                    <span v-if="customer">
                                        <span v-if="customer">
                                        </span>
                                        <input v-model="customer.name" type="text" readonly="true" class="form-control"/>
                                    </span>

                                    <span v-else>
                                        <input v-model="customer" type="text" class="form-control"/>
                                    </span>
                                </div>-->
                                
                                <span v-if="sale.mop == 0">
                                    <div class="form-group">
                                        <label>Part Payment</label>
                                        <input v-model="form.part_payment" type="number" class="form-control"/>
                                    </div>

                                    <!--<div class="form-group">
                                        <label>Date of Repayment</label>
                                        <input v-model="form.date" type="date" class="form-control" />
                                    </div>-->
                                    <div class="form-group">
                                        <label>Grace Period (Days)</label>
                                        <input v-model="form.grace_period" type="number" value="1" class="form-control" />
                                    </div>
                                </span>   

                                <span v-else>
                                    <b-form-group label="Method of Fund:" label-for="payment_method">
                                        <select v-model="form.channel" class="form-control">
                                            <option v-for="option in payment_types" v-bind:value="option.id">
                                                <span v-if="(option.id==1) || (option.id==7)">{{ option.title }}</span>
                                            </option>
                                        </select>
                                    </b-form-group>

                                    <b-form-group label="Select Bank:" v-if="form.channel==3">
                                        <select v-model="form.link" class="form-control">
                                            <option v-for="option in banks" v-bind:value="option.id">
                                                {{ option.get_bank_name }} ({{ option.account_number }})
                                            </option>
                                        </select>
                                    </b-form-group>

                                    <b-form-group label="Select POS:" v-if="form.channel==2">
                                        <select v-model="form.link" class="form-control">
                                            <option v-for="option in pos" v-bind:value="option.id">
                                                {{ option.name }} ({{ option.code }})
                                            </option>
                                        </select>
                                    </b-form-group>

                                    <div class="form-group" v-if="form.channel==4 || form.channel==5">
                                        <label>Put ID</label>
                                        <input v-model="form.draft_id" type="text" class="form-control" />
                                    </div>

                                    <div class="form-group" v-if="form.channel==6">
                                        <label>Customer Account Balance</label>
                                        <input v-model="customer.wallet_balance" type="text" readonly="true" class="form-control"/>
                                        <small><a href="javascript:void(0)" @click="WalletModal(customer)" class="pull-right">Fund Account</a></small>
                                    </div>
                                </span> 

                                <div class="form-group">
                                    <label>Select Steward</label>
                                    <v-select label="name" :options="stewards" @input="getUserID($event)"></v-select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>

                                <button type="submit" class="btn btn-success">
                                    Conclude
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        
        <span v-if="customer">
            <div class="modal fade" id="markUser" tabindex="-1" role="dialog" aria-labelledby="markUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewUserLabel">
                                Mark as Invoice
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="updateCreditSale()">
                            <div class="modal-body">
                 
                                <div class="form-group">
                                   
                                    <b-form-checkbox v-model="account.credit" value="1" unchecked-value="0"></b-form-checkbox> On Credit?
                                </div>

                                <div class="form-group" v-if="account.credit==1">
                                    <label>Date of Payment</label>
                                    <input v-model="account.date" type="date" class="form-control" />
                                </div>

                                <div class="form-group">
                                    <label>Debit</label>
                                    <v-select label="account" :options="accounts" @input="setSelected" ></v-select>
                                </div>

                                <div class="form-group">
                                    <label>Credit</label>
                                    <v-select label="account" :options="accounts" @input="setSec" ></v-select>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>

                                <button type="submit" class="btn btn-success">
                                    Set
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
                                Adjust Credit Level
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="updateCredit()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <input v-model="credit.name" type="text" readonly="true" class="form-control"/>
                                </div>

                               
                                <div class="form-group">
                                    <label>Customer Credit Unit</label>
                                    <input v-model="credit.credit_unit" type="number" class="form-control"/>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>

                                <button type="submit" class="btn btn-success">
                                   Adjust
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addNewWallet" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Fund Customer
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="updateWallet()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <input v-model="wallet.name" type="text" readonly="true" class="form-control"/>
                                </div>

                               
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input v-model="wallet.amount" type="number" class="form-control"/>
                                </div>

                                <b-form-group label="Method of Fund:" label-for="payment_method">
                                    <select v-model="wallet.mop" class="form-control">
                                        <option v-for="option in options2" v-bind:value="option.value">
                                            {{ option.text }}
                                        </option>
                                    </select>
                                </b-form-group>

                                <div class="form-group">
                                    <label v-if="wallet.mop=='Cash'">Receipt Number</label>
                                    <label v-else-if="wallet.mop=='Transfer'">Transfer ID</label>

                                    <label v-else>Cheque Number</label>

                                    <input v-model="wallet.tran_type" type="text" class="form-control" required/>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>

                                <button type="submit" class="btn btn-success">
                                    Fund
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </span>
    </b-overlay>
</template>

<script>
    import VueBootstrap4Table from 'vue-bootstrap4-table';
    export default {
    created() {
        
        this.getUser();
        this.loadChannels();
        this.viewOrder();
        this.loadSite();
    },

    data() {
        return {
            is_busy: false,
            sale_id: '',
            sale: '',
            sec_id: '',
            ready: '',
            items: {},
            totalPrice: '',
            site: "",
            user: "",
            store: "",
            mainprice: "",
            customer: '',
            services: {},
            form: new Form({
                id: "",
                user: '',
                wallet_balance: '',
                credit_unit: '',
                sale_id: '',
                totalPrice: '',
                date: '',
                channel: '',
                link: '',
                draft_id: '',
                part_payment: '',
                grace_period: '',
                steward_id: '',
            }),
            accounts: [],
            nairaSign: "&#x20A6;",
            stewards: [],
            account: new Form({
                credit: '',
                date: '',
                account_one: "",
                account_two: "",
            }),

            credit: new Form({
                name: '',
                payer_id: '',
                credit_unit: '',
            }),

            wallet: new Form({
                name: '',
                payer_id: '',
                mop: 'Cash',
                amount: '',
                tran_type: '',
            }),

            options: [
                { text: 'Account Balance', value: 'Wallet' },
                { text: 'Credit', value: 'Credit Unit' }
            ],

            filterForm: {
                selected: 0,
            },

            options2: [
                { text: 'Cash', value: 'Cash' },
                { text: 'Bank Transfer', value: 'Transfer' },
                { text: 'Cheque', value: 'Cheque' },
            ],

            payment_types: [],
            banks: [],
            pos: [],
        };
    },

    methods: {
        getUser() {
            axios.get("/api/user")
            .then(({ data }) => {
                console.log(data.stewards)
                this.user = data.user;
                this.accounts = data.accounts;
                this.stewards = data.stewards;
            });
        },

        loadChannels() {
            axios.get("/api/payment/channels", { params: this.filterForm })
            .then(({ data }) => {
                this.payment_types = data.selected_channel;
                this.banks = data.banks;
                this.pos = data.pos;
                console.log(data);
            });
        },

        getUserID(data){
            this.form.steward_id = data.id;
        },

        setSelected(value) {
            this.form.account_one = value.id;
        },

        setSec(value) {
            this.form.account_two = value.id;
        },

        fundSelected(value) {
            this.wallet.account_one = value.id;
        },

        fundSec(value) {
            this.wallet.account_two = value.id;
        },

        viewRec() {
            this.$router.push({ path: "/receipt/" + this.sale_id });
        },

        viewOrder() {
            if(this.is_busy) return;
            this.is_busy = true;

            this.sale_id = this.$route.params.sale_id;

            axios.get("/api/cart/getorder/" + this.sale_id)

            .then((response) => {
                this.sale = response.data.sale
                this.form = response.data.sale;
                this.customer = response.data.customer;
                this.items = response.data.items;
                this.services = response.data.services;
                this.store = response.data.store;
            })

            .catch((err) => {
                console.log(err);
            })

            .finally(() => {
                this.is_busy = false;
            });
        },

        loadSite() {
            axios.get("/api/setting")
            .then(({ data }) => {
                this.site = data;
            });
        },

        onPrint() {
            this.$htmlToPaper('printMe');
        },

        newModal() {
            $("#addNewUser").modal("show");
        },

        markModal() {
            $("#markUser").modal("show");
        },

        CreditModal(customer) {
            this.credit.payer_id = customer.id;
            this.credit.name = customer.name;
            this.credit.credit_unit = customer.credit_unit;
            $("#addNewUser").modal("hide");
            $("#addNewCredit").modal("show");
        },

        WalletModal(customer) {
            this.wallet.payer_id = customer.id;
            this.wallet.name = customer.name;
            $("#addNewUser").modal("hide");
            $("#addNewWallet").modal("show");
        },

        onChange($event){
            let value = event.target.value;
            if (value == "Credit Unit")
            {
                this.sec_id = 1;
                if(this.customer.credit_unit >= this.form.totalPrice){
                    this.ready = 1;
                }
                else{
                    this.ready = '';
                }
            }
            else
            {
                this.sec_id = '';
                if(this.customer.wallet_balance >= this.form.totalPrice){
                    this.ready = 1;
                }
                else{
                    this.ready = '';
                }
            }
        },

        updateDeal() {
            if(this.is_busy) return;
            this.is_busy = true;
            $("#addNewUser").modal("hide");
            axios.post("/api/cart/closedeal", this.form)
            .then((data) => {
                
                if(data.data.error){
                    Swal.fire(
                        "Failed!",
                        data.data.error,
                        "warning"
                    );
                }
                else{
                    Swal.fire("Updated!", "Deal Closed Successfully.", "success");
                }
            })
            .catch()
            .finally(() => {
                this.is_busy = false;
                this.viewOrder();
                this.loadSite();
            });
        },

        updateCredit() {
            axios.post("/api/user/credituser", this.credit)

            .then((data) => {
                $("#addNewCredit").modal("hide");

                Swal.fire("Updated!", "Credit Topped Up Successfully.", "success");
                this.viewOrder();
                this.loadSite();
                this.getUser();
                this.form.payment_method = 'Credit Unit';
                $("#addNewUser").modal("show");
            })

            .catch();
        },

        updateCreditSale() {
            
            axios.post("/api/user/credituser", this.account)

            .then((data) => {
                $("#addNewCredit").modal("hide");

                Swal.fire("Updated!", "Approve.", "success");
                this.viewOrder();
                this.loadSite();
                this.getUser();
               
            })

            .catch();
        },

        approve(sale){
            axios.post("/api/cart/approvequote", this.form)

            .then((data) => {
                Swal.fire("Updated!", "Quote approved as invoice.", "success");
                this.viewOrder();
                this.loadSite();
            })

            .catch();
        },

        updateWallet() {
            axios.post("/api/user/walletuser", this.wallet)

            .then((data) => {
                $("#addNewWallet").modal("hide");

                Swal.fire("Updated!", "Customer's fund awaiting approval.", "success");
                this.wallet = '';
                this.viewOrder();
                this.loadSite();
                this.getUser();
                this.form.payment_method = 'Wallet';
                $("#addNewUser").modal("show");
            })

            .catch();
        },

        editInvoice() {
            this.$router.push({ path: "/sale/shopping-cart/" + this.sale.sale_id });
        },

        formatPrice(value) {
            let val = (value/1).toFixed(2).replace(',', '.')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        }
    }
};
</script>
