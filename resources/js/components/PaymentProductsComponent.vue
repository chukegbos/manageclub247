<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Payment Products</strong></h2>
                </div>

                <div class="col-md-8">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal class="pull-right m-2">
                        <i class="fa fa-filter"></i> Filter
                    </b-button>

                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2" v-if="admin.role==1 || admin.role==5">
                        Add Payment Products
                    </b-button>

                    
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Category:" label-for="staff">
                                <b-form-select
                                    v-model="filterForm.category"
                                    :options="category"
                                    value-field="value"
                                    text-field="title">

                                    <template v-slot:first>
                                        <b-form-select-option :value=null>
                                            All
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-form-group>

                            <b-form-group label="Type:">
                                <b-form-select
                                    v-model="filterForm.type"
                                    :options="type"
                                    value-field="value"
                                    text-field="title">

                                    <template v-slot:first>
                                        <b-form-select-option :value=null>
                                            All
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-form-group>

                            <b-form-group label="Keyword:" label-for="staff">
                                <b-form-input v-model="filterForm.keyword" type="text" ></b-form-input>
                            </b-form-group>

                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                       
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="payments.data.length>0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Grace Period</th>
                                <th>Category</th>
                                <th>Door Access</th>
                                <th>Creator </th>
                                <th v-if="admin.role==1 || admin.role==5">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="method in payments.data" :key="method.id">
                                <td>{{ method.payment_name }}</td>
                                <td>{{ method.product_id }}</td>
                                <td>
                                    <span v-if="method.type==0">One Off</span>
                                    <span v-else>Monthly <br>Every {{ method.reoccuring_day }}</span>
                                </td>
                                <td><span v-html="nairaSign"></span>{{ formatPrice(method.amount ) }}</td>
                                <td>{{ method.grace_period }} days</td>
                                <td>
                                    <span v-if="method.category==0">Member</span>
                                    <span v-else>Non-Member</span></td>
                                <td>
                                    <span v-if="method.door_access==0" class="badge badge-danger">Disabled</span>
                                    <span v-else class="badge badge-primary">Enabled</span>
                                </td>
                                <td><span v-if="method.created_by">{{ method.created_by.name }}<br>{{ method.created_at | myDate }}</span></td>
                                <td v-if="admin.role==1 || admin.role==5">
                                    <b-dropdown id="dropdown-right" text="Action" variant="info"> 
                                        <b-dropdown-item href="javascript:void(0)" @click="editModal(method)">Edit</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="debitModal(method)">Group Debit Members</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="onDeleteAll(method.id)">Delete</b-dropdown-item>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Payment Product Found.</strong></h3></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-4">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Payment Products</b>
                            
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="payments" @pagination-change-page="getResults" :limit="-1"></pagination>
                            page {{ page }} of {{ Number(total_page) + 1 }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="addNewstore" tabindex="-1" role="dialog" aria-labelledby="addNewstoreLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewstoreLabel"
                                v-show="!editMode"
                            >
                                Add Payment Products
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewstoreLabel"
                                v-show="editMode"
                            >
                                Update Payment Products
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
                        <form @submit.prevent="editMode ? updateStore() : createStore()">
                            <div class="modal-body row">
                                <div class="form-group col-md-6">
                                    <label>Product Name <span class="text-danger pulll-right">*</span></label>
                                    <input
                                        v-model="form.payment_name"
                                        type="text"
                                        name="payment_name"
                                        required
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'payment_name'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="payment_name"
                                    ></has-error>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Amount <span class="text-danger pulll-right">*</span></label>
                                    <input
                                        v-model="form.amount"
                                        type="number"
                                        name="amount"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'amount'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="amount"
                                    ></has-error>
                                </div>

                                <b-form-group class="col-md-6">
                                    <label>Category <span class="text-danger pulll-right">*</span></label>
                                    <b-form-select
                                        v-model="form.category"
                                        :options="category"
                                        value-field="value"
                                        text-field="title">
                                    </b-form-select>
                                </b-form-group>
                      
                                <b-form-group class="col-md-6">
                                    <label>Require Door Access? <span class="text-danger pulll-right">*</span></label>
                                    <b-form-select
                                        v-model="form.door_access"
                                        :options="door_access"
                                        value-field="value"
                                        text-field="title">
                                    </b-form-select>
                                </b-form-group>

                                <div class="form-group col-md-6">
                                    <label>Grace Period (Days)</label>
                                    <input
                                        v-model="form.grace_period"
                                        type="number"
                                        name="grace_period"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'grace_period'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="grace_period"
                                    ></has-error>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Deduction Wallet</label>

                                    <select v-model="form.wallet" class="form-control">
                                        <option v-for="wallet in wallets" v-bind:value="wallet.value">
                                            {{ wallet.text }}
                                        </option>
                                    </select>
                                </div>

                                <b-form-group class="col-md-6">
                                    <label>Type <span class="text-danger pulll-right">*</span></label>
                                    <b-form-select
                                        v-model="form.type"
                                        :options="type"
                                        value-field="value"
                                        text-field="title">
                                    </b-form-select>
                                </b-form-group>

                                <div class="form-group col-md-6" v-if="form.type==1">
                                    <label>Reoccuring Day</label>
                                    <input
                                        v-model="form.reoccuring_day"
                                        type="number"
                                        max="28"
                                        name="reoccuring_day"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'reoccuring_day'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="reoccuring_day"
                                    ></has-error>
                                </div>

                                <div class="form-group col-md-12">
                                    <fieldset>
                                        <label>Select Member Types</label>
                                        <div style="border: 1px solid grey; height: 15em; overflow-y: auto; white-space: nowrap; padding:5px">
                                            <div class="c-inputs-stacked" v-for="member_type in member_types" :key="member_type.id">
                                                <div class="m-1">
                                                    <input type="checkbox" v-model="form.member_type" :value="member_type.id" number> {{ member_type.title }}
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
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

            <div class="modal fade" id="debitmodal" tabindex="-1" role="dialog" aria-labelledby="addNewstoreLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Debit All Members
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

                        <form @submit.prevent="createDebit()">
                            <div class="modal-body row">
                                <div class="form-group col-md-12">
                                    <label>Product Description <span class="text-danger pulll-right">*</span></label>
                                    <input
                                        v-model="formDebit.description"
                                        type="text"
                                        name="description"
                                        required
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'description'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="payment_name"
                                    ></has-error>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Amount <span class="text-danger pulll-right">*</span></label>
                                    <input
                                        v-model="formDebit.amount"
                                        type="number"
                                        name="amount"
                                        class="form-control"
                                        readonly="true"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'amount'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="amount"
                                    ></has-error>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Grace Period (Days)</label>
                                    <input
                                        v-model="formDebit.grace_period"
                                        type="number"
                                        max="28"
                                        name="grace_period"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'grace_period'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="grace_period"
                                    ></has-error>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Members to debit</label>
                                    
                                    <div v-for="pp in formDebit.people" :key="pp.id">
                                        - {{ pp }}
                                    </div>
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
                                    Debit
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
    import axios from 'axios';
    import moment from 'moment';

    export default {
        created() {
            this.getUser();
            this.loadPayment();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                nairaSign: "&#x20A6;",
                model: {},
                payments: {},
                page: 1,
                total_page: '',
                filterForm: {
                    category: null,
                    selected: 10,
                    keyword: '',
                    type: null,
                },
                category: [
                    { value: '0', title: 'Member' },
                    { value: '1', title: 'Non Member' },
                ],

                type: [
                    { value: '0', title: 'One Off' },
                    { value: '1', title: 'Monthly' },
                ],

                wallets: [
                    { text: 'Bar/Kitchen Wallet', value: 0 },
                    { text: 'Monthly Subscription Wallet', value: 1 },
                    { text: 'Levy Wallet', value: 2 },
                ],

                door_access: [
                    { value: '0', title: 'No' },
                    { value: '1', title: 'Yes' },
                ],

                member_types: [],

                form: new Form({
                    id: "",
                    payment_name: "",
                    amount: "",
                    type: 1,
                    category: 0,
                    door_access: 0,
                    reoccuring_day: 28,
                    grace_period: 10,
                    member_type: [],
                    wallet: 1,
                }),

                formDebit: new Form({
                    product: "",
                    description: "",
                    debit_type: 1,
                    amount: 0,
                    grace_period: 0,
                    people: [],
                    people_id: [],
                }),

                
                action: {
                    selected: []
                },
                payments: {
                    data: {},
                },
                count_all: '',
                unprintable: false,
                admin: '',
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadPayment();
                this.getUser();
            },

            loadPayment() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/payment/method", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.payments.data = data.payments;
                    }
                    else{
                        this.payments = data.payments;
                    }
                    this.count_all = data.all;
                    this.member_types = data.member_types;
                    this.total_page = Number(this.count_all/this.filterForm.selected).toFixed(0);
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

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewstore").modal("show");
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
            },

            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.unprintable = true;
                this.$htmlToPaper('printMe');
                //this.unprintable = false;
                this.is_busy = false;
                this.loadPayment();
                this.getUser();
            },

            getResults(page = 1) {
                if (this.is_busy) return;
                this.is_busy = true;
                this.page = page;
                axios.get("/api/payment/method?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.payments = response.data.payments;
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

            debitModal(store) {
                this.formDebit.reset();
                $("#debitmodal").modal("show");

                this.formDebit.product = store.id;
                this.formDebit.description = store.payment_name;
                this.formDebit.debit_type = 1;
                this.formDebit.amount = store.amount;
                this.formDebit.grace_period = store.grace_period;

                var selectedType = [];
                var selectedId = [];

                store.types.forEach(function (ty) {
                    selectedType.push(ty.title);
                    selectedId.push(ty.pivot.type_id);
                });

                this.formDebit.people = selectedType;
                this.formDebit.people_id = selectedId;
            },

            editModal(store) {
                (this.editMode = true), this.form.reset();
                $("#addNewstore").modal("show");
                this.form.fill(store);
                var selectedType = [];

                store.types.forEach(function (ty) {
                    selectedType.push(ty.pivot.type_id);
                });
                console.log(selectedType)
                this.form.member_type = selectedType;
            },

            onFilterSubmit()
            {
                this.loadPayment();
                this.getUser();
                this.$refs.filter.hide();
            },

            createStore() {  
                if(this.form.category==0 && this.form.member_type.length==0)
                {
                    Swal.fire(
                        "Failed!",
                        "Please select member type.",
                        "warning"
                    );
                    //$("#addNewstore").modal("hide");
                }
                else
                {
                    if (this.is_busy) return;
                    this.is_busy = true;
                    $("#addNewstore").modal("hide");
                    this.form.post("/api/payment/method")
                    .then(() => {
                        Swal.fire(
                            "Created!",
                            "Payment Product Created Successfully.",
                            "success"
                        );
                        this.loadPayment(); 
                        this.getUser();      
                    })
                    .catch((err) => {

                        Swal.fire(
                            "Failed!",
                            "Ops, Something went wrong, try again.",
                            "warning"
                        );
                    })
                    .finally(() => {
                        this.is_busy = false;
                        this.getUser();
                        this.loadPayment();
                    });
                }
            },

            updateStore() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewstore").modal("hide");
                this.form.put("/api/payment/method/" + this.form.id)
                .then(() => {
                    Swal.fire("Updated!", "Payment Product Updated Successfully.", "success");        
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
                    this.loadPayment(); 
                    this.getUser(); 
                });
            },

            createDebit() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#debitmodal").modal("hide");
                this.formDebit.post("/api/payment/debit")
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
                            "Members Debited Successfully.",
                            "success"
                        );
                    }
                    this.loadPayment(); 
                    this.getUser();      
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

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
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
                        axios.get('/api/payment/method/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Payment Product(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadPayment();
                            this.getUser();
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

            orderByName() {
                if(this.filterForm.orderName==1) {
                   this.filterForm.orderName = 0; 
                }
                else {
                    this.filterForm.orderName = 1;
                }

                this.filterForm.orderEmail = 2;
                this.filterForm.orderTarget = 2; 
                this.filterForm.orderLimit = 2; 
                this.loadPayment();
                this.getUser();
            },

            orderByEmail() {
                if(this.filterForm.orderEmail==1) {
                   this.filterForm.orderEmail = 0; 
                }
                else {
                    this.filterForm.orderEmail = 1;
                }

                this.filterForm.orderTarget = 2;
                this.filterForm.orderLimit = 2;
                this.filterForm.orderName = 2; 
                this.loadPayment();
                this.getUser();
            },

            orderByTarget() {
                if(this.filterForm.orderTarget==1) {
                   this.filterForm.orderTarget = 0; 
                }
                else {
                    this.filterForm.orderTarget = 1;
                }

                this.filterForm.orderLimit = 2;
                this.filterForm.orderEmail = 2;
                this.filterForm.orderName = 2; 
                this.loadPayment();
                this.getUser();
            },

            orderByLimit() {
                if(this.filterForm.orderLimit==1) {
                   this.filterForm.orderLimit = 0; 
                }
                else {
                    this.filterForm.orderLimit = 1;
                }

                this.filterForm.orderTarget = 2;
                this.filterForm.orderEmail = 2;
                this.filterForm.orderName = 2; 
                this.loadPayment();
                this.getUser();
            },
        },

        computed: {
            selectAll: {
                get: function () {
                    return this.payments.data ? this.action.selected.length == this.payments.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.payments.data.forEach(function (store) {
                            selected.push(store.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
