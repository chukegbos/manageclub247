<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>My Item Requests</strong></h2>
                </div>

                <div class="col-md-6">
                    <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right m-2" @click="deleteAll"><i class="fa fa-trash"></i> Delete</b-button> 

                    <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right m-2"> <i class="fa fa-trash"></i> Delete</b-button>

                    <b-button variant="outline-warning" size="sm" v-if="action.selected.length" class="pull-right m-2" @click="reject"><i class="fa fa-times"></i> Reject</b-button> 

                    <b-button disabled size="sm" variant="outline-warning" v-else class="pull-right m-2"> <i class="fa fa-times"></i> Reject</b-button>

                    <b-button variant="outline-info" size="sm" v-if="action.selected.length" class="pull-right m-2" @click="accept"><i class="fa fa-check"></i> Accept</b-button> 

                    <b-button disabled size="sm" variant="outline-info" v-else class="pull-right m-2"> <i class="fa fa-check"></i> Accept</b-button>
                 

                    <!--<b-button variant="outline-primary" size="sm" class="pull-right m-2" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Status:">
                                <b-form-input v-model="filterForm.status" type="text" placeholder="Status"></b-form-input>
                            </b-form-group>
                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>  -->             
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="inventories.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <!--<input type="checkbox" v-model="selectAll">-->
                                </th>

                                <th>Product </th>
                                <th>From</th>
                                <th>Requested By</th>
                                <th>Qty(Crate)</th>
                                <th>Approved/Disapproved By</th>
                                <th>Received/Rejected By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(inventory, index) in inventories.data">
                                <td v-if="user.role==7">
                                    <input type="checkbox" v-model="action.selected" :value="inventory.id" number >
                                </td>
                              
                                <td>{{ inventory.title }}</td>
                                <td>{{ inventory.moved }}</td>
                                <td>{{ inventory.user_id }}</td>
                                <td>{{ inventory.qty }}</td>
                                <td>{{ inventory.approved_by }}</td>
                                <td>{{ inventory.manager_id }}</td>
                                <td>{{ inventory.status | capitalize }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-center mt-1" v-if="unprintable==true">
                        <hr>
                        <p>Total of {{ count_all }} item(s) printed as at {{ today_date }}</p>
                    </div>
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
                stores: [],
                nairaSign: "&#x20A6;",
                form: new Form({
                    id: "",
                    product_name: "",
                    price: "0",
                    category: 1,
                }),

                site: '', 
              
                filterForm: {
                    store_id: '',
                    status: '',
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
                user: '',
            };
        },

        created() {
            this.loadInventory();
            this.getUser();
        },

        components: {
            VueBootstrap4Table
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.stores = data.stores;
                    this.user = data.user;
                });
            },

            loadInventory() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/movement/request", { params: this.filterForm })
                .then(({ data }) => {
                    console.log(data)
                    if(this.filterForm.selected==0)
                    {
                        this.inventories.data = data.inventories;
                    }
                    else{
                        this.inventories = data.inventories;
                    }
                    this.count_all = data.all;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadInventory(); 
                this.getUser();
                this.$refs.filter.hide();
            },

            getResults(page = 1) {
                axios.get("/api/movement/request?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.inventories = response.data.inventories;
                });
            }, 

            accept() {
                Swal.fire({
                    title: "Note that only approved products will be accepted. Are you sure you want to accept?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, accept!"
                })
                .then(result => {
                    if (result.value) {
                        axios.get('/api/movement/store/accept', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Accepted!",
                                "Approved product accepted successfully.",
                                "success"
                            );
                            this.action.selected = [];
                            this.loadInventory();
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
             
            reject() {
                Swal.fire({
                    title: "Note that only approved products will be rejected. Are you sure?",
                    text: "You are cancelling the product you requested for, the items will be returned back. You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, Cancel!"
                })
                .then(result => {
                    if (result.value) {
                        axios.get('/api/movement/store/reject', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Cancel!",
                                "Product cancel.",
                                "success"
                            );
                            this.action.selected = [];
                            this.loadInventory();
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
             
            deleteAll() {
                Swal.fire({
                    title: "Are you sure you want to delete?",
                    text: "You won't be able to revert this and note that already approved products cannot be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete!"
                })
                .then(result => {
                    if (result.value) {
                       
                        axios.get('/api/movement/store/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Requested Deleted.",
                                "success"
                            );
                            this.loadInventory();
                            this.getUser();
                            this.action.selected = [];
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
