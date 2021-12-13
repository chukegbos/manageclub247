<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mb-2">
            <div class="row mt-2">
                <div class="col-md-1"></div>
                <div class="col-md-10">
                    <button @click=onPrint class="btn btn-success btn-sm mb-2">Print</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-1"></div>


                <div class="col-md-10" id="printMe">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-12 text-center p-3">
                                    <h5><strong>{{ site.sitename }}</strong></h5>
                                    <small>
                                        {{ site.address }}<br>
                                        {{ site.email }} | {{ site.phone }}
                                    <hr></small>

                                </div>

                                <div class="col-md-4 text-blue">
                                    <h6><strong>RECEIPT</strong></h6>
                                </div>
                                <div class="col-md-4">
                                    Date: <b><u>{{ sale.created_at | myDate }}</u></b>
                                </div>

                                <div class="col-md-4">
                                    Receipt No: <b><u>{{ sale.sale_id }}</u></b>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    Received From: <b><u>{{ customer.name }}</u></b><br>
                                </div>
                                <div class="col-md-4 border">
                                    Amount: <b><u><span v-html="nairaSign"></span>{{ formatPrice(mainprice) }}</u></b><br>
                                </div> 
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-9">
                                    Amount in word: <b><u>{{ word_price |capitalize }} Naira</u></b><br>
                                    <b>________________________________________________________________________________________________________</b>
                                </div>  
                            </div>
                               
                            <div class="row mb-3"> 
                                <div class="col-md-12">
                                    For payment of : <b><u>Goods with reference id of #{{ sale.sale_id }}</u></b><b>_______________________________________________________________________________________________</b><br>
                                    <b>_____________________________________________________________________________________________________________________________________________________________________</b>
                                </div>                      
                            </div>
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
            sale_id: '',
            sale: '',
            sec_id: '',
            ready: '',
            items: {},
            totalPrice: '',
            site: "",
            user: "",
            vat: "",
            mainprice: "",
            customer: '',
            word_price: '',

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

            this.sale_id = this.$route.params.sale_id;

            axios.get("/api/cart/getorder/" + this.sale_id)

            .then((response) => {
                this.sale = response.data.sale
                this.word_price = response.data.word_price;
                this.form = response.data.sale;
                this.customer = response.data.customer;
                this.form.payment_method = 'Wallet';
                this.items = response.data.items;
                this.vat = (7.5/100) * this.sale.totalPrice;
                this.mainprice = Number(this.vat) + Number(this.sale.totalPrice);
                console.log(this.form.payment_method)
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

        newModal() {
            $("#addNewUser").modal("show");
        },

        CreditModal(customer) {
            this.credit.payer_id = customer.id;
            this.credit.name = customer.name;
            this.credit.credit_unit = customer.credit_unit;
            $("#addNewUser").modal("hide");
            $("#addNewCredit").modal("show");
        },

        WalletModal(customer) {
            this.wallet.payer_id = customer.id;
            this.wallet.name = customer.name;
            $("#addNewUser").modal("hide");
            $("#addNewWallet").modal("show");
        },

        onChange($event){
            let value = event.target.value;
            console.log(value)
            if (value == "Credit Unit")
            {
                this.sec_id = 1;
                if(this.customer.credit_unit >= this.form.totalPrice){
                    this.ready = 1;
                }
                else{
                    this.ready = '';
                }
            }
            else
            {
                this.sec_id = '';
                if(this.customer.wallet_balance >= this.form.totalPrice){
                    this.ready = 1;
                }
                else{
                    this.ready = '';
                }
            }
        },

        updateDeal() {
            console.log(this.form)
            axios.post("/api/cart/closedeal", this.form)

            .then((data) => {
                $("#addNewUser").modal("hide");
                if(data.data.error){
                    Swal.fire(
                        "Failed!",
                        data.data.error,
                        "warning"
                    );
                }
                else{
                    Swal.fire("Updated!", "Deal Closed Successfully.", "success");
                }

                this.viewOrder();
                this.loadSite();
            })

            .catch();
        },

        updateCredit() {
            console.log(this.credit);
            axios.post("/api/user/credituser", this.credit)

            .then((data) => {
                $("#addNewCredit").modal("hide");

                Swal.fire("Updated!", "Credit Topped Up Successfully.", "success");

                this.form.payment_method = 'Credit Unit';
                $("#addNewUser").modal("show");
            })

            .catch();
        },
        approve(sale){
            axios.post("/api/cart/approvequote", this.form)

            .then((data) => {
                Swal.fire("Updated!", "Quote approved as invoice.", "success");
                this.viewOrder();
                this.loadSite();
            })

            .catch();
        },

        updateWallet() {
            console.log(this.wallet);
            axios.post("/api/user/walletuser", this.wallet)

            .then((data) => {
                $("#addNewWallet").modal("hide");

                Swal.fire("Updated!", "Customer Credited Successfully.", "success");
                this.viewOrder();
                this.loadSite();
                this.getUser();
                this.form.payment_method = 'Wallet';
                $("#addNewUser").modal("show");
            })

            .catch();
        },

        editInvoice() {
            this.$router.push({ path: "/sale/shopping-cart/" + this.sale.sale_id });
        },

        formatPrice(value) {
            let val = (value/1).toFixed(2).replace(',', '.')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        }
    }
};
</script>
