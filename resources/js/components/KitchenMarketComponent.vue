<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Market List</strong></h2>
                </div>

                <div class="col-md-8">
                    <span v-if="admin.role==1 || admin.role==11 || admin.role==13">
                        <router-link to="/kitchen/market/create" class="pull-right m-2">
                            <b-button variant="outline-primary" size="sm">
                                Create List
                            </b-button>
                        </router-link>
                    </span>
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

                            <b-form-group label="market Code:">
                                <b-form-input id="name" v-model="filterForm.name" type="text"></b-form-input>
                            </b-form-group>

                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                      
                </div>
            </div>

            <div class="card">
                <div class="card-header text-center">
                    <button class="btn btn-info" @click="oldOrder">Old Transactions</button>
                    <button class="btn btn-info" @click="newOrder">New Transactions</button>
                    <p>
                        Older transactions are from <b>Inception to 31st of March 2023</b><br>
                        New transactions are from <b>1st of April 2023 to date.</b>
                    </p>
                </div>
                
                <div class="card-body table-responsive p-0" v-if="markets.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th>Market ID</th>
                            <th>Bought By</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>

                        <tr v-for="market in markets.data" :key="market.market_id">
                            <td>{{ market.market_id }}<br><b>{{ market.purchase_date | myDate }}</b></td>
                            <td>{{ market.user_id }}</td>
                            <td>{{ market.status }}</td>
                            <td>{{ market.approved_by }}</td>
                            <td>
                                <span v-html="nairaSign"></span>{{ formatPrice(market.amount) }}
                            </td>
                            <td>
                                <b-dropdown id="dropdown-right" text="Action" size="sm" variant="info">
                                    <b-dropdown-item>
                                        <router-link :to="'/kitchen/markets/' + market.market_id">
                                            View Items
                                        </router-link>
                                    </b-dropdown-item>

                                    <b-dropdown-item v-if="(admin.role==9 || admin.role==1) && market.approved_by=='---'">
                                        <a href="javascript:void(0)" @click="approve(market)">
                                            Approve
                                        </a>
                                    </b-dropdown-item>

                                    <b-dropdown-item v-if="(admin.role==9 || admin.role==1) && market.approved_by=='---'">
                                        <a href="javascript:void(0)" @click="reject(market)">
                                            Reject
                                        </a>
                                    </b-dropdown-item>
                                </b-dropdown>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Market List Found.</strong></h3></div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-4">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                           
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="markets" @pagination-change-page="getResults" :limit="-1"></pagination>
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
            this.loadMarket();
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
                markets: {},
                action: {
                    selected: []
                },
                markets: {
                    data: {},
                },
                count_all: '',
                admin: '',
                unprintable: false,
            };
        },

        methods: {     
            getResults(page = 1) {
                axios.get("/api/markets?page=" + page)
                .then(response => {
                    this.markets = response.data.markets;
                });
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadUsers();
                this.getUser();
            },

            loadMarket() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/markets", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.markets.data = data.markets;
                    }
                    else{
                        this.markets = data.markets;
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
                this.loadMarket();
                this.getUser();
                this.$refs.filter.hide();
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            newOrder(){
                this.filterForm.time= 1;
                this.loadMarket();
                this.getUser();
            },

            oldOrder(){
                this.filterForm.time= 0;
                this.loadMarket();
                this.getUser();
            },

            approve(market)
            {
                axios.get('/api/markets/approve/' + market.market_id)
                .then(({ data }) => {
                    Swal.fire(
                        "Approved!",
                        "You have approved the list.",
                        "success"
                    );
                    this.loadMarket();
                    this.getUser();
                });
            },

            reject(market)
            {
                axios.get('/api/markets/reject/' + market.market_id)
                .then(({ data }) => {
                    Swal.fire(
                        "Accepted!",
                        "You have rejected the list.",
                        "success"
                    );
                    this.loadMarket();
                    this.getUser();
                });
            },

            edit(market)
            {
                this.$router.push({ path: '/admin/market/' + market.market_id });
            },
        }
    };
</script>
