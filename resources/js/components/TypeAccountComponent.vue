<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="mb-2 p-2">
                <h2><strong>Account Type</strong></h2>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="types.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Description </th>
                        </tr>

                        <tr v-for="type in types.data">
                            <td>{{ type.id }}</td>
                            <td>{{ type.name }}</td>
                            <td>{{ type.description }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import moment from 'moment'
    export default {
        created() {
            this.loadType();
        },

        data() {
            return {
                filterForm: {
                    name: '',
                    type: null,
                },

                types: [],
                types: {
                    data: '',
                },
                editMode: false,
                is_busy: false,

                type: [
                    { text: 'Debit', value: 'Debit' },
                    { text: 'Credit', value: 'Credit' },
                ],

                form: new Form({
                    id: "",
                    name: "",
                    type: "Credit",
                }),
            };
        },

        methods: {
            getResults(page = 1) {
                axios.get("/api/type?page=" + page)
                .then(response => {
                    this.types = response.data;
                });
            },

            loadType() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/type", { params: this.filterForm })
                .then(({ data }) => {
                    this.types = data;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadType();
                this.$refs.filter.hide();
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNew").modal("show");
            },

            create() {
                this.form.post("/api/type")
                .then(() => {
                    $("#addNew").modal("hide");

                    Swal.fire(
                        "Created!",
                        "Type Created Successfully.",
                        "success"
                    );
                    this.loadType();
                })
                .catch(() => {
                    Swal(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                });
            },

            editModal(type) {
                (this.editMode = true), this.form.reset();
                $("#addNew").modal("show");
                this.form.fill(type);
            },

            update() {
                this.form.put("/api/type/" + this.form.id)

                .then(() => {
                    $("#addNew").modal("hide");

                    Swal.fire("Updated!", "Type Updated Successfully.", "success");
                    this.$Progress.finish();
                    this.loadType();
                })

                .catch();
            },

            deleteType(id) {
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
                        axios.delete("/api/type/" + id)
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Type has been deleted.",
                                "success"
                            );
                            this.$Progress.finish();
                            this.loadType();
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
