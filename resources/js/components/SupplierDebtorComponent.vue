<template>
    <b-overlay :show="is_busy" rounded="sm">
         <div class="container-fluid">
            
            <div class="row mb-2 p-2">
                <div class="col-md-7">
                    <h2><strong>List of Supplier Debtors</strong></h2>
                </div>

                <div class="col-md-5">
                    <b-button variant="outline-primary" class="pull-right m-2" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>

                    <b-modal id="filter-modal" ref="filter" title="Report Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Supplier:" label-for="keyword">
                                <b-form-input
                                    id="keyword"
                                    v-model="filterForm.keyword"
                                    type="text"
                                ></b-form-input>
                            </b-form-group>

                            <div class="form-group">
                                <label>Select Supplier</label>
                                
                                <v-select label="supplier_name" :options="suppliers" @input="setSelected" ></v-select>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select v-model="filterForm.status" class="form-control">
                                    <option v-for="option in options" v-bind:value="option.value">
                                        {{ option.text }}
                                    </option>
                                </select>
                            </div>

                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>  

                  <b-button size="sm" variant="outline-info"class="pull-right m-2" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>                      
                </div>
            </div>

            <div class="card" id="printMe">
                <div class="card-body table-responsive p-0" v-if="debtors.data.length">
                        <table class="table table-hover">
                            <tr>
                                <th>Ref ID</th>
                                <th>Supplier</th>
                                
                                <th>Processed By</th>
                                <th>Processing Outlet</th>
                                <th>Amount Owed (<span v-html="nairaSign"></span>)</th>
                                <th>Amount Paid (<span v-html="nairaSign"></span>)</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>

                            <tr v-for="order in debtors.data" :key="order.id">
                                <td>
                                    {{ order.trans_id }}
                                </td>
                                <td>{{ order.supplier_id }}</td>
                                
                                <td>{{ order.processed_by }}</td>
                                <td>{{ order.store_id }}</td>
                                <td>{{ formatPrice(order.amount) }}</td>
                                <td>{{ formatPrice(order.amount_paid) }}</td>
                                <td>
                                    <span class="badge badge-info" v-if="order.status==1">
                                        Paid
                                    </span>
                                    <span class="badge badge-danger" v-else>
                                        Not Paid
                                    </span>
                                </td>
                                
                                <td>
                                    <b-dropdown size="sm" right text="Action">
                                        <b-dropdown-item-button @click="viewItems(order)" v-if="order.amount_paid!=order.amount">Pay</b-dropdown-item-button>

                                        <b-dropdown-item-button @click="breakdown(order)">Break Down</b-dropdown-item-button>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Debtor Found.</strong></h3></div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="0">All</option>
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} debtors</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="debtors" @pagination-change-page="getResults"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addPay" tabindex="-1" role="dialog" aria-labelledby="addNewstoreLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pay Debt</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="create()">
                            <div class="modal-body row">
                                <div class="form-group col-md-12">
                                    <label>Ref ID</label>
                                    <input v-model="form.trans_id" type="text" name="trans_id" readonly="true" class="form-control"/>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Date of Payment</label>
                                    <input v-model="form.purchase_date" type="date" name="purchase_date" required class="form-control"/>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Amount Owed (<span v-html="nairaSign"></span>)</label>
                                    <input v-model="form.amount"
                                        type="number"
                                        name="amount"
                                        class="form-control"
                                        readonly="true"
                                    />
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Amount to pay (<span v-html="nairaSign"></span>)</label>
                                    <input v-model="form.amount_paid"
                                        type="number"
                                        name="amount_paid"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'amount_paid'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="amount_paid"
                                    ></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    Complete Transaction
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
    import VueBootstrap4Table from 'vue-bootstrap4-table';
    import moment from 'moment';

    export default {
        created() {
            this.loadSales();
            this.getUser();
        },

        data() {
            return {
                filterForm: {
                    keyword: '',
                    status: '',
                    supplier: '',
                    selected: '10',
                },

                form: new Form({
                    id: "",
                    trans_id: "",
                    amount: "",
                    amount_paid: "",
                    purchase_date: "",
                }),

                options: [
                    { text: 'Paid', value: '1' },
                    { text: 'Not Paid', value: 'o' },
                ],

                payments: [
                  { value: 'Cash', text: 'Cash' },
                  { value: 'POS', text: 'POS' },
                  { value: 'Transfer', text: 'Transfer' },
                ],
                stores: {},
                is_busy: false,
                debtors: null,
                summary: null,
                debtors: {
                    data: '',
                },
                suppliers: [],
                action: {
                    selected: []
                },
                count_all: '',
                unprintable: false,
                nairaSign: "&#x20A6;",
            };
        },

        methods: {
            onPrint() {
                this.$htmlToPaper('printMe');
            },

            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.suppliers = data.suppliers;
                });
            },

            setSelected(value) {
                this.filterForm.supplier = value.id;
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadStores();
                this.getUser();
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            getResults(page = 1) {

                axios.get('/api/suppliers/debtors?page=' + page, { params: this.filterForm })
                .then(response => {
                    this.debtors = response.data.report_data;
                });
            },

            loadSales() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/suppliers/debtors', { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.debtors.data = data.report_data;
                    }
                    else{
                        this.debtors = data.report_data;
                    }
                    this.count_all = data.all;
                })
                .catch(() => {
                    Swal,fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadSales();
                this.getUser();
                this.$refs.filter.hide();
            },

            viewItems(order) {
                $("#addPay").modal("show");
                this.form.trans_id = order.trans_id;
                this.form.amount = Number(order.amount) - Number(order.amount_paid);
                this.form.amount_paid = '';
            },

            breakdown(order) {
                this.$router.push({ path: '/admin/purchases/' + order.trans_id});
            },

            create() {
                
                this.form.post("/api/suppliers/debtors")
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Paid",
                        "success"
                    );
                    $("#addPay").modal("hide");
                    this.loadSales(); 
                    this.getUser();    
                })
                .catch(() => {
                    Swal,fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                });
            },
        },
    };
</script>

<style>
    .list_product{
        list-style: none;
    }
</style>
