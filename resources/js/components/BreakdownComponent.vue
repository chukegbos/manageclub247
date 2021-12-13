<template>
    <b-overlay :show="is_busy" rounded="sm">  
        <div class="container-fluid">
            <div class="row mb-2 p-1">
                <div class="col-md-6">
                    <h2><strong>{{ item.product_name }} Break down</strong></h2>
                </div>

                <div class="col-md-6">

                    <!--<b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-1" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search Item"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>-->               
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
                                <th>Outlet </th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(inventory, index) in inventories.data">
                                <td>{{ inventory.name }}</td>
                                <td>{{ inventory.number }}</td>
                            </tr>
                            <tr>
                                <td><b>Total</b></td>
                                <td><b>{{ total }}</b></td>
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
                inventories: {},
                item: '', 
                store: '',
                filterForm: {
                    store_id: '',
                    selected: '20',
                },
                inventories: {
                    data: {},
                },
                unprintable: false,
                count_all: '',
                total: '',
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
                axios.get("/api/item/" + code, { params: this.filterForm })
                .then(({ data }) => {
                    console.log(data)
                    if (data.item){
                        if(this.filterForm.selected==0)
                        {
                            this.inventories.data = data.inventories;
                        }
                        else{
                            this.inventories = data.inventories;
                        }
                        this.count_all = data.all;
                        this.item = data.item;
                        this.total = data.total;
                    }
                    else
                    {
                        Swal.fire(
                            "Failed!",
                            "Such item does not exist",
                            "error"
                        );
                        this.$router.push({ path: '/admin/inventory'});
                    }
                });
            },

            getResults(page = 1) {
                let code = this.$route.params.code;
                axios.get("/api/item/"  + code + "?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.inventories = response.data.inventories;
                    this.item = data.item;
                });
            },  
        },
    };
</script>
