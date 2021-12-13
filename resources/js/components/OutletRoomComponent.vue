<template>
    <b-overlay :show="is_busy" rounded="sm">

      
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-7">
                    <h2><strong>List of Showroom Items ({{ store.name }})</strong></h2>
                </div>

                <div class="col-md-5">
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
                                <th>Code </th>

                                <th width="200px">Name</th>

                                <th>Category</th>

                                <th>Amount (<span v-html="nairaSign"></span>)</th>
                                <th>Quantity</th>
                                <!--<th v-if="unprintable==false">Action</th>-->
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(inventory, index) in inventories.data">
                              
                                <td>{{ inventory.product_id }}</td>
                                <td>{{ inventory.product_name }}</td>
                                <td>{{ inventory.name }}</td>
                                <td>{{ formatPrice(inventory.price) }}</td>
                                <td>
                                    <span class="text-red" v-if="inventory.threshold > inventory.quantity">{{ inventory.quantity }}</span>
                                    <span v-else>{{ inventory.quantity }}</span>
                                </td>
                                <!--<td v-if="unprintable==false">
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                        <b-dropdown-item href="javascript:void(0)" @click="view(user)">Sales Report</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="view(user)">Quantity Breakdown</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="editModal(inventory)">Edit</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="deleteProduct(inventory.id)">Delete</b-dropdown-item>
                                    </b-dropdown>
                                </td>-->
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

                        axios.get("/api/store/room/" + code + '/' + data.id, { params: this.filterForm })

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
                axios.get("/api/store/room/" + code + "/" + data.id + "?page=", { params: this.filterForm })
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
