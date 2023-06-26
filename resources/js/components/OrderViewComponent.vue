<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="card my-2">
                <div class="card-body">
                    <h2><b>
                        <span v-if="store">{{ store.name }}</span>
                        <span v-else>{{ kitchen.name }}</span>
                    </b></h2>
                    <p>
                        <span v-if="sale.status=='concluded'">Sold to</span>
                        <span v-else>Biiled To</span>:
                        <b>
                            <span v-if="customer">
                                <span v-if="customer.first_name">
                                    {{ customer.get_member }}
                                </span>
                                <span v-else>
                                    {{ customer }}
                                </span>
                            </span>
                        </b>
                    </p>

                    <p>Date: <b>{{ sale.created_at | myDate }}</b></p>
                    <p>
                        <span v-if="sale.status=='pending' && sale.approved==1">Invoice No</span>

                        <span v-else-if="sale.status=='concluded'">Sale No</span>

                        <span v-else-if="sale.status=='returned'">Invoice No</span>

                        <span v-else>Quote No</span>:
                        <b>{{ sale.sale_id }}</b>
                    </p>
                    <span v-if="items.length!=0">
                        <h5>Drink List</h5>
                        <span v-for="(item) in items" :key="item.id">
                            {{ item.product_name}} ({{ item.qty }}) - {{ formatPrice((item.qty * item.price) - ((item.discount/100)*(item.qty * item.price))) }}<br>
                        </span> 
                    </span>  
                    <span v-if="services.length!=0">                              
                        <br>
                        <h5>Food List</h5>
                        <p v-for="(item) in services" :key="item.id">
                            {{ item.food }} ({{ item.qty }}) - {{ formatPrice(item.qty * item.amount) }}
                        </p>
                    </span> 
                    <h6>Total: <b><span v-html="nairaSign"></span>{{ formatPrice(this.sale.totalPrice) }}</b></h6>
                  
                    <p>
                        Steward: <b>{{ sale.market_id }}</b><br>
                        Desk Officer: <b>{{ sale.cashier_id}}</b>
                    </p>
                    <i v-if="sale.status=='concluded'">Thanks and please call again</i>              
                </div>
            </div>

            <div class="row hidden-print">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <button v-if="sale.status=='pending'" class="btn btn-info mb-2"  @click="newModal">Complete Transaction</button>
                    <!-- <button v-if="sale.status=='pending'" class=s"btn btn-primary mb-2"  @click="editInvoice">Edit</button> -->
                    <button id="btnPrint" class="btn btn-success mb-2">Print</button>

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
                                        <option v-for="option in payment_types" v-bind:value="option.id" :key="option.id">
                                            <span v-if="(option.id==1) || (option.id==2) || (option.id==7)">{{ option.title }}</span>
                                        </option>
                                    </select>
                                </b-form-group>

                                <b-form-group label="Select Bank:" v-if="form.channel==3">
                                    <select v-model="form.link" class="form-control">
                                        <option v-for="option in banks" v-bind:value="option.id" :key="option.id">
                                            {{ option.get_bank_name }} ({{ option.account_number }})
                                        </option>
                                    </select>
                                </b-form-group>

                                <b-form-group label="Select POS:" v-if="form.channel==2">
                                    <select v-model="form.link" class="form-control">
                                        <option v-for="option in pos" v-bind:value="option.id" :key="option.id">
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
            kitchen: "",
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
                this.kitchen = response.data.kitchen;
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
