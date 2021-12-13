<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Name:">
                                <b-form-input id="name" v-model="filterForm.name" tax="text"></b-form-input>
                            </b-form-group>
                            <b-button tax="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Account Tax Line
                            </div>
                            <div class="card-tools">
                                <button class="btn btn-success" @click="newModal">
                                    Add <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0" v-if="taxes.data.length>0">
                            <table class="table table-hover">
                                <tr>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>

                                <tr v-for="tax in taxes.data" :key="tax.tax_id">
                                    <td>{{ tax.name }}</td>
                                    <td>
                                        <button class="btn btn-success btn-sm" @click="editModal(tax)">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        
                                        <button class="btn btn-danger btn-sm" @click="deleteTax(tax.id)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div v-else class="text-center alert alert-danger p-3">
                            Nothing found
                        </div>
                        <div class="card-footer" v-if="taxes.data.length>0">
                            <pagination
                                :data="taxes"
                                @pagination-change-page="getResults"
                            ></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addNewUserLabel" v-show="!editMode">
                            Create
                        </h5>
                        <h5 class="modal-title" id="addNewUserLabel" v-show="editMode">
                            Update
                        </h5>

                        <button tax="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="editMode ? update() : create()">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Name</label>
                                <input v-model="form.name" tax="text"
                                    name="name" class="form-control" :class="{'is-invalid': form.errors.has('name')}"/>
                                <has-error :form="form" field="name"></has-error>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button tax="button" class="btn btn-danger" data-dismiss="modal">
                                Close
                            </button>
                            <button v-show="editMode" tax="submit" class="btn btn-success">
                                Update
                            </button>
                            <button v-show="!editMode" tax="submit" class="btn btn-primary">
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
    import moment from 'moment'
    export default {
        created() {
            this.loadTax();
        },

        data() {
            return {
                filterForm: {
                    name: '',
                },

                taxes: [],
                taxes: {
                    data: '',
                },
                editMode: false,
                is_busy: false,

                form: new Form({
                    id: "",
                    name: "",
                }),
            };
        },

        methods: {
            getResults(page = 1) {
                axios.get("/api/tax?page=" + page)
                .then(response => {
                    this.taxs = response.data;
                });
            },

            loadTax() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/tax", { params: this.filterForm })
                .then(({ data }) => {
                    this.taxes = data;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadTax();
                this.$refs.filter.hide();
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNew").modal("show");
            },

            create() {
                this.form.post("/api/tax")
                .then(() => {
                    $("#addNew").modal("hide");

                    Swal.fire(
                        "Created!",
                        "Tax Line Created Successfully.",
                        "success"
                    );
                    this.loadTax();
                })
                .catch(() => {
                    Swal(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                });
            },

            editModal(tax) {
                (this.editMode = true), this.form.reset();
                $("#addNew").modal("show");
                this.form.fill(tax);
            },

            update() {
                this.form.put("/api/tax/" + this.form.id)

                .then(() => {
                    $("#addNew").modal("hide");

                    Swal.fire("Updated!", "Tax Line Updated Successfully.", "success");
                    this.$Progress.finish();
                    this.loadTax();
                })

                .catch();
            },

            deleteTax(id) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then(result => {
                    if (result.value) {
                        this.$Progress.start();
                        axios.delete("/api/tax/" + id)
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Tax line has been deleted.",
                                "success"
                            );
                            this.$Progress.finish();
                            this.loadTax();
                        })

                        .catch(() => {
                            Swal(
                                "Failed!",
                                "Ops, Something went wrong, try again.",
                                "warning"
                            );
                        });
                    }
                });
            }
        }
    };
</script>
