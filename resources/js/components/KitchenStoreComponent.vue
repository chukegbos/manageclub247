<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="modal fade" id="addNewInventory" tabindex="-1" role="dialog" aria-labelledby="addNewInventoryLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5
                            class="modal-title"
                            id="addNewInventoryLabel"
                            v-show="!editMode"
                        >
                            Add New
                        </h5>
                        <h5
                            class="modal-title"
                            id="addNewInventoryLabel"
                            v-show="editMode"
                        >
                            Update Info
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
                    <form @submit.prevent=" editMode ? updateInventory() : createInventory()">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Name</label>
                                <input v-model="form.name" type="text" name="name" class="form-control" :class="{'is-invalid': form.errors.has('name')}"/>
                                <has-error :form="form" field="name"></has-error>
                            </div>

                            <div class="form-group">
                                <label>Unit of Measurement</label>
                                <input v-model="form.unit" type="text" name="unit" class="form-control" :class="{'is-invalid': form.errors.has('unit')}"/>
                                <has-error :form="form" field="unit"></has-error>
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
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-5">
                    <h2><strong>Food Store Items</strong></h2>
                </div>

                <div class="col-md-7">
                    <span v-if="admin.role==1 || admin.role==11 || admin.role==13">
                        <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2">
                            Add An Item
                        </b-button>

                        <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right m-2" @click="deleteProduct"><i class="fa fa-trash"></i> Remove Selected</b-button>

                        <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right m-2"> <i class="fa fa-trash"></i> Remove Selected</b-button> 
                    </span>               
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="inventories.data.length>0">
                  
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th v-if="admin.role==1 || admin.role==11 || admin.role==13"><input type="checkbox" v-model="selectAll">
                                </th>

                                <th>Name</th>
                                <th>Unit</th>
                                <th>Last Amount(<span v-html="nairaSign"></span>)</th>
                                <th>Quantity Available</th>
                                <th v-if="admin.role==1 || admin.role==11 || admin.role==13"><span v-if="unprintable==false">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(inventory, index) in inventories.data">
                                <td v-if="admin.role==1 || admin.role==11 || admin.role==13">
                                    <span  v-if="unprintable==false">
                                        <input type="checkbox" v-model="action.selected" :value="inventory.id" number>
                                    </span>
                                </td>
                                <td>{{ inventory.name }}</td>
                                <td>{{ inventory.unit }}</td>
                                <td><span v-html="nairaSign"></span>{{ formatPrice(inventory.amount) }}</td>
                                <td>{{ inventory.quantity }}</td>
                                
                                <td v-if="admin.role==1 || admin.role==11 || admin.role==13">
                                    <span v-if="unprintable==false">
                                        <b-dropdown id="dropdown-right" text="Action" variant="info">
                                            <b-dropdown-item href="javascript:void(0)" @click="editModal(inventory)">Edit</b-dropdown-item>

                                            <b-dropdown-item href="javascript:void(0)" @click="deleteProduct(inventory.id)">Delete</b-dropdown-item>
                                        </b-dropdown>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Item Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} Items</b>
                        </div>

                        <div class="col-md-10" v-if="this.filterForm.selected!=0">
                            <pagination :data="inventories" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>
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
        data() {
            return {
                is_busy: false,
                editMode: false,
               
                today_date: moment().format("YYYY-MM-DD"),
                inventories: {},
                admin: "",
                form: new Form({
                    id: "",
                    name: "",
                    amount: "0",
                    unit: '',
                }),

                site: '', 
                nairaSign: "&#x20A6;",
                filterForm: {
                    name: '',
                    selected: '10',
                },

                action: {
                    selected: []
                },

                inventories: {
                    data: {},
                },
                unprintable: false,
                count_all: '',
            };
        },

        created() {
            this.getUser();
            this.loadSite();
            this.loadInventory();
        },

        components: {
            VueBootstrap4Table
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadInventory();
                this.getUser();
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            onFilterSubmit()
            {
                this.loadInventory();
                this.getUser();
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
                    this.admin = data.user;
                });
            },

            editModal(inventory) {
                (this.editMode = true), this.form.reset();
                $("#addNewInventory").modal("show");
                this.form.fill(inventory);             
            },

            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.unprintable = true;
                this.$htmlToPaper('printMe');
                this.unprintable = false;
                this.is_busy = false;
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewInventory").modal("show");
            },

            loadInventory() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/food/inventory", { params: this.filterForm })

                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.inventories.data = data.inventories;
                    }
                    else{
                        this.inventories = data.inventories;
                    }
                    this.count_all = data.all;
                    this.categories = data.categories;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            getResults(page = 1) {
                axios.get("/api/food/inventory?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.inventories = response.data.inventories;
                });
            },

            createInventory() {
                $("#addNewInventory").modal("hide");

                if (this.is_busy) return;
                this.is_busy = true;

                axios.post("/api/food/inventory", this.form)
                .then(() => {
                    this.is_busy = false;
                    Swal.fire(
                        "Created!",
                        "Item Created Successfully.",
                        "success"
                    );
                })

                .catch(error => {
                    
                    if (
                        error.response.data.error == "Inventory Already Exist"
                    ) {
                        Swal.fire(
                            "Failed!",
                            "Inventory Already Exist",
                            "error"
                        );
                    }
                })
                .finally(() => {
                    this.is_busy = false;
                    this.loadInventory();
                    this.getUser();
                    this.loadSite();
                });
            },

            updateInventory() {
                $("#addNewInventory").modal("hide");
                if (this.is_busy) return;
                this.is_busy = true;

                axios.put("/api/food/inventory/" + this.form.id, this.form)
                .then(() => {
                    this.is_busy = false;

                    Swal.fire(
                        "Updated!",
                        "Item Updated Successfully.",
                        "success"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                    this.loadInventory();
                    this.getUser();
                    this.loadSite();
                });
            },

            onReset() {
                axios.get("/api/food/inventory/reset")
                .then((data) => {
                    Swal.fire(
                        "Created!",
                        "Inventory Reset Successfully.",
                        "success"
                    );
                    this.loadInventory();
                    this.getUser();
                    this.loadSite();
                });
            },


            deleteProduct(id) {
                
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
                        axios.get('/api/food/inventory/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Product(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadInventory();
                            this.loadSite();
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

                this.filterForm.orderCategory = 2;
                this.filterForm.orderAmount = 2; 
                this.filterForm.orderQuantity = 2;
                this.filterForm.orderCost = 2; 
                this.loadInventory();
                this.getUser();
                this.loadSite();
            },

            orderByCategory() {
                if(this.filterForm.orderCategory==1) {
                   this.filterForm.orderCategory = 0; 
                }
                else {
                    this.filterForm.orderCategory = 1;
                }

                this.filterForm.orderName = 2;
                this.filterForm.orderAmount = 2; 
                this.filterForm.orderQuantity = 2;
                this.filterForm.orderCost = 2; 
                this.loadInventory();
                this.getUser();
                this.loadSite();
            },

            orderByAmount() {
                if(this.filterForm.orderAmount==1) {
                   this.filterForm.orderAmount = 0; 
                }
                else {
                    this.filterForm.orderAmount = 1;
                }

                this.filterForm.orderName = 2;
                this.filterForm.orderCategory = 2; 
                this.filterForm.orderQuantity = 2;
                this.filterForm.orderCost = 2; 
                this.loadInventory();
                this.getUser();
                this.loadSite()
            },

            orderByQuantity() {
                if(this.filterForm.orderQuantity==1) {
                   this.filterForm.orderQuantity = 0; 
                }
                else {
                    this.filterForm.orderQuantity = 1;
                }
                console.log(this.filterForm);
                this.filterForm.orderName = 2;
                this.filterForm.orderCategory = 2; 
                this.filterForm.orderAmount = 2;
                this.filterForm.orderCost = 2; 
                this.loadInventory();
                this.getUser();
                this.loadSite()
            },

            orderByCost() {
                if(this.filterForm.orderCost==1) {
                   this.filterForm.orderCost = 0; 
                }
                else {
                    this.filterForm.orderCost = 1;
                }

                this.filterForm.orderName = 2;
                this.filterForm.orderCategory = 2; 
                this.filterForm.orderQuantity = 2;
                this.filterForm.orderAmount= 2; 
                this.loadInventory();
                this.getUser();
                this.loadSite();
            },  
        },

        computed: {
            selectAll: {
                get: function () {
                    return this.inventories.data ? this.action.selected.length == this.inventories.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.inventories.data.forEach(function (item) {
                            selected.push(item.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
