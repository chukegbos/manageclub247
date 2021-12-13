<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>List of Registered POS</strong></h2>
                </div>

                <div class="col-md-6">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2">
                        Add POS
                    </b-button>
               
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search POS"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="pos.data.length>0" id="printMe">

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" v-model="selectAll"></th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="channel in pos.data" :key="channel.id">
                                <td> <input type="checkbox" v-model="action.selected" :value="channel.id" number></td>
                                <td>{{ channel.code }}</td>
                                <td>{{ channel.name }}</td>
                                <td v-if="unprintable==false">
                                    <b-dropdown id="dropdown-right" text="Action" variant="info">
                                        <b-dropdown-item href="javascript:void(0)" @click="editModal(channel)">Edit</b-dropdown-item>

                                        <b-dropdown-item href="javascript:void(0)" @click="onDeleteAll(channel.id)">Delete</b-dropdown-item>
                                    </b-dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No POS Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} POS</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="pos" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="addNewChannel" tabindex="-1" role="dialog" aria-labelledby="addNewChannelLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewChannelLabel"
                                v-show="!editMode"
                            >
                                Add New Channel
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewchannelLabel"
                                v-show="editMode"
                            >
                                Update Channel
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
                        <form @submit.prevent="editMode ? updateChannel() : createChannel()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Code<span class="text-danger pulll-right">*</span></label>
                                    <input v-model="form.code" type="text" name="code" required class="form-control" :class="{'is-invalid': form.errors.has('code')}"/>
                                    <has-error :form="form" field="code"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Name<span class="text-danger pulll-right">*</span></label>
                                    <input v-model="form.name" type="text" name="name" required class="form-control" :class="{'is-invalid': form.errors.has('name')}"/>
                                    <has-error :form="form" field="name"></has-error>
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
            this.loadPos();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                model: {},
                pos: {},
                channel: "",
                form: new Form({
                    id: "",
                    name: "",
                    code: "",
                }),

                filterForm: {
                    name: '',
                    selected: '10',
                },
                action: {
                    selected: []
                },
                pos: {
                    data: {},
                },
                count_all: '',
                unprintable: false,
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadPos();
            },

            loadPos() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/payment/pos", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.pos.data = data.pos;
                    }
                    else{
                        this.pos = data.pos;
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
                $("#addNewChannel").modal("show");
            },

            getResults(page = 1) {
                axios.get("/api/payment/pos?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.pos = response.data.pos;
                });
            },

            editModal(channel) {
                (this.editMode = true), this.form.reset();
                $("#addNewChannel").modal("show");
                this.form.fill(channel);
            },

            onFilterSubmit()
            {
                this.loadPos();
            },

            createChannel() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewChannel").modal("hide");
                this.form.post("/api/payment/pos")
                .then((data) => {
                    console.log(data.data.Error)
                    if(data.data.Error){
                        Swal.fire(
                            "Failed!",
                            data.data.Error,
                            "warning"
                        );
                    }
                    else {
                        Swal.fire(
                            "Created!",
                            "Payment POS Created Successfully.",
                            "success"
                        );
                    }
                    this.loadPos(); 
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
                    this.loadPos(); 
                });
            },

            updateChannel() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewChannel").modal("hide");
                this.form.put("/api/payment/pos/" + this.form.id)
                .then(() => {
                    Swal.fire("Updated!", "Payment POS Updated Successfully.", "success");
                    this.loadPos(); 
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
                    this.loadPos(); 
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
                        axios.get('/api/payment/pos/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "POS deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadPos();
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
                    return this.pos.data ? this.action.selected.length == this.pos.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.pos.data.forEach(function (channel) {
                            selected.push(channel.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
