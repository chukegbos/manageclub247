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
                            Update Inventory Info
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
                                <label>Product Name</label>
                                <input v-model="form.product_name" type="text" name="product_name" class="form-control" :class="{'is-invalid': form.errors.has('product_name')}"/>
                                <has-error :form="form" field="product_name"></has-error>
                            </div>
                            
                            <div class="form-group">
                                <label>Selling Price</label>
                                <input v-model="form.price" type="number" name="price" class="form-control":class="{'is-invalid': form.errors.has('price')}"/>
                                <has-error :form="form" field="price"></has-error>
                            </div>

                            <div class="form-group">
                                <label>Select Category</label>
                                <b-form-select
                                    v-model="form.category"
                                    :options="categories"
                                    value-field="id"
                                    text-field="name"
                                >
                                <template v-slot:first>
                                    <b-form-select-option :value="null" disabled>
                                        -- Please select category--
                                    </b-form-select-option>
                                </template>
                                </b-form-select>
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
                <div class="col-md-7">
                    <h2><strong>List of Store Items ({{ store.name }})</strong></h2>
                </div>

                <div class="col-md-5">

                    <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right m-2" @click="deleteProduct"><i class="fa fa-trash"></i> Delete Selected</b-button>

                    <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right m-2"> <i class="fa fa-trash"></i> Delete Selected</b-button> 

                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search Item"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                 
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="inventories.data.length>0" id="printMe">
                    <div class="text-center" v-if="unprintable==true">
                        <h2>{{ site.sitename }} - List of Items</h2>
                    </div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th v-if="unprintable==false"><input type="checkbox" v-model="selectAll"></th>

                                <th v-if="unprintable==true">S/N</th>

                                <th>Code </th>

                                <th width="200px">Name</th>

                                <th>Category</th>

                                <th>Amount (<span v-html="nairaSign"></span>)</th>
                                <th>Quantity</th>
                                <th>Threshold</th>
                                <th>Age Period</th>
                                <th v-if="unprintable==false">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(inventory, index) in inventories.data">
                                <td v-if="unprintable==false"> <input type="checkbox" v-model="action.selected" :value="inventory.id" number></td>
                                <td v-if="unprintable==true">{{ index + 1 }}</td>
                                <td>{{ inventory.product_id }}</td>
                                <td>{{ inventory.product_name }}</td>
                                <td>{{ inventory.name }}</td>
                                <td>{{ formatPrice(inventory.price) }}</td>
                                <td>
                                    <span class="text-red" v-if="inventory.threshold > inventory.quantity">{{ inventory.quantity }}</span>
                                    <span v-else>{{ inventory.quantity }}</span>
                                </td>
                                <td>{{ inventory.threshold }}</td>
                                <td>{{ inventory.updated_at }}</td>
                                
                                <td v-if="unprintable==false">
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                        <b-dropdown-item href="javascript:void(0)" @click="view(user)">Sales Report</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="view(user)">Quantity Breakdown</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="editModal(inventory)">Edit</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="deleteProduct(inventory.id)">Delete</b-dropdown-item>
                                    </b-dropdown>
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
                queue: {
                    payload: '',
                },
                today_date: moment().format("YYYY-MM-DD"),
                inventories: {},
                categories: [],
                user: "",
                nairaSign: "&#x20A6;",
                form: new Form({
                    id: "",
                    product_name: "",
                    price: "0",
                    category: 1,
                }),

                site: '', 
                store: '',
                filterForm: {
                    name: '',
                    selected: '5',
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
            this.loadInventory();
        },

        components: {
            VueBootstrap4Table
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadInventory();
            },

            onFilterSubmit()
            {
                this.loadInventory();
            },

            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.unprintable = true;
                this.$htmlToPaper('printMe');
                this.unprintable = false;
                this.is_busy = false;
            },

            loadInventory() {
                let code = this.$route.params.code;
               
                axios.get("/api/store/" + code)
                .then(({ data }) => {
                    this.store = data;
                    if(data)
                    {
                        if (this.is_busy) return;
                        this.is_busy = true;

                        axios.get("/api/store/" + code + '/' + data.id, { params: this.filterForm })

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
                    }
                    else
                    {
                        this.$router.push({ path: '/404'});
                    }
                })
            },

            getResults(page = 1) {
                axios.get("/api/store/" + code + "/" + data.id + "?page=", { params: this.filterForm })
                .then(response => {
                    this.inventories = response.data.inventories;
                });
            },   

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
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
