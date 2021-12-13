<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>List of Member's Sections</strong></h2>
                </div>

                <div class="col-md-6">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2">
                        Add Section
                    </b-button>
               
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search Section"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="types.data.length>0" id="printMe">

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" v-model="selectAll"></th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="type in types.data" :key="type.id">
                                <td> <input type="checkbox" v-model="action.selected" :value="type.id" number></td>
                                <td>{{ type.title }}</td>
                                <td v-if="unprintable==false">
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                        <b-dropdown-item href="javascript:void(0)" @click="editModal(type)">Edit</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="onDeleteAll(type.id)">Delete</b-dropdown-item>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Member Type Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} Member Sections</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="types" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="addNewType" tabindex="-1" role="dialog" aria-labelledby="addNewtypeLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewtypeLabel"
                                v-show="!editMode"
                            >
                                Add New Section
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewtypeLabel"
                                v-show="editMode"
                            >
                                Update Section
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
                        <form @submit.prevent="editMode ? updateType() : createType()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Name<span class="text-danger pulll-right">*</span></label>
                                    <input v-model="form.title" type="text" name="name" required class="form-control" :class="{'is-invalid': form.errors.has('title')}"/>
                                    <has-error :form="form" field="title"></has-error>
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
        </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.loadTypes();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                model: {},
                types: {},
                type: "",
                form: new Form({
                    id: "",
                    title: "",
                }),

                filterForm: {
                    name: '',
                    selected: '10',
                },
                action: {
                    selected: []
                },
                types: {
                    data: {},
                },
                count_all: '',
                unprintable: false,
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadTypes();
            },

            loadTypes() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/members/sections", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.types.data = data.types;
                    }
                    else{
                        this.types = data.types;
                    }
                    this.count_all = data.all;
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
                });
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewType").modal("show");
            },

            getResults(page = 1) {
                axios.get("/api/members/sections?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.types = response.data.types;
                });
            },

            editModal(type) {
                (this.editMode = true), this.form.reset();
                $("#addNewType").modal("show");
                this.form.fill(type);
            },

            onFilterSubmit()
            {
                this.loadTypes();
            },

            createType() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewType").modal("hide");
                this.form.post("/api/members/sections")
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Member Section Created Successfully.",
                        "success"
                    );
                    this.loadTypes(); 
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
                    this.loadTypes(); 
                });
            },

            updateType() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewType").modal("hide");
                this.form.put("/api/members/sections/" + this.form.id)
                .then(() => {
                    Swal.fire("Updated!", "Member Section Updated Successfully.", "success");
                    this.loadTypes(); 
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
                    this.loadTypes(); 
                });
            },

            onDeleteAll(id) {
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
                        axios.get('/api/members/sections/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Member Type(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadTypes();
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
                    return this.types.data ? this.action.selected.length == this.types.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.types.data.forEach(function (type) {
                            selected.push(type.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
