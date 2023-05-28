<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>List of Purchase</strong></h2>
                </div>

                <div class="col-md-8">
                    <!--
                        <router-link to="/admin/purchase/create" class="pull-right m-2">
                            <b-button variant="outline-primary" size="sm">
                                Item Purchase
                            </b-button>
                        </router-link>

                        <router-link to="/admin/purchase/nonitem" class="pull-right m-2">
                            <b-button variant="outline-primary" size="sm">
                               Non-item Purchase
                            </b-button>
                        </router-link>
                    -->

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

                            <b-form-group label="Purchase Code:">
                                <b-form-input id="name" v-model="filterForm.name" type="text"></b-form-input>
                            </b-form-group>

                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                      
                </div>
            </div>

            <div class="card">
                <div class="card-header text-center">
                    <button class="btn btn-info" @click="oldOrder">Old Purchase</button>
                    <button class="btn btn-info" @click="newOrder">New Purchase</button>
                    <p>
                        Older purchase are from <b>Inception to 31st of March 2023</b><br>
                        New purchase are from <b>1st of April 2023 to date.</b>
                    </p>
                </div>
                <div class="card-body table-responsive p-0" v-if="purchases.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th>Purchase ID</th>
                            <th>Initiated By</th>
                            <th>Approved By</th>
                            <th>Accepted By</th>
                            <th>Amount</th>
                            <th>Supplier</th>
                            <th>Action</th>
                        </tr>

                        <tr v-for="purchase in purchases.data" :key="purchase.purchase_id">
                          
                            <td>{{ purchase.purchase_id }}<br><b>{{ purchase.purchase_date | myDate }}</b></td>
                            <td>{{ purchase.initiated_by }}</td>
                            <td>{{ purchase.approved_by }}</td>
                            <td>{{ purchase.accepted_by }}</td>
                            <td>
                                <span v-html="nairaSign"></span>{{ formatPrice(purchase.total_price) }}
                            </td>
                            <td>{{ purchase.supplier }}<br><b>{{ purchase.mop }}</b></td>
                          
                            <td>
                                <b-dropdown id="dropdown-right" text="Action" size="sm" variant="info">
                                    <b-dropdown-item>
                                        <router-link :to="'/admin/purchases/' + purchase.purchase_id">
                                            View Items
                                        </router-link>
                                    </b-dropdown-item>

                                    <span v-if="(user.role==6 || user.role==1) && purchase.status==0 && purchase.status_accept==0">
                                        <b-dropdown-item>
                                            <a href="javascript:void(0)" @click="edit(purchase)">
                                                Edit Items
                                            </a>
                                        </b-dropdown-item>
                                    </span>

                                    <span v-if="purchase.status==1 && purchase.status_accept==0">
                                        <b-dropdown-item v-if="(user.role==12 || user.role==1) && purchase.approved_by=='---'">
                                            <a href="javascript:void(0)" @click="approve(purchase)">
                                                Approve
                                            </a>
                                        </b-dropdown-item>
                                        <b-dropdown-item v-if="(user.role==12 || user.role==1) && purchase.approved_by=='---'">
                                            <a href="javascript:void(0)" @click="reject(purchase)">
                                                Reject
                                            </a>
                                        </b-dropdown-item>
                                        <b-dropdown-item v-if="(user.role==6 || user.role==1) && purchase.approved_by!='---'">
                                            <a href="javascript:void(0)" @click="accept(purchase)">
                                                Accept
                                            </a>
                                        </b-dropdown-item>
                                    </span>
                                </b-dropdown>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Purchase Found.</strong></h3></div>
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
                            <br> Total: <b>{{ count_all }} Purchases</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="purchases" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <!--<div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>-->
                        <!-- </div> -->
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import moment from 'moment'
    export default {
        created() {
            this.loadPurchase();
            this.getUser();
        },

        data() {
            return {
                filterForm: {
                    start_date: '',
                    end_date: '',
                    name: '',
                    selected: '10',
                    time: 1,
                },

                is_busy: false,
                nairaSign: "&#x20A6;",
                editMode: false,
                model: {},
                purchases: {},
                action: {
                    selected: []
                },
                purchases: {
                    data: {},
                },
                count_all: '',
                user: '',
                unprintable: false,
            };
        },

        methods: {     
            getResults(page = 1) {
                axios.get("/api/purchases?page=" + page)
                .then(response => {
                    this.purchases = response.data.purchases;
                });
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.user = data.user;
                });
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadPurchase();
                this.getUser();
            },

            newOrder(){
                this.filterForm.time= 1;
                this.loadPurchase();
                this.getUser();
            },

            oldOrder(){
                this.filterForm.time= 0;
                this.loadPurchase();
                this.getUser();
            },
            
            loadPurchase() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/purchases", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.purchases.data = data.purchases;
                    }
                    else{
                        this.purchases = data.purchases;
                    }
                    this.count_all = data.all;
                })
                .catch(() => {
                    Swal,fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadPurchase();
                this.getUser();
                this.$refs.filter.hide();
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            approve(purchase)
            {
                if (this.is_busy) return;
                this.is_busy = true;
                axios.get('/api/purchases/approve/' + purchase.purchase_id)
                .then(({ data }) => {
                    Swal.fire(
                        "Accepted!",
                        "You have approved the purchase.",
                        "success"
                    );
                })
                .catch(() => {
                    Swal,fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                    this.loadPurchase();
                    this.getUser();
                });
            },

            accept(purchase)
            {
                if (this.is_busy) return;
                this.is_busy = true;
                axios.get('/api/purchases/accept/' + purchase.purchase_id)
                .then(({ data }) => {
                    Swal.fire(
                        "Accepted!",
                        "You have accepted the purchase.",
                        "success"
                    );
                })
                .catch(() => {
                    Swal,fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                    this.loadPurchase();
                    this.getUser();
                });
            },

            reject(purchase)
            {
                if (this.is_busy) return;
                this.is_busy = true;
                axios.get('/api/purchases/reject/' + purchase.purchase_id)
                .then(({ data }) => {
                    Swal.fire(
                        "Rejected!",
                        "You have rejected the purchase.",
                        "success"
                    );
                })
                .catch(() => {
                    Swal,fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                    this.loadPurchase();
                    this.getUser();
                });
            },

            edit(purchase)
            {
                this.$router.push({ path: '/admin/purchase/' + purchase.purchase_id });
            },
        }
    };
</script>
