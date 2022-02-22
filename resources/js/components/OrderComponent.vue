<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>List of Transactions</strong></h2>
                </div>

                <div class="col-md-8">
                    <b-button size="sm" variant="outline-info"class="pull-right m-2" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>

                    <b-button variant="outline-primary" class="pull-right m-2" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>

                    <b-modal id="filter-modal" ref="filter" title="Report Filter" hide-footer>
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

                            <b-form-group label="Customer:" label-for="keyword">
                                <b-form-input id="name" v-model="filterForm.customer" type="text"></b-form-input>
                            </b-form-group>

                            <b-form-group label="Store:">
                                <v-select label="name" :options="stores" @input="setSelected" ></v-select>
                            </b-form-group>

                            <!--<b-form-group label="Marketer:" label-for="staff">
                                <b-form-select
                                    v-model="filterForm.staff"
                                    :options="staff"
                                    value-field="id"
                                    text-field="name">

                                    <template v-slot:first>
                                        <b-form-select-option :value="0">
                                            All
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-form-group>-->

                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                
                </div>
            </div>

            <div class="card">

                <div class="card-body table-responsive p-0" v-if="report_items.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <tr>
                            <th>Date</th>
                            <th>Ref ID</th>
                            <th>Bar</th>
                            <th>Payment Type</th>
                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                        <tr v-for="order in report_items.data" :key="order.id">
                            <td>{{ order.created_at | myDate }}</td>
                            <td>{{ order.sale_id }}</td>
                            <td>{{ order.store_id }}</td>
                            <td>
                                <span v-if="order.mop==1">Cash Sale</span>
                                <span v-else>Credit Sale</span>
                            </td>
                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                            <td>{{ order.status | capitalize }}</td>
                            <td>
                                <b-dropdown id="dropdown-right" text="Action" variant="info">
                                    <b-dropdown-item href="javascript:void(0)"  @click=viewItems(order)>View</b-dropdown-item>

                                    <!--<b-dropdown-item href="javascript:void(0)" @click="viewRec(order)">Receipt</b-dropdown-item>

                                    <b-dropdown-item href="javascript:void(0)" @click=cancel(order) v-if="user.invoice==1 && order.status=='concluded'">Return Items</b-dropdown-item>-->
                                </b-dropdown>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Transaction Found.</strong></h3></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                     <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="0">All</option>
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Items</b>
                        </div>

                        <div class="col-md-10" v-if="this.filterForm.selected!=0">
                            <pagination :data="report_items" @pagination-change-page="getResults" :limit="-1"></pagination>
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
        created() {
            this.loadSales();
            this.getUser();
        },

        data() {
            return {
                filterForm: {
                    start_date: '',
                    end_date: '',
                    customer: '',
                    store: '',
                    selected: '15',
                },
                action: {
                    selected: []
                },
                stores: [],
                unprintable: false,
                count_all: '',
                nairaSign: "&#x20A6;",
                staff: {},
                user: "",
                is_busy: false,
                report_items: {},
                report_items: {
                    data: '',
                },
            };
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                    this.stores = data.stores;
                });
            },

             onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadSales();
                this.getUser();
            },

            setSelected(value) {
                this.filterForm.store = value.id;
            },

            getResults(page = 1) {

                axios.get('/api/store/orders?page=' + page, { params: this.filterForm })
                .then(response => {
                    this.report_items = response.data.report_data;
                })
                .catch((err) => {
                        console.log(err);
                });
            },

            onPrint() {
                this.$htmlToPaper('printMe');
            },

            loadSales() {
                if(this.is_busy) return;
                this.is_busy = true;

                this.filterForm.buyer = this.$route.params.buyer;
               

                axios.get('/api/store/orders', { params: this.filterForm })
                .then((data) => {

                    console.log(data.data)
                    if(this.filterForm.selected==0)
                    {
                        this.report_items.data = data.data.report_data;
                    }
                    else{
                        this.report_items = data.data.report_data;
                    }
                    this.count_all = data.data.all;
                    this.staff = data.data.users;
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadSales();
                this.getUser();
                this.$refs.filter.hide();
            },

            viewItems(order) {
                this.$router.push({ path: "/orderview/" + order.sale_id });
            },

            viewRec(order) {
                console.log(order)
                this.$router.push({ path: "/receipt/" + order.sale_id });
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            cancel(order) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, cancel it!"
                })
                .then(result => {
                    if (result.value) {
                        axios.get('/api/cart/cancel', { params: order})
                        .then(() => {
                            Swal.fire(
                                "Done!",
                                "Order Removed.",
                                "success"
                            );
                            this.loadSales();
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
    };
</script>

<style>
    .list_product{
        list-style: none;
    }

    .sell{
        color: #fff ! important;
    }
</style>
