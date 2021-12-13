<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="mb-2 ml-2 row">
                <b-button variant="outline-info" size="sm" @click=onPrint>Print</b-button>
            </div>

            <div class="card" id="printMe">
                <div class="card-header">
                    <div class="card-title">
                        Debtor View
                    </div>
                </div>
                <div v-if="report_items.data.length" style="text-align:center">
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <tr>
                                <th>Trans ID</th>
                                <th>Customer</th>
                                <th>Amount Owe</th>
                                <th>Amount Paid</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>

                            <tr
                                v-for="order in report_items.data"
                                :key="order.id"
                            >
                                <td>
                                    {{ order.sale_id }}
                                </td>
                                <td>{{ order.customer }}</td>
                                <td>{{ order.amount }}</td>
                                <td>
                                    <span v-if="order.amount_paid">{{ order.amount_paid }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td>
                                    <span v-if="order.status == 0">
                                        Not Paid
                                    </span>
                                    <span v-else>
                                        Paid 
                                    </span>
                                </td>
                                 <td>
                                    <a href="#" @click=onPay(order) v-if="order.status == 0">Pay |</a>
                                   
                                   <a href="#" @click=viewItems(order)>View</a>
                                </td>
                                
                                
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pagination :data="report_items" @pagination-change-page="getResults">
                        </pagination>
                    </div>
                </div>
            </div>

            <div 
                class="modal fade" 
                id="getItem" 
                tabindex="-1" 
                role="dialog" 
                aria-labelledby="addNewFridgeLabel" 
                aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewFridgeLabel">Items</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">                         
                            <div class="table-responsive">
                                <table class="table table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Unit Price (Naira)</th>
                                            <th>Price (Naira)</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-for="item in items" :key="item.id">
                                            <td>{{ item.item.product_name}}</td>
                                            <td>
                                                {{ item.quantity }}
                                            </td>
                                            <td>{{ item.item.price}}</td>
                                            <td>{{ item.price }}</td>
                                        </tr>                                
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th>
                                                Total Price <br>
                                                Amount Paid
                                            </th>
                                            <th> 
                                                N{{ totalPrice }} <br>
                                                N{{ amount_paid }}
                                            </th>
                                        </tr>
                                    </tfoot>

                                </table>  
                            </div>                           
                        </div>
                      
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addpay" tabindex="-1" role="dialog" aria-labelledby="addNewstoreLabel" aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Payment</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="editMode">
                            <div class="modal-body">
                                <div class="form-group">
                                    <input v-model="form.amount" type="number" class="form-control"/>
                                </div>
                                <div class="form-group">
                                    <label>Debit</label>
                                    <v-select label="account" :options="accounts" @input="fundSelected" ></v-select>
                                </div>

                                <div class="form-group">
                                    <label>Credit</label>
                                    <v-select label="account" :options="accounts" @input="fundSec" ></v-select>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">
                                    Pay
                                </button>
                            </div>
                        </form>
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
            this.viewDebt();
            this.getUser();
        },

        data() {
            return {
                is_busy: false,
                report_items: null,
                summary: null,
                report_items: {
                    data: '',
                },
               
                items: {},
                totalPrice: '',
                amount_paid: '',
                form: {
                    amount: "",
                    sale_id: "",
                    account_one: "",
                    account_two: "",
                },
                accounts: [],
            };
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                    this.accounts = data.accounts;
                });
            },

            onPrint() {
                this.$htmlToPaper('printMe');
            },

            fundSelected(value) {
                this.wallet.account_one = value.id;
            },

            fundSec(value) {
                this.wallet.account_two = value.id;
            },
        
            getResults(page = 1) {
                axios.get('/api/store/debtors' + this.$route.params.sale_id + '?page=' + page)
                .then(response => {
                    this.report_items = response.data.report_data;
                    this.summary = response.data.summary;
                });
            },

            viewDebt() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/store/debtors/" + this.$route.params.sale_id)
                .then((response) => {
                    this.report_items = response.data.report_data;
                    this.summary = response.data.summary;
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            viewItems(order) {
                console.log(order)
                this.$router.push({ path: "/orderview/" + order.sale_id });
               
            },

            onPay(order) {
                this.form.sale_id = order.sale_id;
                this.form.amount = order.amount;
                $('#addpay').modal('show');
            },

            editMode() {
                axios.post("/api/store/debt", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Payment Added.",
                        "success"
                    );
                    $("#addpay").modal("hide");
                    this.viewDebt();
                    this.getUser();
                })

                .catch(error => {
                   
                });
            },
        }
    };
</script>
