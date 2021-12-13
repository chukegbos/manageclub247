<template>
    <div class="container-fluid">
        <div class="row mb-2 p-1">
            <div class="col-md-12">
                <h2><strong>Customers' Reports</strong></h2>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <b-row>
                    <b-col cols="12" md="4">
                        <ul>
                            <li><router-link to="/customer/statement" class="report_link">Customer's Statement</router-link></li>
                            <li><router-link to="/customer/sales" class="report_link">Customer's Sales</router-link></li>
                            <li><router-link to="/sale/orders" class="report_link">Customer's Transactions</router-link></li>

                            <li><router-link to="/sale/quote" class="report_link">Customer's Quotes</router-link></li>

                            <li><router-link to="/sale/invoice" class="report_link">Customer's Invoice</router-link></li>
                        </ul>
                    </b-col>
                </b-row>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        
    }
</script>
<style>
    .report_link {
        color:blue !important;
    }
</style>