<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Login Histories</strong></h2>
                </div>

                <div class="col-md-6">
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search logins"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="logins.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Bar</th>
                                <th>Manager</th>
                                <th>Login</th>
                                <th>Logout</th>
                                <th>Status</th>
                                <th>Verified</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="login in logins.data" :key="login.id">
                                <td>{{ login.bar_name }}</td>
                                <td>{{ login.manager }}</td>
                                <td>{{ login.login }}</td>
                                <td>{{ login.logout }}</td>

                                <td>
                                    <span v-if="login.verified_by" class="btn btn-success btn-sm">Verified</span>
                                    <span v-else class="btn btn-danger btn-sm">Not Verified</span>
                                </td>
                                <td>{{ login.verified_by }}</td>
                                <td v-if="unprintable==false">
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                        <!--<b-dropdown-item href="javascript:void(0)" @click="editModal(login)">View PRogress</b-dropdown-item>-->

                                        <b-dropdown-item href="javascript:void(0)" @click="onApprove(login.id)">Sign-off</b-dropdown-item>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Login Detail Found.</strong></h3></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Login Details</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="logins" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.loadLogin();
        },

        data() {
            return {
                is_busy: false,
                logins: {},
                filterForm: {
                    name: '',
                    selected: '10',
                },
                action: {
                    selected: []
                },
                logins: {
                    data: {},
                },
                count_all: '',
                unprintable: false,
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadLogin();
            },

            loadLogin() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/admin/logins", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.logins.data = data.logins;
                    }
                    else{
                        this.logins = data.logins;
                    }
                    this.count_all = data.all;
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

            getResults(page = 1) {
                axios.get("/api/admin/logins?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.logins = response.data.logins;
                });
            },

            editModal(login) {
                (this.editMode = true), this.form.reset();
                $("#addNewlogin").modal("show");
                this.form.fill(login);
            },

            onFilterSubmit()
            {
                this.loadLogin();
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
                        axios.get('/api/payment/logins/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "logins deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadLogin();
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

            onApprove(id) {
                Swal.fire({
                    title: "Are you sure to approve?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, Approve it!"
                })
                .then(result => {
                    if (result.value) {
                        if (this.is_busy) return;
                        this.is_busy = true;
                        axios.get('/api/admin/logins/' + id)
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "logins deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadLogin();
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
        },

        computed: {
            selectAll: {
                get: function () {
                    return this.logins.data ? this.action.selected.length == this.logins.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.logins.data.forEach(function (login) {
                            selected.push(login.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
