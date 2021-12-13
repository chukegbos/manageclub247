<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>All Item Requests</strong></h2>
                </div>

                <div class="col-md-6">
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
                                <th>Product </th>
                                <th>Quantity</th>
                                <th>Moved From</th>
                                <th>Moved To</th>
                                <th>Moved By</th>
                                <th>Approved/Disapproved By</th>
                                <th>Received/Rejected By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(inventory, index) in inventories.data">
                                <td>{{ inventory.title }}</td>
                                <td>{{ inventory.quantity }}</td>
                                <td>{{ inventory.moved }}</td>
                                <td>{{ inventory.store_name }}</td>
                                <td>{{ inventory.user_id }}</td>
                                <td>{{ inventory.approved_by }}</td>
                                <td>{{ inventory.manager_id }}</td>
                                <td>
                                    {{ inventory.status | capitalize }}
                                </td>
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
                                    <option value="0">All</option>
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Items</b>
                        </div>

                        <div class="col-md-10" v-if="this.filterForm.selected!=0">
                            <pagination :data="inventories" @pagination-change-page="getResults"></pagination>
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

                axios.get("/api/movement/allrequest", { params: this.filterForm })
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
                axios.get("/api/movement/allmovement?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.inventories = response.data.inventories;
                });
            }, 

            accept() {
                Swal.fire({
                    title: "Are you sure you want to accept?",
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
                        axios.get('/api/movement/outlet/accept', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Accepted!",
                                "Product movement approved.",
                                "success"
                            );
                            this.is_busy = false;
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
                    title: "Are you sure you do not want to approve?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, don't approve!"
                })
                .then(result => {
                    if (result.value) {
                        if (this.is_busy) return;
                        this.is_busy = true;
                        axios.get('/api/movement/outlet/reject', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "No approved!",
                                "Product movement not approved.",
                                "success"
                            );
                            this.is_busy = false;
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
