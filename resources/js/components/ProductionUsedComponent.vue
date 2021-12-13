<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Use Raw Materia Production</strong></h2>
                </div>

                <div class="col-md-6">
                    <router-link to="/production/used/create" class="pull-right m-2">
                        <b-button variant="outline-primary" size="sm">
                            Add <i class="fa fa-plus"></i>
                        </b-button>
                    </router-link>

                    <b-button variant="outline-primary" size="sm" class="pull-right m-2" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>

                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">

                            <div class="row">
                                <div class="col-lg-6">
                                    <b-form-group label="Start Date:" label-for="Start Date">
                                        <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>

                                <div class="col-lg-6">
                                    <b-form-group label="End Date:" label-for="End Date">
                                        <b-form-datepicker v-model="filterForm.end_date" placeholder="End date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>
                            </div>

                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                       
                </div>
            </div>
            <div class="card">
               
                <div class="card-body table-responsive p-0" v-if="productions.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th>List Code</th>
                            <th>Date of Production</th>
                            <th>Action</th>
                        </tr>

                        <tr v-for="production in productions.data" :key="production.production_id">
                            <td>{{ production.code }}</td>
                            <td>{{ production.process_date | myDate }}</td>
                            <td>
                                <router-link :to="'/process/' + production.code">
                                    <i class="fa fa-edit text-blue"></i>
                                </router-link>
                              
                                <!--<a href="#" @click="deleteProduction(production.id)">
                                    <i class="fa fa-trash text-red"></i>
                                </a>-->
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Item Found.</strong></h3></div>
                </div>

                <div class="card-footer">
                    <pagination
                        :data="productions"
                        @pagination-change-page="getResults"
                    ></pagination>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import moment from 'moment'
    export default {
        created() {
            this.loadProduction();
        },

        data() {
            return {
                filterForm: {
                    start_date: '',
                    end_date: '',
                    name: '',
                },
                productions: [],
                productions: {
                    data: '',
                },

                is_busy: false,
            };
        },

        methods: {
            getResults(page = 1) {
                axios.get("/api/production/used?page=" + page)
                .then(response => {
                    this.productions = response.data;
                });
            },

            loadProduction() {
                if (this.is_busy) return;
                this.is_busy = true;
                axios.get("/api/production/used", { params: this.filterForm })
                .then(({ data }) => {
                    this.productions = data;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadProduction();
                this.$refs.filter.hide();
            },

            deleteProduction(id) {
                console.log(id)
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
                        axios.delete("/api/production/used" + id)
                        .then((response) => {
                            if(response.data=="You cannot delete this production detail because the product has decreased in the Inventory"){
                                Swal.fire(
                                    "Error!",
                                    "You cannot delete this production detail because the product has decreased in the Inventory",
                                    "Warning"
                                );
                            }
                            else{
                                Swal.fire(
                                    "Deleted!",
                                    "Production Record has been deleted.",
                                    "success"
                                );
                            }
                            this.loadProduction();
                        })

                        .catch((err) => {
                            console.log(err)
                            Swal.fire(
                                "Failed!",
                                "Ops, Something went wrong, try again.",
                                "error"
                            );
                        });
                    }
                });
            },
        }
    };
</script>
