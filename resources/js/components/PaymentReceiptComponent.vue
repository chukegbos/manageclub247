<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid pt-2">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <h5><strong>PAYMENT RECEIPT</strong></h5>
                        <h6><strong>{{ site.sitename }}</strong></h6>
                    </div>
    
                    <p>
                        <b>Receipt No: </b> {{ payment.rec_id }}<br>
                        <b>Name: </b> {{ payment.get_member.get_member }}<br>
                        <b>Description: </b> {{ payment.get_product.description }}<br>
                        <b>Amount: </b> <span v-html="nairaSign"></span>{{ formatPrice(payment.amount)  }}<br><br>
                        <b>Date: </b> {{ payment.created_at | myDate }}<br>
                        <b>Front Desk: </b> {{ payment.created_by }}
                    </p>
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
                payment: {
                    get_member: '',
                },

                payment: {
                    get_product: '',
                },

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