<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">    
            <div class="row mb-2 p-2">
                <div class="col-md-8">
                    <h2><strong>Sales Details ({{ sale_id }})</strong></h2>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive border">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Price (<span v-html="nairaSign"></span>)</th>
                                <th>Discount</th>
                                <th>Amount (<span v-html="nairaSign"></span>)</th>
                                <th>Dis. Amount (<span v-html="nairaSign"></span>)</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in items">
                                <!--<td>{{ index + 1}}</td>-->
                                <td>{{ item.product_name}}</td>
                                <td>{{ item.qty }}</td>
                                <td>{{ formatPrice(item.price) }}</td>
                                <td>{{ item.discount }}%</td>
                                <td>{{ formatPrice(item.qty*item.price) }}</td>
                                <td>{{ formatPrice((item.qty * item.price) - ((item.discount/100)*(item.qty * item.price))) }}</td>
                            </tr>                                
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Total</th>
                                <th><span v-html="nairaSign"></span>{{ sale.totalPrice }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>


            <div class="card" v-if="histories && histories.length>0">
                <div class="text-center p-2">
                    <h6>Debt Payment History</h6>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <tr>
                            <th>Amount Paid (<span v-html="nairaSign"></span>)</th>
                            <th>Date Paid</th>
                            <th>Processed By</th>
                            <th>Processing Outlet</th>
                        </tr>

                        <tr v-for="history in histories" :key="history.id">
                            <td>{{ formatPrice(history.amount_paid) }}</td>
                            <td>{{ history.purchase_date | myDate }}</td>
                            <td>{{ history.processed_by }}</td>
                            <td>{{ history.store_id }}</td>
                        </tr>

                        <tr>
                            <th><b>{{ formatPrice(total_paid) }}</b></th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
export default {
    created() {
        this.viewPurchase();
        this.getProduct();
        this.getUser();
    },

    data() {
        return {
            is_busy: false,
            nairaSign: "&#x20A6;",
            editMode: false,
            model: {},
            sale: "",
            items: "",
            histories: "",  
            user: "",
            sale_id: "",
            total_purchase: "",
            total_paid: "",
            errors: []
        };
    },

    methods: {
        getUser() {
            axios.get("/api/user")
            .then(({ data }) => {
                this.user = data.user;
            });
        },

        viewPurchase() {
            if (this.is_busy) return;
            this.is_busy = true;

            this.sale_id = this.$route.params.trans_id;

            axios.get("/api/cart/getorder/" + this.sale_id)

            .then(( { data }) => {
                console.log(data)
                if(data.error){
                    Swal.fire(
                        "Failed!",
                        'This transaction does not exist',
                        "warning"
                    );
                    this.$router.push({ path: '/debtors'});
                }            

                this.sale = data.sale
                this.customer = data.customer;
                this.items = data.items;
                this.store = data.store;

                this.histories = data.histories;
                this.total_purchase = data.total_purchase;
                this.total_paid = data.total_paid;
            })
            .finally(() => {
                this.is_busy = false;
            });
        },

        getProduct() {
            axios.get("/api/inventory")
            .then(({ data }) => {
                this.products = data.data;
            });
        },

        formatPrice(value) {
            let val = (value/1).toFixed(2).replace(',', '.')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        },
    }
};
</script>
