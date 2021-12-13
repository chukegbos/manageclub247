<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">          
            <div class="row mb-2 p-2"> 
                <div class="col-md-6">
                    <h2><strong>Customer Statement</strong></h2>
                </div>
                <div class="col-md-6">
                    <vue-typeahead-bootstrap
                        v-model="userQuery"
                        :ieCloseFix="false"
                        :data="users"
                        :serializer="data => data.name"
                        @hit="getUserID($event)"
                        placeholder="Search for customer"
                        @input="lookUser"
                    />
                </div>               
                <div class="col-md-10">
                    <b-form @submit.stop.prevent="onFilterSubmit" size="sm">
                        <b-input-group>
                            <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                    :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                            </b-form-datepicker>

                            <b-form-datepicker v-model="filterForm.end_date" placeholder="End date"
                                    :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                            </b-form-datepicker>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>  
                </div>
                <div class="col-md-2">
                    <b-button size="sm" variant="outline-info" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>
                </div> 
            </div>

            <div class="card" id="printMe" v-if="this.unique_id">
                <div class="card-header text-center">
                    <h6>{{ user.name }} Statement as at {{ filterForm.start_date | myDate }} to {{ filterForm.end_date | myDate }}</h6>
                    <p>
                        {{ user.address }} {{ user.city }} {{ user.state }}<br>
                        {{ user.email }}<br>
                        {{ user.phone }}
                    </p>
                </div>
               
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Credit</h4>
                            
                                <table class="table table-hover" style="height:10px">
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Method</th>
                                        <th>Amount (<span v-html="nairaSign"></span>)</th>
                                    </tr>

                                    <tr v-for="fund in funds" :key="fund.id">
                                        <td>{{ fund.created_at | myDate }}</td> 
                                        <td>{{ fund.ref_id }}</td> 
                                        <td>{{ fund.mop  }}</td>
                                        <td>{{ formatPrice(fund.amount)  }}</td>
                                    </tr>

                                    <tr>
                                        <td></td> 
                                        <td></td>
                                        <td>Total</td>
                                        <td><b>{{ formatPrice(sum_fund)  }}</b></td>
                                    </tr>
                                </table>
                           
                        </div>

                        <!--<div class="col-md-6">
                            <h4>Debit</h4>
                            
                                <table class="table table-hover" style="height:10px">
                                    <tr>
                                        <th>Payment</th>
                                        <th>Description</th>
                                        <th>Amount (<span v-html="nairaSign"></span>)</th>
                                        <th>Date Created</th>
                                        <th>Grace Period</th>
                                    </tr>

                                    <tr v-for="debt in all_orders" :key="debt.id">
                                        <td><span v-if="debt.product">{{ debt.product.payment_name }}</span></td>
                                        <td>{{ debt.description }}</td>
                                        <td><span v-if="debt.product"><span v-html="nairaSign"></span>{{ formatPrice(debt.product.amount)  }} </span><br> <span class="badge badge-danger btn-sm">Unpaid</span></td>
                                        <td>{{ debt.start_date | myDate }}</td> 
                                        <td>
                                            {{ debt.grace_period }} Days 
                                            <span class="badge badge-danger" v-if="debt.period==0">Expired</span><br>
                                            {{ startDateMoment(debt.start_date, debt.grace_period) }}
                                        </td> 

                                    </tr>

                                    <tr>
                                        <td></td> 
                                        <td></td>
                                        <td>Total</td>
                                        <td><b>{{ formatPrice(value_order_count)  }}</b></td>
                                    </tr>
                                </table>
                        </div>-->
                    </div>
                </div>

                <div class="card-footer text-center">
                    Account balance: <b><span v-html="nairaSign"></span>{{ formatPrice(sum_fund - value_order_count) }}</b>
                </div>
            </div> 
        </div>
    </b-overlay>
</template>

<script>
    import VueBootstrap4Table from 'vue-bootstrap4-table';
    import moment from 'moment';
    import {debounce} from 'lodash';
    export default {
        created() {
            this.loadSite();
            this.loadPage();
        },

        data() {
            return {
                is_busy: false,
                filterForm: {
                    start_date: moment().subtract(30, 'days').format("YYYY-MM-DD"),
                    end_date: moment().add(1, 'days').format("YYYY-MM-DD"),
                },
                user: '',
                unique_id: '',
                value_order_count: '',
                all_orders: [],
                sum_fund: '',
                funds: '',
                nairaSign: "&#x20A6;",
                users: [],
                userQuery: '',
            };
        },

        methods: {
            lookUser(){
                debounce(() => {

                    fetch('/api/searchcustomer', {params: this.userQuery})
                    .then(response => {
                        return response.json();
                    })
                    .then(data => {
                        this.users = data;

                    })
                }, 500)();
            },

            startDateMoment(value, grace_period) {
                return moment(value).add(grace_period, 'days').format('MMMM Do YYYY');
            },


            getUserID(data){
                if(this.is_busy) return;
                this.is_busy = true;
                this.unique_id = data.unique_id;
                this.userQuery = data.name;
                axios.get('/api/customer/' + this.unique_id, { params: this.filterForm })
                .then((response) => {
                    if(response.data.error)
                    {
                        Swal.fire(
                            "Failed!",
                            response.data.error,
                            "warning"
                        );
                        this.$router.push({ path: "/admin/customers"});
                    }
                    else
                    {
                        this.funds = response.data.funds;
                        this.sum_fund = response.data.sum_fund;
                        this.user = response.data.user;

                        this.all_orders = response.data.all_orders;
                        this.value_order_count = response.data.value_order_count;
                    }               
                });
                this.is_busy = false;
            },

            loadPage(){
                if(this.is_busy) return;
                this.is_busy = true;

                if(this.$route.params.unique_id){
                    this.unique_id = this.$route.params.unique_id;
                
                    axios.get('/api/customer/' + this.unique_id, { params: this.filterForm })
                    .then((response) => {
                        if(response.data.error)
                        {
                            Swal.fire(
                                "Failed!",
                                response.data.error,
                                "warning"
                            );
                            //this.$router.push({ path: "/admin/customers"});
                        }
                        else
                        {
                            this.funds = response.data.funds;
                            this.sum_fund = response.data.sum_fund;
                            this.user = response.data.user;

                            this.all_orders = response.data.all_orders;
                            this.value_order_count = response.data.value_order_count;
                        }                    
                    })

                    .catch((err) => {
                        console.log(err);
                    });
                }
                this.is_busy = false;
            },

            onFilterSubmit()
            {
                this.loadSite();
                this.loadPage();
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },

            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.$htmlToPaper('printMe');
                this.is_busy = false;
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            }
        }
    };
</script>
