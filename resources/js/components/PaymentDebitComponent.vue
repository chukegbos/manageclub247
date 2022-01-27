<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Payment Debits</strong></h2>
                </div>

                <div class="col-md-6">
                    <b-button variant="outline-primary" size="sm" @click="newModal()" class="pull-right m-2" v-if="admin.role==1 || admin.role==5">
                        Add Debit
                    </b-button>
               
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search debit"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="debits.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th v-if="admin.role==1 || admin.role==5"><input type="checkbox" v-model="selectAll"></th>
                                <th>Member</th>
                                <th>Payment</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Date Created</th>
                                <th>Grace Period</th>
                                <th v-if="admin.role==1 || admin.role==5">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="debt in debits.data" :key="debt.id">
                                <td v-if="admin.role==1 || admin.role==5"> <input type="checkbox" v-model="action.selected" :value="debt.id" number></td>
                                <td>{{ debt.last_name }} {{ debt.first_name }} {{ debt.middle_name }}</td>
                                <td><span v-if="debt.product">{{ debt.product.payment_name }}</span></td>
                                <td>{{ debt.description }}</td>
                                <td>
                                    <span v-html="nairaSign"></span>{{ formatPrice(debt.amount)  }}
                                    <br> <span class="badge badge-danger btn-sm">Unpaid</span>
                                </td>
                                <td>{{ debt.start_date | myDate }}</td> 
                                <td>
                                    {{ debt.grace_period }} Days 
                                    <span class="badge badge-danger" v-if="debt.period==0">Expired</span><br>
                                    {{ startDateMoment(debt.start_date, debt.grace_period) }}
                                </td> 
                                <td v-if="admin.role==1 || admin.role==5">
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                      
                                            <b-dropdown-item href="javascript:void(0)" @click="extendPeriod(debt)">Extend Period</b-dropdown-item>

                                            <b-dropdown-item href="javascript:void(0)" @click="onPay(debt)">Pay</b-dropdown-item>

                                            <b-dropdown-item href="javascript:void(0)" @click="editDebit(debt)">Edit Debit</b-dropdown-item>
                                     
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Payment Debit Found.</strong></h3></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Payment Debits</b>
                        </div>

                        <div class="col-md-4" v-if="this.filterForm.selected!=0">
                            <pagination :data="debits" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>
            
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

            <div class="modal fade" id="addNewdebit" tabindex="-1" role="dialog" aria-labelledby="addNewdebitLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewdebitLabel"
                                v-show="!editMode"
                            >
                                Add New Debit
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewdebitLabel"
                                v-show="editMode"
                            >
                                Update Debit
                            </h5>
                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                            >
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="editMode ? updatedebit() : createdebit()">
                            <div class="modal-body">
                                
                                <div class="form-group" v-if="editMode">
                                    <label>Product</label>
                                    <input v-model="form.description" type="text" readonly="true" class="form-control"/>
                                </div>

                                <b-form-group v-else>
                                    <label>Payment Products</label>
                                    <v-select label="payment_name" :options="products" @input="setProduct" ></v-select>
                                </b-form-group>

                                <div class="form-group" v-if="editMode">
                                    <label>Member</label>
                                    <input v-model="form.name" type="text" readonly="true" class="form-control"/>
                                </div>

                                <b-form-group v-else>
                                    <label>Members</label>
                                    <v-select label="get_member" :options="members" @input="setMember" ></v-select>
                                </b-form-group>

                                <div class="form-group">
                                    <label>Amount</label>
                                    <input v-model="form.amount" type="number" name="amount" class="form-control"/>
                                </div>

                                <div class="form-group" v-if="!editMode">
                                    <label>Grace Period (Days)</label>
                                    <input v-model="form.grace_period" type="number" max="30" name="grace_period" class="form-control"/>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    data-dismiss="modal"
                                >
                                    Close
                                </button>
                                <button
                                    v-show="editMode"
                                    type="submit"
                                    class="btn btn-success"
                                >
                                    Update
                                </button>
                                <button
                                    v-show="!editMode"
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Create
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addPay" tabindex="-1" role="dialog" aria-labelledby="addPayLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Make Payment</h5>
                          
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="makePay()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Member</label>
                                    <input v-model="formPay.name" type="text" readonly="true" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label>Amount</label>
                                    <input v-model="formPay.amount" type="number" name="amount" class="form-control"/>
                                </div>

                                <b-form-group label="Method of Payment:" label-for="payment_method">
                                        <select v-model="formPay.mop" class="form-control">
                                            <option v-for="option in payment_types" v-bind:value="option.id">
                                                {{ option.title }}
                                            </option>
                                        </select>
                                    </b-form-group>

                                    <b-form-group label="Select Bank:" v-if="formPay.mop==3">
                                        <select v-model="formPay.link" class="form-control">
                                            <option v-for="option in banks" v-bind:value="option.id">
                                                {{ option.get_bank_name }} ({{ option.account_number }})
                                            </option>
                                        </select>
                                    </b-form-group>

                                    <b-form-group label="Select POS:" v-if="formPay.mop==2">
                                        <select v-model="formPay.link" class="form-control">
                                            <option v-for="option in pos" v-bind:value="option.id">
                                                {{ option.name }} ({{ option.code }})
                                            </option>
                                        </select>
                                    </b-form-group>

                                    <div class="form-group" v-if="formPay.mop==4 || formPay.mop==5">
                                        <label>Put ID</label>
                                        <input v-model="formPay.draft_id" type="text" class="form-control" />
                                    </div>

                                    <div class="form-group" v-if="formPay.mop==6">
                                        <label>Subscripption Account Balance</label>
                                        <input v-model="customer.wallet_balance" type="text" readonly="true" class="form-control"/>
                                        <small><i class="text-red">Amount will be deducted from wallet</i></small>
                                    </div>

                                    <div class="form-group" v-if="formPay.mop==7">
                                        <label>Bar Account Balance</label>
                                        <input v-model="customer.bar_wallet" type="text" readonly="true" class="form-control"/>
                                        <small><i class="text-red">Amount will be deducted from wallet</i></small>
                                    </div>

                                    <div class="form-group" v-if="formPay.mop==8">
                                        <label>Levy Account Balance</label>
                                        <input v-model="customer.credit_unit" type="text" readonly="true" class="form-control"/>
                                        <small><i class="text-red">Amount will be deducted from wallet</i></small>
                                    </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
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
    import moment from 'moment';

    export default {
        created() {
            this.getUser();
            this.loadChannels();
            this.loadDebits();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                model: {},
                debits: {},
                debit: "",
                form: new Form({
                    id: "",
                    amount: "",
                    grace_period: "",
                    product: "",
                    member: "",
                    description: '',
                    name: '',
                }),
                customer: '',
                formPay: new Form({
                    id: "",
                    amount: "",
                    product: "",
                    name: '',
                    mop: '1',
                    link: '',
                    draft_id: '',


                    amount: "",
                    debit_id: "",
                    receipt_number: "",
                    member_id: "",
                    payment_channel: 0,
                    description: '',
                    receipt_number: '',
                    bank: '',
                    pos: '',
                }),
                nairaSign: "&#x20A6;",
                filterForm: {
                    name: '',
                    selected: '10',
                },
                action: {
                    selected: []
                },
                admin: "",
                debits: {
                    data: {},
                },

                extend: new Form({
                    id: '',
                    grace_period: '',
                }),
                members: [],
                products: [],
                count_all: '',
                unprintable: false,
                payment_types: [],
                banks: [],
                pos: [],
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.getUser();
                this.loadChannels();
                this.loadDebits();
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
            },

            loadChannels() {
                axios.get("/api/payment/channels", { params: this.filterForm })
                .then(({ data }) => {
                    this.payment_types = data.channels.data;
                    this.banks = data.banks;
                    this.pos = data.pos;
                });
            },

            loadDebits() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/payment/debits", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.debits.data = data.debits;
                    }
                    else{
                        this.debits = data.debits;
                    }
                    this.count_all = data.all;
                    this.products = data.products;
                    this.members = data.members;
                })
                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            setProduct(value) {
                this.form.product = value.id;
            },

            setMember(value) {
                this.form.member = value.id;
            },

            newModal() {
                $("#addNewdebit").modal("show");
            },

            extendPeriod(debt) {
                this.extend.id = debt.id;
                this.extend.grace_period = debt.grace_period;
                $("#extend").modal("show");
            },

            updateExtend() {
                axios.post("/api/payment/graceperiod", this.extend)

                .then((data) => {
                    $("#extend").modal("hide");
                    Swal.fire("Updated!", "Grace Period Extended Successfully.", "success");
                    this.loadDebits();
                })

                .catch();
            },
            
            getResults(page = 1) {
                axios.get("/api/payment/debits?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.debits = response.data.debits;
                });
            },

            editDebit(debit) {
                (this.editMode = true);
                this.form.fill(debit);
                this.form.name = debit.first_name +' '+debit.last_name
                $("#addNewdebit").modal("show");            
            },

            startDateMoment(value, grace_period) {
                return moment(value).add(grace_period, 'days').format('MMMM Do YYYY');
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            onFilterSubmit()
            {
                this.loadDebits();
            },

            createdebit() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewdebit").modal("hide");
                this.form.post("/api/payment/debits")
                .then((data) => {
                    if(data.data.error){
                        Swal.fire(
                            "Failed!",
                            data.data.error,
                            "warning"
                        );
                    }
                    else {
                        Swal.fire(
                            "Created!",
                            "Payment debit Created Successfully.",
                            "success"
                        );
                    }
                })
                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                    this.getUser();
                    this.loadChannels();
                    this.loadDebits(); 
                });
            },

            updatedebit() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewdebit").modal("hide");
                this.form.put("/api/payment/debits/" + this.form.id)
                .then(() => {
                    Swal.fire("Updated!", "Payment debit Updated Successfully.", "success");
                    this.loadDebits(); 
                })

                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                    this.getUser();
                    this.loadChannels();
                    this.loadDebits(); 
                });
            },

            onDeleteAll(id) {
                if(id){
                    this.action.selected.push(id);
                }
                
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                })
                .then(result => {
                    if (result.value) {
                        if (this.is_busy) return;
                        this.is_busy = true;
                        axios.get('/api/payment/debits/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Payment debit(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadDebits();
                        })

                        .catch(() => {
                            Swal.fire(
                                "Failed!",
                                "Ops, Something went wrong, try again.",
                                "warning"
                            );
                            this.is_busy = false;
                        });
                    }
                });
            },

            onPay(debt) {
                this.formPay.fill(debt);
                axios.get("/api/payment/debits/member/" + debt.member_id)
                .then(({ data }) => {
                    this.customer = data;
                    console.log(this.customer);
                })
                .catch()

                this.formPay.name = debt.first_name +' '+debt.last_name
                $("#addPay").modal("show");   
            },


            makePay() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addPay").modal("hide");
                this.formPay.post("/api/payment/debits/pay")
                .then((data) => {
                    if(data.data.error){
                        Swal.fire(
                            "Failed!",
                            data.data.error,
                            "warning"
                        );
                    }
                    else {
                        Swal.fire(
                            "Created!",
                            "Payment Created Successfully.",
                            "success"
                        );
                    }
                })
                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                    this.getUser();
                    this.loadChannels();
                    this.loadDebits(); 
                });
            },
        },

        computed: {
            selectAll: {
                get: function () {
                    return this.debits.data ? this.action.selected.length == this.debits.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.debits.data.forEach(function (debit) {
                            selected.push(debit.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
