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
                            Update Food Info
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
                                <label>Name of food</label>
                                <input v-model="form.name" type="text" name="name" class="form-control" :class="{'is-invalid': form.errors.has('name')}"/>
                                <has-error :form="form" field="name"></has-error>
                            </div>

                            <div class="form-group">
                                <label>Amount</label>
                                <input v-model="form.amount" type="number" name="amount" class="form-control":class="{'is-invalid': form.errors.has('amount')}"/>
                                <has-error :form="form" field="amount"></has-error>
                            </div>

                            <!--<div class="form-group">
                                <label>Duration of Wait(Min)</label>
                                <input v-model="form.period" type="number" name="period" class="form-control":class="{'is-invalid': form.errors.has('period')}"/>
                                <has-error :form="form" field="period"></has-error>
                            </div>-->


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
                <div class="col-md-3">
                    <h2><strong>List of Food</strong></h2>
                </div>

                <div class="col-md-9">
                    <span v-if="admin.role==1 || admin.role==11 || admin.role==14">
                        <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2">
                            Add Food
                        </b-button>


                        <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right m-2" @click="deleteProduct"><i class="fa fa-trash"></i> Delete Selected</b-button>

                        <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right m-2"> <i class="fa fa-trash"></i> Delete Selected</b-button> 
                    </span>
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search Food"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                 
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="foods.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th v-if="admin.role==1 || admin.role==11 || admin.role==14">
                                    <span  v-if="unprintable==false">
                                        <input type="checkbox" v-model="selectAll">
                                    </span>
                                </th>
                                <th>Name</th>
                                <th>Amount</th>
                                <!--<th>Waiting Period</th>-->
                                <th v-if="admin.role==1 || admin.role==11 || admin.role==14"><span v-if="unprintable==false">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(food, index) in foods.data">
                                <td v-if="admin.role==1 || admin.role==11 || admin.role==14">
                                    <span  v-if="unprintable==false">
                                        <input type="checkbox" v-model="action.selected" :value="food.id" number>
                                    </span>
                                </td>
                               
                                <td>{{ food.name }}</td>

                                <td><span v-html="nairaSign"></span>{{ formatPrice(food.amount) }}</td>
                                <!--<td>{{ food.period }} Mins</td>-->
                                <td v-if="admin.role==1 || admin.role==11 || admin.role==14">
                                    <span v-if="unprintable==false">
                                        <b-dropdown id="dropdown-right" text="Action" variant="info">
                                            <b-dropdown-item href="javascript:void(0)" @click="editModal(food)">Edit</b-dropdown-item>

                                            <b-dropdown-item href="javascript:void(0)" @click="deleteProduct(food.id)">Delete</b-dropdown-item>
                                        </b-dropdown>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Food Item Found.</strong></h3></div>
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
                            <pagination :data="foods" @pagination-change-page="getResults" :limit="-1"></pagination>
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
                foods: {},
                categories: [],
                admin: "",

                form: new Form({
                    id: "",
                    name: "",
                    amount: "0",
                    period: 20,
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
                foods: {
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

            editModal(food) {
                (this.editMode = true), this.form.reset();
                $("#addNewInventory").modal("show");
                this.form.fill(food);             
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

                axios.get("/api/food", { params: this.filterForm })

                .then(({ data }) => {
                    console.log(data)
                    if(this.filterForm.selected==0)
                    {
                        this.foods.data = data.foods;
                    }
                    else{
                        this.foods = data.foods;
                    }
                    this.count_all = data.all;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            getResults(page = 1) {
                axios.get("/api/food?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.foods = response.data.foods;
                });
            },

            createInventory() {
                $("#addNewInventory").modal("hide");

                if (this.is_busy) return;
                this.is_busy = true;

                axios.post("/api/food", this.form)
                .then(() => {
                    this.is_busy = false;
                    Swal.fire(
                        "Created!",
                        "Food Created Successfully.",
                        "success"
                    );
                })

                .catch(error => {
                    
                    if (
                        error.response.data.error == "Food Already Exist"
                    ) {
                        Swal.fire(
                            "Failed!",
                            "Food Already Exist",
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

                axios.put("/api/food/" + this.form.id, this.form)
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

            deleteProduct(id) {
                
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
                        axios.get('/api/food/delete', { params: this.action})
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
                            selected.push(item.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
