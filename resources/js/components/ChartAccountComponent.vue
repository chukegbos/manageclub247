<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-5">
                    <h2><strong>Chart of Account</strong></h2>
                </div>

                <div class="col-md-7">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2">
                        Add New
                    </b-button>

                    <b-button variant="outline-primary" size="sm" @click="refresh()" class="pull-right m-2">
                        Refresh
                    </b-button>

                    <b-button variant="outline-primary" size="sm" class="pull-right m-2" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Account ID:">
                                <b-form-input v-model="filterForm.ref_id" type="text"></b-form-input>
                            </b-form-group>
                            <b-form-group label="Account:">
                                <b-form-input v-model="filterForm.account" type="text"></b-form-input>
                            </b-form-group>
                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="accounts.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th>Ref ID</th>
                            <th>Account</th>
                            <th>Category</th>
                            <th>Corresponding Account</th>
                            <th width="110px">Action</th>
                        </tr>

                        <tr v-for="account in accounts.data" :key="account.account_id">
                            <td>{{ account.ref_id }}</td>
                            <td>{{ account.account }}</td>
                            <td>{{ account.type }}</td>
                            <td>{{ account.balancing_account.account }}</td>
                            <!--<td>{{ account.description }}</td>-->
                           
                            <td>
                                <button class="btn btn-success btn-sm" @click="editModal(account)">
                                    <i class="fa fa-edit"></i>
                                </button>

                                <!--<<button class="btn btn-info btn-sm" @click="onView(account)">
                                    <i class="fa fa-eye"></i>
                                </button>
                                
                                button class="btn btn-danger btn-sm" @click="deleteTax(tax.id)">
                                    <i class="fa fa-trash"></i>
                                </button>-->
                            </td>
                        </tr>
                    </table>
                </div>
          
               
                <!--<div class="card-footer" v-if="accounts.data.length>0">
                    <pagination
                        :data="accounts"
                        @pagination-change-page="getResults"
                    ></pagination>
                </div>-->

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Account Found.</strong></h3></div>
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
                            <br>Showing <b>{{ filterForm.selected }}</b>  out of <b>{{ count_all }}</b> Accounts
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="accounts" @pagination-change-page="getResults" :limit="-1"></pagination>
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
                                <label>Account</label>
                                <input v-model="form.account" type="text"
                                    name="account" class="form-control" :class="{'is-invalid': form.errors.has('account')}"/>
                                <has-error :form="form" field="account"></has-error>
                            </div>

                            <div class="form-group">
                                <label>Types</label>
                                <v-select label="name" :options="types" @input="setSelected" :class="{'is-invalid': form.errors.has('type')}"></v-select>
                                <has-error :form="form" field="type"></has-error>
                            </div>

                            <div class="form-group">
                                <label>Corresponding Account</label>
                                <v-select label="account" :options="all_accounts" @input="setAcc" :class="{'is-invalid': form.errors.has('corresponding_account')}"></v-select>
                                <has-error :form="form" field="corresponding_account"></has-error>
                            </div>

                            <!--<div class="form-group">
                                <label>Description</label>
                                <textarea v-model="form.description" class="form-control" :class="{'is-invalid': form.errors.has('description')}" ></textarea>
                                <has-error :form="form" field="description"></has-error>
                            </div>-->
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
        },

        data() {
            return {
                filterForm: {
                    ref_id: '',
                    type: '',
                    account: '',
                    selected: 10,
                },
                count_all: '',
                types: [],
                all_accounts: [],
                accounts: [],

                accounts: {
                    data: [],
                },

                onChange(event) {
                    this.filterForm.selected = event.target.value;
                    this.loadAccount();
                    this.getUser();
                },

                editMode: false,
                is_busy: false,

                form: new Form({
                    id: "",
                    account: "",
                    corresponding_account: '',
                    type: '',
                    description: '',
                }),
            };
        },

        methods: {
            getResults(page = 1) {
                axios.get("/api/account?page=" + page)
                .then(response => {
                    this.accounts = response.data;
                });
            },

            refresh() {
                this.filterForm.ref_id = '';
                this.filterForm.type = '';
                this.filterForm.account = '';
                this.filterForm.selected = 10;
                this.loadAccount();
                this.getUser();
            },

            setSelected(value) {
                this.form.type = value.id;
            },

            setAcc(value) {
                this.form.corresponding_account = value.id;
            },

            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.types = data.types;
                    this.all_accounts = data.accounts;
                });
            },

            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/account", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.accounts.data = data.accounts;
                    }
                    else{
                        this.accounts = data.accounts;
                    }
                    this.count_all = data.count_all;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadAccount();
                this.getUser();
                this.$refs.filter.hide();
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                this.form.type = '';
                $("#addNew").modal("show");
            },


            create() {
                this.form.post("/api/account")
                .then(() => {
                    $("#addNew").modal("hide");

                    Swal.fire(
                        "Created!",
                        "Account Created Successfully.",
                        "success"
                    );
                    
                    this.loadAccount();
                    this.getUser();
                })
                .catch(() => {
                    Swal(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                });
            },

            update() {
                this.form.put("/api/account/" + this.form.id)

                .then(() => {
                    $("#addNew").modal("hide");

                    Swal.fire("Updated!", "Account Updated Successfully.", "success");
                    this.loadAccount();
                    this.getUser();
                })

                .catch();
            },

            editModal(account) {
                (this.editMode = true), this.form.reset();
                this.form.id = account.id;
                this.form.account = account.account;
                this.form.type = account.type_id;
                this.form.corresponding_account = account.balancing_account;
                $("#addNew").modal("show");
            },
        }
    };
</script>
