<template>
    <b-overlay :show="is_busy" rounded="sm">

        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-5">
                    <h3>Book of Original Entry (Journal)</h3>
                
                </div>

                <div class="col-md-7">

                    <!--<b-button size="sm" variant="outline-info" class="pull-right m-2" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>-->
                        
                </div>
            </div>
     
            <div class="card">
                <div class="card-body table-responsive p-0" v-if="ledgers.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th width="200px">Date</th>
                            <th width="300px">Particular</th>
                            <th width="300px">Account</th>
                            
                            <th>Debit</th>
                            <th>Credit</th>
                            
                        </tr>

                        <tr  v-for="(account, index) in ledgers.data">
                            <td>{{ account.ledger_date | myDate }}<br>
                            </td>

                            <td>{{ account.description }}<br></td>
                            <td><b>{{ account.debit }} a/c</b><br><br>
                                <span class="ml-2">To <b>{{ account.credit }} a/c </b></span>
                            </td>
                            <td>
                                <span v-html="nairaSign"></span>
                                {{ formatPrice(account.amount) }}<br>
                            </td>  
                            <td>
                                <br><br>
                                <span v-html="nairaSign"></span>
                                {{ formatPrice(account.amount) }}
                            </td>
                        </tr>


                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Ledger Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }}</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="ledgers" @pagination-change-page="getResults"></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addNewUserLabel" v-show="!editMode">
                            Post Transaction
                        </h5>
                        <h5 class="modal-title" id="addNewUserLabel" v-show="editMode">
                            Update
                        </h5>

                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="editMode ? update() : create()">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Date</label>
                                <input v-model="form.ledger_date" type="date"
                                    name="date" class="form-control" :class="{'is-invalid': form.errors.has('ledger_date')}"/>
                                <has-error :form="form" field="ledger_date"></has-error>
                            </div>
                      
                            <div class="form-group">
                                <label>Debit</label>
                                <v-select label="account" :options="accounts" @input="setSelected" ></v-select>
                            </div>

                            <div class="form-group">
                                <label>Credit</label>
                                <v-select label="account" :options="accounts" @input="setSec" ></v-select>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea v-model="form.description" class="form-control" :class="{'is-invalid': form.errors.has('description')}" ></textarea>
                                <has-error :form="form" field="description"></has-error>
                            </div>

                            <div class="form-group">
                                <label>Amount</label>
                                <input v-model="form.amount" type="number"
                                    name="amount" class="form-control" :class="{'is-invalid': form.errors.has('amount')}"/>
                                <has-error :form="form" field="amount"></has-error>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                Close
                            </button>
                            <button v-show="editMode" type="submit" class="btn btn-success">
                                Update
                            </button>
                            <button v-show="!editMode" type="submit" class="btn btn-primary">
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import moment from 'moment';
    import axios from 'axios';

    import {debounce} from 'lodash';

    export default {
        created() {
            this.loadAccount(); 
            this.getUser();
            this.loadSite();
        },

        data() {
            return {
                site: '',
                filterForm: {
                    end_date: moment().format("YYYY-MM-DD"),
                    start_date: '',
                    account: '',
                    selected: '10',
                },

              
                accounts: [],
                nairaSign: "&#x20A6;",
                ledgers: {},
                ledgers: {
                    data: '',
                },
                editMode: false,
                is_busy: false,
                account: '',
                form: new Form({
                    id: "",
                    ledger_date: '',
                    account_one: "",
                    account_two: "",
                    amount: '',
                    description: '',
                }),

                action: {
                    selected: []
                },
                count_all: '',
                page: '',
                errors: []
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.page = 0;
                this.loadAccount(); 
                this.getUser();

                this.loadSite();
            },

            setSelected(value) {
                this.form.account_one = value.id;
            },

            setSec(value) {
                this.form.account_two = value.id;
            },

            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.unprintable = true;
                this.$htmlToPaper('printMe');
                this.unprintable = false;
                this.is_busy = false;
            },

            getResults(page = 1) {
                this.page = page
                axios.get("/api/ledger?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.ledgers = response.data.ledgers;
                });
            },

            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;

                
                if(this.$route.params.ref_id){
                    this.filterForm.account = this.$route.params.ref_id;
                }
                axios.get("/api/ledger", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.ledgers.data = data.ledgers;
                    }
                    else{
                        this.ledgers = data.ledgers;
                    }
                    this.count_all = data.all;
                    this.account = data.account;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadAccount(); this.getUser();
                this.loadSite();
                this.$refs.filter.hide();
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNew").modal("show");
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            create() {
                this.form.post("/api/ledger")
                .then((data) => {
                    $("#addNew").modal("hide");
                    if(data.data.error){
                        Swal.fire(
                            "Failed!",
                            data.data.error,
                            "warning"
                        );
                    }
                    else
                    {
                        Swal.fire(
                            "Created!",
                            "Ledger Created Successfully.",
                            "success"
                        );
                    }
                    this.loadAccount(); 
                    this.getUser();
                    this.loadSite();
                    this.form.account_two = '';
                    this.form.account_one = '';
                })
                .catch(() => {
                    Swal(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                });
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },

            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.accounts = data.accounts;
                });
            },
        }
    };
</script>
