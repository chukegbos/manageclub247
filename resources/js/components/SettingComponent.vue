<template>
    <b-overlay :show="is_busy" rounded="sm">  
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Site Settings</strong></h2>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="updateSite()">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sitename</label>
                                    <input v-model="form.sitename" type="text" name="sitename" class="form-control" required/>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input v-model="form.email" type="email" name="email" class="form-control"/>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input v-model="form.phone" type="tel" class="form-control"/>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input v-model="form.address" type="text" class="form-control"/>
                                </div>
                            </div>

                            <!--<div class="col-md-4">
                                <div class="form-group">
                                    <label>Admin Discount (%)</label>
                                    <input v-model="form.admin_percent" type="number" class="form-control" required/>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Manager Discount (%)</label>
                                    <input v-model="form.manager_percent" type="number" class="form-control" required/>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cashier Discount (%)</label>
                                    <input v-model="form.cashier_percent" type="number" class="form-control" required/>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Expense Ratio</label>
                                    <input v-model="form.expense_ratio" type="number" class="form-control" required/>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>1 Chinese Yuan</label>
                                    <input v-model="form.naira_value" type="text" class="form-control" required/>
                                </div>
                            </div>  

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Percent Gain</label>
                                    <input v-model="form.percent_gain" type="text" class="form-control" required/>
                                </div>
                            </div> -->


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Sales Cash Account</label>
                                    <select v-model="form.cash_account" class="form-control">
                                        <option v-for="account in accounts" v-bind:value="account.id">
                                            {{ account.account}}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Sales Credit Account</label>
                                    <select v-model="form.credit_account" class="form-control">
                                        <option v-for="account in accounts" v-bind:value="account.id">
                                            {{ account.account}}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Purchase Cash Account</label>
                                    <select v-model="form.purchase_cash_account" class="form-control">
                                        <option v-for="account in accounts" v-bind:value="account.id">
                                            {{ account.account}}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Purchase Credit Account</label>
                                    <select v-model="form.purchase_credit_account" class="form-control">
                                        <option v-for="account in accounts" v-bind:value="account.id">
                                            {{ account.account}}
                                        </option>
                                    </select>
                                </div>
                            </div>

                        </div>  

                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    export default {
        data() {
            return {
                is_busy: false,
                form: new Form({
                    sitename: '',
                    email: '',
                    phone: '',
                    address: '',
                    admin_percent: '',
                    manager_percent: '',
                    cashier_percent: '',
                    naira_value: '',
                    expense_ratio: '',
                    percent_gain: '',
                    credit_account: '',
                    cash_account: '',
                    purchase_credit_account: '',
                    purchase_cash_account: '',
                }),
                view_error: '',
                accounts: [],
            };
        },

        created() {
            this.loadSite();
            this.getUser();
        },

        methods: {
            loadSite() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/setting")
                .then(({ data }) => {
                    this.form = data;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            updateSite() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.put("/api/setting/" + this.form.id, this.form)
                .then(() => {
                    Swal.fire("Updated!", "Site Updated Successfully.", "success");
                    this.loadSite();
                    this.getUser();
                })
                .catch((data) => {
                    this.view_error = data;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.types = data.types;
                    this.accounts = data.accounts;
                });
            },
        },

        
    };
</script>
