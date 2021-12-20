<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid pt-2">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body" id="printMe">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h4><strong>PAYMENT RECEIPT</strong></h4>
                                    <h6><strong>{{ site.sitename }}</strong></h6>
                                </div>
                              

                                <div class="col-md-6">
                                    <p><strong>Paid by</strong></p>
                                    <div class="table-responsive advance-table">
                                        <table class="table table-bordered">
                                           <tr>
                                                <th>Name</th>
                                                <td>{{ fund.customer_id.last_name }} {{ fund.customer_id.first_name }} {{ fund.customer_id.middle_name }} ({{ fund.customer_id.membership_id }})</td>
                                            </tr>

                                            <tr>
                                                <th>Phone Number</th>
                                                <td>{{ fund.customer_id.phone_1 }}</td>
                                            </tr> 

                                            <tr>
                                                <th>Email</th>
                                                <td>{{ fund.customer_id.email }}</td>
                                            </tr>
                                            
                                            <tr>
                                                <th>Address</th>
                                                <td>{{ fund.customer_id.address }}</td>
                                            </tr>
                                        </table> 
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <p><strong>Received by </strong></p>
                                    <div class="table-responsive advance-table">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Outlet</th>
                                                <td>{{ site.sitename }}</td>
                                            </tr>

                                            <tr>
                                                <th>From</th>
                                                <td>{{ fund.user_id }}</td>
                                            </tr>

                                            <tr>
                                                <th>Phone</th>
                                                <td>{{ site.phone }}</td>
                                            </tr>
                                            
                                            <tr>
                                                <th>Address</th>
                                                <td>{{ site.address}}</td>
                                            </tr>
                                        </table> 
                                    </div>
                                </div>

                                <div class="col-md-3"></div>
                                <div class="col-md-6">
                                    <p><b>Date Received:</b> {{ fund.created_at | myDate }}<br>
                                    <b>Payment Method:</b> {{ fund.mop }} <span v-if="fund.tran_type">({{ fund.tran_type }})</span>
                                    <br>
                                    <b>Amount:</b> <span v-html="nairaSign"></span>{{ formatPrice(fund.amount) }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-center mt-1">
                                <small><b>NB</b>: Always present this receipt as it serves as evidence of payment</small>
                            </p>
                        </div>

                        <div class="card-footer">
                            <button @click=onPrint class="btn btn-success btn-lg btn-block mb-2">Print</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import VueBootstrap4Table from 'vue-bootstrap4-table';
    export default {
        created() {
            this.viewOrder();
            this.loadSite();
            this.getUser();
        },

        data() {
            return {
                is_busy: false,
                site: "",
                user: "",
              
                customer: '',
                fund: '',
                customer: {
                    id: '',
                },
                nairaSign: "&#x20A6;",
            };
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                });
            },

            viewOrder() {
                if(this.is_busy) return;
                this.is_busy = true;

                let ref_id = this.$route.params.ref_id;

                axios.get("/api/funding/" + ref_id)

                .then((response) => {
                    console.log(response)
                    this.fund = response.data
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },

            onPrint() {
                this.$htmlToPaper('printMe');
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            }
        }
    };
</script>

<style>
    tr {
        height: 10px;
        font-size: 12px;
    }
</style>