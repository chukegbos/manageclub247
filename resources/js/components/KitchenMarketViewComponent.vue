<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">    
            <div class="row mb-2 p-2">
                <div class="col-md-8">
                    <h2><strong>Kitchen Purchase Details ({{purchase_id}})</strong></h2>
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
                            <td>{{ purchase.item }}</td>
                            <td>{{ purchase.quantity }}</td>
                            <td>{{ formatPrice(purchase.amount) }}</td>
                        </tr>

                        <tr>
                            <th></th>
                            <th><b>Total</b></th>
                            <th><b>{{ formatPrice(thePurchase.amount ) }}</b></th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><b>Date</b></th>
                            <th><b>{{ thePurchase.purchase_date | myDate }}</b></th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><b>By</b></th>
                            <th><b>{{ thePurchase.user_id }}</b></th>
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

            this.purchase_id = this.$route.params.market_id;
            console.log(this.purchase_id)
            axios.get("/api/markets/" + this.$route.params.market_id)
            
            .then(({ data }) => {
                console.log(data)
                if((data.length==0) || (data=='error')){
                    Swal.fire(
                        "Failed!",
                        'This purchase transaction does not exist',
                        "warning"
                    );
                    this.$router.push({ path: '/kitchen/market'});
                }
               
                this.groupPurchases = data.lists;
                this.thePurchase = data.marketlist;
                //this.total_purchase = data.total_purchase;
                //this.total_paid = data.total_paid;
            })
            .finally(() => {
                this.is_busy = false;
            });
        },

        formatPrice(value) {
            let val = (value/1).toFixed(2).replace(',', '.')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        },
    }
};
</script>
