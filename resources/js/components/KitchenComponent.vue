<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>List of Kitchens</strong></h2>
                </div>

                <div class="col-md-8">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2" v-if="admin.role==1 || admin.role==9 || admin.role==11">
                        Add Kitchen
                    </b-button>

                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search Kitchen"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="kitchens.data.length>0" id="printMe">               
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th v-if="admin.role==1 || admin.role==9 || admin.role==11">
                                    <input type="checkbox" v-model="selectAll">
                                </th>
                                
                                <th>Name</th>
                                <th>Code</th>
                                <th v-if="unprintable==false">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="kitchen in kitchens.data" :key="kitchen.id">
                                <td v-if="admin.role==1 || admin.role==9 || admin.role==11"> 
                                    <input type="checkbox" v-model="action.selected" :value="kitchen.id" number>
                                </td>
                                <td>{{ kitchen.name }}</td>
                                <td>{{ kitchen.code }}</td>
                                <td>
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                        <b-dropdown-item href="javascript:void(0)" @click="view(kitchen)">View</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="editModal(kitchen)" v-if="admin.role==1 || admin.role==6 || admin.role==7">Edit</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="onDeleteAll(kitchen.id)" v-if="admin.role==1 || admin.role==6 || admin.role==7">Delete</b-dropdown-item>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Kitchen Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} Kitchens</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="kitchens" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="addNewKitchen" tabindex="-1" role="dialog" aria-labelledby="addNewKitchenLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewKitchenLabel"
                                v-show="!editMode"
                            >
                                Add New Kitchen
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewKitchenLabel"
                                v-show="editMode"
                            >
                                Update Kitchen Information
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
                        <form @submit.prevent="editMode ? updateKitchen() : createKitchen()">
                            <div class="modal-body row">
                                <div class="form-group col-md-12">
                                    <label>Name of Kitchen <span class="text-danger pulll-right">*</span></label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        name="name"
                                        required
                                        class="form-control"
                                        placeholder="Name of Kitchen"
                                        :class="{
                                            'is-invalid': form.errors.has(
                                                'name'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="form"
                                        field="name"
                                    ></has-error>
                                </div>

                                <!--<b-form-group class="col-md-12">
                                  <label>Kitchen Group</label>
                                  <b-form-select v-model="form.group_bar" :options="group_bar" required></b-form-select>
                                </b-form-group>-->
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
            this.getUser();
            this.loadKitchens();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                nairaSign: "&#x20A6;",
                model: {},
                kitchens: {},
                kitchen: "",
                form: new Form({
                    id: "",
                    name: "",
                    code: "",
                    group_bar: null,
                }),

                group_bar: [
                    { value: null, text: 'Select Group Kitchen:' },
                    { value: 1, text: 'Group 1' },
                    { value: 2, text: 'Group 2' },
                ],


                filterForm: {
                    name: '',
                    selected: '10',
                },

                action: {
                    selected: []
                },
                kitchens: {
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
                this.loadKitchens();
                this.getUser();
            },

            loadKitchens() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/kitchen", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.kitchens.data = data.kitchens;
                    }
                    else{
                        this.kitchens = data.kitchens;
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

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewKitchen").modal("show");
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
                this.loadkitchens();
                this.getUser();
                
            },

            getResults(page = 1) {
                axios.get("/api/kitchen?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.kitchens = response.data.kitchens;
                });
            },

            editModal(kitchen) {
                (this.editMode = true), this.form.reset();
                $("#addNewKitchen").modal("show");
                this.form.fill(kitchen);
            },

            onFilterSubmit()
            {
                this.loadKitchens();
                this.getUser();
            },

            createKitchen() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewKitchen").modal("hide");
                this.form.post("/api/kitchen")
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Kitchen Created Successfully.",
                        "success"
                    );    
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
                    this.loadKitchens(); 
                    this.getUser(); 
                });
            },

            updateKitchen(){
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewKitchen").modal("hide");
                this.form.put("/api/kitchen/" + this.form.id)
                .then(() => {
                    Swal.fire("Updated!", "Kitchen Updated Successfully.", "success");     
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
                    this.loadKitchens(); 
                    this.getUser(); 
                });
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            view(kitchen) {
                this.$router.push({ path: "/kitchen/" + kitchen.code });
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
                        axios.get('/api/kitchen/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Kitchen(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadKitchens();
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
                this.loadKitchens();
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
                this.loadKitchens();
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
                this.loadkitchens();
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
                this.loadkitchens();
                this.getUser();
            },
        },

        computed: {
            selectAll: {
                get: function () {
                    return this.kitchens.data ? this.action.selected.length == this.kitchens.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.kitchens.data.forEach(function (kitchen) {
                            selected.push(kitchen.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
