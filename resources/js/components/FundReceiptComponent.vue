<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid pt-2">
            <!--<div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body" id="printMe">
                            <div class="border p-2">
                                <div class="row">
                                    <div class="col-12 col-md-12 text-center">
                                        <h4><strong>PAYMENT RECEIPT</strong></h4>
                                        <h6><strong>{{ site.sitename }}</strong></h6>
                                    </div>                             

                                    <div class="col-6 col-md-6">
                                        <p><strong>Paid by</strong></p>
                                        <div class="table-responsive advance-table">
                                            <table class="table table-bordered">
                                               <tr>
                                                    <th><h4>Name</h4></th>
                                                    <td><h4>{{ fund.customer_id.last_name }} {{ fund.customer_id.first_name }} {{ fund.customer_id.middle_name }} ({{ fund.customer_id.membership_id }})</h4></td>
                                                </tr>

                                                <tr>
                                                    <th><h4>Phone Number</h4></th>
                                                    <td><h4>{{ fund.customer_id.phone_1 }}</h4></td>
                                                </tr> 

                                                <tr>
                                                    <th><h4>Email</h4></th>
                                                    <td><h4>{{ fund.customer_id.email }}</h4></td>
                                                </tr>
                                                
                                                <tr>
                                                    <th><h4>Address</h4></th>
                                                    <td><h4>{{ fund.customer_id.address }}</h4></td>
                                                </tr>
                                            </table> 
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-6">
                                        <p><strong>Received by </strong></p>
                                        <div class="table-responsive advance-table">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th><h4>Club</h4></th>
                                                    <td><h4>{{ site.sitename }}</h4></td>
                                                </tr>

                                                <tr>
                                                    <th><h4>From</h4></th>
                                                    <td><h4>{{ fund.user_id }}</h4></td>
                                                </tr>

                                                <tr>
                                                    <th><h4>Phone</h4></th>
                                                    <td><h4>{{ site.phone }}</h4></td>
                                                </tr>
                                                
                                                <tr>
                                                    <th><h4>Address</h4></th>
                                                    <td><h4>{{ site.address}}</h4></td>
                                                </tr>
                                            </table> 
                                        </div>
                                    </div>

                                    <div class="col-3 col-md-3"></div>
                                    <div class="col-6 col-md-6">
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
                        </div>

                        <div class="card-footer">
                            <button @click="print" class="btn btn-success btn-lg btn-block mb-2">Print</button>
                        </div>
                    </div>
                </div>
            </div>-->

            <div class="card">
                <div class="card-body" id="printMe">
                    <h1><strong>PAYMENT RECEIPT</strong></h1>
                    <h2><strong>{{ site.sitename }}</strong></h2>
                   
                    

                    <br><br>
                    <h3><strong>Paid by</strong></h3>
                    <h4>
                        <b>Name:</b> {{ fund.customer_id.last_name }} {{ fund.customer_id.first_name }} {{ fund.customer_id.middle_name }} ({{ fund.customer_id.membership_id }})
                    </h4>

                    <h4><b>Phone Number: </b>{{ fund.customer_id.phone_1 }}</h4>
                    <!--<h4><b>Email: </b>{{ fund.customer_id.email }}</h4>
                    <h4><b>Address: </b>{{ fund.customer_id.address }}</h4>-->
                            

                    <br><br>
                    <h3><strong>Received by</strong></h3>

                    <h4><b>Club: </b>{{ site.sitename }}</h4>
                    <h4><b>From: </b>{{ fund.user_id }}</h4>
                    <h4><b>Phone: </b>{{ site.phone }}</h4>
                    <!--<h4><b>Address: </b>{{ site.address }}</h4>--> 
                   
               
                    <h4>
                        <b>Date Received:</b> {{ fund.created_at | myDate }}<br><br>
                        <b>Payment Method:</b> {{ fund.mop }} 
                        <span v-if="fund.tran_type">({{ fund.tran_type }})</span>
                        <br><br>
                        
                        <b>Amount:</b> <span v-html="nairaSign"></span>{{ formatPrice(fund.amount) }}
                    </h4>
                </div>
            </div>
            <button id="btnPrint" class="hidden-print btn btn-success p-1 m-2 text-center">Print</button>
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
                fund: {
                    customer_id: '',
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

            async print () {
              // Pass the element id here
              await this.$htmlToPaper('printMe');
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