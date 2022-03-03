<template>
    <div>
        <b-container>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span class="text-center">All Reports </span>
                    </div>
                </div>
                <div class="card-body">
                    <b-row>
                        <b-col cols="12" md="6">
                            <h4>Company Reports</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                    </tr>

                                    <tr>
                                        <td><router-link to="/admin/customers">List of Members</router-link></td>
                                        <td>This lists Member details. </td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/sale/orders">Sale Transactions</router-link></td>
                                        <td>This lists Member successful transaction. </td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/sale/quote">Company Quotes</router-link></td>
                                        <td>This lists Members' unattended invoice. </td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/admin/purchases">Item Purchases</router-link></td>
                                        <td>This lists the company's purchase reports. </td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/debtors">Member Debtors' Report</router-link></td>
                                        <td>This is the lists of Members owing the company. </td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/cleared">Undebted Member Report</router-link></td>
                                        <td>This is the lists of Members without indebtedness. </td> 
                                    </tr>

                                </table>
                            </div>
                        </b-col>

                        <!--<b-col cols="12" md="6">
                            <h4>Financial Statement Reports</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                    </tr>

                                    <tr>
                                        <td><router-link to="/account/journal">Journal</router-link></td>
                                        <td>The Transaction records perform by the company </td>
                                    </tr> 
                                    <tr>
                                        <td><router-link to="/account/trial-balance">Trial Balance</router-link></td>
                                        <td>The Trial Balance shows account balances as well as the Net Profit/Loss </td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/account/profit-and-loss">A statement of profit or loss </router-link></td>
                                        <td>A breakdown of income and expenditure and company profit/loss with comparatives.</td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/account/balance-sheet">A Statement of financial position</router-link></td>
                                        <td>Presentation of Financial Statements also known as the balance sheet</td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/account/ledger">Ledger Account</router-link></td>
                                        <td>Presentation of the company transactions</td> 
                                    </tr>

                                    <tr>
                                        <td><router-link to="/company/opening-balance">Operating Balance</router-link></td>
                                        <td>This presents the funding report of the working capital</td> 
                                    </tr>
                                </table>
                            </div>
                        </b-col>-->
                    </b-row>
                </div>
            </div>
        </b-container>
    </div>
</template>

<script>
    export default {
        
    }
</script>
