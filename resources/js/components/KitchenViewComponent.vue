<template>
    <b-overlay :show="is_busy" rounded="sm"> 
        <div class="container-fluid">
            <div class="row mb-2 p-1">
                <div class="col-md-8">
                    <h2><strong>Menu List ({{ kitchen.name }})</strong></h2>
                </div>

                <div class="col-md-4">
                    <span v-if="(admin.role==1 || admin.role==7) && admin.kitchen!=1">
                    <b-button variant="outline-success" size="sm" v-if="action.selected.length" class="pull-right m-1" @click="onRequestAll">Request Item</b-button>

                    <b-button disabled size="sm" variant="outline-success" v-else class="pull-right m-1">Request Item</b-button>
                    </span>                 
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="foods.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <thead>
                            <tr>

                                <th>Item</th>
                                <th>Amount</th>
                                <th>Available</th>
                                <!--<th>Waiting Period</th>-->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(inventory, index) in foods.data">
                                
                                <td>{{ inventory.item }}</td>
                                <td>
                                    <span v-html="nairaSign"></span>{{ formatPrice(inventory.amount) }}
                                </td>
                                <td>{{ inventory.number }}</td>
                                <!--<td>{{ inventory.period }} Mins</td>-->
                                <td>
                                    <a href="javascript:void(0)" @click="editModal(inventory)" v-if="admin.role==1 || admin.role==14 || admin.role==15">Update Dish</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Food Found.</strong></h3></div>
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
                            <pagination :data="foods" @pagination-change-page="getResults"></pagination>
                        </div>
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
                            Update Dish Status
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
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Name</label>
                                <input
                                    v-model="form.item"
                                    type="text"
                                    class="form-control"
                                    readonly="true"
                                />
                            </div>

                            <div class="form-group">
                                <label>Number of Dish<span class="text-danger pulll-right">*</span></label>
                                <input
                                    v-model="form.number"
                                    type="number"
                                    name="number"
                                    required
                                    class="form-control"
                                    :class="{
                                        'is-invalid': form.errors.has(
                                            'number'
                                        )
                                    }"
                                />
                                <has-error
                                    :form="form"
                                    field="number"
                                ></has-error>
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
                foods: {},
                admin: "",
                nairaSign: "&#x20A6;",
                form: new Form({
                    id: "",
                    number: "",
                    item: "",
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
                foods: {
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

            editModal(inventory) {
                (this.editMode = true), this.form.reset();
                $("#addNewKitchen").modal("show");
                this.form.fill(inventory);
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
                        axios.get("/api/kitchen/" + code + '/' + data.id, { params: this.filterForm })
                        .then(({ data }) => {
                            if(this.filterForm.selected==0)
                            {
                                this.foods.data = data.foods;
                            }
                            else{
                                this.foods = data.foods;
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
                axios.get("/api/kitchen/" + code + "/" + data.id + "?page=", { params: this.filterForm })
                .then(response => {
                    this.foods = response.data.food;
                });
            },   

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            updateKitchen(){
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewKitchen").modal("hide");

                this.form.put("/api/kitchen/dish/" + this.form.id)
                .then(() => {
                    Swal.fire("Updated!", "Dish Updated Successfully.", "success");     
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
                    this.getUser();
                    this.loadInventory();
                });
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
                            this.$router.push({ path: '/kitchen-movements/'+ response.data});
                        });
                    }
                });
            },

            onRequestAll(){
                axios.get('/api/movement/requestinitiate', { params: this.action})
                .then((response) => {
                    this.$router.push({ path: '/kitchenrequest/'+ response.data});
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
                this.filterForm.orderThreshold = 2; 
                this.filterForm.orderPeriod = 2; 
                this.loadInventory();
                this.getUser();
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
                this.filterForm.orderThreshold = 2; 
                this.filterForm.orderPeriod = 2; 
                this.loadInventory();
                this.getUser();
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
                this.filterForm.orderThreshold = 2; 
                this.filterForm.orderPeriod = 2; 
                this.loadInventory();
                this.getUser();
            },

            orderByQuantity() {
                if(this.filterForm.orderQuantity==1) {
                   this.filterForm.orderQuantity = 0; 
                }
                else {
                    this.filterForm.orderQuantity = 1;
                }

                this.filterForm.orderName = 2;
                this.filterForm.orderCategory = 2; 
                this.filterForm.orderAmount = 2;
                this.filterForm.orderThreshold = 2; 
                this.filterForm.orderPeriod = 2; 
                this.loadInventory();
                this.getUser();
            },

            orderByPeriod() {
                if(this.filterForm.orderPeriod==1) {
                   this.filterForm.orderPeriod = 0; 
                }
                else {
                    this.filterForm.orderPeriod = 1;
                }

                this.filterForm.orderName = 2;
                this.filterForm.orderCategory = 2; 
                this.filterForm.orderQuantity = 2;
                this.filterForm.orderThreshold = 2; 
                this.filterForm.orderAmount= 2; 
                this.loadInventory();
                this.getUser();
            },

            orderByThreshold() {
                if(this.filterForm.orderThreshold==1) {
                   this.filterForm.orderThreshold = 0; 
                }
                else {
                    this.filterForm.orderThreshold = 1;
                }

                this.filterForm.orderName = 2;
                this.filterForm.orderCategory = 2; 
                this.filterForm.orderAmount = 2;
                this.filterForm.orderQuantity = 2; 
                this.filterForm.orderPeriod = 2; 
                this.loadInventory();
                this.getUser();
            },
        },

        computed: {
            selectAll: {
                get: function () {
                    return this.foods.data ? this.action.selected.length == this.foods.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.foods.data.forEach(function (item) {
                            selected.push(item.room_id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>