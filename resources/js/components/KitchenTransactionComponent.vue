<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Kitchen Orders</strong></h2>
                </div>

                <div class="col-md-6">                      
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="services.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Unit Price (<span v-html="nairaSign"></span>)</th>
                                <th>Amount (<span v-html="nairaSign"></span>)</th>
                               
                                <th v-if="admin.role==9">Time Requested</th>
                                <th v-if="admin.role==9">Time Served</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in services.data" :key="item.id">
                                <td>{{ item.kitchen }}</td>
                                <td>{{ item.qty }}</td>
                                <td>{{ formatPrice(item.amount) }}</td>
                                <td>{{ formatPrice(item.qty * item.amount) }}</td>
                                <td v-if="admin.role==9">{{ item.created_at | setDate }}</td>
                                <td v-if="admin.role==9"><span v-if="item.status==1">{{ item.updated_at | setDate }}</span></td>
                                <td>
                                    <span v-if="item.status==1">
                                        <span class="text-success">Delivered</span>
                                    </span>
                                    <span v-else>
                                        <span class="text-danger">Pending</span>
                                        <span v-if="admin.role==14 || admin.role==15">
                                            <a href="javascript:void(0)" @click="mark(item)" class="btn btn-success btn-sm">Mark as Delivered</a>
                                        </span>
                                    </span>

                                    
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Request Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} Bars</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="services" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.getUser();
            this.loadservices();
            
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                nairaSign: "&#x20A6;",
                model: {},
                services: {},
                service: "",
                filterForm: {
                    name: '',
                    selected: '10',
                },

                action: {
                    selected: []
                },
                services: {
                    data: {},
                },
                count_all: '',
                unprintable: false,
                admin: '',
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadservices();
                this.getUser();
            },

            loadservices() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/kitchen/service", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.services.data = data.services;
                    }
                    else{
                        this.services = data.services;
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

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
            },


            getResults(page = 1) {
                axios.get("/api/kitchen/service?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.services = response.data.services;
                });
            },

            onFilterSubmit()
            {
                this.loadservices();
                this.getUser();
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            view(service) {
                this.$router.push({ path: "/outlets/" + service.code });
            },

            mark(item) {
                Swal.fire({
                    title: "Are you sure you have delivered?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, accept!"
                })
                .then(result => {
                    if (result.value) {
                        axios.get('/api/kitchen/service/' + item.id)
                        .then(() => {
                            Swal.fire(
                                "Accepted!",
                                "Food Delivered.",
                                "success"
                            );
                            this.loadservices();
                            this.getUser();
                        })

                        .catch(() => {
                            Swal.fire(
                                "Failed!",
                                "Ops, Something went wrong, try again.",
                                "warning"
                            );
                        });
                    }
                });
            },

        },

        computed: {
            selectAll: {
                get: function () {
                    return this.services.data ? this.action.selected.length == this.services.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.services.data.forEach(function (service) {
                            selected.push(service.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>
