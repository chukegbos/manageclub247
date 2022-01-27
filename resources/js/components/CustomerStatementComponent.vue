<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">          
            <div class="row mb-2 p-2"> 
                <div class="col-md-4">
                    <h2><strong>Statement of Account</strong></h2>
                </div>
                             
                <div class="col-md-6">
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

            <div class="card" id="printMe">
                <div class="card-header text-center" style="background-color:white">
                    <h4>{{ site.sitename }}</h4>
                    <h6>Account Statement as at {{ filterForm.start_date | myDate }} to {{ filterForm.end_date | myDate }}</h6>
                    <p>
                        {{ user.name }}<br>
                        {{ user.email }}<br>
                        {{ user.phone }}
                    </p>
                </div>
               
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-center">Payments</h4>
                            <table class="table table-hover" style="height:10px">
                                <tr>
                                    <th>Payment</th>
                                    <th>Amount</th>
                                    <th>Date Created</th>
                                    <th>Created By</th>
                                    <th>Payment Method</th>
                                </tr>

                                <tr v-for="payment in payments" :key="payment.id">
                                    <td>{{ payment.get_product.description }}</td>
                                    <td><span v-html="nairaSign"></span>{{ formatPrice(payment.amount)  }}</td>
                                    <td>{{ payment.created_at | myDate }}</td> 
                                    <td>{{ payment.created_by }}</td>
                                    <td>
                                        {{ payment.payment_channel }}<br>
                                        <a href="javascript:void(0)" @click="viewReceipt(payment)">Receipt</a>
                                    </td> 
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h4 class="text-center">Debit</h4>
                            <table class="table table-hover" style="height:10px">
                                <tr>
                                    <th>Description</th>
                                    <th>Amount (<span v-html="nairaSign"></span>)</th>
                                    <th>Date Created</th>
                                </tr>

                                <tr v-for="debt in debts" :key="debt.id">
                                    <td>{{ debt.description }}</td>
                                    
                                    <td><span v-html="nairaSign"></span>{{ formatPrice(debt.amount)  }}</td>
                                    <td>{{ debt.start_date | myDate }}</td>
                                </tr>
                            </table>
                        </div>

                        <!--<div class="col-md-6">
                            <h4>Fundings</h4>
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
                        </div>-->
                    </div>
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
                    start_date: moment().subtract(5, 'years').format("YYYY-MM-DD"),
                    end_date: moment().add(1, 'days').format("YYYY-MM-DD"),
                },
                user: '',
                unique_id: '',
                value_order_count: '',
                debts: [],
                payments: [],
                sum_fund: '',
                funds: '',
                nairaSign: "&#x20A6;",
                users: [],
                userQuery: '',
                site: '',
            };
        },

        methods: {
            startDateMoment(value, grace_period) {
                return moment(value).add(grace_period, 'days').format('MMMM Do YYYY');
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
                            this.$router.push({ path: "/admin/customers"});
                        }
                        else
                        {
                            this.funds = response.data.funds;
                            this.payments = response.data.payments;
                            this.user = response.data.user;
                            this.debts = response.data.payment_debts;
                        }                    
                    })

                    .catch((err) => {
                        console.log(err);
                    });
                }

                else {
                    this.$router.push({ path: "/admin/customers"});
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
