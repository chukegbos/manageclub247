<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>List of Item Groups</strong></h2>
                </div>

                <div class="col-md-8">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2">
                        Add Group
                    </b-button>

                    <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right m-2" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                    <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right m-2"> <i class="fa fa-trash"></i> Delete Selected</b-button>

                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search Customer"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="categories.data.length>0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" v-model="selectAll"></th>
                                <th width="200px">Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr  v-for="(cat, index) in categories.data">
                                <td> <input type="checkbox" v-model="action.selected" :value="cat.id" number></td>
                                <td>{{ cat.name }}</td>
                                <td>
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                        <b-dropdown-item href="javascript:void(0)" @click="percent(cat)">General Increase</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="editModal(cat)">Edit</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="onDeleteAll(cat.id)"> Delete</b-dropdown-item>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Group Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} Group</b>
                        </div>

                        <div class="col-md-10" v-if="this.filterForm.selected!=0">
                            <pagination :data="categories" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>
                    </div>
                </div>
            </div>           
        </div>
    
        <div
            class="modal fade"
            id="addNewcategory"
            tabindex="-1"
            role="dialog"
            aria-labelledby="addNewcategoryLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5
                            class="modal-title"
                            id="addNewcategoryLabel"
                            v-show="!editMode"
                        >
                            Add New
                        </h5>
                        <h5
                            class="modal-title"
                            id="addNewcategoryLabel"
                            v-show="editMode"
                        >
                            Update category Info
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
                    <form @submit.prevent=" editMode ? updatecategory() : createcategory()">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input v-model="form.name" type="text" class="form-control" :class="{'is-invalid': form.errors.has('name')}"/>
                                <has-error :form="form" field="name"></has-error>
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

        <div
            class="modal fade"
            id="addPercent"
            tabindex="-1"
            role="dialog"
            aria-labelledby="addPercentLabel"
            aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Increase price by percentage</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="increase()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Percentage Increase</label>
                                    <input v-model="percentage.number" type="number" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">
                                    Increase
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.getUser();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                model: {},
                categories: {},
                user: "",
                form: new Form({
                    id: "",
                    name: "",
                }),
                categories: {
                    data: '',
                },

                filterForm: {
                    name: '',
                    selected: '10',
                },
                action: {
                    selected: []
                },
                count_all: '',

                percentage: {
                    category: '',
                    number: '',
                },
            };
        },

        created() {
            this.loadcategory();
            this.getUser();
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                });
            },

            editModal(category) {
                (this.editMode = true), this.form.reset();
                $("#addNewcategory").modal("show");
                this.form.fill(category);
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewcategory").modal("show");
            },

            view(id) {
                this.$router.push({
                    path: "/admin/inventory/" + id
                });
            },

            onFilterSubmit()
            {
                this.loadcategory();
                this.getUser();
                this.$refs.filter.hide();
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadcategory();
                this.getUser();
            },


            getResults(page = 1) {
                axios.get("/api/inventory/category?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.categories = response.data;
                });
            },

            loadcategory() {
                if (this.is_busy) return;
                this.is_busy = true;
                axios.get("/api/inventory/category", { params: this.filterForm })
                .then(({ data }) => {

                    if(this.filterForm.selected==0)
                    {
                        this.categories.data = data.categories;
                    }
                    else{
                        this.categories = data.categories;
                    }
                    this.count_all = data.all;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            percent(category) {
                this.percentage.category = category.id
                $("#addPercent").modal("show");
            },

            createcategory() {
                axios.post("/api/inventory/category", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Category Created Successfully.",
                        "success"
                    );
                    $("#addNewcategory").modal("hide");
                    this.loadcategory();
                })

                .catch(error => {
                    console.log(error.response.data.error);
                    if (
                        error.response.data.error == "category Already Exist"
                    ) {
                        Swal.fire(
                            "Failed!",
                            "Category Already Exist",
                            "error"
                        );
                    }
                });
            },

            increase() {
                axios.post("/api/inventory/increase", this.percentage)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Price Increased Successfully.",
                        "success"
                    );
                    $("#addPercent").modal("hide");
                    this.loadcategory();
                })

                .catch(error => {
                    console.log(error.response.data.error);
                    if (
                        error.response.data.error == "Error Exist"
                    ) {
                        Swal.fire(
                            "Failed!",
                            "Error Exist",
                            "error"
                        );
                    }
                });
            },

            updatecategory() {
                this.form.put("/api/inventory/category/" + this.form.id)
                .then(() => {
                    Swal.fire(
                        "Updated!",
                        "Category Updated Successfully.",
                        "success"
                    );
                    $("#addNewcategory").modal("hide");
                    this.loadcategory();
                })

                .catch(() => {
                    this.$Progress.fail();
                });
            },

            deleteCategory(id) {
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
                        this.form.delete("/api/inventory/category/" + id)
                        .then((data) => {
                            console.log(data.data.error)
                            if(data.data.error){
                                Swal.fire(
                                    "Oops!",
                                    "You cannot delete the default category",
                                    "warning"
                                );
                            }
                            else {
                                Swal.fire(
                                    "Deleted!",
                                    "Category has been deleted.",
                                    "success"
                                );
                            }
                            this.loadcategory();
                        })

                        .catch(() => {
                            Swal(
                                "Failed!",
                                "Ops, Something went wrong, try again.",
                                "Warning"
                            );
                        });
                    }
                });
            },

            onDeleteAll(id) {
                
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
                        axios.get('/api/inventory/category/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Group(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadcategory();
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
                    return this.categories.data ? this.action.selected.length == this.categories.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.categories.data.forEach(function (cat) {
                            selected.push(cat.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
