<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">    
            <div class="row mb-2 p-2">
                <div class="col-md-8">
                    <h2><strong>Purchase Details ({{purchase_id}})</strong></h2>
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <tr>
                            <th>Products</th>
                            <th>Quantity</th>
                            <th>Price (<span v-html="nairaSign"></span>)</th>
                        </tr>

                        <tr v-for="purchase in groupPurchases" :key="purchase.id">
                            <td>{{ purchase.product_name }}</td>
                            <td>{{ purchase.qty }}</td>
                            <td>{{ formatPrice(purchase.total_price) }}</td>
                        </tr>

                        <tr>
                            <th></th>
                            <th><b>Total</b></th>
                            <th><b>{{ formatPrice(thePurchase.total_price) }}</b></th>
                        </tr>
                        
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
            purchases: {},
            groupPurchases: "",
            histories: "",
            singleSupplier: "",
            singleProduct: "",
            user: "",
            purchase_id: "",
            total_purchase: "",
            thePurchase: '',
            total_paid: "",
            products: {},
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

            this.purchase_id = this.$route.params.id;
            axios.get("/api/purchases/" + this.$route.params.id)
            
            .then(({ data }) => {
                console.log(data)
                if((data.length==0) || (data=='error')){
                    Swal.fire(
                        "Failed!",
                        'This purchase transaction does not exist',
                        "warning"
                    );
                    this.$router.push({ path: '/admin/purchases'});
                }
               
                this.groupPurchases = data.purchases;
                this.thePurchase = data.purchase;
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
