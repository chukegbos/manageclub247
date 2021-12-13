<template>
    <b-overlay :show="is_busy" rounded="sm">   
        <div class="container-fluid">
            <div class="row mb-2 p-1">
                <div class="col-md-12">
                    <h2><strong>Fund Member's Balance</strong></h2>
                </div>
            </div>

            <form @submit.prevent="updateWallet()">
                <div class="card">
                    <div class="card-body row">
                        <div class="form-group col-md-6">
                            <label>Customer</label>
                            <!--<vue-typeahead-bootstrap
                                v-model="userQuery"
                                :ieCloseFix="false"
                                :data="users"
                                :serializer="data => data.name"
                                @hit="getUserID($event)"
                                placeholder="Search for customer"
                                @input="lookUser"
                            />-->

                            <v-select label="name" :options="users" @input="setCustomer($event)"></v-select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Amount</label>
                            <input v-model="wallet.amount" type="number" class="form-control"/>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Method of Payment</label>

                            <select v-model="wallet.mop" class="form-control">
                                <option v-for="option in options2" v-bind:value="option.value">
                                    {{ option.text }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group col-md-6" v-if="wallet.mop!='Cash'"">
                            <label v-if="wallet.mop=='POS'">POS Number</label>
                            <label v-else-if="wallet.mop=='Transfer'">Transfer ID</label>

                            <label v-else>Cheque Number</label>

                            <input v-model="wallet.tran_type" type="text" class="form-control"/>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">Fund</button>
                    </div>
                </div>
            </form>
        </div>
    </b-overlay>
</template>

<script>
    import {debounce} from 'lodash';
    export default {
        data() {
            return {
                is_busy: false,
                userQuery: '',
                wallet: new Form({
                    payer_id: '',
                    mop: 'Cash',
                    amount: '',
                    tran_type: '',
                }),
                users: [],
                admin: '',
                options: [
                    { text: 'Wallet', value: 'Wallet' },
                    { text: 'Credit Unit', value: 'Credit Unit' }
                ],
                accounts: [],
                options2: [
                    { text: 'Cash', value: 'Cash' },
                    { text: 'POS', value: 'POS' },
                    { text: 'Bank Transfer', value: 'Transfer' },
                    { text: 'Cheque', value: 'Cheque' },
                ],
            };
        },

        created() {
            this.getUser();
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

            getUserID(data){
                this.wallet.payer_id = data.id;
            },

            setCustomer(user){
                this.wallet.payer_id = user.id;
                console.log(this.wallet)
            },

            getUser() {
                if(this.is_busy) return;
                this.is_busy = true;
                axios.get("/api/user")
                .then(({ data }) => {
                    this.users = data.customers;
                    this.admin = data.user;
                    this.accounts = data.accounts;
                })
                .catch(error => {
                    Swal.fire(
                        "Failed!",
                        "There is error somewhere",
                        "error"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            setSelected(value) {
                this.wallet.account_one = value.id;
            },

            setSec(value) {
                this.wallet.account_two = value.id;
            },

            updateWallet() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.post("/api/user/walletuser", this.wallet)

                .then((data) => {
                    if(data.data.error){
                        Swal.fire(
                            "Failed!",
                            data.data.error,
                            "warning"
                        );
                    }
                    else{
                        Swal.fire("Updated!", "Customer's fund awaiting approval.", "success");
                        this.wallet = '';
                        this.$router.push({ path: "/admin/customers/fund-history"});
                    }
                })

                .catch();
                this.is_busy = false;
            },
         
        },
    };
</script>
