<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">          
            <div class="mb-2 p-2"> 
                <h4 class="text-center"><strong>Demand Notice</strong></h4>
            </div>   

            <div class="row"> 
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="card" id="printDemand">
                        <div class="border">
                            <div class="card-header bg-light text-center">
                                <h4>{{ site.sitename }}</h4>
                                <h6>Demand Notice as at {{ filterForm.end_date | myDate }}</h6>
                            </div>
                           
                            <div class="card-body text-center">
                                <p><b>Name:</b> {{ user.name }}</p>
                                <p><b>Email:</b>{{ user.email }}</p>
                                <p><b>Debts:</b> <span v-html="nairaSign"></span>{{ formatPrice(payment_debts_sum) }}</p>
                                <p><b>Total Payment:</b> <span v-html="nairaSign"></span>{{ formatPrice(payment_sum) }}</p>
                                <p><b>Subcription Wallet Balance:</b> <span v-html="nairaSign"></span>{{ formatPrice(user.wallet_balance) }}</p>
                                <p><b>Kitchen/Bar Wallet Balance:</b> <span v-html="nairaSign"></span>{{ formatPrice(user.bar_wallet) }}</p>
                                <p><b>Other Wallet Balance:</b> <span v-html="nairaSign"></span>{{ formatPrice(user.credit_unit) }}</p>
                            </div>
                        </div> 
                    </div> 

                    <div class="text-center">
                        <b-button size="sm" variant="outline-info" @click="PrintD"> <i class="fa fa-print"></i> Print</b-button>
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
                    start_date: moment().subtract(30, 'days').format("YYYY-MM-DD"),
                    end_date: moment().add(1, 'days').format("YYYY-MM-DD"),
                },
                user: '',
                user: {
                    c_person:'',
                },
                unique_id: '',
                site: '',
                value_order_count: '',
                all_orders: [],
                sum_fund: '',
                funds: '',
                nairaSign: "&#x20A6;",
                users: [],
                userQuery: '',

                funds: [],
                payment_debts: [],
                payments: [],
                payment_sum: '',
                payment_debts_sum: '',
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
                            this.sum_fund = response.data.sum_fund;
                            this.user = response.data.user;
                            this.member = response.data.member;
                            this.all_orders = response.data.all_orders;
                            this.value_order_count = response.data.value_order_count;
                            this.funds = response.data.funds;
                            this.payment_debts = response.data.payment_debts;
                            this.payments = response.data.payments;
                            this.payment_sum = response.data.payment_sum;
                            this.payment_debts_sum = response.data.payment_debts_sum;
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

            PrintD() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.$htmlToPaper('printDemand');
                this.is_busy = false;
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            }
        }
    };
</script>
