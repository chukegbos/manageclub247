<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>List of Suppliers</strong></h2>
                </div>

                <div class="col-md-8">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2" v-if="admin.role==1 && admin.role==5">
                        Add Supplier
                    </b-button>

                    <b-button variant="outline-primary" size="sm" class="pull-right m-2" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Report Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Keyword:" label-for="keyword">
                                <b-form-input
                                    id="keyword"
                                    v-model="filterForm.keyword"
                                    type="text"
                                ></b-form-input>
                            </b-form-group>
                            <b-button type="submit" variant="primary">Search</b-button>
                        </b-form>
                    </b-modal>

                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="users.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th v-if="admin.role==1 && admin.role==5">
                                <span v-if="unprintable==false">
                                    <input type="checkbox" v-model="selectAll">
                                </span>
                            </th>
                            <th>Supplier</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th v-if="admin.role==1 && admin.role==5">Modify</th>
                        </tr>

                        <tr v-for="user in users.data" :key="user.id">
                            <td v-if="admin.role==1 && admin.role==5"> <input type="checkbox" v-model="action.selected" :value="user.id" number></td>
                            <td>{{ user.supplier_name }}</td>
                            <td>{{ user.contact_person }}</td>
                            <td>{{ user.email }}</td>
                            <td>{{ user.phone }}</td>
                            <td>{{ user.address }}</td>
                           
                            <td v-if="admin.role==1 && admin.role==5">
                                <a href="javascript:void(0)" @click="editModal(user)">
                                    <i class="fa fa-edit"></i>
                                </a>
                                |
                                <a href="javascript:void(0)" @click="onDeleteAll(user.id)">
                                    <i class="fa fa-trash text-red"></i>
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Supplier Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} suppliers</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="users" @pagination-change-page="getResults"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="modal fade"
                id="addNewUser"
                tabindex="-1"
                role="dialog"
                aria-labelledby="addNewUserLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewUserLabel"
                                v-show="!editMode"
                            >
                                Add New
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewUserLabel"
                                v-show="editMode"
                            >
                                Update Supplier
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
                        <form
                            @submit.prevent="
                                editMode ? updateUser() : createUser()
                            "
                        >
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Name of Supplier</label>
                                    <input
                                        v-model="form.supplier_name"
                                        type="text"
                                        name="supplier_name"
                                        required
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'supplier_name'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="supplier_name"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Contact Person</label>
                                    <input
                                        v-model="form.contact_person"
                                        type="text"
                                        name="contact_person"
                                        required
                                        class="form-control"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'contact_person'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="contact_person"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        name="email"
                                        required
                                        class="form-control"
                                        placeholder="Email Address"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'email'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="email"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        required
                                        class="form-control"
                                        placeholder="Phone Number"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'phone'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="phone"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Address</label>
                                    <input
                                        v-model="form.address"
                                        type="text"
                                        required
                                        class="form-control"
                                        placeholder="Email Address"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'address'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="address"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Account Number</label>
                                    <input
                                        v-model="form.bank_account"
                                        class="form-control"
                                        @change="onType()"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Select Bank</label>
                                    <b-form-select
                                        v-model="form.bank_name"
                                        :options="banks"
                                        value-field="code"
                                        text-field="name"
                                        label="Select Bank"
                                        v-on:change="onSet($event)"
                                    >
                                        <template v-slot:first>
                                            <b-form-select-option
                                                :value="null"
                                                disabled
                                                >-- Please select your
                                                bank--</b-form-select-option
                                            >
                                        </template>
                                    </b-form-select>
                                </div>

                                <div class="form-group">
                                    <label>Account Name</label>
                                    <input
                                        v-model="form.account_name"
                                        readonly="true"
                                        class="form-control"
                                    />
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
        </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.getBank();
            this.getUser();
            this.loadUsers();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                model: {},
                users: {},
                user: "",
                admin: '',
                usersss: '',
                form: new Form({
                    id: "",
                    supplier_name: "",
                    contact_person: "",
                    email: "",
                    phone: "",
                    address: "",
                    bank_name: null,
                    account_name: "",
                    bank_account: ""
                }),

                bank_detail: {
                    bank_id: "",
                    bank_account: ""
                },

                banks: {},

                filterForm: {
                    keyword: '',
                    selected: '10',
                },
                action: {
                    selected: []
                },
                users: {
                    data: {},
                },
                count_all: '',
                unprintable: false,
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.getUser();
                this.getBank();
                this.loadUsers();
            },
        
            getResults(page = 1) {
                axios.get("api/suppliers?page=" + page).then(response => {
                    this.users = response.data.suppliers;
                });
            },

            onFilterSubmit() {
                this.getUser();
                this.getBank();
                this.loadUsers();
                
                this.$refs.filter.hide();
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
            },
        
            loadUsers() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/suppliers", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.users.data = data.suppliers;
                    }
                    else{
                        this.users = data.suppliers;
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

            getBank() {
                axios.get("/api/user/bank")
                .then(({ data }) => {
                    console.log(this.banks);
                    this.banks = data;
                });
            },

            onType() {
                this.bank_detail.bank_account = this.form.bank_account;
            },

            onSet(event) {
                this.bank_detail.bank_id = event;
                if (this.is_busy) return;
                this.is_busy = true;
                axios.post("/api/user/fetchbank", this.bank_detail)
                .then(({ data }) => {
                    console.log(data);

                    if (data.data.account_name == "error") {
                        Swal.fire(
                            "Failed!",
                            "Such account do not exist, check to see if you are correct",
                            "error"
                        );
                    } else {
                        this.form.account_name = data.data.account_name;
                    }
                })
                .catch(err => {
                    Swal.fire(
                        "Failed!",
                        "Such account do not exist, check to see if you are correct",
                        "error"
                    );
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            editModal(user) {
                (this.editMode = true), this.form.reset();
                $("#addNewUser").modal("show");
                this.form.fill(user);
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewUser").modal("show");
            },

            createUser() {
                this.$Progress.start();

                this.form
                    .post("api/suppliers")
                    .then(() => {
                        $("#addNewUser").modal("hide");

                        Swal.fire(
                            "Created!",
                            "Supplier Created Successfully.",
                            "success"
                        );
                        this.getUser();
                        this.getBank();
                        this.loadUsers();
                    })

                    .catch(() => {
                        this.$Progress.fail();
                    });
            },

            updateUser() {
                this.$Progress.start();

                this.form
                    .put("api/suppliers/" + this.form.id)
                    .then(() => {
                        $("#addNewUser").modal("hide");

                        Swal.fire(
                            "Updated!",
                            "Supplier Updated Successfully.",
                            "success"
                        );
                        this.$Progress.finish();
                        this.getUser();
                        this.getBank();
                        this.loadUsers();
                    })

                    .catch(() => {
                        this.$Progress.fail();
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
                        axios.get('/api/suppliers/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Outlet(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.getUser();
                            this.getBank();
                            this.loadUsers();                          
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
                    return this.users.data ? this.action.selected.length == this.users.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.users.data.forEach(function (user) {
                            selected.push(user.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
