<template>
    <b-overlay :show="is_busy" rounded="sm">

        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-md-8">
                    <h4 class="text-center"><strong>Operating Balance</strong></h4>
                </div>
                <!--<div class="col-md-3">
                    <h6 class="text-center">Current: <strong><span v-html="nairaSign"></span>{{ formatPrice(site.working_capital) }}</strong></h6>
                </div>-->

                <div class="col-md-4">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2">
                        Add New
                    </b-button>

                    <b-button size="sm" variant="outline-info"class="pull-right m-2" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>

                    <b-button variant="outline-primary" size="sm" class="pull-right m-2" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">

                            <div class="row">
                                <div class="col-lg-6">
                                    <b-form-group label="Start Date:" label-for="Start Date">
                                        <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>

                                <div class="col-lg-6">
                                    <b-form-group label="End Date:" label-for="End Date">
                                        <b-form-datepicker v-model="filterForm.end_date" placeholder="End date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>
                            </div>

                            <b-form-group label="Account Type:">
                                <vue-typeahead-bootstrap
                                  v-model="typeQuery"
                                  :ieCloseFix="false"
                                  :data="accounts"
                                  :serializer="data => data.account"
                                  @hit="getSearchAccountID($event)"
                                  placeholder="Search for Account"
                                  @input="lookType"
                                />
                            </b-form-group>
                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                         
                </div>
            </div>
     
            <div class="card">
                <div class="card-body table-responsive p-0" v-if="ledgers.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th width="200px">Date</th>
                            <th width="300px">Particular</th>
                            <th width="300px">Description</th>
                            <th>Amount</th>
                        </tr>

                        <tr  v-for="(account, index) in ledgers.data">
                            <td>{{ account.ledger_date | myDate }}</td>
                            <td>Owners Equity</td>
                            <td>{{ account.description }}</td>
                            <td><span v-html="nairaSign"></span>{{ formatPrice(account.amount) }}</td> 
                        </tr>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>Nothing Found.</strong></h3></div>
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
                            Create
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
                            <!--<div class="form-group">
                                <label>Date</label>
                                <input v-model="form.ledger_date" type="date"
                                    name="date" class="form-control" :class="{'is-invalid': form.errors.has('ledger_date')}"/>
                                <has-error :form="form" field="ledger_date"></has-error>
                            </div>-->
                      

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
            this.loadSite();
        },

        data() {
            return {
                site: '',
                filterForm: {
                    end_date: moment().format("YYYY-MM-DD"),
                    start_date: '',
                    account: 33,
                    selected: '10',
                },
                typeQuery: '',
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
                    account: 33,
                    amount: '',
                    description: '',
                }),

                action: {
                    selected: []
                },
                count_all: '',
                page: '',
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.page = 0;
                this.loadAccount();

                this.loadSite();
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

                axios.get("/api/ledger", { params: this.filterForm })
                .then(({ data }) => {
                    console.log(data)
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

            lookType(){
                debounce(() => {

                    fetch('/api/account/search', {params: this.typeQuery})
                    .then(response => {
                        return response.json();
                    })
                    .then(data => {
                        this.accounts = data;
                    })
                }, 500)();
            },

            getAccountID(data){
                this.form.account = data.id;
            },

            getSearchAccountID(data){
                this.filterForm.account = data.id;
            },

            onFilterSubmit()
            {
                this.loadAccount();
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
                    $("#addNew").modal("hide");
                    this.loadSite();
                    this.loadAccount();
                    
                })
                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                });
            },

            editModal(account) {
                (this.editMode = true), this.form.reset();
                $("#addNew").modal("show");
                this.form.fill(account);
            },

            update() {
                this.form.put("/api/account/" + this.form.id)

                .then(() => {
                    $("#addNew").modal("hide");

                    Swal.fire("Updated!", "Account Updated Successfully.", "success");
                  
                    this.loadAccount();
                    this.loadSite();
                })

                .catch();
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },

        }
    };
</script>
