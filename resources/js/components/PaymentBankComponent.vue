<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>List of Registered Banks</strong></h2>
                </div>

                <div class="col-md-6">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2" v-if="admin.role==1 || admin.role==5 || admin.role==11">
                        Add Bank
                    </b-button>                       
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="banks.data.length>0" id="printMe">

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th v-if="admin.role==1 || admin.role==5 || admin.role==11"><input type="checkbox" v-model="selectAll"></th>
                                <th>Name of Bank</th>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th v-if="admin.role==1 || admin.role==5 || admin.role==11">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="bank in banks.data" :key="bank.id">
                                <td v-if="admin.role==1 || admin.role==5 || admin.role==11"> <input type="checkbox" v-model="action.selected" :value="bank.id" number></td>
                                <td>{{ bank.get_bank_name }}</td>
                                <td>{{ bank.account_name }}</td>
                                <td>{{ bank.account_number }}</td>
                                <td v-if="admin.role==1 || admin.role==5 || admin.role==11">
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                        <b-dropdown-item href="javascript:void(0)" @click="editModal(bank)">Edit</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="onDeleteAll(bank.id)">Delete</b-dropdown-item>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Payment Bank Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} Payment Banks</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="banks" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="addNewbank" tabindex="-1" role="dialog" aria-labelledby="addNewbankLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewbankLabel"
                                v-show="!editMode"
                            >
                                Add New bank
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewbankLabel"
                                v-show="editMode"
                            >
                                Update bank
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
                        <form @submit.prevent="editMode ? updateBank() : createBank()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Account Number</label>
                                    <input
                                        v-model="form.account_number"
                                        class="form-control"
                                        @change="onType()"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Select Bank</label>
                                    <b-form-select
                                        v-model="form.bank_name"
                                        :options="allbanks"
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
                                        required="true"
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
            this.loadBanks();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                admin: '',
                model: {},
                banks: {},
                allbanks: {},
                bank: "",
                form: new Form({
                    id: "",
                    bank_name: null,
                    account_name: "",
                    account_number: ""
                }),

                filterForm: {
                    name: '',
                    selected: '10',
                },
                action: {
                    selected: []
                },

                bank_detail: {
                    bank_id: "",
                    account_number: ""
                },

                banks: {
                    data: {},
                },
                count_all: '',
                unprintable: false,
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.getBank();
                this.getUser();
                this.loadBanks();
            },

            loadBanks() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/payment/banks", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.banks.data = data.banks;
                    }
                    else{
                        this.banks = data.banks;
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

            getBank() {
                axios.get("/api/user/bank")
                .then(({ data }) => {
                    this.allbanks = data;
                });
            },

            onType() {
                this.bank_detail.account_number = this.form.account_number;
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
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
            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewbank").modal("show");
            },

            getResults(page = 1) {
                axios.get("/api/payment/banks?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.banks = response.data.banks;
                });
            },

            editModal(bank) {
                (this.editMode = true), this.form.reset();
                $("#addNewbank").modal("show");
                this.form.fill(bank);
            },

            onFilterSubmit()
            {
                this.getBank();
                this.getUser();
                this.loadBanks();
            },

            createBank() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewbank").modal("hide");
                this.form.post("/api/payment/banks")
                .then((data) => {
                    if(data.data.Error){
                        Swal.fire(
                            "Failed!",
                            data.data.Error,
                            "warning"
                        );
                    }
                    else {
                        Swal.fire(
                            "Created!",
                            "Payment bank Created Successfully.",
                            "success"
                        );
                    }
                    this.getBank();
                    this.getUser();
                    this.loadBanks(); 
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
                    this.getBank();
                    this.getUser();
                    this.loadBanks(); 
                });
            },

            updateBank() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewbank").modal("hide");
                this.form.put("/api/payment/banks/" + this.form.id)
                .then(() => {
                    Swal.fire("Updated!", "Payment bank Updated Successfully.", "success");
                    this.loadBanks(); 
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
                    this.getBank();
                    this.getUser();
                    this.loadBanks(); 
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
                        axios.get('/api/payment/banks/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Payment bank(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.getBank();
                            this.getUser();
                            this.loadBanks();
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
                    return this.banks.data ? this.action.selected.length == this.banks.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.banks.data.forEach(function (bank) {
                            selected.push(bank.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
