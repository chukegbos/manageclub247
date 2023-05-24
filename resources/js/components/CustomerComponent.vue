<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>List of Members</strong></h2>
                </div>

                <div class="col-md-8">
                    <!-- <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2" v-if="admin.role==1 || admin.role==5  || admin.role==11">
                        Add Member
                    </b-button> -->
                    <b-button size="sm" variant="outline-info" class="pull-right m-2" @click="calcDebt"> <i class="fa fa-print"></i> Recalculate Debt</b-button>
                    <b-button size="sm" variant="outline-info" class="pull-right m-2" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>

                    <b-button variant="outline-primary" size="sm" class="pull-right m-2" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>

                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search Member"></b-form-input>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Member's Types</label>
                                    <select v-model="filterForm.member_type" class="form-control">
                                        <option value=null> -- Select Type-- </option>
                                        <option v-for="option in member_types" :key="option.id" v-bind:value="option.id">
                                            {{ option.title }}
                                        </option>
                                    </select>
                                </div>
                             
                                <div class="col-md-6 form-group">
                                    <label>State of Residence</label>
                                    <select v-model="filterForm.state" class="form-control">
                                        <option value=null> -- Select State-- </option>
                                        <option v-for="option in states" :key="option.id" v-bind:value="option.id">
                                            {{ option.title }}
                                        </option>
                                    </select>
                                </div>
                            </div>


                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="users.data.length>0">
                    
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th v-if="admin.role==1 || admin.role==5 || admin.role==11"> 
                                    <span v-if="unprintable==false">
                                        <input type="checkbox" v-model="selectAll">
                                    </span>
                                </th>
                                <th width="200px">Name</th>
                                <th>Member ID</th>
                                <th>Member Type</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Approval</th>
                                <th>Door Access</th>
                                <th>Admission Date</th>
                                <th>Date Added</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td v-if="admin.role==1 || admin.role==5 || admin.role==11"> 
                                    <span v-if="unprintable==false">
                                        <input type="checkbox" v-model="action.selected" :value="user.id" number>
                                    </span>
                                </td>
                                
                                <td>{{ user.name }} <span v-if="user.state"><br>{{ user.state }} State</span></td>
                                <td>
                                    {{ user.unique_id }}
                                </td>
                                <td>
                                    <span v-if="user.c_person.member_type==15" class="text-warning">
                                        {{ user.c_person.get_member_type }}
                                    </span>
                                    <span v-else-if="user.c_person.member_type==14" class="text-danger">
                                        {{ user.c_person.get_member_type }}
                                    </span>
                                    <span v-else class="text-info">
                                        {{ user.c_person.get_member_type }}
                                    </span>
                                </td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.phone }}</td>
                                <td>
                                    <span v-if="user.approved==1" class="badge badge-info">Yes</span>
                                    <span v-else class="badge badge-danger">No</span>
                                    <br>
                                  
                                    {{ user.approved_by }}
                                </td>
                                <td>
                                    <span v-if="user.door_access==1" class="badge badge-info">Yes</span><span v-else class="badge badge-danger">No</span>
                                    <br>
                                    {{ user.access }}
                                </td>
                                <td>{{ user.entrance_date | myDate}}</td>
                                <td>{{ user.created_at | myDate}}</td>
                                <td>
                                    <b-dropdown id="dropdown-right" text="Action" variant="info" v-if="user.c_person.member_type!=14">
                                        <b-dropdown-item href="javascript:void(0)" @click="view(user)">View</b-dropdown-item>

                                        <span v-if="admin.role==1 || admin.role==4">
                                            <b-dropdown-item href="javascript:void(0)" @click="approveUser(user)" v-if="!user.approved">Approve</b-dropdown-item>
                                        </span>
                                        
                                        <span v-if="admin.role==1 || admin.role==4 || admin.role==5 || admin.role==11">
                                            <b-dropdown-item href="javascript:void(0)" @click="doorUser(user)" v-if="user.door_access==0">Activate Door</b-dropdown-item>

                                            <b-dropdown-item href="javascript:void(0)" @click="doorUser(user)" v-else>Deactivate Door</b-dropdown-item>
                                        </span>

                                        <span v-if="admin.role==1 || admin.role==5 || admin.role==11">
                                            <b-dropdown-item href="javascript:void(0)" @click="editModal(user)">Edit</b-dropdown-item>

                                            <b-dropdown-item href="javascript:void(0)" @click="deleteUser(user.id)">Delete</b-dropdown-item>

                                        
                                            <b-dropdown-item href="javascript:void(0)" @click="suspendUser(user)" v-if="user.c_person.member_type!=15">Suspend</b-dropdown-item>

                                            <b-dropdown-item href="javascript:void(0)" @click="unsuspendUser(user)" v-else>Unsuspend</b-dropdown-item>

                                            <b-dropdown-item href="javascript:void(0)" @click="lateUser(user)">Mark As Deceased</b-dropdown-item>
                                        </span>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Member Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} Members</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="users" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="addNewUser" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewUserLabel" v-show="!editMode">
                                Add New
                            </h5>
                            <h5 class="modal-title" id="addNewUserLabel" v-show="editMode">
                                Update User's Info
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="editMode ? updateUser() : createUser()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input v-model="form.name" type="text"
                                        name="name" class="form-control" placeholder="Fullname" :class="{'is-invalid': form.errors.has('name')}"/>
                                    <has-error :form="form" field="name"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input v-model="form.email" type="email" name="email" class="form-control" placeholder="Email Address"
                                        :class="{'is-invalid': form.errors.has('email')}"/>
                                    <has-error :form="form" field="email"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        class="form-control"
                                        placeholder="Phone Number"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'phone'
                                            )
                                        }"
                                    />
                                    <has-error :form="form" field="phone"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Address</label>
                                    <input v-model="form.address" type="text"
                                        class="form-control"
                                        placeholder="Address"
                                        :class="{'is-invalid': form.errors.has('address')}"
                                    />
                                    <has-error :form="form" field="address"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>State</label>
                                    <input v-model="form.state" type="text"
                                        class="form-control"
                                        placeholder="State"
                                        :class="{'is-invalid': form.errors.has('state')}"
                                    />
                                    <has-error :form="form" field="state"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Credit Unit</label>
                                    <input v-model="form.credit_unit" type="number" step=".01"
                                        class="form-control"
                                        :class="{'is-invalid': form.errors.has('credit_unit')}"
                                    />
                                    <has-error :form="form" field="credit_unit"></has-error>
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


            <div class="modal fade" id="suspendUser" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewUserLabel">
                                Suspend Member
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="suspendGoUser()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input v-model="formSuspend.name" type="text"
                                        name="name" class="form-control" readonly="true"/>
                                </div>


                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input v-model="formSuspend.start_date" type="date"
                                        class="form-control"
                                        :class="{'is-invalid': form.errors.has('start_date')}"
                                    />
                                    <has-error :form="form" field="start_date"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>End Date</label>
                                    <input v-model="formSuspend.end_date" type="date"
                                        class="form-control"
                                        :class="{'is-invalid': form.errors.has('end_date')}"
                                    />
                                    <has-error :form="form" field="end_date"></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-success">
                                    Suspend
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="unsuspendUser" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewUserLabel">
                                Unsuspend Member
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="unsuspendGoUser()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input v-model="formunSuspend.name" type="text"
                                        name="name" class="form-control" readonly="true"/>
                                </div>


                                <div class="form-group">
                                    <label>Reason for unsuspension</label>
                                    <input v-model="formunSuspend.reason" type="type"
                                        class="form-control"
                                        :class="{'is-invalid': form.errors.has('reason')}"
                                    />
                                    <has-error :form="form" field="start_date"></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-success">
                                    UnSuspend
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



            <div class="modal fade" id="debitMember" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewUserLabel">
                                Debit Member
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="suspendGoUser()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input v-model="formSuspend.name" type="text"
                                        name="name" class="form-control" readonly="true"/>
                                </div>


                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input v-model="formSuspend.start_date" type="date"
                                        class="form-control"
                                        :class="{'is-invalid': form.errors.has('start_date')}"
                                    />
                                    <has-error :form="form" field="start_date"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>End Date</label>
                                    <input v-model="formSuspend.end_date" type="date"
                                        class="form-control"
                                        :class="{'is-invalid': form.errors.has('end_date')}"
                                    />
                                    <has-error :form="form" field="end_date"></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-success">
                                    Suspend
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" id="printMe">
            <div v-if="unprintable==true">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-center"><strong>List of Members</strong></h2>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th width="200px">Name</th>
                                    <th>Member ID</th>
                                    <th>Member Type</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Photo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in totalusers" :key="user.id">
                                    <td>{{ user.name }} <span v-if="user.state"><br>{{ user.state }} State</span></td>
                                    <td>{{ user.unique_id }}</td>
                                    <td>
                                        <span v-if="user.c_person.member_type==15" class="text-warning">
                                            {{ user.c_person.get_member_type }}
                                        </span>
                                        <span v-else-if="user.c_person.member_type==14" class="text-danger">
                                            {{ user.c_person.get_member_type }}
                                        </span>
                                        <span v-else class="text-info">
                                            {{ user.c_person.get_member_type }}
                                        </span>
                                    </td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.phone }}</td>
                                    <td>
                                        <span v-if="user.image">
                                            <img class="img-fluid img-responsive" :src="'/img/members/'+ user.image" style="height:40px; display: block; margin-left: auto; margin-right: auto; width: 40px; border-radius:10px">
                                        </span>
                                        <span v-else>
                                            No Photo
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
export default {
    created() {
        this.loadOther();
        this.loadUsers();
    },

    data() {
        return {
            is_busy: false,
            editMode: false,
            nairaSign: "&#x20A6;",
            model: {},
            users: {},
            member_types: [],
            sections: [],
            products: [],
            states: [],
            lgas: {},
            admin: "",
            form: new Form({
                id: "",
                name: "",
                email: "",
                phone: "",
                address: "",
                state: "",
                credit_unit: "",
            }),

            formDebit: new Form({
                id: "",
                name: "",
                start_date: "",
                end_date: "",
            }),

            formSuspend: new Form({
                id: "",
                name: "",
                start_date: "",
                end_date: "",
            }),

            formunSuspend: new Form({
                id: "",
                name: "",
                reason: "",
            }),

            filterForm: {
                name: '',
                selected: '10',
                orderName: 1,
                orderEmail: 1,
                orderPerson: 1,
                member_type: null,
                state: null,
            },
            action: {
                selected: []
            },
            users: {
                data: {},
            },
            count_all: '',
            unprintable: false,
            totalusers: [],
        };
    },

    methods: {
        onChange(event) {
            this.filterForm.selected = event.target.value;
            this.loadUsers();
            
            this.loadOther();
        },

        loadOther(){
            axios.get('/api/customer/details')
            .then((response) => {
                this.member_types = response.data.member_types;
                this.sections = response.data.sections;
                this.states = response.data.states;
                this.products = response.data.products;
            })
        },

        onPrint() {
            if (this.is_busy) return;
            this.is_busy = true;

            axios.get("/api/customer/all")
            .then(({ data }) => {
                this.totalusers = data.totalusers;
                if(this.totalusers.length > 0){
                    this.unprintable = true;
                    this.$htmlToPaper('printMe');
                    this.unprintable = false;
                }
            })
            .finally(() => {
                this.is_busy = false;
            });
            // this.loadUsers();
            
            // this.loadOther();
        },

        getResults(page = 1) {
            axios.get("/api/customer?page=" + page, { params: this.filterForm })
            .then(response => {
                this.users = response.data.customers;
            });
        },

        editModal(user) {
            this.$router.push({ path: "/admin/customers/create/" + user.id});
        },

        newModal() {
            
            // this.$router.push({ path: "/admin/customers/create"});
        },

        calcDebt(){
            if (this.is_busy) return;
            this.is_busy = true;
       
            axios.get("/api/calcDebt")
            .then(() => {
                Swal.fire(
                    "Success!",
                    "Debt Generated Successfully.",
                    "success"
                );
                this.loadUsers();
                 this.loadOther();
            })
            .finally(() => {
                this.is_busy = false;
            });
        },

        loadUsers() {
            if (this.is_busy) return;
            this.is_busy = true;
       
            axios.get("/api/customer", { params: this.filterForm })
            .then(({ data }) => {
                if(this.filterForm.selected==0)
                {
                    this.users.data = data.customers;
                }
                else{
                    this.users = data.customers;
                }
                this.count_all = data.all;
                // this.totalusers = data.totalusers;
                this.admin = data.user;
            })
            .finally(() => {
                this.is_busy = false;
            });
        },

        onFilterSubmit()
        {
            this.loadUsers();
            
            this.loadOther();
            this.$refs.filter.hide();
        },

        createUser() {
            this.form.post("/api/customer")
            .then(() => {
                $("#addNewUser").modal("hide");

                Swal.fire(
                    "Created!",
                    "Member Created Successfully.",
                    "success"
                );
                this.loadUsers();
                
                this.loadOther();
            })
            .catch(() => {
                Swal.fire(
                    "Failed!",
                    "Ops, Something went wrong, try again.",
                    "warning"
                );
            });
        },

        updateUser() {
            this.form.put("/api/customer/" + this.form.id)

            .then(() => {
                $("#addNewUser").modal("hide");

                Swal.fire("Updated!", "Member Updated Successfully.", "success");
                this.$Progress.finish();
                this.loadUsers();
                
                this.loadOther();
            })

            .catch();
        },

        deleteUser(id) {
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
                    this.$Progress.start();
                    this.form.delete("/api/customer/" + id)
                    .then(() => {
                        Swal.fire(
                            "Deleted!",
                            "Member has been deleted.",
                            "success"
                        );
                        this.$Progress.finish();
                        this.loadUsers();
                        
                        this.loadOther();
                    })

                    .catch(() => {
                        Swal(
                            "Failed!",
                            "Ops, Something went wrong, try again.",
                            "warning"
                        );
                    });
                }
            });
        },

        createInvoice(user){
            this.$router.push({ path: "/sale/shopping-cart/" + user.unique_id});
        },

        formatPrice(value) {
            let val = (value/1).toFixed(2).replace(',', '.')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        },

        view(user) {
            this.$router.push({ path: "/admin/customers/" + user.unique_id });
        },

        onDeleteAll(id) { 
            if(id){
                this.action.selected.push(id);
            }

            console.log(this.action)
            
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
                    axios.get('/api/customer/delete', { params: this.action})
                    .then(() => {
                        Swal.fire(
                            "Deleted!",
                            "Member(s) deleted.",
                            "success"
                        );
                        this.is_busy = false;
                        this.loadUsers();
                        
                        this.loadOther();
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
            this.filterForm.orderPerson = 2; 
            this.loadUsers();
            
            this.loadOther();
        },

        orderByEmail() {
            if(this.filterForm.orderEmail==1) {
               this.filterForm.orderEmail = 0; 
            }
            else {
                this.filterForm.orderEmail = 1;
            }

            this.filterForm.orderPerson = 2;
            this.filterForm.orderName = 2; 
            this.loadUsers();
            
            this.loadOther();
        },

        orderByPerson() {
            if(this.filterForm.orderPerson==1) {
               this.filterForm.orderPerson = 0; 
            }
            else {
                this.filterForm.orderPerson = 1;
            }

            this.filterForm.orderEmail = 2;
            this.filterForm.orderName = 2; 
            this.loadUsers();
            
            this.loadOther();
        },

        debitMember(user){ 
            this.formDebit.id = user.unique_id;
            this.formDebit.name = user.name;
            $("#debitMember").modal("show");
        },

        suspendUser(user) { 
            Swal.fire({
                title: "Are you sure  you want to suspend member?",
                text: "The member will be prevented access from the club!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes!"
            })
            .then(result => {
                if (result.value) {
                    this.formSuspend.id = user.unique_id;
                    this.formSuspend.name = user.name;
                    $("#suspendUser").modal("show");
                }
            });
        },

        unsuspendUser(user) { 
            Swal.fire({
                title: "Are you sure  you want to unsuspend member?",
                text: "The member will be granted access to the club!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes!"
            })
            .then(result => {
                if (result.value) {
                    this.formunSuspend.id = user.unique_id;
                    this.formunSuspend.name = user.name;
                    $("#unsuspendUser").modal("show");
                }
            });
        },

        lateUser(user) { 
            Swal.fire({
                title: "Are you sure this member is late?",
                text: "This action cannot be reversed again",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes!"
            })
            .then(result => {
                if (result.value) {
                    axios.get("/api/customer/late/"+user.unique_id)
                    .then(() => {
                        Swal.fire(
                            "Fine!",
                            "Member marked as late.",
                            "success"
                        );
                        this.is_busy = false;
                        this.loadUsers();
                        
                        this.loadOther();
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


        approveUser(user) { 
            Swal.fire({
                title: "Are you sure you want to approve this member?",
                text: "Approving this member means that this member has fulfilled every rights",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes!"
            })
            .then(result => {
                if (result.value) {
                    if (this.is_busy) return;
                this.is_busy = true;
                    axios.get("/api/customer/approve/"+user.unique_id)
                    .then(() => {
                        Swal.fire(
                            "Done!",
                            "Member approved.",
                            "success"
                        );
                        this.is_busy = false;
                        this.loadUsers();
                        
                        this.loadOther();
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


        doorUser(user) { 
            Swal.fire({
                title: "Are you sure?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes!"
            })
            .then(result => {
                if (result.value) {
                    if (this.is_busy) return;
                this.is_busy = true;
                    axios.get("/api/customer/dooraccess/"+user.unique_id)
                    .then(() => {
                        Swal.fire(
                            "Done!",
                            "Done.",
                            "success"
                        );
                        this.is_busy = false;
                        this.loadUsers();
                        
                        this.loadOther();
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

        suspendGoUser(id) { 
            $("#suspendUser").modal("hide");
            if (this.is_busy) return;
            this.is_busy = true;

            this.formSuspend.post("/api/customer/suspend")
            .then(() => {
                Swal.fire(
                    "Deleted!",
                    "Member suspended.",
                    "success"
                );
                this.is_busy = false;
                this.loadUsers();
                
                this.loadOther();
            })

            .catch(() => {
                Swal.fire(
                    "Failed!",
                    "Ops, Something went wrong, try again.",
                    "warning"
                );
                this.is_busy = false;
            });
        },


        unsuspendGoUser(id) { 
            $("#unsuspendUser").modal("hide");
            if (this.is_busy) return;
            this.is_busy = true;

            this.formunSuspend.post("/api/customer/unsuspend")
            .then(() => {
                Swal.fire(
                    "Hurray!",
                    "Member unsuspended.",
                    "success"
                );
                this.is_busy = false;
                this.loadUsers();
                
                this.loadOther();
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
