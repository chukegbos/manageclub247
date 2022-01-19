<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid pt-2">
            <!--<div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body" id="printMe">
                            <div class="border p-2 text-center">
                                <div class="mb-3">
                                    <h4><strong>PAYMENT RECEIPT</strong></h4>
                                    <h6><strong>{{ site.sitename }}</strong></h6>
                                </div>
                                <div class="mb-3">
                                    <p>
                                        <b>Name: </b> {{ payment.get_member.get_member }}<br><br>
                                        <b>Description: </b> {{ payment.get_product.description }}<br><br>
                                        <b>Amount: </b> <span v-html="nairaSign"></span>{{ formatPrice(payment.amount)  }}<br><br>
                                        <b>Date: </b> {{ payment.created_at | myDate }}<br> <br>
                                        <b>Front Desk: </b> {{ payment.created_by }}
                                    </p>
                                </div>
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
                    <div class="border p-2 text-center">
                        <div class="mb-3">
                            <h1><strong>PAYMENT RECEIPT</strong></h1>
                            <h2><strong>{{ site.sitename }}</strong></h2>
                        </div>
                        <div class="mb-3">
                            <h4>
                                <b>Name: </b> {{ payment.get_member.get_member }}<br><br>
                                <b>Description: </b> {{ payment.get_product.description }}<br><br>
                                <b>Amount: </b> <span v-html="nairaSign"></span>{{ formatPrice(payment.amount)  }}<br><br>
                                <b>Date: </b> {{ payment.created_at | myDate }}<br> <br>
                                <b>Front Desk: </b> {{ payment.created_by }}
                            </h4>
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
                payment: '',
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

                let id = this.$route.params.id;

                axios.get("/api/payment/" + id)

                .then((response) => {
                    console.log(response.data)
                    this.payment = response.data;
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