<template>
    <b-overlay :show="is_busy" rounded="sm"> 
        <div class="container-fluid">
            <div class="row mb-2 p-1">
                <div class="col-md-7">
                    <h2><strong>List of Items ({{ kitchen.name }})</strong></h2>
                </div>

                <div class="col-md-5">
                    <span v-if="(admin.role==1 || admin.role==14 || admin.role==15)" class="pull-right m-1">
                        <b-button variant="outline-success" size="sm" v-if="action.selected.length" class="pull-right m-1" @click="onRequestAll">Item Request</b-button>

                        <b-button disabled size="sm" variant="outline-success" v-else class="pull-right m-1">Item Request</b-button>
                    </span>
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-1" size="sm">
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
                <div class="card-body table-responsive p-0" v-if="inventories.data.length>0">
                  
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th v-if="admin.role==1 || admin.role==14 || admin.role==15">
                                    <input type="checkbox" v-model="selectAll">
                                </th>

                                <th>Name</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(inventory, index) in inventories.data">
                                <td v-if="admin.role==1 || admin.role==14 || admin.role==15"> 
                                    <input type="checkbox" v-model="action.selected" :value="inventory.id"  number>
                                </td>
                                <td>{{ inventory.name }}</td>
                                <td>{{ inventory.quantity }}</td>
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
              
                admin: "",
                nairaSign: "&#x20A6;",
                form: new Form({
                    id: "",
                    product_name: "",
                    price: "0",
                    category: 1,
                }),

                site: '', 
                kitchen: '',
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
            this.loadInventory();
            
        },

        components: {
            VueBootstrap4Table
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.getUser();
                this.loadInventory();             
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
            },

            onFilterSubmit()
            {
                this.getUser();
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
                if (this.is_busy) return;
                this.is_busy = true;
                axios.get("/api/kitchen/" + code) 
                .then(({ data }) => {
                    this.kitchen = data;

                    if(data)
                    {
                        axios.get("/api/food/inventory/" + code + '/' + data.id, { params: this.filterForm })
                        .then(({ data }) => {
                            if(this.filterForm.selected==0)
                            {
                                this.inventories.data = data.inventories;
                            }
                            else{
                                this.inventories = data.inventories;
                            }
                            this.count_all = data.all;
                        });
                    }
                    else
                    {
                        this.$router.push({ path: '/404'});
                    }
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            getResults(page = 1) {
                axios.get("/api/food/inventory/" + code + "/" + data.id + "?page=", { params: this.filterForm })
                .then(response => {
                    this.inventories = response.data.inventories;
                });
            },   

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            onSelectAll(){
                Swal.fire({
                    title: "Are you sure?",
                    text: "Items you do not have in your outlet will not be moved.!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, Proceed!"
                })
                .then(result => {
                    if (result.value) {
                        axios.get('/api/movement/initiate', { params: this.action})
                        .then((response) => {
                            this.$router.push({ path: '/store-movements/'+ response.data});
                        });
                    }
                });
            },

            onRequestAll(){
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/food/inventory/requestinitiate/' + this.kitchen.id, { params: this.action})
                
                .then((response) => {
                    this.$router.push({ path: '/kitchen/storerequest/'+ response.data});
                })
                .finally(() => {
                    this.is_busy = false;
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
