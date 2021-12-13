<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
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

            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Account Ledger
                            </div>
                            <div class="card-tools">
                                <button class="btn btn-success" @click="newModal">
                                    Add <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0" v-if="ledgers.data.length>0">
                            <table class="table table-hover">
                                <tr>
                                    <th width="200px">Date</th>
                                    <th width="300px">Account</th>
                                    <th width="300px">Description</th>
                                    <th>Credit</th>
                                    <th>Debit</th>
                                </tr>

                                <tr
                                    v-for="account in ledgers.data"
                                    :key="account.account_id"
                                >
                                    <td>{{ account.ledger_date | myDate }}</td>
                                    <td>{{ account.account }}</td>
                                    <td>{{ account.description }}</td>
                                    <td>{{ account.credit }}</td>       
                                    <td>{{ account.debit }}</td>
                                </tr>
                            </table>
                        </div>
                        <div v-else class="text-center alert alert-danger p-3">
                            Nothing found
                        </div>
                        <div class="card-footer" v-if="ledgers.data.length>0">
                            <pagination
                                :data="ledgers"
                                @pagination-change-page="getResults"
                            ></pagination>
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
                            <div class="form-group">
                                <label>Date</label>
                                <input v-model="form.ledger_date" type="date"
                                    name="date" class="form-control" :class="{'is-invalid': form.errors.has('ledger_date')}"/>
                                <has-error :form="form" field="ledger_date"></has-error>
                            </div>
                      
                            <div class="form-group">
                                <label>Account</label>
                                <vue-typeahead-bootstrap
                                  v-model="typeQuery"
                                  :ieCloseFix="false"
                                  :data="accounts"
                                  :serializer="data => data.account"
                                  @hit="getAccountID($event)"
                                  placeholder="Search for Account"
                                  @input="lookType"
                                />
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
        },

        data() {
            return {
                filterForm: {
                    end_date: '',
                    start_date: '',
                    account: '',
                },
                typeQuery: '',
                accounts: [],

                ledgers: [],
                ledgers: {
                    data: '',
                },
                editMode: false,
                is_busy: false,

                form: new Form({
                    id: "",
                    ledger_date: '',
                    account: "",
                    amount: '',
                    description: '',
                }),
            };
        },

        methods: {
            getResults(page = 1) {
                axios.get("/api/ledger?page=" + page)
                .then(response => {
                    this.ledgers = response.data;
                });
            },

            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;

                this.filterForm.account = this.$route.params.id;

                axios.get("/api/ledger", { params: this.filterForm })
                .then(({ data }) => {
                    this.ledgers = data;
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
                this.$refs.filter.hide();
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNew").modal("show");
            },


            create() {
                this.form.post("/api/ledger")
                .then(() => {
                    $("#addNew").modal("hide");

                    Swal.fire(
                        "Created!",
                        "Ledger Created Successfully.",
                        "success"
                    );
                    this.loadAccount();
                })
                .catch(() => {
                    Swal(
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
                    this.$Progress.finish();
                    this.loadAccount();
                })

                .catch();
            },

        }
    };
</script>
